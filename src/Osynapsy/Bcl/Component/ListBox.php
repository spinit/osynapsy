<?php
namespace Osynapsy\Bcl\Component;

use Osynapsy\Ocl\Component\Component;
use Osynapsy\Ocl\Component\HiddenBox;
use Osynapsy\Core\Lib\Tag;
use Osynapsy\Core\Kernel;

class ListBox extends Component
{
    public $data = array();
    private $hdn;
    private $box;
    private $list;
    
    public function __construct($name)
    {
        $this->requireJs('/__OsynapsyAsset/Bcl/ListBox/script.js');
        $this->requireCss('/__OsynapsyAsset/Bcl/ListBox/style.css');
        parent::__construct('div',$name);
        $this->att('class','listbox');
        $this->hdn = $this->add(new HiddenBox($name));
        $this->box = $this->add(new Tag('div'))
                          ->att('class','listbox-box'); 
    }
    
    protected function __build_extra__()
    {
        $list = $this->add(new Tag('ul'))
                     ->att('class','listbox-list');
        foreach ($this->data as $rec) {
            $selected = '';
            if (array_key_exists($this->id, $_REQUEST) && ($rec[0] == $_REQUEST[$this->id])) {
                $this->box->set($rec[1]);
                $selected = ' selected';
            }
            $list->add(new Tag('li'))                
                 ->add(new Tag('div'))
                 ->att('value',$rec[0])
                 ->att('class','listbox-list-item'.$selected)
                 ->add($rec[1]);
        }
    }
    
    public function SetData($data)
    {
        $this->data = $data;
    }
}
