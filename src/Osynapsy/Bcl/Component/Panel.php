<?php
namespace Osynapsy\Bcl\Component;

use Osynapsy\Core\Lib\Tag as Tag;
use Osynapsy\Ocl\Component\Component as Component;

//Costruttore del pannello html
class Panel extends Component
{
    private $cells = array();
    private $cellClass;
    private $currentRow = null;
    private $rowClass = 'row';
    private $tag = array('div' , 'div');
    private $formType='normal';
    
    private $head;
    private $body;
    private $foot;
    
    public function __construct($id, $tag = 'div', $rowClass = null, $cellClass = null)
    {
        parent::__construct('fieldset', $id);
        $this->att('class','panel')
             ->par('label-position','outside');
        if (!empty($rowClass)) {
            $this->rowClass = $rowClass;
        }
        if (!empty($cellClass)) {
            $this->cellClass = $cellClass;
        }
    }
    
    public function appendToHead($title,$dim=0)
    {
        if (empty($this->head)) {
            $this->head = new Tag('div');
            $this->head->att('class','panel-heading');
        }
        
        if ($dim) {
            $this->head->add(new Tag('h'.$dim))->add($title);
        } else {
            $this->head->add($title);
        }
    }
    
    public function appendToFoot($content)
    {
        if (empty($this->foot)) {
            $this->foot = new Tag('div');
            $this->foot->att('class','panel-footer');
        }
        $this->foot->add($content);
        return $content;
    }
    
    public function append($content)
    {
        if (empty($this->body)) {
            $this->body = new Tag('div');
            $this->body->att('class','panel-body');
        }
        if ($content) {
            $this->body->add($content);
            return $content;
        }
    }
    
    protected function __build_extra__()
    {
        ksort($this->cells);
        foreach($this->cells as $iRow => $Row) {
            ksort($Row);
            $this->addRow();
            foreach ($Row as $iColumn => $Column) {
                //ksort($col);
                foreach ($Column as $iCell => $Cell) {
                    $width = max($Cell['width'],1);
                    $this->buildLabel($Cell, $colspan);
                    switch ($this->formType) {
                        case 'horizontal':
                            $div = new Tag('div');
                            $div->att('class','col-sm-' . $width.' col-lg-'.$width)
                                ->add($Cell['obj']);
                            $Cell['obj'] = $div;
                            break;
                    }
                    $this->addCell($Cell, $width);
                    break;
                }
            }
        }
        if ($this->head) {
            $this->add($this->head);
        }
        if ($this->body) {
            $this->add($this->body);
        }
        if ($this->foot) {
            $this->add($this->foot);
        }
    }
    
    private function addRow()
    {
        $this->currentRow = $this->append(new Tag($this->tag[0]));
        $this->currentRow->att('class', $this->rowClass);
        return $this->currentRow;
    }
    
    private function addCell($cell = null, $width = null)
    {
        if (is_null($cell)) {
            return;
        }
        
        $cel = $this->currentRow->add(new Tag('div'));
        
        switch($this->formType) {
            case 'horizontal':
                $width += 4;
                break;
        }
        $class = 'col-sm-' . $width.' col-lg-'.$width;
        $class .= empty($cell['offset']) ? '' : ' col-lg-offset-'.$cell['offset'];
        $class .= empty($cell['class']) ? '' : ' '.$cell['class'];
        $formGroup = $cel->att('class', $class)->add(new Tag('div'))->att('class','form-group');
        
        if (!empty($this->cellClass)) {
            $cel->att('class',$this->cellClass);
        }
        
        unset($cell['width']);
        unset($cell['class']);
        unset($cell['offset']);
        $formGroup->add2($cell);
        
        return $cel;
    }
    
    public function buildLabel(&$obj, $colspan)
    {
        $style='';
        if (is_object($obj['obj']) && ($obj['obj']->tag == 'button')) {
           $obj['lbl'] = '&nbsp';
           $style = 'display: block';
        }
        if (empty($obj['lbl'])) {
            return;
        }
        $labelText = $obj['lbl'];
        if (is_object($obj['obj'])){
            if ($prefix = $obj['obj']->get_par('label-prefix')){
                $labelText = '<span class="label-prefix">'.$prefix.'</span>'.$labelText;
            }
            if ($postfix = $obj['obj']->get_par('label-postfix')){
                $labelText .= '<span class="label-postfix">'.$postfix.'</span>';
            }
        }
        $label = new Tag('label');
        $label->att('class',($obj['obj'] instanceof panel ? 'osy-form-panel-label' : 'osy-component-label'))
              ->att('class',(is_object($obj['obj']) ? $obj['obj']->get_par('label-class') : ''),true)
              ->att('for',$obj['obj']->id)              
              ->add(trim($labelText));
        if (!empty($style)) {
            $label->att('style',$style);
        }
        switch ($this->formType) {
            case 'horizontal':
                $label->att('class','control-label col-sm-2 col-lg-2',true);
                break;
        }
        $obj['lbl'] = $label;
    }
    
    public function put($lbl, $obj, $row = 0, $col = 0, $width=1, $offset=null, $class='')
    {
        $this->cells[$row][$col][] = array(
            'lbl' => $lbl,
            'obj' => $obj,
            'width' => $width,
            'class' => $class,
            'offset' => $offset
        );
    }
    
    public function setType($type)
    {
        $this->formType = $type;
    }
}
