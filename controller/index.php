<?php
!defined('IN_PTF') && exit('ILLEGAL EXECUTION');
/**
 * @file    index
 * @author  ryan <cumt.xiaochi@gmail.com>
 * @created Jun 27, 2012 6:24:01 PM
 */

require_once Pf::lib('Pdb');
Pdb::setConfig($config['db']);

require_once Pf::model('Message');

if ($is_post) {
    $name  = req('name');
    $email = req('email');
    $text  = req('text');
    if ($text && $name) {
        Message::post($name, $text, $email);
        redirect();
    }
}

// 简单的
require_once Pf::lib('Paginate');
$per_page = 20;
$p = req('p')?:1;
$paging = new Paginate($per_page, Message::count());
$paging->setCurPage($p);
$msgs = Message::listM(array(
    'limit' => $per_page,
    'offset' => $paging->offset()));

$view .= '?master';

$page['scripts'][] = 'widget';

?>