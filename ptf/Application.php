<?php

namespace ptf;

use \Exception;
use ptf\Router;

/**
 * 程序
 * 用来操作 APP
 * @example $app = new Appplication();$app->run();
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

    /**
     * 初始化
     * @return type
     */
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

        $req_uri = $this->getRequestUri();

        list($call, $param) = $this->router->dispatch($req_uri);
        if (is_array($call)) {
            $class = $call[0].'Controller';
            $func = $call[1].'Action';
            $this->view_root = "$this->root/view";
            $c = new $class($this);
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
    public function getRequestUri() {
        $arr = explode('?', $_SERVER['REQUEST_URI']);
        return $arr[0];
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

    /**
     * 将文件内容写到到SAE
     * @param string $domain 域
     * @param string $content 文件内容
     * @param string $file_name 文件名
     * @return string 上传后的地址
     */
    public function putContentsToSae($domain, $content, $file_name = null)
    {
        $s = new SaeStorage();
        $s->write($domain , $file_name , $content);
        return $s->getUrl($domain ,$file_name);
    }
}
