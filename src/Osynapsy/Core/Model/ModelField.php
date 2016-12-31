<?php
namespace Osynapsy\Core\Model;

use Osynapsy\Core\Base;

class ModelField extends Base
{
    private $repo = array(
        'value'       => null,
        'is_pk'       => false,
        'nullable'    => true, 
        'readonly'    => false,
        'rawvalue'    => null
    );
    private $model;
    public $type;
    
    public function __construct($model, $nameOnDb, $nameOnView, $type = 'string')
    {
        $this->model = $model;
        $this->name = $nameOnDb;
        $this->html = $nameOnView;
        $this->type = $type;
    }

    public function __get($key)
    {
        return array_key_exists($key,$this->repo) ? $this->repo[$key] : null;
    }

    public function __set($key, $value)
    {
        $this->repo[$key] = $value;
    }

    public function __toString()
    {
        return implode(',', $this->repo);
    }
    
    public function isPkey($b = null)
    {
        if (is_null($b)) {
            return $this->is_pk; 
        } 
        $this->is_pk = $b;
        $this->model->set('pkField', $this, true);
        
        if ($this->value) {
            $html = $this->html;
            if (empty($_REQUEST[$html])) { 
                $_REQUEST[$html] = $this->value; 
            }
            if (!$this->model->get('record.where.'.$this->name)) {
                $this->model->set('record.where.'.$this->name, $this->value);
            }
        }
        return $this;
    }

    public function isNullable($v=null)
    {
        if (is_null($v)) { 
            return $this->repo['nullable']; 
        }
        $this->repo['nullable'] = $v;
        return $this;
    }

    public function setValue($val, $def = null)
    {
        if ($val !== '0' && $val !== 0 && empty($val)) {
            $val = $def;
        }
        $this->value = $this->rawvalue = $val;
        if ($this->type == 'date' && !empty($val) && strpos($val, '/') !== false) {
            list($dd, $mm, $yy) = explode('/', $this->value );
            $this->value = "$yy-$mm-$dd";
        }
        if ($this->isPkey()) {
            $this->model->set('record.where.'.$this->name, array($this->name, $this->value));
        }
        return $this;
    }
}
