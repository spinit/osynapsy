<?php
namespace Osynapsy\Bcl\Component;

use Osynapsy\Ocl\Component\Component;
use Osynapsy\Core\Lib\Tag;

class PanelNew extends Component
{
    private $sections = array(
        'head' => null,
        'body' => null,
        'foot' => null
    );
    
    private $currentRow = null;
    private $currentColumn = null;
    
    public function __construct($id, $title='', $class = ' panel-default', $tag = 'div')
    {
        parent::__construct($tag, $id);
        $this->att('class','panel'.$class);
        if (!empty($title)) {
            $this->sections['head'] = new Tag('div');
            $this->sections['head']->att('class','panel-heading')
                                   ->add('<h4 class="panel-title">'.$title.'</h4>');
        }
        $this->sections['body'] = new Tag('div');
        $this->sections['body']->att('class','panel-body');
    }
    
    protected function __build_extra__()
    {
        foreach ($this->sections as $section){
            if (empty($section)) {
                continue;
            }
            $this->add($section);
        }
    }
    
    public function addRow()
    {
        $this->currentRow = $this->sections['body']
                                 ->add(new Tag('div'))
                                 ->att('class','row');
        return $this->currentRow;
    }
    
    public function addColumn($colspan = 12, $offset = 0)
    {
        if (empty($this->currentRow)) {
            $this->addRow();
        }
        $this->currentColumn = $this->currentRow->add(new Column($colspan, $offset));
        return $this->currentColumn;
    }
    
    public function getBody()
    {
        return $this->sections['body'];
    }
}
