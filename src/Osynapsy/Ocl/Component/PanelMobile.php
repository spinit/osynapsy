class opanelmobile extends component
{
	protected $panel = null;
	protected $title = null;
	
	public function __construct($id){
		parent::__construct('div',$id);
		$this->panel = $this->add(new panel($id.'_body'));
        $this->panel->att('class','osy-panel-1')
                	->par('label-position','inside');
		$this->att('class','osy-panel-mobile');
		kkernel::$page->add_script(OSY_WEB_ROOT.'/js/component/opanelmobile.js');
	}
	
	protected function __build_extra__(){		
        if ($lp = $this->get_par('label-position')){
			$this->panel->par('label-position',$lp);
		}
        if (!$this->get_par('disable-head')){
            $this->title = $this->add(tag::create('div'))
							->att('class','osy-panel-mobile-title');
            $this->title->add(tag::create('span'))
                		->att('class','osy-win-ico-set fright')
                    	->add('&nbsp;');           
            $this->title->add($this->get_par('label'));
        }	
	}
	
	public function put($lbl,$obj,$row=0,$col=0){
		$this->panel->put($lbl,$obj,$row,$col);
	}
}