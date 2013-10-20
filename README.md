php-tiny-frame
==============

Small framework for website write in PHP.

一个简单的 PHP 框架。使用 MVC 模式。支持 SAE 。

一个人的力量毕竟有限，希望大家可以帮助我完善这个框架。

这个框架有如下 **特征** ：

1. MVC 结构
2. 自带简单的 ORM
1. 自带路由
4. 运行效率较高
5. 支持 SAE

**这个框架参考了**

- **鸡爷** 的自用框架，
- [LazyPHP3](http://ftqq.com/open-source-projects/lazyphp/)
- [Idiorm](http://j4mie.github.io/idiormandparis/)
- [Klein](http://chriso.github.io/klein.php/)
- [Zend FrameWork 2](http://framework.zend.com/)

在此一并表示感谢。

文件结构
---------------

默认的根目录下的文件夹有如下：

* controller

 控制器。

* dao
 
 数据库访问器

* entity

 数据对象

* view

 视图。模版都在里面。

所有的 Dao 都需要继承 IdDao 类，里面有一些基本的增删改查的函数，可以更方便的和数据库交互。

简明教程
--------------

**1**

首先，在网站根目录下建立 vendor 文件夹，然后将整个源码放置到其下的 php-tiny-frame 文件夹下。

**2 控制器**

在网站根目录下建立 controller 文件夹，在里面新建文件 indexController.php 内容如下

```php

use ptf\Controller;

class indexController extends Controller
{
    public function indexAction()
    {
        echo 'Hello, world!';
    }
}
```

这个文件就是控制器。

**3 配置与运行**

在根目录下新建 index.php 文件，内容如下：

```php
use ptf\Application;

require __DIR__.'/vendor/php-tiny-frame/ptf/autoload.php';

$app = new Application;
$app->root = __DIR__; // 配置网站的主目录
$app->run();
```

That's it.

More
-----

在 `config.php` 中，你需要配置数据库连接信息和路由

```php
return array(
    'db' => array(
        'dsn' => 'mysql:host=localhost;dbname=weibo',
        'username' => 'root',
        'password' => 'root',
        'debug' => true,
        'logging' => true,
    ),

    // 网址=>控制器
    'routers' => array(
        array('GET', '/', array('Index', 'index')),
        array('GET', '/about', array('Index', 'about')),
        array('GET', '/role/', array('Role', 'index')),
        array('POST', '/role/', array('Role', 'add')),
        array('GET', '/role/[:id]', array('Role', 'view')),
        array('GET', '/role/[:id]/play', array('Role', 'play')),
        array('POST', '/twit/', array('Twit', 'add')),
    ),
);
```

**路由**

以豆瓣小组为例，我们分析一下网址：

`/group/topic/35708257/`

当用户访问这个网址的时候，我们希望服务器执行我们写的特定代码。这个功能就叫做路由。在 ptf 中，你需要这样做:

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
    $topicModel = new TopicModel;
    $topic = $topicModel->findOne($topicId);
    echo $topic->title;
}
```

`$topic->title` 就是标题，而 `$topic->content` 自然就是内容啦。很简单吧。不过，要想实现这种用面向对象的方式访问数据库，我们首先要写好 Model 层。也就是传说中的 ORM ，也有人叫做 AR。

在 `dao` 文件夹里新建一个 `TopicDao.php`文件。注意，这个类的名称一定要是表名首字母大写。
内容如下：

```php
class TopicDao extends IdDao // 继承自 IdDao，这是重点！
{
    public $table = 'topic'; // 数据库表名
}
```

在 `entity` 文件夹中新建一个 `Topic.php` 的文件。注意，这个类的名称一定要和 `TopicDao` 对应。

```php
class Topic extends IdEntity
{
}
```

现在你就拥有一个最基本的 ORM 了。上面的 `viewAction` 方法。已经可以工作了。

**渲染视图**

```php
$this->layout('master');
$this->renderView('index');
$this->yieldView();
$this->renderBlock('header', array('name' => $value,...));

```

常用函数
--------

/app_core/function.php 中有一些常用函数：

类库
-----

都在文件夹 `/app_core/class` 下。

- **Paginate**

 翻页类

- **QqLogin**

 QQ 平台登录

- **SiteMap**

 生成 Google 的 SiteMap （还不完善）。

更多的思考
-----------

Java 中的DAO和Entity，被我用动态方法和静态方法区分了。

