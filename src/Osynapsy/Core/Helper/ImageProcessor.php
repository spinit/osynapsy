<?php
namespace Osynapsy\Core\Helper;

require_once($_SERVER['DOCUMENT_ROOT'].'/../vendor/phpthu/ThumbLib.inc.php');

use Osynapsy\Core\Base;

class ImageProcessor extends Base
{
    public static $errors = array();
    
    public static function getThumbnail($FilNam,$Dim)
    {
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
        $path_info = pathinfo($pathOnDisk);
        $i = 1;
        while (file_exists($pathOnDisk)) {
            $pathOnDisk = $path_info['dirname'].'/'.$path_info['filename'].'_'.$i.'.'.$path_info['extension'];
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
        $pathUpload = $kernel->controller->getRequest()->get('app.parameters.path-upload');
        if (empty($pathUpload)){
            $kernel->$controller->response->error('alert','configuration parameters.path-upload is empty'.print_r($kernel->get('parameter'),true));         
        } elseif (!is_dir($_SERVER['DOCUMENT_ROOT'].$pathUpload)) {
            $kernel->$controller->response->error('alert','path-upload '.$_SERVER['DOCUMENT_ROOT'].$pathUpload.' not exists');
        } elseif (!is_writeable($_SERVER['DOCUMENT_ROOT'].$pathUpload)) {
            $kernel->$controller->response->error('alert',''.$_SERVER['DOCUMENT_ROOT'].$pathUpload.' is not writeable.');
        }
        if ($kernel->$controller->response->error()) { 
            $kernel->$controller->response->dispatch(); 
        }
        $fileName = $_FILES[$componentName]['name'];
        $tempName = $_FILES[$componentName]['tmp_name'];
        if (empty($fileName) || empty($tempName)) {
            return;
        }
        $arr_name = explode('.',$fileName);
        if (is_array($arr_name)) {
           $ext = $arr_name[count($arr_name) - 1];
        }
        $pathOnWeb = $pathUpload.$fileName;
        $pathOnDisk = self::getUniqueFilename($_SERVER['DOCUMENT_ROOT'].$pathOnWeb);
        $pathOnWeb = str_replace($_SERVER['DOCUMENT_ROOT'],'',$pathOnDisk);
        //Thumbnail path            
        if ($pathOnDisk && move_uploaded_file($tempName,$pathOnDisk)){
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
    
    public static function fileWriteError($filePath)
    {
        if (empty($filePath)){
            return '[fileWriteError] - File path is empty';
        } elseif (!file_exists($filePath)) {
            return '[fileWriteError] - '.$filePath.' not exists';
        } elseif (!is_writeable($filePath)) {
            return '[fileWriteError] - '.$filePath.' is not writeable.';
        }
        return false;
    }
    
    public static function thumbnail($pathFile, $dimension)
    {
        return self::resize($pathFile, $dimension, true);
    }
    
    public static function resize($pathFile, $dimension, $thumbnail=false)
    {
        if ($error = self::fileWriteError($pathFile)) {
            self::$errors[] = $error;
            return false;
        }
       
        $thumbnailName = $pathFile;
        if ($thumbnail) {
            $arrFileName = pathinfo($pathFile);
            $thumbnailName  = $arrFileName['dirname'];
            $thumbnailName .= '/'.$arrFileName['filename'];
            $thumbnailName .= '.'.$dimension[0].'x'.$dimension[1].'.';
            $thumbnailName .= $arrFileName['extension'];        
        }
        $thumb = \PhpThumbFactory::create($pathFile); 
        $thumb->resize($dimension[0], $dimension[1])->save($thumbnailName);
        //Return path web + filename;
        return str_replace($_SERVER['DOCUMENT_ROOT'], '', $thumbnailName);
    }
}
