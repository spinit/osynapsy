<?php
namespace Osynapsy\Bcl\Component;

use Osynapsy\Ocl\Component\InputBox;

class DatePicker extends InputBox
{
    private $text;
    private $datePickerId;
    
    public function __construct($id)
    {
        $this->datePickerId = $id;
        $this->requireJs('/vendor/osynapsy/Bcl/DatePicker/script.js');
        $this->requireCss('/vendor/osynapsy/Bcl/DatePicker/style.css');
        parent::__construct('text',$id.'_datepicker');
        $this->att('class','input-group date date-picker form-control');
        $this->add(new TextBox($id))->att('readonly','readonly');
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