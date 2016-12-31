<?php
namespace Osynapsy\Ocl\Component;

use Osynapsy\Core\Lib\Tag;

class RadioBoolean extends Component
{
    private $radio0;
    private $radio1;
    private $defaultValue;
    
    public function __construct($name, $label = array('No','Si'))
    {
        parent::__construct('div');
        $this->singleton('kernel')->$controller->response->addContent('<link rel="stylesheet" href="/vendor/osynapsy/css/Bcl/RadioBoolean.css">','head');
        $this->att('class','form-group radio-boolean');
        $component = $this->add(new Tag('div'))->att('id',$name);        
        $component->add('<span>'.$label[1].'</span>');
        $this->radio1 = $component->add(new RadioBox($name));
        $this->radio1->value = '1';
        
        $component->add('<span>&nbsp;&nbsp;&nbsp;</span>');
        
        $component->add('<span>'.$label[0].'</span>');
        $this->radio0 = $component->add(new RadioBox($name));
        $this->radio0->value = '0';
    }
    
    public function __build_extra__()
    {
        $currentValue = null;
        if (array_key_exists($this->name,$_REQUEST)) {
            $currentValue = $_REQUEST[$this->name];
        } elseif (!is_null($this->defaultValue)) {
            $currentValue = $this->defaultValue;
        }
        if (is_null($currentValue)) {
            return;
        }
        if (empty($currentValue)) {
            $this->radio0->att('checked','checked');
        } else {
            $this->radio1->att('checked','checked');
        }
    }
    
    public function setDefaultValue($value)
    {
        $this->defaultValue = $value;
    }
}
