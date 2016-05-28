<?php
namespace Osynapsy\Ocl\Component;

require_once('../vendor/phpthu/ThumbLib.inc.php');
/* Datagrid Photo*/

class DatagridPhoto
{
    private $Db;
    private $DbPar = null;
    private $Sql;
    private $RowLen;
    private $DimMin;
    private $MainDiv;
    private $UpdateForm;
    
    public function __construct($Db,$Sql,$RowLen=4,$DimMin=array(100,100))
    {
        if (!is_array($DimMin)) die('Il parametro dimmin deve essere un array');
        $this->Db = $Db;
        $this->Sql = $Sql;
        $this->RowLen = $RowLen;
        $this->DimMin = $DimMin;
    }
    
    private function __build__()
    {
        //Carico foto
        $rs = $this->Db->ExecQuery($this->Sql);
        $this->MainDiv = new Tag('div');
        $this->MainDiv->Att('id','GalleryGrid'); 
        $i = 0;
        if (!empty($this->UpdateForm)){
                $this->MainDiv->Add(new Tag('input'))
                              ->Att('type','button')
                              ->Att('class','button_green')
                              ->Att('value','Aggiungi foto')
                              ->Att('onclick',"Update('{$this->UpdateForm}','')");
        }
        $Ul = $this->MainDiv
                   ->Add(new Tag('ul'))
                   ->Att('id','GalleryGridUl');
        if (is_array($this->DbPar)){
            $Ul->Att('dbpar',implode(',',$this->DbPar));
        }
        while($rec = $this->Db->GetNextRecord($rs,'ASSOC')){ 
            $col = empty($rec['brd']) ? 'black' : $rec['brd'];
            $a = $Ul->Add(new Tag('li'))
                    ->Att('id',$rec['id'])
                    ->Att('style','float: left; margin-top: 5px;')
                    ->Add(new Tag('a'));
            $a->Att('href',"#")
              ->Add(new Tag('img'))
              ->Att('src', $this->GetThumbnail($rec['pth'],$this->DimMin))
              ->Att('style',"margin-left: 5px; border: 1px solid {$col};")
              ->Att('id',$rec['id']);
            if (!empty($rec['ttl'])){
                $a->Att('title',$rec['ttl']);
            }
            //Se non viene specificato una form di update non associo il metodo onclick
            if (!empty($this->UpdateForm)){
                $a->Att('onclick',"Update('{$this->UpdateForm}','{$rec['id']}')");
            }
            $i++;
        }
    }
    
    private function __build_old__()
    {
        $this->MainDiv = new Tag('div');
        $this->MainDiv->Att('id','GalleryGrid'); 
          //Carico foto
        $rs = $this->Db->ExecQuery($this->Sql);
        $i = 0;
        if (!empty($this->UpdateForm)){
                $this->MainDiv->Add(new Tag('input'))
                              ->Att('type','button')
                              ->Att('class','button_green')
                              ->Att('value','Aggiungi foto')
                              ->Att('onclick',"Update('{$this->UpdateForm}','')");
        }
        while($rec = $this->Db->GetNextRecord($rs,'ASSOC')){ 
            
            if ($i % $this->RowLen == 0){
                $Cnt2 = $this->MainDiv->Add(new Tag('div'));
                $Cnt2->Att("style","margin-top: 10px; display: block; padding:0px; margin-bottom: 10px;");
            }   
            $ThuNam = $this->GetThumbnail($rec['pth'],$this->DimMin);
            $a = $this->MainDiv->Add(new Tag('div'))
                               ->Att('class','GridCell')
                               ->Att('style','float: left; margin: 5px')
                               ->Add(new Tag('a'));
            $a->Att('href',"#")
              ->Add(new Tag('img'))
              ->Att('src',$ThuNam)
              ->Att('style',"margin-left: 5px; border: 1px solid {$col};")
              ->Att('id',$rec['id']);
            if (!empty($rec['ttl'])){
                $a->Att('title',$rec['ttl']);
            }
            //Se non viene specificato una form di update non associo il metodo onclick
            if (!empty($this->UpdateForm)){
                $a->Att('onclick',"Update('{$this->UpdateForm}','{$rec['id']}')");
            }
            $i++;
        }
    }
    
    public function Get()
    {
        $this->__build__();
        return $this->MainDiv->Get();
    }
    
    public function SetDbUpdate($tbl,$fld)
    {
        $this->DbPar = array('table'=>$tbl,'field'=>$fld);
    }
    
    public static function GetThumbnail($FilNam,$Dim)
    {
        if (!is_file($_SERVER['DOCUMENT_ROOT'].$FilNam)){
             return '/upl/2.thu190.jpg';
        }
        $PathInfo = pathinfo($FilNam);
        
        $ThuNam = str_replace(' ','_',"{$PathInfo['dirname']}/{$PathInfo['filename']}.thu{$Dim[0]}.{$PathInfo['extension']}");
        $ThuNam = str_replace('_(Custom)','',$ThuNam);
        if (!is_file($_SERVER['DOCUMENT_ROOT'].$ThuNam)){
             //Effettuo il resize delle immagini al fine di creare le thumbneil
            try {
                $thumb = \PhpThumbFactory::create($_SERVER['DOCUMENT_ROOT'].$FilNam);  
                $thumb->adaptiveResize($Dim[0], $Dim[1])->save($_SERVER['DOCUMENT_ROOT'].$ThuNam);
            } catch (Exception $e) {
                echo $e->getMessage();
                return '/upl/2.thu190.jpg';
            }
            
        }
        return $ThuNam;
    }
    
    public function SetForm($f)
    {
        $this->UpdateForm = $f;
    }
}
