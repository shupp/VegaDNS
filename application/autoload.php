<?php

if (!function_exists('__autoload')) {
    function __autoload($class) {
        $file = str_replace('_', '/', $class) . '.php';
        require_once $file;
    }
    spl_autoload_register('__autoload');
}
