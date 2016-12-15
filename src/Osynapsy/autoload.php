<?php
namespace Osynapsy;

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

spl_autoload_register(function ($class)
{
    $root = __DIR__;

    $ns = __NAMESPACE__.'\\';
    if (substr($class,0,strlen($ns)) != $ns) {
        return;
    }

    $class = substr($class, strlen($ns));

    $list_class = explode(DIRECTORY_SEPARATOR, str_replace('\\', DIRECTORY_SEPARATOR, $class));
    $path_class = $root;
    foreach($list_class as $item) {
        $file_init = $path_class . DIRECTORY_SEPARATOR . '__init__.php';
        if (is_file($file_init)) {
            require_once($file_init);
        }
        $path_class .=  DIRECTORY_SEPARATOR . $item;
    }
    $path_class .= '.php';

    if (file_exists($path_class)) {
        require_once $path_class;
    }
});


