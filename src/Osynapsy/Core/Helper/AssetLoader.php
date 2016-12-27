<?php
namespace Osynapsy\Core\Helper;

use Osynapsy\Core\Controller\Controller;

class AssetLoader extends Controller
{
    private $path;
    
    public function init()
    {
        $this->path = $this->getParameter(0);
    }
    
    public function indexAction()
    {            
        if (!$this->checkFile(__DIR__ . '/../../assets/'.$this->path)) {
            return $this->pageNotFound();    
        }        
    }   
            
    private function checkFile($filename)
    {
        if (!is_file($filename)) {
            return false;
        }        
        $this->copyFileToCache('.'.$this->request->get('page.url'), $filename);        
        $this->sendFile($filename);
        return true;
    }
    
    private function copyFileToCache($webPath, $assetsPath)
    {
        if (file_exists($webPath)) {
            return true;
        }
        $path = explode('/', $webPath);
        $file = array_pop($path);
        $current = './';
        foreach($path as $dir){
            $current .= $dir.'/';
            if (file_exists($current)) {
                continue;
            }
            mkdir($current);
        }
        return copy($assetsPath, $webPath);
    }
    
    public function pageNotFound()
    {
        ob_clean();
        header('HTTP/1.1 404 Not Found');
        return 'Page not found';        
    }
    
    private function sendFile($filename)
    {
        $offset = 86400 * 7;
        // calc the string in GMT not localtime and add the offset
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        //output the HTTP header
        $this->response->setHeader('Expires', gmdate("D, d M Y H:i:s", time() + $offset) . " GMT");        
        $this->response->setContentType('text/'.$ext);
        readfile($filename);
    }
}
