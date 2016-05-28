<?php
namespace Osynapsy\Ocl\Component;

class Label extends Component
{
    public function __construct($name)
    {
        parent::__construct('label',$name);
        $this->att('class','normal');
        $this->add(new hidden_box($name));
    }
    
    protected function __build_extra__()
    {
        $val = get_global($this->id,$_REQUEST);
        if ($pointer = $this->get_par('global-pointer'))
        {
            $ref = array(&$GLOBALS,&$_REQUEST,&$_POST);
            foreach ($ref as $global_arr)
            {
                if (key_exists($pointer,$global_arr))
                {
                    $val = $global_arr[$pointer];
                    break;
                }
            }
        }
		if (strstr($val,"\n")){
			$this->add(nvl('<pre>'.$val.'</pre>','&nbsp;'));
		} else {
        	$this->add(nvl($val,'&nbsp;'));
		}
    }
    
    public static function get_from_datasource($val,$lst,$db=null)
    {
        $lbl = $val;
        if (!is_array($lst) && !is_null($db))
        {
            try
            {
				$lst = $db->exec_query($lst,null,'NUM');
            }
             catch(Exception $e)
            {
               echo $lst;
			   $this->att(0,'dummy');
               $this->add('<div class="osy-error" id="'.$this->id.'">SQL ERROR - [LABEL]</div>');
               $this->add('<div class="osy-error-msg">'.($e->getMessage()).'</div>');
               return;
            }
        }
        
        if ($val == '[get-first-value]')
        {
            return !empty($lst[0]) ? nvl($lst[0][1],$lst[0][0]) : null;
        }
         elseif (is_array($lst))
        {
            foreach($lst as $k => $rec)
            {
                if ($rec[0] == $val)
                {
                    return nvl($rec[1],$rec[0]);
                }
            }
        }
        return $lbl;
     }
}