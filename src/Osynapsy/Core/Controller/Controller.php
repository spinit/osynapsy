<?php
namespace Osynapsy\Core\Controller;

use Osynapsy\Core\Request\Request;
use Osynapsy\Ocl\Response\Html as HtmlResponse;
use Osynapsy\Core\Response\JsonResponse;
use Osynapsy\Core\Kernel;

abstract class Controller implements InterfaceController
{
    private $parameters;
    private $templateId;
    private $view;
    protected $keyCommand = 'k-cmd';
    protected $db;
    public $model;
    public $request;
    public $response;
    public $app;
    
    public function __construct(Request $request = null, $db = null, $appController = null)
    {
        $this->templateId = $request->get('page.templateId');
        $this->parameters = $request->get('page.parameters');
        $this->request = $request;
        $this->setDbHandler($db);
        $this->app = $appController;
        $this->init();
    }
    
    abstract public function indexAction();
    
    abstract public function init();

    public function deleteAction()
    {
        if ($this->model) {
            $this->model->delete();
        }
    }
    
    private function execCommand()
    {
        $this->response = new JsonResponse();
        $cmd = $_REQUEST[$this->keyCommand];
        //sleep(0.7);
        if (!method_exists($this, $cmd.'Action')) {
            $res = 'No action '.$cmd.' exist';
        } elseif (!empty($_REQUEST['actionParameters'])){
            $res = call_user_func_array(array($this,$cmd.Action),$_REQUEST['actionParameters']);
        } else {
            $res = $this->{$cmd.'Action'}();
        }
        if (!empty($res) && is_string($res)) {
            $this->response->error('alert',$res);
        }
        return $this->response;
    }

    public function getDb()
    {
        return $this->db;
    }
    
    public function getResponse()
    {
        if (!empty($_REQUEST[$this->keyCommand])) {
            return $this->execCommand();
        }        
        $this->response = new HtmlResponse();        
        if ($path = Kernel::get('layouts.'.$this->templateId)) {
            $this->response->template = $this->response->getBuffer($path, $this);            
        }
        if ($this->model) {
            $this->model->find();
        }
        $resp = $this->indexAction();
        if ($resp) {
            $this->response->addContent($resp);
        }
        return $this->response;
    }
    
    public function getParameter($key)
    {
        return (is_array($this->parameters) && array_key_exists($key,$this->parameters)) ? $this->parameters[$key] : null;
    }
    
    public function loadView($path, $params = array(), $return = false)
    {
        $params = array('Db' => $this->db, 'controller' => $this);
        $view = $this->response->getBuffer($path, $params);
        if ($return) {
            return $view;
        }
        $this->response->addContent($view);
    }
    
    public function saveAction()
    {
        if ($this->model) {
            $this->model->save();
        }
    }
    
    public function setDbHandler($db)
    {
        $this->db = $db;
    }
}
