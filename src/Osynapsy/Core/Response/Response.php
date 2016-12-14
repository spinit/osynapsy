<?php
namespace Osynapsy\Core\Response;

/**
 * Abstract Response
 */
abstract class Response 
{
    protected $repo = array(
        'content' => array(),
        'header' => array()
    );
    
    /**
     * Init response with the content type
     * 
     * @param type $contentType
     */
    public function __construct($contentType = 'text/html')
    {
        $this->setContentType($contentType);
    }
    
    public function addBufferToContent($path = null, $part = 'main')
    {
        $this->addContent($this->getBuffer($path) , $part);
    }
    
    /**
     * Method that add content to the response
     * 
     * @param mixed $content
     * @param mixed $part
     * @param bool $checkUnique
     * @return mixed
     */
    public function addContent($content, $part = 'main', $checkUnique = false)
    {
        if ($checkUnique && !empty($this->repo['content'][$part]) && in_array($content,$this->repo['content'][$part])) {
            return;
        }
        $this->repo['content'][$part][] = $content;
    }
    
    public function send($content, $part =  'main', $checkUnique = false)
    {
        $this->addContent($content, $part, $checkUnique);
    }
    
    public function exec()
    {
        $this->sendHeader();
        echo implode('',$this->repo['content']);
    }
    
    /**
     * Include a php page e return content string
     * 
     * @param string $path
     * @param array $params
     * @return string
     * @throws \Exception
     */
    public static function getBuffer($path = null, $params = array())
    {
        $buffer = 1;
        if (!empty($path)) {
            if (!is_file($path)) {
                throw new \Exception('File '.$path.' not exists');                
            }
            if (!empty($params)) {
                foreach($params as $key => $val){
                    $$key = $val;
                }
            }
            $buffer = include $path;
        }
        if ($buffer === 1) {
            $buffer = ob_get_contents();
            ob_clean();
        }
        return $buffer;
    }
    
    /**
     * Send header location to browser
     * 
     * @param string $url
     */
    public function go($url)
    {
        header('Location: '.$url);
    }
    
    /**
     * Reset content part.
     * 
     * @param mixed $part
     */
    public function resetContent($part = 'main')
    {
        $this->repo['content'][$part] = array();
    }
    
    /**
     * Set content type of the response
     * 
     * @param string $type
     */
    public function setContentType($type)
    {
        $this->repo['header']['Content-Type'] = $type;
    }
    
    /**
     * Buffering of header
     * 
     * @param string $key
     * @param string $value
     */
    public function setHeader($key, $value)
    {
        $this->repo['header'][$key] = $value;
    }
    
    /**
     * Set cookie
     * 
     * @param string $vid
     * @param string $vval
     * @param date $sca
     */
    public static function cookie($vid, $vval, $sca = null)
    {        
        $dom = filter_input(\INPUT_SERVER,'SERVER_NAME');        
        $app = explode('.',$dom);
        if (count($app) == 3){ 
            $dom = ".".$app[1].".".$app[2];            
        }        
        if (empty($sca)) {
            $sca = mktime(0,0,0,date('m'),date('d'),date('Y')+1);
        }
        setcookie($vid, $vval, $sca, "/", $dom);
        return $dom;
    }
    
    /**
     * Send header buffer
     */
    protected function sendHeader()
    {
        foreach ($this->repo['header'] as $key => $value) {
            header($key.': '.$value);
        }
    }
    
    /**
     * Method for build response string
     * @abstract
     */
    abstract public function __toString();
}
