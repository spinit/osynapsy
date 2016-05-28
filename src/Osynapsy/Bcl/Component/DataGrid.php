<?php
namespace Osynapsy\Bcl\Component;

use Osynapsy\Ocl\Component\Component;

class DataGrid extends Component
{
    public $data = array();
    
    public function __construct($name)
    {
        parent::__construct($name);
        
    }
    
    public function __build_extra__()
    {
        foreach ($data as $i => $rec) {
            $cols = array_keys($rec);
        }
    }
    
    public function setData($data)
    {
        $this->data = $data;
    }
}