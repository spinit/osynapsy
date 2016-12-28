<?php
namespace Osynapsy\Core;

use Osynapsy\Core\Network\Router;
use Osynapsy\Core\Request\Request;
use Osynapsy\Core\Driver\DbPdo;
use Osynapsy\Core\Driver\DbOci;

class Kernel
{
    private static $repo = array(
        'xmlconfig' => array(),
        'events' => array(), 
        'layouts' => array()        
    );
    public static $router;
    public static $request;
    public static $controller;
    public static $appController;
    public static $db = array();
    public static $dba = array();

    public static function init($fileconf, $requestRoute)
    {        
        self::loadConfiguration($fileconf);
        self::$request = new Request($_GET, $_POST, array(), $_COOKIE, $_FILES, $_SERVER);
        self::$request->set('app.parameters', self::loadXmlConfig('/configuration/parameters/parameter','name','value'));        
        self::$request->set('app.layouts', self::loadXmlConfig('/configuration/layouts/layout','name','path'));        
        self::$router = new Router($requestRoute, self::$request);
        self::$router->loadXml(self::$repo['xmlconfig'], '/configuration/routes/route');       
        self::$router->addRoute('OsynapsyAssetsManager','/__assets/osynapsy/?*','Osynapsy\\Core\\Controller\\AssetLoader','','Osynapsy');
        if (self::runAppController()) {
            $response = self::runRouteController(self::$router->getRoute('controller'));
            if ($response !== false) {
                return $response;
            }
        }
        return self::pageNotFound();
    }
    
    private static function runAppController()
    {
        $app = self::$router->getRoute('application');      
        if (empty($app)) {
            return true;
        }
        self::loadDatasources("/configuration/app/$app/datasources/db");
        
        if (empty(self::$repo['xmlconfig'][$app]['controller'])) {
            return true;
        }
        //If app has applicationController instance it before recall route controller;
        $classControllerApp = str_replace(':','\\',self::$repo['xmlconfig'][$app]['controller']);
        self::$appController = new $classControllerApp(Kernel::$dba, self::$router->getRoute());
        return self::$appController->run();
    }
    
    private static function runRouteController($classController)
    {
        if (empty($classController)) {
            return false;
        }
        self::$controller = new $classController(self::$request, self::$dba, self::$appController);
        return (string) self::$controller->run();
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

    private static function loadDatasources($path = '/configuration/datasources/db')
    {
        foreach (self::$repo['xmlconfig'] as $xml) {            
            foreach ($xml->xpath($path) as $e) {                     
                $connectionStr = (string) $e[0];
                $connectionSha = sha1($connectionStr);
                if (array_key_exists($connectionSha, self::$db)) {
                    continue;
                }
                self::$db[$connectionSha] = self::getDbConnection($connectionStr);               
                self::$db[$connectionSha]->connect();
                if (empty(self::$dba)) {
                    self::$dba = self::$db[$connectionSha];
                }
            }
        }
    }

    private static function getDbConnection($connectionString)
    {        
        if (strpos($connectionString, 'oracle') !== false) {
            return new DbOci($connectionString);
        } 
        return new DbPdo($connectionString);
    }
    
    public static function loadXmlConfig($xpath, $kkey, $kval)
    {
        $result = array();
        foreach (self::$repo['xmlconfig'] as $xml) {
            foreach ($xml->xpath($xpath) as $e) {
                $result[$e[$kkey]->__toString()] = (isset($e[$kval]) ? $e[$kval]->__toString() : '');
            }
        }
        return $result;
    }

    public static function pageNotFound($message = 'Page not found')
    {
        ob_clean();
        header('HTTP/1.1 404 Not Found');
        return $message;
    }
}
