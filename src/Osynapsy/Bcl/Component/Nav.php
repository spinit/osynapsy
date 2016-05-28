<?php
namespace Osynapsy\Bcl\Component;

use Osynapsy\Core\Lib\Tag as Tag;
use Osynapsy\Ocl\Component\Component as Component;

class Nav extends Component
{
    public function __construct($id)
    {
        parent::__construct('div',$id.'_tab');
        $this->add(new HiddenBox($id));
    }
}
