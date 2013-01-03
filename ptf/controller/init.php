<?php
!defined('IN_PTF') && exit('ILLEGAL EXECUTION');
/**
 * @file    init
 * @author  ryan <cumt.xiaochi@gmail.com>
 */

$page['description'] = 'PHP Tiny Frame 很小很小的 PHP 框架';
$page['keywords'] = array('PHP', '开源', '框架', 'MVC');

if (is_mobile()) {
    $page['styles'][] = 'mobile';
} else {
    $page['styles'][] = 'mouse';
}

?>