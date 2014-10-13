<?php

/**
 * 路由类
 * 
 * 可以指定路由规则
 * 
 * @author xiaochi
 */
class Route {

    private static $rules = array();
    private static $controllerRules = [];

    /**
     * 分发函数
     * 调用此函数时执行 action 方法
     * default indexController::indexAction()
     */
    public static function dispatch($url)
    {
        if ($this->segment !== null) {
            return $this->dispatchBySegment($url);
        } else {
            return $this->dispatchByRule($url);
        }
    }

    public static function dispatchDefault($url)
    {
        // 默认的路由规则 /controller/action
        // 默认 404 page404Controller::indexAction()
        $arr = explode('/', $url);
        unset($arr[0]);
        $controller = isset($arr[1]) && $arr[1] ? $arr[1] : 'index';
        $action = isset($arr[2]) && $arr[2] ? $arr[2] : 'index';
        $result = array(array($controller, $action), array());
        return $result;
    }

    public static function dispatchByRule($url)
    {
        $params = array();
        if (self::$rules) {
            // 解析规则（阻断性）
            foreach (self::$rules as $rule) {
                list($method, $urlPattern, $info) = $rule;
                $is_method_match = $this->macthMethod($method);
                if ($is_method_match && preg_match($urlPattern, $url, $matches)) {

                    // 提取参量
                    foreach ($matches as $key => $value) {
                        if ($key) {
                            $params[] = $value;
                        }
                    }

                    break;
                }
            }
        } else {
            return $this->dispatchDefault();
        }
        $result = array(array($controller, $info), $params);
        return $result;
    }
    
    public function macthMethod($methods) {
        if ($methods == NULL) {
            return TRUE;
        }
        if (in_array($_SERVER['REQUEST_METHOD'], $methods)) {
            return TRUE;
        }
        return false;
    }

    /**
     * 新增一条路由规则
     * Router::get('/user/{:id}', 'UserController@showProfile')
     * Route::get('foo', array('uses' => 'FooController@method',
     *                                         'as' => 'name'));
     * @param $rule URL规则，如 /user/[:id]，其中方括号冒号开头代指一个参数，放到 $_GET 数组中
     * @param $func ClassNameOfController@actionMethod
     * 
     * @author wangxiaochi <cumt.xiaochi@gmail.com>
     */
    public static function __callStatic($name, $args)
    {
        $method = strtoupper($name);
        list($url, $func) = $args;
        if (!is_array($func) && !is_callable($func)) {
            if (is_string($func)) {
                $func = ['uses' => $func];
            }
        }
        self::$routerRules[] = [$method, self::_rule($url), $func];
    }

    private static function _rule($method, $rule, $ca)
    {
        if ($rule === '*') {
            $regex = '.*';
        } else {
            $regex = preg_replace('/\{[a-zA-Z][a-zA-Z\d_]*\}/', '([^/]+)', $rule);
        }
        preg_match_all('/\[:([a-zA-Z][a-zA-Z\d_]*)\]/', $rule, $matches);
        return '/^'.str_replace('/', '\/', $regex).'$/',
    }
    public static function controller($url, $controller)
    {
        self::$controllerRules[] = [$url, $controller];
    }

    public static function resource($url, $controller, $opts = [])
    {
        if (strpos($url, '.')) {
            $url = str_replace('.', '/{id}/', $url);
        }
        $rules = [
            'index' => ['GET', "/$url", ['uses' => "$controller@index", 'as' => 'index']],
            'create' => ['GET', "/$url/create", ['uses' => "$controller@create", 'as' => 'create']],
            'store' => ['POST', "/$url/store", ['uses' => "$controller@store", 'as' => 'store']],
            'show' => ['GET', "/$url/{id}/show", ['uses' => "$controller@show", 'as' => 'show']],
            'edit' => ['GET', "/$url/{id}/edit", ['uses' => "$controller@edit", 'as' => 'edit']],
            'update' => ['PUT', "/$url/{id}", ['uses' => "$controller@update", 'as' => 'update']],
            'update' => ['PATCH', "/$url/{id}", ['uses' => "$controller@update", 'as' => 'update']],
            'destory' => ['DELETE', "/$url/{id}", ['uses' => "$controller@destory", 'as' => 'destory']],
        ];
        $ruleKeys = array_keys($rules);
        if (isset($opts['only'])) {
            $ruleKeys = array_intersect($ruleKeys, $opts['only']);
        }
        if (isset($opts['except'])) {
            $ruleKeys = array_diff($ruleKeys, $opts['except']);
        }
        foreach ($ruleKeys as $key) {
            $rule = $rules[$key];
            $method = get_called_class().'::'.array_shift($rule);
            call_user_func_array($method, $rule);
        }
    }

}

