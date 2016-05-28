<?php
namespace Osynapsy\Bcl\Component;

use Osynapsy\Core\Lib\Tag;
use Osynapsy\Ocl\Component\Component;
use Osynapsy\Ocl\Component\ListUnordered;

class Dropdown extends Component
{
    private $list;
    
    public function __construct($name, $label)
    {
        parent::__construct('div',$name);
        $this->att('class','dropdown')
             ->add(new Button($name.'_btn'))
             ->att('class','dropdown-toggle',true)
             ->att(
                array(
                    'aria-haspopup' => 'true',
                    'aria-expanded' => 'true'
                )
            )->add($label.'<span class="caret"></span>');
        $this->list = $this->add(new ListUnordered($name.'-list','div'));
        $this->list->att('class','dropdown-menu');
    }
    
    public function setData($data)
    {
        $this->list->setData($data);
    }
}
