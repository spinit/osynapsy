<?php
namespace Osynapsy\Bcl\Component;

use Osynapsy\Ocl\Component\CheckList as OclCheckList;

class CheckList extends OclCheckList
{
    public function __construct($name)
    {
        parent::__construct($name);        
        $this->requireCss('/vendor/osynapsy/Bcl/CheckList/style.css');
    }
}
