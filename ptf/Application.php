<?php

namespace ptf;

use \Exception;
use ptf\Router;

/**
 * 这个文件定义了一系列全局函数，用来操作 APP
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
            $file_name = str_replace('\\', '/', $classname) . '.php';

            if (preg_match('/Dao$/', $classname)) {
                $file = "$root/dao/$file_name";
                require $file;
                return;
            }

            if (preg_match('/Controller$/', $classname)) {
                $controller_file = "$root/controller/$file_name";
                require $controller_file;
                return;
            }

            $file = "$root/entity/$file_name";
            if (file_exists($file)) {
                require $file;
            }
        });

        $this->router = new Router();
        $this->router->rules($this->config['routers']);
    }

    /**
     * 运行框架
     * @return type
     */
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
            $c->app = $this;
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

    // write file content to dst
    public function writeUpload($content, $file_name) {
        if (isset($_SERVER['HTTP_APPNAME'])) {
            return saeUpload($content, $file_name);
        } else {
            $dir = "/data/upload/".date('Ymd');
            $absolute_dir = $this->root.$dir;
            $this->mkdir($absolute_dir);
            file_put_contents("$absolute_dir/$file_name", $content);
            return "$dir/$file_name";
        }
    }
    
    protected function mkdir($dir)
    {
        if (!file_exists($dir)) {
            $rs = mkdir($dir, 0777, true);
            if (!$rs) {
                throw new Exception("unable to mkdir $dir", 1);
            }
            return $rs;
        }
        return true;
    }

    public function saeUpload($content, $file_name)
    {
        $up_domain = UP_DOMAIN;
        $s = new SaeStorage();
        $s->write($up_domain , $file_name , $content);
        return $s->getUrl($up_domain ,$file_name);
    }
}
