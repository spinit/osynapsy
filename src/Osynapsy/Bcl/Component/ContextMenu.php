<?php
namespace Osynapsy\Bcl\Component;

use Osynapsy\Core\Lib\Tag;
use Osynapsy\Ocl\Component\Component;
use Osynapsy\Ocl\Component\HiddenBox;
use Osynapsy\Core\Kernel;

class ContextMenu extends Component
{
    private $actions = array();
    private $ul;
    
    public function __construct($id, $link, $label, $class='')
    {
        Kernel::$controller->response->addContent('<link href="/vendor/osynapsy/css/Bcl/ContextMenu.css" rel="stylesheet">','head');
        Kernel::$controller->response->addContent('<script src="/vendor/osynapsy/js/Bcl/ContextMenu.js"></script>','head');
        
        parent::__construct('div', $id);
        $this->att('class', 'BclContextMenu dropdown clearfix');
        $this->ul = $this->add(new Tag('ul'))
                         ->att('class','dropdown-menu')
                         ->att('role','menu')
                         ->att('aria-labelledby','dropdownMenu')
                         ->att('style','display: block; position: static; margin-bottom: 5px;');
        
    }
    
    public function addAction($label, $action, $params='')
    {
        $this->ul
             ->add(new Tag('li'))
             ->add(new Tag('a'))
             ->att('href','javascript:void(0);')
             ->att('data-action',$action)
             ->att('data-action-param',$params)
             ->add($label);
    }
}