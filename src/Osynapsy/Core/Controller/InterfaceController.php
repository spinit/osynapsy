<?php
namespace Osynapsy\Core\Controller;

use Osynapsy\Core\Request\Request;

interface InterfaceController
{
    public function __construct(Request $request = null, $db = null);
    
    public function getResponse();
}