<?php
namespace Osynapsy\Bcl\Component;

use Osynapsy\Core\Lib\Tag as Tag;
use Osynapsy\Ocl\Component\Component as Component;

//Costruttore del pannello html
class PanelAccordion extends Component
{
    private $panels = array();
    
    public function __construct($id)
    {
        parent::__construct('div', $id);
        $this->att('class','panel-group')
             ->att('role','tablist');
    }
    
    public function __build_extra__()
    {
        foreach($this->panels as $panel) {
            $this->add($panel);
        }
    }
    
    public function addPanel($title)
    {
        $panelIdx = count($this->panels);
        $panelId = $this->id.$panelIdx;
        $panelTitle = '<a data-toggle="collapse" data-parent="#'.$this->id.'" href="#'.$panelId.'-body" class="'.(empty($panelIdx) ? 'collapsed' : '').'">'.$title.'</a>';
        $this->panels[] = new PanelNew($panelId, $panelTitle);
        $this->panels[$panelIdx]
             ->getBody()
             ->att('id', $panelId.'-body')
             ->att('class', 'collapse' .(empty($panelIdx) ? ' in' : ''), true);
        return $this->panels[$panelIdx];
    }
}
