<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Osynapsy\Core;

/**
 * Description of Env
 * Classe proxy per le chiamate di sistema
 * @author ermanno
 */
class Env
{
    public function __call($name, $arguments)
    {
        return call_user_func_array($name, $arguments);
    }
}
