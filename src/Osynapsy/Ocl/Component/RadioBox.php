<?php
namespace Osynapsy\Ocl\Component;

class RadioBox extends InputBox
{
    public function __construct($name)
    {
        parent::__construct('radio',$name);
    }
    
    public function __build_extra__()
    {
        if (array_key_exists($this->name,$_REQUEST) && $_REQUEST[$this->name] == $this->value){
            $this->att('checked','checked');
        }
    }
}
