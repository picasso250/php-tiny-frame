php-tiny-frame
==============

Very small framework for website write in PHP.

一个小而简单的 PHP 框架。使用 MVC 模式。支持 SAE 。

（我猜很多 PHP 程序员都坐过这种事情，写了个小框架，然后慢慢放弃，转而使用别人的框架（比较大的）。或许这就是成长，没有小框架，要我们如何理解大框架？）

**我的目的**

- 我自己用的顺手
- 运行效率比较高的框架
- 简洁
- 支持 SAE

这个框架参考了
- **鸡爷** 的自用框架，
- [LazyPHP3](https://github.com/easychen/LazyPHP)
- Idiorm
- Klein
在此一并表示感谢。

这个框架有如下特征：

1. MVC 结构
2. 使用 BootStrap 作为前端框架
1. PDO 封装的 DB 访问类，杜绝 SQL 注入
2. 模仿 [Idiorm](http://www.doctrine-project.org/) 的一个简单的 ORM
1. 使用 PHP 做 router
2. Controller 层使用 function
1. 使用自己的 Test 库

**这个框架还是极端的不成熟，不推荐日常使用。**

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

**配置与运行**

```php
$app = new PtfApp;
$app->config(require __DIR__.'/config.php');
$app->run();
```

在 `config.php` 中，你需要配置各种

```php
return array(
    'db' => array()
);
```

**路由**

以豆瓣小组为例，我们分析一下网址：

`/group/topic/35708257/`

当用户访问这个网址的时候，我们希望服务器执行我们写的特定代码。这个功能就叫做路由。在ptf中，你需要这样做。

```php
$router = new Router;
$router->respond('/group/topic/[:id]', array('Group\Topic', 'view'));
```

这样就新建了一个路由规则，用户访问指定的网址时，服务器将会加载Group模块下的 TopicController 类，并调用 viewAction 方法。路由规则中的[:id] 代表参数，将会赋给Controller的同名属性。

所以，你需要在 `controller` 文件夹里加入一个 `Group` 文件夹，并在其下新建 `Topic.php` 文件。

在这个文件里，你需要定义一个类：

```php
class TopicController extends Controller
{
    public function viewAction() 
    {
        // 获取数据
        $topicId = $this->id; // $topicId === '35708257'
        // ...

        // 渲染视图
        $this->renderView('master');
    }
}
```

这个方法的前半部分从数据库里获取数据，而后半部分渲染视图。下面，我们将分别将这两部分补充完成。

首先，让我们假设数据库已经齐备。有一个 topic 表。

```mysql
--
-- 表的结构 `topic`
--

CREATE TABLE `topic` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `author` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `title` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `content` varchar(6000) COLLATE utf8_unicode_ci NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
```

大家可以粗略浏览一下两个表的结构。

那么我们如何获取数据呢？这个样子就可以了：

```php
public function viewAction() 
{
    // 获取数据
    $topicId = $this->id; // $topicId === '35708257'
    $topic = Topic::findOne($topicId);
    echo $topic->title;
}
```

`$topic->title` 就是标题，而 `$topic->content` 自然就是内容啦。很简单吧。不过，要想实现这种用面向对象的方式访问数据库，我们首先要写好 Model 层。也就是传说中的 ORM ，也有人叫做 AR。

在 `model` 文件夹里新建一个 `Topic.php`文件。注意，这个类的名称一定要是表名首字母大写。
内容如下：

```php
class Topic extends IdModel // 继承自 IdModel，这是重点！
{
}
```

现在你就拥有一个最基本的 ORM 了。上面的 `viewAction` 方法。已经可以工作了。

render
```php

```

** ORM 进阶 **

你把这个 豆瓣小组 交付之后，主管很严肃的过来找你谈心，他说你这里有个重要的功能还没做，那就是回复功能。于是你开始思考回复功能怎么做。

首先构建出数据库：

```mysql
--
-- 表的结构 `comment`
--

CREATE TABLE `comment` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `topic` int(10) unsigned NOT NULL COMMENT 'topic id',
  `author` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `content` varchar(6000) COLLATE utf8_unicode_ci NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
```

可以看到，表 `comment` 的 `topic` 字段对应 `topic` 表。

这样之后，我们在 `model` 文件夹下新建一个文件：`Comment.php`

```php
class Comment extends BasicModel 
{
    protected $relationMap = array('topic' => 'topic');
}
```

但是，注意，我们 comment 表有一个外键，所以，要写一个 relation map。这个 relation map 的 key 是外键的名称，value 是对应的表名。一般而言，二者是相同的名称。

然后，我们想当然的在 `controller` 里这么调用：

```php
public function topic()
{
    $topic = new Topic($topicId);
    $comments = $topic->comments();
}
```

`$comments` 就是一个 array，里面装满了 Comment 对象。

为了让我们想当然的代码工作，我们还得填写 `Topic::comments()` 方法。

这个方法是这样的：

```php
class Topic extends BasicModel 
{
    public function comments()
    {
        return Comment::search()->filterBy('topic', $this)->find(); // 这就是见证奇迹的代码
    }
}
```

好了，现在我们的代码已经可以正常工作了。
不过，你肯定很好奇见证奇迹的代码是如何工作的。我来一步步的讲解一下吧。

```php
$data =
    Model::search();        // 这是一个搜索者，专门用来获取数据库中的数据。
    ->filterBy('key', $value); // 指定一个 filter（过滤器）
    ->findMany();          // 使用 `Searcher::findMany()` 方法获取数据
```

首先看网址：

`/group/reply/35708257/`

当然，真实的豆瓣不是这个网址，为了更加适应 ptf 框架，所以我这个样子设计网址。

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

Java 中的DAO和Entity，被我用动态方法和静态方法区分了。

