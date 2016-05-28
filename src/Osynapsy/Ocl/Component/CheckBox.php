<?php
namespace Osynapsy\Ocl\Component;

class CheckBox extends Component
{
    private $hidden = null;
    private $checkbox = null;
    
    public function __construct($nam)
    {
        parent::__construct('span',$nam);
        $this->hidden = $this->add(new HiddenBox($nam));
        $this->checkbox = $this->add(new InputBox('checkbox','chk_'.$nam,'chk_'.$nam));
        $this->checkbox->att('class','osy-check')->att('value','1');
    }
    
    protected function __build_extra__()
    {
        if (array_key_exists($this->id,$_REQUEST) && !empty($_REQUEST[$this->id])) {
            $this->checkbox->att('checked','checked');
        }
    }
}
