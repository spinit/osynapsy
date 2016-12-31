<?php
/*
 +-----------------------------------------------------------------------+
 | core/Ocl/Component/TextSearchInLine.php                               |
 |                                                                       |
 | This file is part of the Opensymap                                    |
 | Copyright (C) 2005-2013, Pietro Celeste - Italy                       |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Create TextSearchInLine component                                   |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Pietro Celeste <pietro.celeste@gmail.com>                     |
 +-----------------------------------------------------------------------+

 $Id:  $

/**
 * @email           pietro.celeste@opensymap.org
 * @date-creation   09/04/2015
 * @date-update     09/04/2015
 */
namespace Osynapsy\Ocl\Component;

use Osynapsy\Core\Lib\Tag;

class TextSearchInLine extends Component
{
    public $searchBox = null;
    private $hdnBox = null;
    private $spanSrc = null;
    private $fieldSearchName;
    
    public function __construct($name)
    {
        parent::__construct('div');
        $this->class = 'osy-textsearch-inline input-group';
        $this->id = $name;
        $this->fieldSearchName = $name.'_lbl';
        $this->hdnBox = $this->add(new HiddenBox($name));
        $this->searchBox = $this->add(new TextBox($name.'_lbl'))->att('class','form-control');
        $this->spanSrc = $this->add(new Tag('span'))->att('class','fa fa-search input-group-addon');
        $this->requireCss('/vendor/osynapsy/Bcl/TextSearchInLine/style.css');
        $this->requireJs('/vendor/osynapsy/Bcl/TextSearchInLine/script.js');
    }
    
    public function __build_extra__()
    {
        $form_pkey = array();       
        if ($form = $this->get_par('form-related')) {
            $form_par = $this->get_form_param($form,true);          
            $form_pkey = $form_par['pkey'];
            $str_par = "obj_src=".$this->id;
            $frm_par = (key_exists('rel_fields',$this->__par)) ? explode(',',$this->__par['rel_fields']) : array();            
            foreach($frm_par as $fld) { 
                $str_par .= '&'.$fld.'='.get_global($fld,$_REQUEST);
            }
            if (!empty($_REQUEST[$this->id])) {
                $str_par.='&pkey[id]='.$_REQUEST[$this->id];                
            }
            $this->att('data-form',$form)
                 ->att('data-form-dim',$form_par['width'].','.$form_par['height'])
                 ->att('data-form-nam',$form_par['name'])
                 ->att('data-form-pag',$form_par['page'])
                 ->att('data-form-par',$str_par);
        }
        
        if ($_REQUEST['ajax'] == $this->id) {
            $this->ajaxResp($form_pkey);
            return;
        } elseif (!empty($_REQUEST[$this->id]) && ($sql = $this->get_par('datasource-sql-label'))) {            
            $sql = env::replaceVariable($sql);          
            $this->searchBox->value = env::$dba->exec_unique($sql,null,'NUM');            
        }
    }
    
    public function setIdentity()
    {
        if (empty($_REQUEST[$this->fieldSearchName])) {
            return false;
        }
        $sql = $this->singleton('kernel')->replaceVariable($this->get_par('datasource-sql'));
        $rs  = $this->singleton('kernel')->$dba->execQuery("SELECT * FROM (".$sql.") a",null,'ASSOC');
        $ncor = count($rs);
        if (empty($ncor) || $ncor > 1) {
            return false;
        }
        return $_REQUEST[$this->id] = $rs[0]['_id'];
    }
    
    public function ajaxResponse($pkey_list=null) 
    {        
        if (!empty($_POST[$this->id.'_lbl'])) {
            $_POST[$this->id.'_lbl'] = str_replace(' ','%',$_POST[$this->id.'_lbl']);
        }
        $tbl = new Tag('div');
        $sql = $this->singleton('kernel')->replaceVariable($this->get_par('datasource-sql'));
        $rs  = $this->singleton('kernel')->$dba->execQuery("SELECT * FROM (".$sql.") a",null,'ASSOC');
        $cols = $this->singleton('kernel')->$dba->getColumns();
        foreach($cols as $col) {
            if ($col['name']=='_group') {
                $rs = $this->groupRs($rs);
            }
        }
        $__g = '';
        
        foreach ($rs as $rec) {
            $tr = new Tag('div');
            $tr->att('class','row');
            $__k = array(); 
            $_oid = array();
            foreach ($rec as $key=> $fld) {
                $val = $fld;
                if (in_array($key, $pkey_list)) {
                    $__k[] = 'pkey['.$key.']='. $val;
                    $_oid[] = $val;
                    continue;
                }
                $print = true;
                if ($key[0]=='_') {
                    $print = false;
                    switch ($key) {
                        case '_id' :  
                            $tr->att('data-oid',$val);
                            $print=false;
                            break;
                        case '_label' : 
                            $tr->att('data-label',$val);
                            $print=false;
                            break;
                        case '_group' :
                            if ($val != $__g) {
                              $__g = $val;  
                            } else {
                              $val = '&nbsp;';
                            }
                            $val = '<span class="osy-textsearch-inline-group">'.$val.'</span>';
                            $print = true;
                            break;
                        case '_img64x2' :
                             $dimcls = 'osy-image-med';
                             //no-break
                        case '_img64' :                                                          
                            $val = '<span class="'.(empty($dimcls) ? 'osy-image-min' : $dimcls).'">';
                            $val .= empty($fld) ? '<span class="fa fa-ban"></span>': '<img src="data:image/png;base64,'.base64_encode($fld).'">';
                            $val .= '</span>';
                            $print = true;
                            break;
                        case '_label' :
                            $tr->att('data-label',$val);                                        
                            break;             
                    }
                }
                if ($print) {
                    $tr->add(new Tag('div'))->add($val);
                }
            }
            $tbl->add($tr);
            if (!empty($__k)) {
                $tr->att('data-pkey',implode('&',$__k));                
                $tr->att('data-oid',implode('&',$_oid));   
            }
        }
        if (empty($rs)) {
            $msg = $this->get_par('empty-message');
            $tbl->add('<div class="row-empty">'.(empty($msg) ? 'Nessun risultato' : $msg).'</div>');
        }
        //mail('pietro.celeste@gmail.com','div',$tbl);
        die($tbl);   
    }
    
    private function groupRs($rs)
    {
        $rsg = array();
        foreach($rs as $rec) {
            $grp = $rec['_group'];            
            $rsg[$grp][] = $rec;            
        }        
        $rs = array();
        $extra_get = 0;
        ksort($rsg);
        foreach($rsg as $k => $group) {
            $nget = min(5,count($group)) + $extra_get;
            $group = array_slice($group, 0,$nget);
            $extra_get = ($nget < 5) ? 5 - $nget : 0;
            foreach($group as $rec) {
                $rs[] = $rec;
            }
        }
        return $rs;
    }
}