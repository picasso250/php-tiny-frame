php-tiny-frame
==============

Very small framework for website write in PHP.

一个小而简单的 PHP 框架。使用 MVC 模式。支持 SAE 。

（我猜很多 PHP 程序员都坐过这种事情，写了个小框架，然后慢慢放弃，转而使用别人的框架（比较大的）。或许这就是成长，没有小框架，要我们如何理解大框架？）

我的初衷就是写一个自己用的顺手，又很轻型的 PHP 框架。这个框架参考了 **鸡爷** 的自用框架，也参考了 [LazyPHP3](https://github.com/easychen/LazyPHP)。在此一并表示感谢。

这个框架比 LazyPHP 相同之处有：

1. MVC 结构
2. 使用 BootStrap 作为前端框架

现在这个框架比 LazyPHP 多的特征有：

1. 一个 PDO 封装的db访问类，杜绝 SQL 注入
2. 一个极端简单的 ORM

和 LazyPHP 不同的地方有：

1. 使用 PHP 做 router
1. 使用自己的 Test 库

这个框架还是极端的不成熟，不推荐日常使用。请使用久经考验的 LazyPHP 框架。

文件结构
---------------

根目录下的文件夹有如下：

* core

 我们施展魔法的地方。其他地方都是你们施展魔法的地方。

* controller

 控制器。

* model
 
 模型层

* view

 视图。css 和 js 都在里面

model 是类。

所有的 Model 都可以继承了一个我已经写好的 BasicModel 类，里面有一些基本的增删改查的函数。


简明教程
--------------

自古以来，示例就是快速学习的不二法门，本教程也不例外。

假设著名互联网豆瓣的服务器不幸坏掉了，整个网站都需要重做，而你被分配做豆瓣小组。

首先，我们分析一下网址：

`/group/topic/35708257/`

这个网站分为三个部分，`group`，`topic`和`35708257`。当用户访问这个网址的时候，ptf 框架将会做这样一个工作：

```php
$controller = new GroupController();
$controller->topic('35708257');
```

所以，你需要在 `controller` 文件夹里加入一个 `group.php` 文件，作为 `group` 的控制器。

在这个文件里，你需要定义一个类：

```php
class GroupController extends BasicController
{
    public function topic($topicId) // $topicId === '35708257'
    {
        // 获取数据
        // ...

        // 渲染视图
        render_view('master');
    }
}
```

这个方法的前半部分从数据库里获取数据，而后半部分渲染视图。下面，我们将分别将这两部分补充完成。

首先，让我们假设数据库已经齐备。有两个表，topic 和 user。

```mysql
-- --------------------------------------------------------

--
-- 表的结构 `topic`
--

CREATE TABLE `topic` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `author` int(10) unsigned NOT NULL,
  `title` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `content` varchar(6000) COLLATE utf8_unicode_ci NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `user`
--

CREATE TABLE `user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
```

大家可以粗略的看一下 topic 表的结构。

那么我们如何获取数据呢？这个样子就可以了：

```php
public function topic($topicId)
{
    // 获取数据
    $topic = new Topic($topicId);
    echo $topic->title;
}
```

`$topic->title` 就是标题，而 `$topic->content` 自然就是内容啦。很简单吧。不过，要想实现这种用面向对象的方式访问数据库，我们首先要写好 Model 层。也就是传说中的 ORM 啦。

在 `model` 文件夹里新建一个 `Topic.php` 文件。内容如下：

```php
class Topic extends BasicModel
{
}
```

现在


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

