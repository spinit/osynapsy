/*
 * Autocomplete component
 */
class autocomplete extends component
{
    public function __construct($nam,$id=null)
    {
        parent::__construct('input',nvl($id,$nam));
        $this->att('type','text');
        $this->att('name',$nam);
        $this->att('class','autocomplete');
        kkernel::$page->add_script(OSY_WEB_ROOT.'/js/component/oautocomplete.js');
    }
    
    public function __build_extra__()
    {
        $this->att('ops',$_REQUEST['ajax']);
        if (!empty($_REQUEST['ajax']))
        {
            $sql = kkernel::ReplaceVariable($this->get_par('datasource-sql'));
            $sql = kkernel::parse_string($sql);
            $res = kkernel::$dba->exec_query($sql,null,'ASSOC');
            die(json_encode($res)); 
        }
         else
        {
            $val = get_global($this->id,$_REQUEST);
            if (!empty($val)) $this->att('value',$val);
        }
    }
}