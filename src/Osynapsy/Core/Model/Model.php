<?php
namespace Osynapsy\Core\Model;

use Osynapsy\Core\Lib\Dictionary;
use Osynapsy\Core\Model\ModelField;
use Osynapsy\Core\Helper\ImageProcessor;

abstract class Model
{
    private $repo;
    protected $controller = null;
    protected $sequence = null;
    protected $table = null;
    protected $db = null;   
    protected $values = array();    
    protected $softdelete;

    public function __construct($controller)
    {
        $this->controller = $controller;
        $this->db = $this->controller->getDb();
        $this->repo = new Dictionary();
        $this->repo->set('actions.after-insert', $this->controller->request->get('page.url'))
                   ->set('actions.after-update', 'back')
                   ->set('actions.after-delete', 'back')
                   ->set('fields',array());
        $this->init();
        if (empty($this->table)) {
            throw new \Exception('Model table is empty');
        }
        $this->repo->set('table', $this->table);
    }
    
    public function get($key)
    {
        return $this->repo->get($key);
    }
    
    public function set($key, $value, $append=false)
    {
        $this->repo->get($key, $value, $append);
        return $this;
    }
    
    public function setSequence($seq)
    {
        $this->sequence = $seq;
    }
    
    public function delete()
    {
        $this->beforeDelete();
        if ($this->controller->response->error()){ 
            return; 
        }
        $where = array();
        foreach ($this->repo->get('fields') as $i => $field) {
            if ($field->isPkey()) { 
                $where[$field->name] =  $field->value;
            }
        }
        if (empty($where)) { 
            return; 
        }
        if (!empty($this->softdelete)) {
            $this->db->update(
                $this->repo->get('table'),
                $this->softdelete,
                $where
            );
        } else {
            $this->db->delete(
                $this->repo->get('table'),
                $where
            );
        }
        $this->afterDelete();
        $this->controller->response->go($this->repo->get('actions.after-delete'));
    }

    public function insert($values, $where=null)
    {
        $this->beforeInsert();        
        if ($this->controller->response->error()) {
            return;
        }        
        $lastId = null;
         
        if ($this->sequence && is_array($where) && count($where) == 1) {
            $lastId = $values[$where[0]] = $this->db->execUnique("SELECT {$this->sequence}.nextval FROM DUAL",'NUM');
            $this->db->insert($this->repo->get('table'), $values);
        } else {
            $lastId = $this->db->insert($this->repo->get('table'), $values);
        }
        if (!empty($lastId)) {
            if ($pkField = $this->repo->get('pkField')) {
                $pkField->setValue($lastId);
            }
        }
        $this->afterInsert($lastId);
        switch ($this->repo->get('actions.after-insert')) {
            case 'back':
            case 'refresh':
                $this->controller->response->go($this->repo->get('actions.after-insert'));                
                break;
            default: 
                $this->controller->response->go($this->repo->get('actions.after-insert').$lastId);                
                break;
        }
    }

    public function update($values, $where)
    {
        $this->beforeUpdate();
        if ($this->controller->response->error()) {
            return;
        }
        $this->db->update($this->repo->get('table'), $values, $where);
        $this->afterUpdate();
        $this->controller->response->go($this->repo->get('actions.after-update'), false);        
    }    

    public function find()
    {
        $sqlField = $sqlWhere = $sqlParam = array();
        $fields = $this->repo->get('fields');
        
        $k=0;
        foreach ($fields as $field) {
            if ($field->isPkey()) {
                $sqlWhere[] = $field->name . ' = '.($this->db->getType() == 'oracle' ? ':'.$k : '?');
                $sqlParam[] = $field->value;
                $k++;
            } 
            $sqlField[] = $field->name;
        }
        
        if (empty($sqlWhere)){ 
            return; 
        }
        
        $sql  = " SELECT *".PHP_EOL;        
        $sql .= " FROM  ".$this->repo->get('table')." ".PHP_EOL;
        $sql .= " WHERE ".implode(' AND ',$sqlWhere);
        try {
            $rec = $this->db->execUnique($sql, $sqlParam, 'ASSOC');
            if (!empty($rec)) {
                $this->values = $rec;
            }
        } catch (\Exception $e) {
            $this->controller->response->addContent('MODEL FIND ERROR: <pre>'.$e->getMessage()."\n".$sql.'</pre>');
        }
        $this->assocData();
    }
    
    protected function addError($errorId, $field)
    {
        $error = str_replace(
            array('<fieldname>','<value>'),
            array('<!--'.$field->html.'-->',$field->value),            
            $this->errorMessages[$errorId]
        );
        $this->controller->response->error($field->html, $error);
    }
    
