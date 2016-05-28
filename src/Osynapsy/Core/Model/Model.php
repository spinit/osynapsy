<?php
namespace Osynapsy\Core\Model;

use Osynapsy\Core\Kernel;
use Osynapsy\Core\Lib\Dictionary;
use Osynapsy\Core\Model\ModelField;
use Osynapsy\Core\Util\ImageProcessor;

abstract class Model
{
    private $repo;
    protected $controller = null;
    protected $table = null;
    protected $db = null;
    protected $values = array();
    protected $debug;
    protected $softdelete;

    public function __construct($controller, $db = null)
    {
        $this->controller = $controller;
        $this->db = empty($db) ? Kernel::$dba : $db;
        $this->repo = new Dictionary();
        $this->repo->set('actions.after-insert', Kernel::get('page.url'))
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

    private function formatCommand($action,$parameter=null)
    {
        return array('command' => array(array($action,$parameter)));
    }
    
    public function insert($values)
    {
        $this->beforeInsert();
        if ($this->controller->response->error()) {
            return;
        }
        if ($this->debug) {
            mail($this->debug,'Debug mail',print_r($this->repo->get('table'),true).' '.print_r($values,true));
        }
        $lastId = $_REQUEST['pk'] = $this->db->insert($this->repo->get('table'), $values);
        if (!empty($_REQUEST['pk'])) {
            if ($pkField = $this->repo->get('pkField')) {
                $pkField->setValue($_REQUEST['pk']);
            }
        }
        $this->afterInsert($lastId);
        switch ($this->repo->get('actions.after-insert')) {
            case 'back':
            case 'refresh':
                $this->controller->response->go($this->repo->get('actions.after-insert'));                
                break;
            default: 
                $this->controller->response->go($this->repo->get('actions.after-insert').$_REQUEST['pk']);                
                break;
        }
    }

    public function update($values, $where)
    {
        $this->beforeUpdate();
        if ($this->controller->response->error()) {
            return;
        }
        if ($this->debug) {
            mail($this->debug,'Debug mail',print_r($this->repo->get('table'),true).' '.print_r($values,true).' '.print_r($where,true));
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
        foreach ($fields as $i => $field) {
            if ($field->isPkey()) {
                $sqlWhere[] = $field->name . ' = :'.$k;
                $sqlParam[] = $field->value;
                $k++;
            } 
            $sqlField[] = $field->name;
        }
        
        if (empty($sqlWhere)){ 
            return; 
        }
        
        $sql  = " SELECT *".PHP_EOL;
        //$sql .= implode(',',$sqlField).PHP_EOL;
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
    
    public function assocData()
    {
        if (is_array($this->values)) {
            foreach ($this->repo->get('fields') as $k => $f) {
                if (!array_key_exists($f->html, $_REQUEST) && array_key_exists($f->name, $this->values)) {
                    if ($f->html == '_fk') { 
                        $_REQUEST[$f->html] = $this->values[ $f->name ]; 
                    }
                    $_REQUEST[ $f->html ] = $this->values[ $f->name ];
                }
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
        $this->beforeExec();
        $values = array();
        $where = array();
        
        foreach ($this->repo->get('fields') as $k => $f) {
            $val = $f->value;
            if (in_array($f->type,array('file','image')) && empty($val)) {
                //print_r($f);
                //exit;
                //continue;
            }
            if (!$f->isNullable() && $val !== '0' && empty($val)) {
                $this->controller->response->error($f->html,'Il campo <!--'.$f->html.'--> è obbligatorio.');
            }
            switch ($f->type) {
                case 'float':
                case 'money':
                case 'numeric':
                case 'number':
                    if (filter_var($val, FILTER_VALIDATE_FLOAT) === false) {
                        $this->controller->response->error($f->html,'Il campo '.$f->html.' non è numerico.');
                    }
                    break;
                case 'integer':
                case 'int':
                    if (filter_var($val, FILTER_VALIDATE_INT) === false) {
                        $this->controller->response->error($f->html,'Il campo '.$f->html.' non è numerico.');
                    }
                    break;
            }
            if ($f->isPkey() && !empty($val)) {
                $where[$f->name] = $val;
            }
            if (!$f->readonly) {
                $values[$f->name] = $val; 
            }

            if (!is_array($_FILES) || !array_key_exists($f->html, $_FILES)) {
                continue;
            }
            $values[$f->name] = ImageProcessor::upload($f->html);
            //Make thumbnail
            if ($th = $f->thumbnail) {
                if (!is_array($th) && count($th) === count($th, COUNT_RECURSIVE)) {
                    $th = array($th);
                }
                foreach ($th as $dim) {
                    $thumbnailName = ImageProcessor::thumbnail(
                        $_SERVER['DOCUMENT_ROOT'].$values[$f->name],
                        $dim
                    );
                    if (!empty($dim[2])) {
                        $values[$dim[2]] = $thumbnailName;
                    }
                }
            }
        }
        if ($this->controller->response->error()) { 
            return; 
        }        
        if (empty($where)) {
            $this->insert($values);
        } else {
            $this->update($values,$where);
        }
        $this->afterExec();
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
