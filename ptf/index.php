<?php
/**
 * @file    index
 * @author  ryan <cumt.xiaochi@gmail.com>
 * @created Jun 30, 2012 10:38:22 AM
 * app logic
 * 此框架由王霄池纯粹手写而成，当然参照了不少鸡爷的框架，也参照了 LazyPHP
 */

// 打开错误提示
ini_set('display_errors', 1); // 在 SAE 上 ini_set() 不起作用，但也不会报错
error_reporting(E_ALL);

define('IN_APP', 1);

define('DS', DIRECTORY_SEPARATOR);
define('APP_ROOT', __DIR__ . DS);
define('CORE_ROOT', APP_ROOT . 'core' . DS);

include APP_ROOT . 'config/common.php';

// if not debug, mute all error reportings
if (!(defined('DEBUG') ? DEBUG : 0)) {
    ini_set('display_errors', 0);
    error_reporting(0);
}

require CORE_ROOT . 'function.php';
require CORE_ROOT . 'app.php';
init_var();
init_env();

require CORE_ROOT . 'BasicModel.php'; // 似乎可以到autoloader里面去

$user_lib_file = APP_ROOT . 'lib' . DS . 'function.php';
if (file_exists($user_lib_file))
    require_once $user_lib_file;

$basic_controller_file = AppFile::controller('basic');
if (file_exists($basic_controller_file))
    include $basic_controller_file;

$controller_file = AppFile::controller($controller);
if (!file_exists($controller_file)) {
    $controller = 'default'; // page 404
    $controller_file = AppFile::controller($controller);
}

$view = $controller; // default view

// include and execute controller
include $controller_file;
$controller_class = ucfirst($controller) . 'Controller';
$c = new $controller_class();
if ($target) {
    $c->$target($argument);
}
