<?php
namespace Osynapsy\Ocl\Component;

class TextArea extends Component
{
    public function __construct($name)
    {
        parent::__construct('textarea',$name);
        $this->name = $name;
    }
    
    public function __build_extra__()
    {
        $this->add($_REQUEST[$this->id]);
    }
}
