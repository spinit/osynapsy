<?php
namespace Osynapsy\Bcl\Component;

use Osynapsy\Core\Lib\Tag;
use Osynapsy\Ocl\Component\Component;
use Osynapsy\Ocl\Component\ListUnordered;
use Osynapsy\Ocl\Component\HiddenBox;

class Dropdown extends Component
{
    private $list;
    
    public function __construct($name, $label, $tag='div')
    {
        parent::__construct($tag);
        $this->add(new HiddenBox($name));
        $this->att('class','dropdown')
             ->add(new Button($name.'_btn'))
             ->att('type', 'button')
             ->att('class','dropdown-toggle',true)
             ->att('data-toggle','dropdown')
             ->att('aria-haspopup','true')
             ->att('aria-expanded','false')
             ->add($label.' <span class="caret"></span>');
        $this->list = $this->add(
            new Tag('ul')
        )->att('class','dropdown-menu')
         ->att('aria-labelledby',$name);

    }
    
    protected function __build_extra__()
    {
        foreach ($this->data as $rec) {
            $rec = array_values($rec);
            $this->list
                 ->add(new Tag('li'))
                 ->att('data-value',$rec[0])
                 ->add('<a href="#">'.$rec[1].'</a>');
        }
    }
    
    public function setData($data)
    {
        $this->data = $data;
    }
}
