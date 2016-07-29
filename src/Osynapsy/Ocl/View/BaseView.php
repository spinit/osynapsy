<?php
namespace Osynapsy\Ocl\View;

use Osynapsy\Core\Controller\Controller;
use Osynapsy\Ocl\Response\Html as HtmlResponse;
use Osynapsy\Ocl\Component\Component;

abstract class BaseView
{
    protected $components = array();
    protected $controller;
    protected $reponse;
    protected $db;
    
    public function __construct(Controller $controller, $title=null)
    {
        $this->controller = $controller;
        $this->request = $controller->request;
        $this->db = $controller->getDb();
        $this->response = $this->controller->response;
        //new HtmlResponse($this->request->get('page.templateId'), $this->db);
        if ($title) {
            $this->response->addContent($title,'title');
        }
    }
    
    public function get()
    {
        $this->init();
        //return $this->response;
    }

    public function setTitle($title)
    {
        $this->response->addContent($title,'title');
    }
    
    public function __toString()
    {
        return $this->get();
    }
    
    abstract public function init();
}
