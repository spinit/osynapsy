<?php
namespace Osynapsy\Core;

use Osynapsy\Core\Lib\Dictionary;
use Osynapsy\Core\Network\Router;
use Osynapsy\Core\Request\Request;
use Osynapsy\Core\Driver\DbPdo;
use Osynapsy\Core\Driver\DbOci;

/**
 * Inizializzazione del sistema
 */
class Kernel extends Base
{
    private $repo = array(
        'xmlconfig' => array(),
        'events' => array(), 
        'layouts' => array()        
    );
    public $router;
    public $request;
    public $controller;
    public $appController;
    public $db = array();
    public $dba = array();

    public function __call($name, $arguments)
    {
        return call_user_func_array($name, $arguments);
    }

    public function init($fileconf, $requestRoute)
    {        
        $this->loadConfiguration($fileconf);
        $this->loadXmlConfig('/configuration/parameters/parameter','parameters','name','value');        
        $this->loadXmlConfig('/configuration/layouts/layout','layouts','name','path');   
        $this->$request = new Request($_GET, $_POST, array(), $_COOKIE, $_FILES, $_SERVER);
        $this->$router = new Router($requestRoute, $this->$request);
        $this->$router->loadXml($this->$repo['xmlconfig'], '/configuration/routes/route');       
        $this->$router->addRoute('OsynapsyAssetsManager','/__assets/osynapsy/?*','Osynapsy\\Core\\Helper\\AssetLoader','','Osynapsy');
        if ($this->runAppController()) {
            $response = $this->runRouteController($this->$router->getRoute('controller'));
            if ($response !== false) {
                return $response;
            }
        }
        return $this->pageNotFound();
    }
    
    private  function runAppController()
    {
        $app = $this->$router->getRoute('application');      
        if (empty($app)) {
            return true;
        }
        $this->loadDatasources("/configuration/app/$app/datasources/db");
        
        if (empty($this->$repo['xmlconfig'][$app]['controller'])) {
            return true;
        }
        //If app has applicationController instance it before recall route controller;
        $classControllerApp = str_replace(':','\\',$this->$repo['xmlconfig'][$app]['controller']);
        $this->$appController = new $classControllerApp($this->$dba, $this->$router->getRoute());
        return $this->$appController->run();
    }
    
    private  function runRouteController($classController)
    {
        if (empty($classController)) {
            return false;
        }
        $this->$controller = new $classController($this->$request, $this->$dba, $this->$appController);
        return (string) $this->$controller->run();
    }
    
    private  function loadConfiguration($path)
    {
        if (!is_file($path)) {
            return;
        }
        $this->$repo['xmlconfig'][0] = simplexml_load_file($path);
        if (!empty($this->$repo['xmlconfig'][0]) && $this->$repo['xmlconfig'][0]->app) {
            foreach ($this->$repo['xmlconfig'][0]->app[0] as $e) {
                $appName = $e->getName();
                $appConf = filter_input(\INPUT_SERVER,'DOCUMENT_ROOT').'/../vendor/'.str_replace('_','/',$appName).'/etc/config.xml';                
                if (is_file($appConf)) {
                    $this->$repo['xmlconfig'][$appName] = simplexml_load_file($appConf);
                }
            }
        }
    }

    private function loadDatasources($path = '/configuration/datasources/db')
    {
        foreach ($this->$repo['xmlconfig'] as $xml) {            
            foreach ($xml->xpath($path) as $e) {                     
                $connectionStr = (string) $e[0];
                $connectionSha = sha1($connectionStr);
                if (array_key_exists($connectionSha, $this->$db)) {
                    continue;
                }
                $this->$db[$connectionSha] = $this->getDbConnection($connectionStr);               
                $this->$db[$connectionSha]->connect();
                if (empty($this->$dba)) {
                    $this->$dba = $this->$db[$connectionSha];
                }
            }
        }
    }

    private function getDbConnection($connectionString)
    {        
        if (strpos($connectionString, 'oracle') !== false) {
            return new DbOci($connectionString);
        } 
        return new DbPdo($connectionString);
    }
    
    public function loadXmlConfig($xpath, $dest, $kkey, $kval)
    {
        foreach ($this->$repo['xmlconfig'] as $xml) {
            foreach ($xml->xpath($xpath) as $e) {
                $this->$repo[$dest][$e[$kkey]->__toString()] = (isset($e[$kval]) ? $e[$kval]->__toString() : '');
            }
        }
    }

    public  function set($p,$v)
    {
        $ksearch = explode('.',$p);
        $klast   = count($ksearch)-1;
        $target = &$this->$repo;
        foreach($ksearch as $i => $k){
            if ($klast == $i){
                $target[$k] = $v;
            } elseif (array_key_exists($k,$target)) {
                $target = &$target[$k];
            } elseif(count($ksearch) != ($i+1)) {
                $target[$k] = array();
                $target = &$target[$k];
            }
        }
    }
    
    public function get($p)
    {
        if (empty($p)) {
            return $this->$repo;
        }
        $ksearch = explode('.',$p);
        $target = $this->$repo;
        foreach ($ksearch as $k) {
            if (!is_array($target)) {
                return $target;
            }
            $target = array_key_exists($k, $target) ? $target[$k] : null;
        }        
        return $target;
    }

    public  function sendEmail($from, $a, $subject, $body, $html=false)
    {
        $head = "From: $from\r\n".
                "Reply-To: $from\r\n".
                "X-Mailer: PHP/".phpversion()."\n";
        if ($html) {
          $head .= "MIME-Version: 1.0\n";
          $head .= "Content-Type: text/html; charset=\"iso-8859-1\"\n";
          $head .= "Content-Transfer-Encoding: 7bit\n\n";
        }
        return $this->env()->mail($a,$subject,$body,$head," -f ".$from);
    }
    
    public  function pageNotFound($message = 'Page not found')
    {
        $this->env()->ob_clean();
        $this->env()->header('HTTP/1.1 404 Not Found');
        return $message;
    }
}
