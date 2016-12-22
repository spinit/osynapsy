<?php
namespace Osynapsy\Bcl\Component;

use Osynapsy\Ocl\Component\ComboBox2;

/**
 * Description of Select
 *
 * @author Peter
 */
class Select extends ComboBox2
{
    //put your code here
    public function __construct($name, $multiple=false)
    {        
        parent::__construct($name);
        $this->class = 'osy-select';
        $this->requireCss('/__OsynapsyAsset/Bcl/Select/bootstrap-select.css');
        $this->requireJs('/__OsynapsyAsset/Bcl/Select/bootstrap-select.js');
        $this->requireJs('/__OsynapsyAsset/Bcl/Select/script.js');
        //$this->par('option-select-disable',false);
        if ($multiple) {
            $this->setMultiSelect();
        }
    }
    
    public function setMultiSelect()
    {
        $this->att('multiple','multiple');
        if (strpos($this->name,'[') === false) {
            $this->name = $this->name.'[]';
        }
        return $this;
    }
}
