<?php
namespace Osynapsy\Core\Network;

use Osynapsy\Core\Request\Request as Request;

class Router
{
    public $request;
    private $routes;
    private $debug=false;
    //Rispettare l'ordine
    private $patternPlaceholder = array(
        '?i' => '([\\d]+){1}', 
        '?I' => '([\\d]*){1}',
        '?.' => '([.]+){1}',
        '?w' => '([\\w-,]+){1}', 
        '?*'  => '(.*){1}',
        '?' => '([^\/]*)',
        '/'  => '\\/'        
    );
    
    public function __construct()
    {
        $this->routes = new RouteCollection();
        if (empty($_GET['q'])) { //Workaround
            $_GET['q'] = '/';
        }
        $this->request = new Request( $_GET, $_POST, array(), $_COOKIE, $_FILES, $_SERVER);
    }
    
    public function loadXml($xmlDocs, $path)
    {
        foreach ($xmlDocs as $appName => $xml) {
            foreach ($xml->xpath($path) as $e) {
                $rid = (string) $e['id'];
                $url = (string) $e['path'];
                $ctl = (string) trim(str_replace(':', '\\', $e[0]));
                $tpl = (string) $e['template'];
                $this->addRoute($id, $url, $ctl, $tpl, $appName, $e->attributes());                
            }
        }
        if ($this->debug) {
            var_dump($this->routes);
        }        
    }
    
    private function isCurrentRoute($url, $ctl, $tpl, $app, $attr=null)
    {
        $nParams = substr_count($url, '?'); 
        if ($nParams === 0) {
           if ($url === $this->request->get('query.q')) {
                $this->routes->set('current.url', $url);
                $this->routes->set('current.controller', $ctl); 
                $this->routes->set('current.templateId', $tpl);
                $this->routes->set('current.application', $app);
                $this->routes->set('current.parameters', $out);
                $this->routes->set('current.attributes', $attr);
           }
           return;
        }
        
        $pattern = str_replace(
            array_keys(
                $this->patternPlaceholder
            ),
            array_values(
                $this->patternPlaceholder
            ),
            $url
        );
        preg_match('|^'.$pattern.'$|', $this->request->get('query.q'), $out);
        
        if (empty($out)){
            return;
        }  
        $this->routes->set('current.url', array_shift($out));
        $this->routes->set('current.controller', $ctl); 
        $this->routes->set('current.templateId', $tpl);
        $this->routes->set('current.application', $app);
        $this->routes->set('current.parameters', $out);
        $this->routes->set('current.attributes', $attr);
        $this->request->set('page', $this->routes->get('current'));
    }
    
    public function get($key)
    {
        return $this->routes->get($key);
    }
    
    public function addRoute($id, $url, $controller, $templateId, $application, $attributes=array())
    {    
        $this->routes->addRoute($id, $url, $controller, $templateId);
        $this->isCurrentRoute($url, $controller, $templateId, $application, $attributes);
        
    }
    
    public function getRoute($key='')
    {
        if (!empty($key)){
            $key = '.'.$key;
        }
        return $this->get('current'.$key);
    }
    
    public function getRequest()
    {
        return $this->request;
    }
}
