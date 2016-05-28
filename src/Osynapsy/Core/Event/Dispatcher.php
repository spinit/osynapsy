<?php
namespace Osynapsy\Core\Event;

class Dispatcher
{
    private $repo = array();
    private $response;
    
    public function __construct($response)
    {
        $this->response = $response;
    }
    
    public function addListener($event, $listener)
    {
        if (!array_key_exists($event,$this->repo)) {
            $this->repo[$event] = array($listener);
            return;
        }
        $this->repo[$event][] = $listener;
    }
    
    //Eseguo gli eventi
    public function dispatch($event, $par=array())
    {
       if (!array_key_exists($event,$this->repo)) {
            $this->response->error('alert','Recall event '.$event.' inexistent');
       }
       foreach ($this->repo[$event] as $i => $listener) {
            try {
                if ($err = $listener()){
                    $this->response->error('alert',$err);
                }
            } catch (Exception $e){
                $this->response->error('alert',$e->getMessage());
            }
        }
    }
}
