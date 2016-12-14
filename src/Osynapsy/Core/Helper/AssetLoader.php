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
        
        $filename = __DIR__ . '/../../assets/'.$this->path;
        if (is_file($filename)) {
            $this->sendFile($filename);
            return;
        }
        return $this->pageNotFound();
    }   
    
    public function sendFile($filename)
    {
        $offset = 3600 * 24 * 7;
        // calc the string in GMT not localtime and add the offset
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        //output the HTTP header
        $this->response->setHeader('Expires', gmdate("D, d M Y H:i:s", time() + $offset) . " GMT");        
        $this->response->setContentType('text/'.$ext);
        readfile($filename);
    }
    
    public function pageNotFound()
    {
        ob_clean();
        header('HTTP/1.1 404 Not Found');
        return 'Page not found';        
    }
}
