<?php
!defined('IN_PTF') && exit('ILLEGAL EXECUTION');
/**
 * @file    master
 * @author  ryan <cumt.xiaochi@gmail.com>
 * @created
 */
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <title><?php echo $page['title']; ?></title>
        <meta property="qc:admins" content="245072732114631611006375" />
        <meta name="description" content="<?php echo i($page['description']); ?>" />
        <meta name="keywords" content="<?php echo implode(', ', i($page['keywords'], array())); ?>" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <?php
        echo css_node('reset'), "\n";
        echo css_node('style'), "\n";
        foreach ($page['styles'] as $style) {
            echo css_node($style), "\n";
        }
        ?>
    </head>
    <body>
        <div class="append_parent"></div>
        <div class="header">
            <?php include smart_view('header'); ?>
        </div>
        <div class="content">
            <?php include smart_view($content); ?>
        </div>
        <div class="footer">
            <?php include smart_view('footer'); ?>
        </div>
        <?php
        echo js_node('jquery-1.7.2.min'), "\n";
        echo js_var('_G', array('ROOT'=>ROOT)), "\n";
        echo js_node('every');
        foreach ($page['scripts'] as $script) {
            echo js_node($script), "\n";
        }
        ?>
    </body>
</html>
