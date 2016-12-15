<?php
ob_start();
if (is_file('vendor/autoload.php')) {
    include('vendor/autoload.php');
} else {
    include('src/Osynapsy/autoload.php');
}

function array_get($array, $field, $default = '')
{
    if (!isset($array[$field])) {
        return $default;
    }
    return $array[$field];
}
