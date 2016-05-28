<?php
namespace Osynapsy\Bcl\Component;

use Osynapsy\Ocl\Component\TextBox as OclTextBox;

class TextBox extends OclTextBox
{
    public function __construct($name)
    {
        parent::__construct($name);
        $this->att('class','form-control',true);
    }
}