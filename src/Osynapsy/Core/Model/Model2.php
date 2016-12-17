<?php
namespace Osynapsy\Core\Model;

abstract class Model2
{
    private $db;
    private $table = array();
    
    public function __construct($db)
    {
        $this->db = $db;
    }
    
    protected function addTable($table)
    {
        $this->table[$table] = [];
    }
    
    protected function addField($field, $type='text', $table=null)
    {
        end($this->table);
        $table = key($this->table);
        $this->table[$table][$field] = array($field, $type);
    }
    
    abstract protected function init();
}