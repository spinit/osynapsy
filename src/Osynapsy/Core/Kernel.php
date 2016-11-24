<?php
namespace Osynapsy\Core;

use Osynapsy\Core\Lib\Dictionary;
use Osynapsy\Core\Network\Router;
use Osynapsy\Core\Driver\DbPdo;
use Osynapsy\Core\Driver\DbOci;

class Kernel
{
    private static $repo = array(
        'xmlconfig' => array(),
        'events' => array(), 
        'layouts' => array(),
        'query' => null
    );
    public static $router;
    public static $request;
    public static $controller;
    public static $appController;
    public static $db = array();
    public static $dba = array();

    public static function init($fileconf, $query)
    {
        self::set('query',is_null($query) ? '/' :  $query);
        self::loadConfiguration($fileconf);
        self::loadXmlConfig('/configuration/parameters/parameter','parameters','name','value');
        self::loadDatasources();
        self::loadXmlConfig('/configuration/layouts/layout','layouts','name','path');
        self::$router = new Router();
        self::$router->loadXml(self::$repo['xmlconfig'], '/configuration/routes/route');
        self::$request = self::$router->getRequest();
        
        $app = self::$router->getRoute('application');                   
        $run = true;
        //If app has applicationController instance it before recall route controller;
        if (!empty($app) && !empty(self::$repo['xmlconfig'][$app]['controller'])) {
            $classControllerApp = str_replace(':','\\',self::$repo['xmlconfig'][$app]['controller']);
            self::$appController = new $classControllerApp(
                Kernel::$dba,
                self::$router->getRoute()
            );
            $run = self::$appController->run();
        }
        if ($run && $classController = self::$router->getRoute('controller')) {
            file_put_contents('/var/tmp/cart.txt', $classController);
            self::$controller = new $classController(
                self::$request,
                self::$dba, 
                self::$appController
            );
            return (string) self::$controller->getResponse();
        }
        return self::pageNotFound();
    }

    private static function loadConfiguration($path)
    {
        if (!is_file($path)) {
            return;
        }
        self::$repo['xmlconfig'][0] = simplexml_load_file($path);
        if (!empty(self::$repo['xmlconfig'][0]) && self::$repo['xmlconfig'][0]->app) {
            foreach (self::$repo['xmlconfig'][0]->app[0] as $e) {
                $appName = $e->getName();
                $appConf = filter_input(\INPUT_SERVER,'DOCUMENT_ROOT').'/../vendor/'.str_replace('_','/',$appName).'/etc/config.xml';                
                if (is_file($appConf)) {
                    self::$repo['xmlconfig'][$appName] = simplexml_load_file($appConf);
                }
            }
        }
    }

    private static function loadDatasources()
    {
        foreach (self::$repo['xmlconfig'] as $xml) {
            $nConn = 0;
            foreach ($xml->xpath('/configuration/datasources/db') as $e) {
                $par = (array) $e->attributes(); //['@attributes'];
                $connectionStr = (string) $e[0];
                $connectionSha = sha1($connectionStr);
                if (array_key_exists($connectionSha, self::$db)) {
                    continue;
                }
                if (strpos($connectionStr,'oracle') !== false) {
                    self::$db[$connectionSha] = new DbOci($connectionStr);
                } else {
                    self::$db[$connectionSha] = new DbPdo($connectionStr);
                }               
                self::$db[$connectionSha]->connect();
                if ($nConn === 0) {
                    self::$dba = self::$db[$connectionSha];
                }
                $nConn++;
            }
        }
    }

    public static function loadXmlConfig($xpath, $dest, $kkey, $kval)
    {
        foreach (self::$repo['xmlconfig'] as $xml) {
            foreach ($xml->xpath($xpath) as $e) {
                self::$repo[$dest][$e[$kkey]->__toString()] = (isset($e[$kval]) ? $e[$kval]->__toString() : '');
            }
        }
    }

    public static function set($p,$v)
    {
        $ksearch = explode('.',$p);
        $klast   = count($ksearch)-1;
        $target = &self::$repo;
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
            return self::$repo;
        }
        $ksearch = explode('.',$p);
        $target = self::$repo;
        foreach ($ksearch as $k) {
            if (!is_array($target)) {
                return $target;
            }
            $target = array_key_exists($k, $target) ? $target[$k] : null;
        }        
        return $target;
    }

    public static function sendEmail($from,$a,$subject,$body,$html=false)
    {
        $head = "From: $from\r\n".
                "Reply-To: $from\r\n".
                "X-Mailer: PHP/".phpversion()."\n";
        if ($html)
        {
          $head .= "MIME-Version: 1.0\n";
          $head .= "Content-Type: text/html; charset=\"iso-8859-1\"\n";
          $head .= "Content-Transfer-Encoding: 7bit\n\n";
        }
        return mail($a,$subject,$body,$head," -f ".$from);
    }
    
    public static function pageNotFound($message = 'Page not found')
    {
        ob_clean();
        header('HTTP/1.1 404 Not Found');
        return $message;
    }
}
