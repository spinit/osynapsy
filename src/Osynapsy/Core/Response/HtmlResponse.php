<?php
namespace Osynapsy\Core\Response;

use Osynapsy\Core\Response\Response as Response;
use Osynapsy\Core\Lib\Tag;
use Osynapsy\Core\Kernel as Kernel;

class HtmlResponse extends Response
{
    public $template = null;
    
    public function __construct()
    {
        parent::__construct('text/html');
        $this->repo['content'] = array('main' => '');        
    }
    
    public function addBufferToContent($path = null, $part = 'main')
    {
        $buffer = self::getBuffer($path);
        $buffer = $this->replaceContent($buffer);
        $this->addContent($buffer , $part);
    }
    
    private function replaceContent($buffer)
    {
        $dummy = array_map(
            function ($v) {
                return '<!--'.$v.'-->';
            },
            array_keys(
                $this->repo['content']
            )
        );
        $parts = array_map(
            function ($p) {
                return is_array($p) ? implode("\n",$p) : $p;
            },
            array_values(
                $this->repo['content']
            )
        );
        return str_replace($dummy, $parts, $buffer);
    }
    
    public function __toString()
    {
        $this->sendHeader();
        $this->buildResponse();
        if (!empty($this->template)) {
            return $this->replaceContent($this->template);
        } 
        $response = '';
        foreach ($this->repo['content'] as $content) {
            $response .= is_array($content) ? implode('',$content) : $content;
        }
        return $response;
    }

    //overwrite
    protected function buildResponse()
    {
        //overwrite this method for extra content manipulation
    }
    
    public function addJs($path)
    {
        $this->addContent('<script src="'.$path.'"></script>', 'js', true);
    }
    
    public function addJsCode($code)
    {
        $this->addContent('<script>'.PHP_EOL.$code.PHP_EOL.'</script>', 'js', true);
    }
    
    public function addCss($path)
    {
        $this->addContent('<link href="'.$path.'" rel="stylesheet" />', 'css', true);
    }
    
    public function resetTemplate()
    {
        $this->template = '';
    }
}
