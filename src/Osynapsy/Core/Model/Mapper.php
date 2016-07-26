<?php
namespace Osynapsy\Core\Model;

class Mapper
{
    private $model;
    private $map = [];
    
    public function __construct(ModelNew $model)
    {
        $this->model = $model;
    }
    
    public function push($htmlField, $modelField)
    {
        
    }
}