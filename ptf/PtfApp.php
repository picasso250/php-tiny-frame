<?php

namespace ptf;

use ptf\Router;

/**
 * 这个文件定义了一系列全局函数，用来操作APP
 * @author  ryan <cumt.xiaochi@gmail.com>
 */
class PtfApp
{
    public $app_root;

    function init()
    {
        ob_start();
        session_start();
        date_default_timezone_set('PRC');

        if ($app_root === null) {
            $app_root = dirname(__DIR__);
        }

        // auto require when using class (model)
        spl_autoload_register(function ($classname) use ($this) {
            $filename = str_replace('\\', '/', $classname) . '.php';
            $model_file = $this->app_root . 'model' . '/' . $filename;
            if (file_exists($model_file)) 
                require_once $model_file;
        });

        $this->router = new Router();
    }

    public function run()
    {
        $req_uri = reset(explode('?', $_SERVER['REQUEST_URI']));

        list($call, $param) = $this->router->dispatch($req_uri);
        if (is_array($call)) {
            $class = $call[0].'Controller';
            $func = $call[1].'Action';
            $c = new $class;
            $c->viewRoot = dirname(__DIR__).'/view';
            foreach ($param as $key => $value) {
                $c->$key = $value;
            }
            return $c->$func();
        } else {
            return $call($param);
        }
    }
}