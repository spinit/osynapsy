<?php
namespace Osynapsy\Ocl\Component;

use Osynapsy\Ocl\Component\InputBox as InputBox;

//Field hidden
class HiddenBox extends InputBox
{
    public function __construct($name, $id=null)
    {
        parent::__construct('hidden', $name, $this->nvl($id, $name));
    }
}
