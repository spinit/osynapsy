<?php
namespace Osynapsy\Ocl\Component;

use Osynapsy\Core\Lib\Tag;

class Tab extends Component
{
    private $__head = null;
    private $__body = null;
    private $__tabs = array();
    
    public function __construct($name)
    {
        parent::__construct('div', $name);
        $this->att('class', 'tabs');
        $this->add(new HiddenBox($name))
             ->att('class','req-reinit');
        //osy_form::$page->add_script('../lib/jquery/jquery.scrollabletab.js');
    }
    
    protected function __build_extra__()
    {
        $head = $this->add(new Tag('ul'));
        ksort($this->__tabs);
        $it = 0;
        foreach($this->__tabs as $row) {
            ksort($row);
            foreach($row as $cols) {
                foreach($cols as $obj) {
                    $prefix = is_object($obj['obj']) ? $obj['obj']->get_par('label-prefix').' ' : '';
					$head->add('<li><a href="#'.$this->id.'_'.$it.'" idx="'.$it.'"><p><span>'.$prefix.$obj['lbl']."</span></p></a></li>\n");
                    $div = $this->add(tag::create('div'))->att('id',$this->id.'_'.$it);
                    if ($this->get_par('cell-height'))
                    {
                        $h = intval($this->get_par('cell-height'));
                        $obj['obj']->att('style','height : '.($h-30).'px');
                    }
                    $div->add($obj['obj']);
                    $it++;
                }
            }
        }
    }
    
    public function put($lbl,$obj,$r=0,$c=0)
    {
        //var_dump($lbl,$r,$c);
        $this->__tabs[$r][$c][] = array('lbl'=>$lbl,'obj'=>$obj);
    }
    
    public function build_pdf($pdf,$xwidth,$xstart)
    {
		foreach ($this->__tabs as $row) {
			ksort($row);
			foreach ($row as $cols) {
				$cury = $pdf->getY();
				ksort($cols);
				foreach ($cols as $obj) {
					if (!empty($obj['obj']) && is_object($obj['obj'])) {
						$pdf->setFont('helvetica','B',10);
						if (is_object($obj['obj']) && method_exists($obj['obj'],'build_pdf')){
							$pdf->SetFillColor(230,230,230);
							$pdf->Cell($wcel,7,strtoupper($obj['lbl']),'LT',0,'C',1);
						} else {
 						    $pdf->Cell($wcel,7,$obj['lbl'],'LT',0,'L',0);
						}
						$pdf->SetFillColor(0);
						$pdf->setFont('helvetica','',12);
						$pdf->Ln();
						if (method_exists($obj['obj'],'build_pdf')){
							$obj['obj']->build_pdf($pdf,$xwidth,$xstart);
						}
					}
				}
			}
		}
	}
}