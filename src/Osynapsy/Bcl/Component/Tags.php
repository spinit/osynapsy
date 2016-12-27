<?php
namespace Osynapsy\Bcl\Component;

use Osynapsy\Core\Lib\Tag;
use Osynapsy\Ocl\Component\Component;
use Osynapsy\Ocl\Component\HiddenBox;

class Tags extends Component
{
    private $labelClass;
    private $modal;
    private $dropdown;
    private $hidden;
    
    public function __construct($name, $class="label-info")
    {
        parent::__construct('div', $name);
        $this->hidden = $this->add(new HiddenBox($name));        
        $this->requireJs('/__asset/osynapsy/Bcl/Tags/script.js');
        $this->requireCss('/__asset/osynapsy/Bcl/Tags/style.css');
        $this->labelClass = $class;
    }
    
    public function __build_extra__()
    {
        $this->att('class','bclTags');
        $cont = $this->add(new Tag('span'))
                     ->att('class','bclTags-container');
        if (!empty($_REQUEST[$this->id])) {
            $list = explode(';',$_REQUEST[$this->id]);
            foreach($list as $item) {
                if (!$item) {
                    continue;
                }
                $cont->add('<span class="label '.$this->labelClass.'" data-parent="#'.$this->id.'">'.$item.' <span class="fa fa-close bclTags-delete"></span></span>');
            }
        }
        
        if (!empty($this->modal)) {
            $buttonAdd = $this->add(new Button('btn'.$this->id));
            $buttonAdd->att('class','btn-info btn-xs',true)
                      ->att('data-toggle','modal')
                      ->att('data-target','#modal'.$this->id)
                      ->add('<span class="fa fa-plus"></span>');
        }
        if (!empty($this->dropdown)) {
            $this->add($this->dropdown);
        }
        
    }
    
    public function addModal($title, $body, $buttonAdd)
    {
        $this->modal = $this->add(new Modal('modal'.$this->id, $title));
        $this->modal->addBody($body);
        $buttonCls = new Button('clsModal'.$this->id);
        $buttonCls->att('class','btn-default pull-left',true)
                  ->att('data-dismiss','modal')              
                  ->add('Annulla');
        $this->modal->addFooter($buttonCls);
        if (is_object($buttonAdd)) {
            $buttonAdd->att('class', 'bclTags-add', true)
                      ->att('data-parent', '#'.$this->id);
        }
        $this->modal->addFooter($buttonAdd);
    }
    
    public function addDropDown($label, $data)
    {
        $this->dropdown = new Dropdown($this->id.'_list', $label, 'span');       
        $this->dropdown->setData($data);
    }
}
