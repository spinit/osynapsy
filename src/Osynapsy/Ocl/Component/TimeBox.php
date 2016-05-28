class time_box extends input_box
{
    public function __construct($name)
    {
        //kkernel::$page->add_script('/lib/jquery/jquery.timepicker.js');
        parent::__construct('time',$name,$name);
        $this->att('autocomplete','off')
             ->att('class','osy-time');
    }
}