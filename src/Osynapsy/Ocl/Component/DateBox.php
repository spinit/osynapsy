<?php
namespace Osynapsy\Ocl\Component;

class DateBox extends component
{
    public $__dat;
	
	public function __construct($nam,$id=null)
    {
        parent::__construct('div', $nam, nvl($id,$nam));
		$this->att('class','osy-datebox');
        $this->__dat = $this->add(new TextBox($nam))
							->att('readonly')
				            ->att('size',8)
				            ->att('maxlength',12);
		$this->add('<span class="fa fa-calendar"></span>');
		kkernel::$page->add_script(OSY_WEB_ROOT.'/js/component/ocalendar.js');
    }
    
    protected function __build_extra__()
    {
        $val = get_global($this->id,$_REQUEST);
		if (!empty($val)){	$_REQUEST[$this->id] = $val; }
		if (!empty($_REQUEST[$this->id]) && $this->get_par('date-format'))
        {
           if (strlen($_REQUEST[$this->id])>10){
                list($data,$ora) = explode(' ',$_REQUEST[$this->id]);
                $adat = explode('-',$data);
           } else {
                $adat = explode('-',$_REQUEST[$this->id]);
           }
           if (count($adat) == 3)
           {
               $_REQUEST[$this->id] = str_replace(array('yyyy','mm','dd'),$adat,$this->get_par('date-format'));
           }
        }
        $this->__dat->att('value',$_REQUEST[$this->id]);
    }
    
    public static function convert($d,$df='dd/mm/yyyy')
    {
        if (!empty($d) && !empty($df))
        {
           $adat = explode('-',$d);
           if (count($adat) == 3)
           {
               return str_replace(array('yyyy','mm','dd'),$adat,$df);
           }
        }
        return $d;
    }
}