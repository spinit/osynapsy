<?php
namespace Osynapsy\Bcl\Component;

use Osynapsy\Ocl\Component\Button as OclButton;

class Button extends OclButton
{
    
    public function __construct($id, $type = 'button')
    {
        parent::__construct($id);
        $this->att('type',$type)->att('class','btn');
    }
    
    public function setAction($action, $parameters=null)
    {
        $this->att('class','cmd-execute',true)
             ->att('data-action',$action);
        if (!empty($parameters)) {
            $this->att('data-action-parameters',$parameters);
        }
        return $this;
    }    
}
