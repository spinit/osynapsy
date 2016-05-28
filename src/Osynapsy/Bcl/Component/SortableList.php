<?php
namespace Osynapsy\Bcl\Component;

use Osynapsy\Core\Lib\Tag;
use Osynapsy\Ocl\Component\ListUnordered;


class SortableList extends ListUnordered
{
    private $label;
    private $labelColor;
    private $classType = 'sortable-list-destination';
    
    public function __construct($name)
    {
        parent::__construct($name);
        
        $this->requireCss('/vendor/osynapsy/Bcl/SortableList/style.css');
        $this->requireJs('/vendor/osynapsy/Bcl/SortableList/jquery.sortable.js');
        $this->requireJs('/vendor/osynapsy/Bcl/SortableList/script.js');        
        $this->att('class','sortable-list');
    }
    
    public function __build_extra__()
    {
        $this->att('class', $this->classType, true);
        foreach ($this->data as $rec) {
            $li = $this->add(new Tag('li'))->att('data-value',$rec[0]);
            if ($this->label) {
                $li->add('<span class="label '.$this->labelColor.'">'.$this->label.'</span> ');
            }
            $li->add($rec[1]);
        }
    }
    
    public function connectTo($elementId)
    {
        $this->att('data-connected',$elementId);
        return $this;
    }
    
    public function setLabel($label, $colorClass = 'label-default')
    {
        $this->label = $label;
        $this->labelColor = $colorClass;
        $this->classType = 'sortable-list-source';
        return $this;
    }
}
