<?php
namespace Osynapsy\Bcl\Component;

use Osynapsy\Core\Kernel;
use Osynapsy\Core\Lib\Tag;
use Osynapsy\Ocl\Component\Component;
use Osynapsy\Ocl\Component\HiddenBox;

class LabelBox extends Component
{
    protected $hiddenBox;
    protected $label;
    
    public function __construct($id, $label='')
    {
        $this->requireCss('/__OsynapsyAsset/Bcl/LabelBox/style.css');
        parent::__construct('div', $id.'_labelbox');
        $this->att('class','osynapsy-labelbox');
        $this->hiddenBox = $this->add(new HiddenBox($id));
        $this->add($label);
    }
    
    public function setValue($value)
    {
        $this->hiddenBox->att('value',$value);
    }
    
    public function setLabelFromSQL($db, $sql, $par=array())
    {
        $this->label = $db->execUnique($sql, $par);
    }
    
    public function setLabel($label)
    {
        $this->label = $label;
    }
    
    public function __build_extra__()
    {
        if (is_null($this->label)) {
            $this->add($_REQUEST[$this->hiddenBox->id]);
        } else {
            $this->add('<span>'.$this->label.'</span>');
        }
    }
}
