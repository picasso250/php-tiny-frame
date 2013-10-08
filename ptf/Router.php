<?php

namespace ptf;

/**
 * 路由类
 * 
 * 可以指定路由规则
 * 
 * @author xiaochi
 */
class Router {

    private $rules = array();

    public function __construct()
    {
    }

    /**
     * 分发函数
     * 调用此函数时执行 action 方法
     * default indexController::indexAction()
     */
    public function dispatch($url)
    {
        $param = array();
        if ($this->rules) {
            // 解析规则（阻断性）
            foreach ($this->rules as $rule) {
                $method_match = $rule['method'] === null || in_array($_SERVER['REQUEST_METHOD'], $rule['method']);
                if ($method_match && preg_match($rule['regex'], $url, $matches)) {
                    
                    // 提取参量
                    foreach ($matches as $key => $value) {
                        if ($key) {
                            $name = $rule['names'][$key-1];
                            $param[$name] = $value;
                        }
                    }

                    $controller = $rule['controller'];
                    $action = $rule['action'];
                    foreach ($rule['names'] as $name) {
                        $controller = preg_replace('/\{\$'.$name.'\}/', $param[$name], $controller);
                        $action = preg_replace('/\{\$'.$name.'\}/', $param[$name], $action);
                    }

                    break;
                }
            }
        } else {
            // 默认的路由规则 /controller/action
            // 默认 404 page404Controller::indexAction()
            $arr = explode('/', $url);
            unset($arr[0]);
            $controller = isset($arr[1]) && $arr[1] ? $arr[1] : 'index';
            $action = isset($arr[2]) && $arr[2] ? $arr[2] : 'index';
        }
        $result = array(array($controller, $action), $param);
        return $result;
    }

    /**
     * 新增一条路由规则
     * $router->rule('GET', '/user/[:id]', array('user', 'view'))
     * $router->rule('POST', '/user/[:id]', array('user', 'edit'))
     * $router->rule('/user/', array('user', 'list'))
     * $router->rule('*', array('page404', 'index'))
     * 第一个参数可以不填
     * @param $method HTTP方法 'GET'|'POST'|'PUT'|'DELETE'
     * @param $rule URL规则，如 /user/[:id]，其中方括号冒号开头代指一个参数，放到 $_GET 数组中
     * @param $ca 数组 array('控制器', 'Action')
     * 
     * @author wangxiaochi <cumt.xiaochi@gmail.com>
     */
    public function rule()
    {
        $args_num = func_num_args();
        if ($args_num == 2) {
            return $this->_rule(null, func_get_arg(0), func_get_arg(1));
        }
        if ($args_num == 3) {
            return $this->_rule(func_get_arg(0), func_get_arg(1), func_get_arg(2));
        }
    }

    public function rules($rules)
    {
        foreach ($rules as $rule) {
            $this->_rule($rule[0], $rule[1], $rule[2]);
        }
    }

    private function _rule($method, $rule, $ca)
    {
        if ($rule === '*') {
            $regex = '.*';
        } else {
            $regex = preg_replace('/\[:[a-zA-Z][a-zA-Z\d_]*\]/', '([^/]+)', $rule);
        }
        preg_match_all('/\[:([a-zA-Z][a-zA-Z\d_]*)\]/', $rule, $matches);
        $this->rules[] = array(
            'method' => is_string($method) ? array($method) : $method,
            'regex' => '/^'.str_replace('/', '\/', $regex).'$/',
            'names' => $matches[1],
            'controller' => $ca[0],
            'action' => $ca[1],
        );
        return $this;
    }
}

