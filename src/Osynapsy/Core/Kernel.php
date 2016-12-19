<?php
namespace Osynapsy\Core;

use Osynapsy\Core\Lib\Dictionary;
use Osynapsy\Core\Network\Router;
use Osynapsy\Core\Driver\DbPdo;
use Osynapsy\Core\Driver\DbOci;

/**
 * Inizializzazione del sistema
 */
class Kernel
{
    private $repo = array(
        'xmlconfig' => array(),
        'events' => array(), 
        'layouts' => array(),
        'query' => null
    );
    public $router;
    public $request;
    public $controller;
    public $appController;
    public $db = array();
    public $dba = array();

    public function init($fileconf, $query)
    {
        $this->set('query',is_null($query) ? '/' :  $query);
        $this->loadConfiguration($fileconf);
        $this->loadXmlConfig('/configuration/parameters/parameter','parameters','name','value');
        $this->loadDatasources();
        $this->loadXmlConfig('/configuration/layouts/layout','layouts','name','path');
        $this->$router = new Router();        
        $this->$router->loadXml($this->$repo['xmlconfig'], '/configuration/routes/route');
        $this->$request = $this->$router->getRequest();
        $this->$router->addRoute('OsynapsyAssetsManager','/__OsynapsyAsset/?*','Osynapsy\\Core\\Helper\\AssetLoader','','Osynapsy');
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
        if (empty($this->$repo['xmlconfig'][$app]['controller'])) {
            return true;
        }
        //If app has applicationController instance it before recall route controller;
        $classControllerApp = str_replace(':','\\',$this->$repo['xmlconfig'][$app]['controller']);
        $this->$appController = new $classControllerApp(Kernel::$dba, $this->$router->getRoute());
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

    private  function loadDatasources()
    {
        foreach ($this->$repo['xmlconfig'] as $xml) {
            $nConn = 0;
            foreach ($xml->xpath('/configuration/datasources/db') as $e) {
                $par = (array) $e->attributes(); //['@attributes'];
                $connectionStr = (string) $e[0];
                $connectionSha = sha1($connectionStr);
                if (array_key_exists($connectionSha, $this->$db)) {
                    continue;
                }
                if (strpos($connectionStr,'oracle') !== false) {
                    $this->$db[$connectionSha] = new DbOci($connectionStr);
                } else {
                    $this->$db[$connectionSha] = new DbPdo($connectionStr);
                }               
                $this->$db[$connectionSha]->connect();
                if ($nConn === 0) {
                    $this->$dba = $this->$db[$connectionSha];
                }
                $nConn++;
            }
        }
    }

    public  function loadXmlConfig($xpath, $dest, $kkey, $kval)
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
        return mail($a,$subject,$body,$head," -f ".$from);
    }
    
    public  function pageNotFound($message = 'Page not found')
    {
        ob_clean();
        header('HTTP/1.1 404 Not Found');
        return $message;
    }
}
