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

function Service($name = null, $value = null)
{
    static $container;
    $num = func_num_args();
    if ($num === 1) {
        return $container[$name];
    } elseif ($num === 2) {
        $container[$name] = $value;
    }
}

function try_json_decode($str)
{
    $obj = json_decode($str, true);
    if (json_last_error()) {
        throw new Exception("json decode error", json_last_error());
    }
    return $obj;
}

function echo_json($data, $msg = '')
{
    if (is_int($data)) {
        $json = ['code' => $data, 'message' => $msg];
    } else {
        $json = ['code' => 0, 'data' => $data, 'message' => $msg ?: 'OK'];
    }
    header('Content-type: application/javascript');
    echo json_encode($json);
}

function json($data, $msg = '')
{
    if (is_int($data)) {
        $json = ['code' => $data, 'message' => $msg];
    } else {
        $json = ['code' => 0, 'data' => $data, 'message' => $msg ?: 'OK'];
    }
    header('Content-type: application/javascript');
    return json_encode($json);
}

/**
 * 运行框架
 * ['GET', '%^get/(\d+)$%', function, before]
 * @return type
 */
function run($rules, $page404 = null)
{
    $arr = explode('?', $_SERVER['REQUEST_URI']);
    $uri = $arr[0];

    $params = array();
    // 解析规则（阻断性）
    foreach ($rules as $rule) {
        if ($_SERVER['REQUEST_METHOD'] === $rule[0] && preg_match($rule[1], $uri, $params)) {
            if (isset($rule[3])) {
                $before = $rule[3];
                if ($before() === false) {
                    return;
                }
            }
            $func = $rule[2];
            return $func($params);
        }
    }
    if ($page404) {
        header('HTTP/1.1 404 Not Found');
        return $page404();
    }
}

function get_request_uri() {
    $arr = explode('?', $_SERVER['REQUEST_URI']);
    return $arr[0];
}

function render($file, $data = [], $layout = null)
{
    extract($data);
    if ($layout) {
        $_inner_ = $file;
        include $layout;
    } else {
        include $file;
    }
}

function redirect($url)
{
    header("Location: $url");
}