    public function assocData()
    {
        if (!is_array($this->values)) {
            return;
        }
        foreach ($this->repo->get('fields') as $f) {
            if (!array_key_exists($f->html, $_REQUEST) && array_key_exists($f->name, $this->values)) {
                $_REQUEST[ $f->html ] = $this->values[ $f->name ];
            }
        }
    }
    
    public function getValue($key)
    {
        return array_key_exists($key, $this->values) ? $this->values[$key] : null;
    }
    
    public function map($htmlField, $dbField = null, $value = null, $type = 'string')
    {
        $modelField = new ModelField($this, $dbField, $htmlField, $type);
        $modelField->setValue($_REQUEST[$modelField->html],$value);
        $this->repo->set('fields.'.$modelField->html, $modelField);
        return $modelField;
    }

    public function save()
    {
        //Recall before exec method with arbirtary code
        $this->beforeExec();
        
        //Init arrays
        $values = array();
        $where = array();
        $keys = array();
        
        //skim the field list for check value and build $values, $where and $key list
        foreach ($this->repo->get('fields') as $f) {
            //Check if value respect rule
            $val = $this->sanitizeFieldValue($f);
            //If field isn't in readonly mode assign values to values list for store it in db
            if (!$f->readonly) {
                $values[$f->name] = $val; 
            }
            //If field isn't primary key skip key assignment
            if (!$f->isPkey()) {
                continue;
            }
            //Add field to keys list
            $keys[] = $f->name;
            //If field has value assign field to where condition
            if (!empty($val)) {
                $where[$f->name] = $val;
            }
        }
        //If occurred some error stop db updating
        if ($this->controller->response->error()) { 
            return; 
        }
        //If where list is empty execute db insert else execute a db update
        if (empty($where)) {
            $this->insert($values, $keys);
        } else {
            $this->update($values, $where);
        }
        //Recall after exec method with arbirtary code
        $this->afterExec();
    }
    
    private function sanitizeFieldValue(&$f)
    {
        $val = $f->value;
        if (!$f->isNullable() && $val !== '0' && empty($val)) {
            $this->controller->response->error($f->html,'Il campo <!--'.$f->html.'--> è obbligatorio.');
        }
        if ($f->isUnique() && $val) {
            $nOccurence = $this->db->execUnique(
                "SELECT COUNT(*) FROM {$this->table} WHERE {$f->name} = ?",
                array($val)
            );
            if (!empty($nOccurence)) {
                $this->addError('unique', $f);
            }
        }
        //Controllo la lunghezza massima della stringa. Se impostata.
        if ($f->maxlength && (strlen($val) > $f->maxlength)) {
            $this->controller->response->error(
                $f->html,
                'Il campo <!--'.$f->html.'--> accetta massimo '.$f->maxlength.' caratteri.'
            );
        } elseif ($f->minlength && (strlen($val) < $f->minlength)) {
            $this->controller->response->error(
                $f->html,
                'Il campo <!--'.$f->html.'--> accetta minimo '.$f->minlength.' caratteri.'
            );
        } elseif ($f->fixlength && !in_array(strlen($val),$f->fixlength)) {
            $this->controller->response->error(
                $f->html,
                'Il campo <!--'.$f->html.'--> accetta solo valori con lunghezza pari a '.implode(' o ',$f->fixlength).' caratteri'
            );
        }
        switch ($f->type) {
            case 'float':
            case 'money':
            case 'numeric':
            case 'number':
                if (filter_var($val, \FILTER_VALIDATE_FLOAT) === false) {
                    $this->controller->response->error($f->html,'Il campo '.$f->html.' non è numerico.');
                }
                break;
            case 'integer':
            case 'int':
                if (filter_var($val, \FILTER_VALIDATE_INT) === false) {
                    $this->controller->response->error($f->html,'Il campo '.$f->html.' non è numerico.');
                }
                break;
            case 'file':
            case 'image':
                if (is_array($_FILES) && array_key_exists($f->html, $_FILES)) {
                    $val = ImageProcessor::upload($f->html);
                } else {
                    //For prevent overwrite of db value
                    $f->readonly = true;
                }
                break;
        }
        return $val;
    }
    
    public function softDelete($field, $value)
    {
        $this->softdelete = array($field => $value);
    }
    
    protected function beforeExec()
    {
    }
    
    protected function beforeInsert()
    {
    }
    
    protected function beforeUpdate()
    {
    }
    
    protected function beforeDelete()
    {
    }
    
    protected function afterExec()
    {
    }
    
    protected function afterInsert()
    {
    }
    
    protected function afterUpdate()
    {
    }
    
    protected function afterDelete()
    {
    }
    
    abstract protected function init();        
}
