<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Osynapsy\Bcl\Component;

use Osynapsy\Ocl\Component\ComboBox;
/**
 * Description of Multiselect
 *
 * @author Peter
 */
class Multiselect extends ComboBox
{
    public function __construct($name)
    {
        parent::__construct($name);
        $this->requireJs('/__OsynapsyAsset/Bcl/Multiselect/bootstrap-multiselect.js');
        $this->requireJs('/__OsynapsyAsset/Bcl/Multiselect/script.js');
        $this->requireCss('/__OsynapsyAsset/Bcl/Multiselect/style.css');
        $this->setClass('osy-multiselect')->att('multiple','multiple');
        $this->par('option-select-disable',true);
    }
}
