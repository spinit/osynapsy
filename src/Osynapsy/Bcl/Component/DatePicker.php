<?php
namespace Osynapsy\Bcl\Component;

use Osynapsy\Ocl\Component\Component;

class DatePicker extends Component
{
    private $text;
    private $datePickerId;
    
    public function __construct($id)
    {
        $this->datePickerId = $id;
        $this->requireJs('/__OsynapsyAsset/Bcl/DatePicker/script.js');
        $this->requireCss('/__OsynapsyAsset/Bcl/DatePicker/style.css');
        parent::__construct('div',$id.'_datepicker');
        $this->att('class','input-group');
        $this->add(new TextBox($id))->att('class','date date-picker form-control');
        $this->add('<span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>');
    }
    
    protected function __build_extra__()
    {
        if (!empty($_REQUEST[$this->datePickerId])) {
            $data = $_REQUEST[$this->datePickerId];
            $data = explode('-',$data);
            if (count($data) >= 3 && strlen($data[0]) == 4) {
                $_REQUEST[$this->datePickerId] = $data[2].'/'.$data[1].'/'.$data[0];
            }
        }
    }
}