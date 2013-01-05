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

假设你在做豆瓣小组。

首先，看看网址：

`/group/topic/35708257/`

当用户访问这个网址的时候，框架将会做这样一个工作：

```php
$controller = new GroupController();
$controller->topic('35708257');
```

所以，你需要在 `controller` 文件夹里加入一个 `Group.php` 文件，作为 `group` 的控制器。

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

现在让我们继续完善这个 `topic()` 方法吧。

假设数据库已经齐备。

那么我们如何获取数据呢？我们要写好 Model 层。

在 `model` 文件夹里新建一个 `Topic.php` 文件。内容如下：

```php
class Topic extends BasicModel
{
}
```

现在我们回到 `GroupController` 的 `topic()` 方法：

```php
public function topic($topicId)
{
    // 获取数据
    $topic = new Topic($topicId);
    echo $topic->title;
}
```


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

