<?php
namespace Osynapsy\Core\Network;

use Osynapsy\Core\Lib\Dictionary;

class RouteCollection extends Dictionary
{
    public function __construct()
    {
        $this->repo = array(
            'routes' => array()
        );
    }
    
    public function addRoute($id, $route, $controller, $templateId = null)
    {
        $param = array(
            'path' => $route,
            'controller' => $controller,
            'templateId' => $templateId
        );
        $this->set('routes.'.$id, $param);
    }
}
