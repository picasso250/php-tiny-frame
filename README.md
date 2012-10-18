php-tiny-frame
==============

Very small framework for website write in PHP.

很小的一个 PHP 框架。使用 MVC 模式。支持 SAE 。

我猜很多 PHP 程序员都坐过这种事情，写了个小框架，然后慢慢放弃，转而使用别人的框架（比较大的）。然而，这就是成长的过程，没有小框架，要我们如何理解大框架。

简介
-------

初衷就是写一个自己用的顺手，又很轻型的 PHP 框架。

所有的请求都是给 index.php 的，用 GET 参数 c 指定 controller。如果想要伪静态化，请使用 .htaccess 或者 config.yaml（已经有实例代码）。

所以，登录页面的地址看起来就是这样的 `/index.php?c=login`

文件结构
-------

M: model

V: view

C: controller

MVC 都使用 PHP 文件，其中 model 是类。

所有的 Model 都可以继承了一个我已经写好的 Model 类，里面有一个基本的构造函数。

常用函数
--------

/lib/function.php 中是一些常用函数：

```php
// 防止未定义错误
function i(&$param, $or='') {
    return isset($param)? $param : $or;
}

// 防止写那么长的函数名
// 任何来自用户的输入要显示在页面上都要经过这个函数过滤
function h($str) {
    return htmlspecialchars($str);
}
```

类库
-----

都在文件夹 lib 下。

- Pdb

 数据库类

- Paginate

 翻页类

- QqLogin

 QQ 平台登录

- SiteMap
 生成 Google 的 SiteMap （还不完善）。