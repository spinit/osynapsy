<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Osynapsy\Core;

/**
 * Description of Base
 *
 * @author ermanno
 */
class Base
{
    //put your code here
    private static $instances = array();
    
    private static $instanceDefault = array(
        'env'=>'\\Osynapsy\\Core\\Env',
        'kernel'=>'\\Osynapsy\\Core\\Kernel'
    );

    /**
     * Impostazione/Recupero oggetti globali
     * Se si passa un array siconfigurano le classi di default da utilizzare per inizializzare
     * determinati elementi
     * 
     * @param string $name
     * @return any
     */
    public function singleton($name)
    {
        if (is_array($name)) {
            foreach($name as $k=>$v) {
                self::$instanceDefault[$k] = $v;
            }
            return;
        }
        $args = func_get_args();
        if(count($args)>1) {
            self::$instances[$name] = $args[1];
        }
        if (!isset(self::$instances[$name]) and isset(self::$instanceDefault[$name])) {
            $class = self::$instanceDefault[$name];
            self::$instances[$name] = new $class;
        }
        return @self::$instances[$name];
    }
}
