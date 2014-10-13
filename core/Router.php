<?php

class Router
{
    public static function __callStatic($name, $args)
    {
        $method = strtoupper($name);
        list($url, $func) = $args;
        if (is_string($func)) {
            $func = (explode('@', $func));
        }
        App::app()->routerRules[] = [$name, $url, $func];
    }
}
