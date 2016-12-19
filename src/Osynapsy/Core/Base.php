<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Osynapsy\Core;

use Osynapsy\Core\Env;
use Osynapsy\Core\Kernel;

/**
 * Description of Base
 *
 * @author ermanno
 */
class Base {
    //put your code here
    private static $instances = array();

    /**
     * Impostazione/Recupero oggetti globali
     * @param string $name
     * @return any
     */
    public function singleton($name)
    {
        $args = func_get_args();
        if(count($args)>1) {
            self::$instances[$name] = $args[1];
        }
        return @self::$instances[$name];
    }
    
    public function env()
    {
        $args = func_get_args();
        if (count($args)) {
            $this->singleton('env', $args[0]);
        }
        $util = $this->singleton('env');
        if (!$util) {
            $util = $this->singleton('env', new Env());
        }
        return $util;
    }
        
    /**
     * Method Factory per recuperare l'oggetto kernel
     */
    public function kernel()
    {
        $args = func_get_args();
        if (count($args)) {
            $kernel = $this->singleton('kernel', $args[0]);
        } else {
            $kernel = $kernel = $this->singleton('kernel');
            if (!$kernel) {
                $kernel = $this->singleton('kernel', new Kernel());
            }
        }
        return $kernel;
    }
}
