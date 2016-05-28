class radio_list extends panel
{
       public function __construct($name)
       {
            parent::__construct($name);
            $this->att('class','osy-radio-list');
       }
       
       protected function __build_extra__()
       {
           $a_val = array();
    	   if ($val = $this->get_par('values'))
           {
    	      $a_val_raw = explode(',',$val);
              foreach($a_val_raw as $k => $val)
              {
                $a_val[] = explode('=',$val);
              }
           }
           if ($sql = $this->get_par('datasource-sql'))
           {
              $sql = kkernel::ReplaceVariable($sql);
              $sql = kkernel::parse_string($sql);
              $a_val = kkernel::$dba->exec_query($sql,null,'NUM');
           }
		   $dir = $this->get_par('direction');
    	   foreach($a_val as $k => $val)
    	   {
               //$tr = $this->add(tag::create('tr'));
               //$tr->add(tag::create('td'))->add('<input type="radio" name="'.$this->id.'" value="'.$val[0].'"'.(!empty($_REQUEST[$this->id]) && $_REQUEST[$this->id] == $val[0] ? ' checked' : '').'>');
               //$tr->add(tag::create('td'))->add($val[1]);
               $rd = new radio_box($this->id);
               $rd->value = $val[0];
			   if ($this->cols){
				   $rst = $k % $this->cols;
				   if (empty($rst)) $row += 10;
				   $col = ($resto * 10)+10;
				   $this->put(null,$rd.'&nbsp;'.$val[1],$row,$col);
			   } elseif ($dir == 'O'){
		   	  	   $this->put(null,$rd.'&nbsp;'.$val[1],10,($k*10)+9);
			   } else {
               	   $this->put(null,$rd.'&nbsp;'.$val[1],($k*10)+9,10);
			   }
               //$this->put(null,$val[1],$k+9,10);
    	   }
           parent::__build_extra__();
       }
}