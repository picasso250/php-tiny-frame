<?php

function _get($name, $default = null)
{
    return isset($_GET[$name]) ? $_GET[$name] : $default;
}
function _post($name, $default = null)
{
    return isset($_POST[$name]) ? $_POST[$name] : $default;
}
function _req($name, $default = null)
{
    return isset($_REQUEST[$name]) ? $_REQUEST[$name] : $default;
}

/**
 * 运行路由
 * @prame $rules array of ['GET', '%^get/(\d+)$%', function]
 * @return type
 */
function run($rules, $missing = null)
{
    $uri = get_request_uri();

    if ($rules) {
        // 解析规则（阻断性）
        foreach ($rules as $rule) {
            if ($_SERVER['REQUEST_METHOD'] === $rule[0] && preg_match($rule[1], $url, $params)) {
                $func = $rule[2]
                return $func($params);
                break;
            }
        }
    } elseif ($missing) {
        return $page404();
    }
}

function get_request_uri() {
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

