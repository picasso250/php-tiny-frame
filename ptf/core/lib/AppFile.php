<?php

/**
 * 现在看来这个也可以用来 js css 啥的
 * Usage: FrameFile::controller('index');
 * 应该有个CoreFile类？
 */

class AppFile
{
    public static function controller($name)
    {
        return APP_ROOT . 'controller' . DS . "$name.php";
    }

    public static function view($name)
    {
        return APP_ROOT . 'view' . DS . "$name.html";
    }

    public static function lib($name)
    {
        return CORE_ROOT . 'lib' . DS . "$name.app";
    }

    public static function js($name)
    {
        return APP_ROOT . 'view' . DS . 'js' . DS . "$name.js";
    }

    public static function css($name)
    {
        return APP_ROOT . 'view' . DS . 'js' . DS . "$name.css";
    }
}
