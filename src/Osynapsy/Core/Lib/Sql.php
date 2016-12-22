<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Osynapsy\Core\Lib;

/**
 * Description of Sql
 *
 * @author Peter
 */
class Sql {
    //put your code here
    private $debug;
    private $part = [
        'select' => [' ', PHP_EOL],
        'from'   => [' ', PHP_EOL],
        'where'  => [' ', PHP_EOL],
        'on' => [' (', ')'.PHP_EOL]
    ];
    
    public function __construct($debug = false)
    {
        $this->debug = $debug;
    }
    
    public function __call($method, $params)
    {
        if (!is_array($params) || count($params) < 2) {
            $params = array($params, true);
        }
        $this->elements[$method] = $params;
        return $this;
    }
    
    public function __toString()
    {
        $string = '';
        foreach ($this->elements as $method => $params) {
            if (!$params[1]) {
                continue;
            }
            $params = $params[0];
            $string .= $method . $this->prefix($method);
            foreach ($params as $i => $par) {
                $string .= empty($i) ? '' : ',';
                $string .= is_string($par) ? addslashes($par) : $par;
            }
            $string .= $this->postfix($method);
        }

        return $this->debug ? '<pre>'. $string .'</pre>': $string;
    }
    
    private function prefix($method)
    {
        if (array_key_exists($method, $this->part)) {
            return $this->part[$method][0];
        }
        return ' ';
    }
    
    private function postfix($method)
    {
        if (array_key_exists($method, $this->part)) {
            return $this->part[$method][1];
        }
        return ' ';
    }
}
