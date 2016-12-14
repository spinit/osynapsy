<?php
namespace Osynapsy\Bcl\Component;

use Osynapsy\Ocl\Component\Component;
use Osynapsy\Ocl\Component\HiddenBox;
use Osynapsy\Core\Lib\Tag as Tag;
use Osynapsy\Bcl\Component\Panel as Panel;
use Osynapsy\Bcl\Component\Tab;
use Osynapsy\Bcl\Component\Alert;

class Form extends Component
{
    private $components = array();
    private $head;
    private $alert;
    private $alertCount=0;
    private $body;
    private $foot;

    public function __construct($name, $mainComponent = 'Panel', $tag = 'form')
    {
        parent::__construct($tag, $name);
        //Form setting
        $this->att('name',$name)
             ->att('method','post')
             ->att('role','form');
        $mainComponent = '\\Osynapsy\\Bcl\\Component\\'.$mainComponent;
        //Body setting
        $this->body = new $mainComponent($name.'_panel', 'div');
        $this->body->par('label-position','inside');
        $this->body->tagdep =& $this->tagdep;        
    }
    
    protected function __build_extra__()
    {
        if ($this->head) {
            $this->add(new Tag('div'))
                 ->att('class','block-header')
                 ->add($this->head);
        }
        
        if ($this->alert) {
            $this->add($this->alert);
        }
        
        $container = $this->add(new Tag('div'))->att('class','content');
        $container->add($this->body);
        //Append foot
        if ($this->foot) {
            //$this->foot('<div style="clear : both;"></div>');
            $this->body->put('',$this->foot->get(),10000,10,10,1);
            //$container->add('<br>');
            //$container->add($this->foot);
        }
    }
    
    public function addCard($title)
    {
        $this->body->addCard($title);
    }
    
    public function head($obj)
    {
        //Head setting
        if (empty($this->head)) {
            $this->head = new Tag('dummy');
        } 
        $this->head->add($obj);
        return is_object($obj) ? $obj : $this->head;
    }
    
    public function alert($label, $type='danger')
    {
        if (empty($this->alert)) {
            $this->alert = new Tag('div');
            $this->alert->att('class','normalheader transition animated fadeIn');
        }
        $icon = '';
        switch ($type) {
            case 'danger':
                $icon = '<span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span><span class="sr-only">Error:</span>';
                break;
        }
        $alert = new Alert('al'.$this->alertCount, $icon.' '.$label, $type);
        $alert->att('class','alert-dismissible text-center',true)
              ->add(' <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>');
        $this->alert->add($alert);
        $this->alertCount++;
        return $this->alert;
    }
    
    public function foot($obj)
    {
        if (empty($this->foot)) {
            $this->foot = new Tag('div');
        }
        $this->foot->add($obj);
        return is_object($obj) ? $obj : $this->foot;
    }
    
    public function getPanel()
    {
        return $this->body;
    }
    
    public function put($lbl, $obj, $x=0, $y=0, $width=1, $offset=null, $class='')
    {
        $this->body->put($lbl, $obj, $x, $y, $width, $offset, $class);
        return $this->body;
    }
    
    public function setCommand($delete=false, $save=true, $back=true)
    {
        if ($save) {
            $this->foot(new Button('btn_save'))
                 ->att('class','cmd-save btn btn-primary pull-right')
                 ->att('style','width: 100px; margin-right: 10px;')
                 ->add(($save === true ? '<span class="glyphicon glyphicon-floppy-disk"></span> Salva' : $save));
        }
        
        if ($delete) {
            $this->foot(new Button('btn_delete'))
                 ->att('class','btn btn-danger pull-right cmd-delete')
                 ->att('style','width: 100px; margin-right: 10px;')
                 ->add('<span class="glyphicon glyphicon-trash"></span> Elimina');
        }

        if ($back) {
            $this->foot(new Button('btn_back'))
                 ->att('class','cmd-back btn btn-default pull-left')
                 ->att('style','margin-right: 10px; width: 100px;')
                 ->add('<span class="glyphicon glyphicon-chevron-left"></span> Indietro');
        }
    }
    
    public function setType($type)
    {
        if ($type == 'horizontal') {
            $this->att('class','form-horizontal',true);
        }
        $this->body->setType($type);
    }
    
    public function setTitle($title, $subTitle=null)
    {
        if (!empty($title)) {
            /*$this->head(
                '<a class="small-header-action" href="">
                    <div class="clip-header">
                        <i class="fa fa-arrow-up"></i>
                    </div>
                </a>'
            );*/
            $this->head(new Tag('h2'))
                 ->att('class','font-light m-b-xs')
                 ->add($title);
        }
        
        if (!empty($subTitle)) {
            $this->head('<small>'.$subTitle.'</small>');
        }
    }

}
