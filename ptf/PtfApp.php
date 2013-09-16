<?php

namespace ptf;

/**
 * 这个文件定义了一系列全局函数，用来操作APP
 * @author  ryan <cumt.xiaochi@gmail.com>
 */
class PtfApp
{
    function init()
    {
        ob_start();
        session_start();
        date_default_timezone_set('PRC');

        // auto require when using class (model or lib)
        spl_autoload_register(function ($classname) {
            $filename = str_replace('\\', '/', $classname) . '.php';
            $model_file = APP_ROOT . 'model' . '/' . $filename;
            $lib_file = CORE_ROOT . 'lib' . '/' . $filename;
            if (file_exists($model_file)) 
                require_once $model_file;
            elseif (file_exists($lib_file))
                require_once $lib_file;
        });
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