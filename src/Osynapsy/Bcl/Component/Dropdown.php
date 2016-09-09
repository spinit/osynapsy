<?php
namespace Osynapsy\Bcl\Component;

use Osynapsy\Core\Lib\Tag;
use Osynapsy\Ocl\Component\Component;
use Osynapsy\Ocl\Component\ListUnordered;
use Osynapsy\Ocl\Component\HiddenBox;

class Dropdown extends Component
{
    private $list;
    
    public function __construct($name, $label)
    {
        parent::__construct('div');
        $this->add(new HiddenBox($name));
        $this->att('class','dropdown')
             ->add(new Button($name.'_btn'))
             ->att('class','dropdown-toggle',true)
             ->att('data-toggle','dropdown')
             ->add($label.' <span class="caret"></span>');
        $this->list = $this->add(new ListUnordered($name.'-list','ul'));
        $this->list->att('class','dropdown-menu')->att('aria-labelledby',$this->id);
    }
    
    public function setData($data)
    {
        $this->list->setData($data);
    }
}
