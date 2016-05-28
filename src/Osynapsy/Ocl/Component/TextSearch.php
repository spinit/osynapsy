<?php
namespace Osynpasy\Ocl\Component;

class TextSearch extends Component 
{    
    private $text_box = null;
    private $span_src = null;
    
    public function __construct($name)
    {
        parent::__construct('div');
        $this->class = 'osy-textsearch';
        $this->id = $name;
        $this->add(new hidden_box($name));
        $this->text_box = $this->add(tag::create('input'))
                         ->att('type','text')
                         ->att('name',$name.'_lbl')
                         ->att('readonly','readonly');
        $this->span_src = $this->add(tag::create('span'))->att('class','fa fa-search');
    }
    
    public function __build_extra__()
    {
        foreach (array('form-related-search','form-related') as $par){
            if ($form = $this->get_par($par)){
                $str_par = "obj_src=".$this->id;
                $frm_par = (key_exists('rel_fields',$this->__par)) ? explode(',',$this->__par['rel_fields']) : array();
                foreach($frm_par as $fld)
                { 
                    $str_par .= '&'.$fld.'='.get_global($fld,$_REQUEST);
                }
                if ($par=='form-related' && ($sql = $this->get_par('datasource-sql')))
                {
                    $this->text_box->value = kkernel::$dba->exec_unique(kkernel::replacevariable($sql));
                }
                list($w,$h,$p) = kkernel::$dbo->exec_unique("SELECT coalesce(".kkernel::$dba->cast('w.p_vl','integer').",640),
                                                                coalesce(".kkernel::$dba->cast('h.p_vl','integer').",480),
                                                                REPLACE(r.p1,'/core/','') as pag
                                                        FROM osy_obj f
                                                        INNER JOIN osy_res r ON (f.o_sty = r.v_id AND r.k_id = 'osy-object-subtype')
                                                        LEFT JOIN osy_obj_prp h ON (f.o_id = h.o_id AND h.p_id = 'height')
                                                        LEFT JOIN osy_obj_prp w ON (f.o_id = w.o_id AND w.p_id = 'width')
                                                        WHERE f.o_id = ?",array($form));
                 if ($par=='form-related'){
                    if (!empty($_REQUEST[$this->id])){
                        $str_par.='&pkey[id]='.$_REQUEST[$this->id];
                        $this->text_box->att('onclick',"oform.command.open_window('{$form}','{$str_par}',$w,$h,'index.php','search'); return false;");
                    }
                 } else {
                    $this->span_src->att('onclick',"oform.command.open_window('{$form}','{$str_par}',$w,$h,'index.php','search'); return false;");
                 }
            }
        }
    }
}
