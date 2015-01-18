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
     * ['GET', '%^get/(\d+)$%', function]
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
                    $func = $rule[2]
                    return $func($params);
                    break;
                }
            }
        } else {
            return $this->page404();
        }
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

    public function getRequestUri() {
        $arr = explode('?', $_SERVER['REQUEST_URI']);
        return $arr[0];
    }

    public function render($file, $data = [], $layout = null)
    {
        extract($data);
        if ($layout) {
            $_inner_ = $file;
            include $layout;
        } else {
            include $file;
        }
    }

}
