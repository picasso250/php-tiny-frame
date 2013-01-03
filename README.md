php-tiny-frame
==============

Very small framework for website write in PHP.

很小的一个 PHP 框架。使用 MVC 模式。支持 SAE 。

我猜很多 PHP 程序员都坐过这种事情，写了个小框架，然后慢慢放弃，转而使用别人的框架（比较大的）。然而，这就是成长的过程，没有小框架，要我们如何理解大框架。

简介
-------

初衷就是写一个自己用的顺手，又很轻型的 PHP 框架。

所有的请求都是给 index.php 的，由 PHP 实现 router。

使用面向对象和面向过程的双重思想（会不会人格分裂？）在 M 部分使用 OO，在 C 的部分使用面向过程的思想。

这个框架参考了 鸡爷 的自用框架，也参考了 lazyPHP。在此一并表示感谢。

M 和 OO
-------

Model 层是用 OO 的思想写的，我尽力让它被调用的方式更加优雅。

比如传值

```php
$admin->delProduct($arg); // 应该传什么进去？
```

没问题，但一般来说，从前端传过来的是 id，那么我们可不可以这样呢？

```php
$admin->delProduct($id); // 一个字符串
```

如果这个可以，那么我们想要批量删除的时候：

```php
$admin->delProduct($arr_of_ids); // 一个数组
```

那么这三种调用方式应该都是可以的。

文件结构
--------

**M**: model

**V**: view

**C**: controller

model 是类。

所有的 Model 都可以继承了一个我已经写好的 BasicModel 类，里面有一些基本的增删改查的函数。

常用函数
--------

/app_core/function.php 中有一些常用函数：

```php
function _get($name);

function _post($name);

function _req($name);
```

类库
-----

都在文件夹 `/app_core/class` 下。

- **Pdb**

 数据库类

- **Paginate**

 翻页类

- **QqLogin**

 QQ 平台登录

- **SiteMap**

 生成 Google 的 SiteMap （还不完善）。

更多的思考
-----------

最近看了好多的框架。

原来 Java 考虑的好全面啊。

如何用好ORM？ORM和高效是否背道而驰？

还是得写ORM。

