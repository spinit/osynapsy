<?php
namespace Osynapsy\Core\Lib\Util;

use Osynapsy\Core\Base;

class Upload extends Base
{
    private $path;

    public function __construct($pathToSave)
    {
        $this->$path = $pathToSave;
    }

    public static function getThumbnail($FilNam,$Dim){
        require_once($_SERVER['DOCUMENT_ROOT'].'/../vendor/phpthu/ThumbLib.inc.php');
        $PathInfo = pathinfo($FilNam);
        $ThuNam = str_replace(' ','_',"{$PathInfo['dirname']}/{$PathInfo['filename']}.thu{$Dim[0]}.{$PathInfo['extension']}");
        $ThuNam = str_replace('_(Custom)','',$ThuNam);
        if (!is_file($_SERVER['DOCUMENT_ROOT'].$ThuNam)){
             //Effettuo il resize delle immagini al fine di creare le thumbneil
            $thumb = \PhpThumbFactory::create($_SERVER['DOCUMENT_ROOT'].$FilNam);  
            $thumb->adaptiveResize($Dim[0], $Dim[1])->save($_SERVER['DOCUMENT_ROOT'].$ThuNam);
        }
        return $ThuNam;
    }

    public static function getUniqueFilename($pathOnDisk)
    {
        if (empty($pathOnDisk)) {
            return false;
        }
        //Se il Path non eiste su disco lo restituisco.
        if (!file_exists($pathOnDisk)) {
            return $pathOnDisk;
        } 
        $pathInfo = pathinfo($pathOnDisk);
        $i = 1;
        while (file_exists($pathOnDisk)) {
            $pathOnDisk = $pathInfo['dirname'].'/'.$pathInfo['filename'].'_'.$i.'.'.$pathInfo['extension'];
            $i++;
        }
        return $pathOnDisk;
    }

    public static function upload($componentName, $Option=null)
    {
        $kernel = $this->singleton('kernel');
        
        if (!is_array($_FILES) || !array_key_exists($componentName,$_FILES)){ 
            return; 
        }
       
        if (empty($this->path)){
            $kernel->$controller->response->error('alert','configuration parameters.path-upload is empty');         
        } elseif (!is_dir($_SERVER['DOCUMENT_ROOT'].$this->path)) {
            $kernel->$controller->response->error('alert','path-upload '.$_SERVER['DOCUMENT_ROOT'].$this->path.' not exists');
        } elseif (!is_writeable($_SERVER['DOCUMENT_ROOT'].$this->path)) {
            $kernel->$controller->response->error('alert',''.$_SERVER['DOCUMENT_ROOT'].$this->path.' is not writeable.');
        }
        if ($kernel->$controller->response->error()) { 
            $kernel->$controller->response->dispatch(); 
        }
        $fileName = $_FILES[$componentName]['name'];
        $tempName = $_FILES[$componentName]['tmp_name'];
        if (empty($fileName) || empty($tempName)){
            return;
        }
        $arr_name = explode('.',$fileName);
        if (is_array($arr_name)){ 
           $ext = $arr_name[count($arr_name) - 1];
        }
        $pathOnWeb = $$this->path.$fileName;
        $pathOnDisk = self::getUniqueFilename($_SERVER['DOCUMENT_ROOT'].$pathOnWeb);
        $pathOnWeb = str_replace($_SERVER['DOCUMENT_ROOT'],'',$pathOnDisk);
        //Thumbnail path            
        if ($pathOnDisk && move_uploaded_file($tempName, $pathOnDisk)){
            //Effettuo il resize delle immagini al fine di creare le thumbneil            
            if (is_array($Option)) {
                $thumb = \PhpThumbFactory::create($pathOnDisk); 
                $thumb->resize($DimResize[0], $DimResize[1])->save($pathOnDisk); 
            }
            //$dim = $thumb2->getCurrentDimensions();
            //Inserisco sul db l'immagine
            $_REQUEST[$componentName] = $_POST[$componentName] = $pathOnWeb;
            return $pathOnWeb;
        }
        return null;
    }
}