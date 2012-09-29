<?php
/**
 * @file    index
 * @author  ryan <cumt.xiaochi@gmail.com>
 * @created Jun 30, 2012 10:38:22 AM
 * app logic
 * 此框架由王霄池纯粹手写而成，当然参照了不少鸡爷的框架，也参照了 LazyPHP
 */

define('IN_PTF', 1);

require 'config/common.php';
require 'lib/function.php';

// 变量初始化
require 'core/init.php';

ob_start();
session_start();
date_default_timezone_set('PRC');

require Pf::controller('init');

if (isset($force_redirect)) { // 强制跳转 这个在整站关闭的时候很有用
    $controller = $force_redirect;
}
$view = $controller;

if (!file_exists(Pf::controller($controller))) {
    $controller = 'default'; // page 404
}

if (file_exists(_css($controller)))
    $page['styles'][] = $controller;
if (file_exists(_js($controller)))
    $page['scripts'][] = $controller;
include Pf::controller($controller); // 执行 controller

$arr = explode('?', $view);
if (count($arr) == 2 && $arr[1] == 'master') {
    $content = $arr[0];
    $view = 'master';
}
include smart_view($view); // 渲染 view
?>