<?php
namespace Osynapsy\Core\Controller;

interface InterfaceApplication
{
    public function __construct($db, $route);
    
    public function run();
}
