<?php
ob_start();

include('vendor/autoload.php');

function array_get($array, $field, $default = '')
{
    if (!isset($array[$field])) {
        return $default;
    }
    return $array[$field];
}
