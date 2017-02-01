<?php
namespace Osynapsy\Bcl\Component;

use Osynapsy\Ocl\Component\ComboBox;
/**
 * Description of Multiselect
 *
 * @author p.celeste@osynapsy.org
 */
class Multiselect extends ComboBox
{
    public function __construct($name)
    {
        parent::__construct($name);
        $this->requireCss('/__assets/osynapsy/Lib/boostrap-multiselect-2.0/bootstrap-multiselect.css');
        $this->requireJs('/__assets/osynapsy/Lib/boostrap-multiselect-2.0/bootstrap-multiselect.js');
        $this->requireJs('/__assets/osynapsy/Bcl/Multiselect/script.js');        
        $this->setClass('osy-multiselect')->att('multiple','multiple');
        $this->par('option-select-disable',true);
    }
}
