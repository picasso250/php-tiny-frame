<?php

namespace ptf;

use ptf\Router;

/**
 * 这个文件定义了一系列全局函数，用来操作APP
 * @author  ryan <cumt.xiaochi@gmail.com>
 */
class Application
{
    public $root;

    private $config = array();

    public function config($config)
    {
        $this->config = $config;
    }

    public function init()
    {
        ob_start();
        session_start();
        date_default_timezone_set('PRC');

        if ($this->root === null) {
            $this->root = dirname(__DIR__);
        }
        $root = $this->root;

        // auto require when using class (model)
        spl_autoload_register(function ($classname) use ($root) {
            $filename = str_replace('\\', '/', $classname) . '.php';
            $model_file = "$root/model/$filename";
            if (file_exists($model_file)) {
                require_once $model_file;
                return;
            }
            $controller_file = "$root/controller/$filename";
            if (file_exists($controller_file)) {
                require_once $controller_file;
            }
        });

        $this->router = new Router();
        $this->router->rules($this->config['routers']);
    }

    public function run()
    {
        $this->init();

        $req_uri = reset(explode('?', $_SERVER['REQUEST_URI']));

        list($call, $param) = $this->router->dispatch($req_uri);
        if (is_array($call)) {
            $class = $call[0].'Controller';
            $func = $call[1].'Action';
            $c = new $class;
            $c->view_root = "$this->root/view";
            $c->config = $this->config;
            if (method_exists($c, 'init')) {
                $c->init();
            }
            foreach ($param as $key => $value) {
                $c->$key = $value;
            }
            return $c->{$func}();
        } else {
            return $call($param);
        }
    }
}
