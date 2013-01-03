<?php
!defined('IN_PTF') && exit('ILLEGAL EXECUTION');
/**
 * @file    index
 * @author  ryan <cumt.xiaochi@gmail.com>
 * @created Jul 17, 2012 9:51:11 AM
 */
?>
<div class="introduction">PTF, PHP Tiny Framework, 是一个很小很小的一个 PHP 框架，使用 MVC 模式。因此很简单。<br />
    <a href="https://github.com/picasso250/php-tiny-frame">On GitHub</a>
</div>
<div class="post">
    <h2>留言板</h2>
    <form method="post">
        <div>
            <span class="intro">
                <strong>*</strong><label for="name">姓名</label>
            </span>
            <input type="text" name="name" id="name" />
        </div>
        <div>
            <span class="intro">
                <label for="email">邮件</label>
            </span>
            <input type="email" name="email" id="email" />
            <span>（不会显示在下面）</span>
        </div>
        <div>
            <strong>*</strong>
            <label for="text">留言内容</label>
            <textarea name="text" id="text" class="" placeholder="写下留言吧"></textarea>
        </div>
        <input type="submit" value="提交" />
    </form>
</div>
<?php include smart_view('messages'); ?>