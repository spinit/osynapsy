<?php
namespace Osynapsy\Bcl\Component;

use Osynapsy\Core\Lib\Tag;
use Osynapsy\Ocl\Component\Component;
use Osynapsy\Ocl\Component\HiddenBox;

class Label extends Component
{
    protected $hiddenBox;
    
    public function __construct($id, $label, $type='info', $dim='3')
    {
        parent::__construct('h'.$dim, $id.'_label');
        $this->hiddenBox = $this->add(new HiddenBox($id));
        $this->add(new Tag('span'))
             ->att('class','label label-'.$type)
             ->add($label);
    }
    
    public function setValue($value)
    {
        $this->hiddenBox->att('value',$value);
    }
}
