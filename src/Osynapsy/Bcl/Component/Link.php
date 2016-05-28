<?php
namespace Osynapsy\Bcl\Component;

use Osynapsy\Core\Lib\Tag;
use Osynapsy\Ocl\Component\Component;
use Osynapsy\Ocl\Component\HiddenBox;

class Link extends Component
{
    public function __construct($id, $link, $label, $class='')
    {
        parent::__construct('a', $id.'_label');        
        $this->att('href', $link)
             ->add($label);
        if ($class) {
            $this->att('class', $class);
        }
    }
}
