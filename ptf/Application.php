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
    /**
     * ['GET', '%^get/(\d+)$%', 'Index', 'getItem']
     * @var $rules
     */
    public $rules = array();

    /**
     * 运行框架
     * @return type
     */
    public function run()
    {
        $uri = $this->getRequestUri();

        $params = array();
        if ($this->rules) {
            // 解析规则（阻断性）
            foreach ($this->rules as $rule) {
                if ($this->macthMethod($rule[0]) && preg_match($rule[1], $url, $params)) {
                    $controller = $rule[2];
                    $action = $rule[3];
                    break;
                }
            }
        } else {
            $controller = 'index';
            $action = 'code404';
            $params = [];
        }

        $class = $controller.'Controller';
        require "controller/$class.php";
        $func = $action.'Action';
        $c = new $class();
        if (method_exists($c, 'init')) {
            $c->init();
        }
        return $c->{$func}($params);
    }

    public function macthMethod($method) {
        if ($method == NULL) {
            return TRUE;
        }
        if ($_SERVER['REQUEST_METHOD'] == $method) {
            return TRUE;
        }
        return false;
    }

    // write file content to dst
    public function getRequestUri() {
        $arr = explode('?', $_SERVER['REQUEST_URI']);
        return $arr[0];
    }

}
