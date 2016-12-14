<?php
namespace Osynapsy\Bcl\Component;

use Osynapsy\Core\Lib\Tag;
use Osynapsy\Ocl\Component\ListUnordered;


class SortableList extends ListUnordered
{
    private $label;
    private $labelColor;
    private $classType = 'sortable-list-destination';
    private $ListConnected;
    
    public function __construct($name)
    {
        parent::__construct($name);
        
        $this->requireCss('/__OsynapsyAsset/Bcl/SortableList/style.css');
        $this->requireJs('/__OsynapsyAsset/Bcl/SortableList/jquery.sortable.js');
        $this->requireJs('/__OsynapsyAsset/Bcl/SortableList/script.js');        
        $this->att('class','sortable-list');
    }
    
    public function __build_extra__()
    {
        $this->att('class', $this->classType, true);
        foreach ($this->data as $rec) {
            $li = $this->add(new Tag('li'))
                       ->att('data-source',$this->id)
                       ->att('data-value',$rec[0]);
            if ($this->label) {
                $li->add('<span class="label '.$this->labelColor.'">'.$this->label.'</span> ');
            }
            $li->add($rec[1]);
            $li->add('<span class="sortable-list-item-plus glyphicon glyphicon-plus"></span>');
            $li->add('<span class="sortable-list-item-minus glyphicon glyphicon-minus"></span>');
        }
    }
    
    public function connectTo(SortableList $list)
    {
        $this->ListConnected = $list;
        $this->att('data-connected',$this->ListConnected->id);
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
