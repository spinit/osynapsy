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
    public function __construct($name)
    {
        parent::__construct($name);
        $this->class = 'osy-select';
        $this->requireCss('/vendor/osynapsy/Bcl/Select/bootstrap-select.css');
        $this->requireJs('/vendor/osynapsy/Bcl/Select/bootstrap-select.js');
        $this->requireJs('/vendor/osynapsy/Bcl/Select/script.js');
        $this->par('option-select-disable',true);
    }
    
    public function setMultiSelect()
    {
        $this->att('multiple','multiple');
        return $this;
    }
}
