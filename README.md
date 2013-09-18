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
1. PDO 封装的 DB 访问类，杜绝 SQL 注入
2. 模仿 [Idiorm](http://www.doctrine-project.org/) 的一个简单的 ORM
1. 使用 PHP 做 router
1. 使用自己的 Test 库

**这个框架还是极端的不成熟，不推荐日常使用。**

文件结构
---------------

根目录下的文件夹有如下：

* ptf

 框架的源文件。

* controller

 控制器。

* model
 
 模型层

* view

 视图。模版都在里面。

model 是类。

所有的 Model 都可以继承了一个我已经写好的 BasicModel 类，里面有一些基本的增删改查的函数。


简明教程
--------------

**配置与运行**

```php
require __DIR__.'/ptf/autoload.php';
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

以回复功能为例：

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
        return Comment::search()->where('topic', $this)->findMany(); // 这就是见证奇迹的代码
    }
}
```

好了，现在我们的代码已经可以正常工作了。
不过，你肯定很好奇见证奇迹的代码是如何工作的。我来一步步的讲解一下吧。

```php
$data =
    Model::search()        // 这是一个搜索者，专门用来获取数据库中的数据。
    ->where('key', $value) // 指定搜索条件
    ->findMany()           // 使用 `Searcher::findMany()` 方法获取数据
```

ORM配置
```php
PdoWrapper::config('mysql:host=localhost;dbname=my_database');
PdoWrapper::config('username', 'database_user');
PdoWrapper::config('password', 'top_secret');
```
还可以用config()方法设置一些其他的选项。
PdoWrapper::config('选项名', '选项值');

还可以一次性传多个键值对：
PdoWrapper::config(array(
    '选项名1' => '选项值1',
    '选项名2' => '选项值2',
    '等等' => '其他'
));

getConfig可以用来获取选项值

$isLoggingEnabled = ORM::getConfig('logging');
ORM::configure('logging', false);
// 全速循环，会产生超多sql，乃至你想要屏蔽日志
ORM::configure('logging', $isLoggingEnabled);

有时候，有些数据库适配器允许指定自己的配置，比如mysql的中文，我们就可以这样：
ORM::configure('driver_options', array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'));

实际上，我们可以用getDb方法获取Pdo对象，也可以直接用setDb注入Pdo对象。当然，但愿你用不着这个功能。

查询
----

查询接口采用了“连贯接口”的设计模式，也就是通常所说的链式调用。
当我们建立了对应的model之后，就可以进行查询了：

Model::search()->where('name', 'Fred Bloggs')->findOne();

如果是想要找到某一个主键的一行记录。
IdModel::findOne(5);
上述语句找到了主键id为5的记录。

注意，Model为是最基本的基类。IdModel是指有主键的。

查找多个
如果方法链以findMany结尾，即是找到多个。

找到所有记录：
$people = ORM::for_table('person')->find_many();

找到性别为女的记录
$females = Model::search()->where('gender', '女')->findMany();

默认返回的是一个Model对象，如果想要返回关联数组，则
$females = Model::search()->where('gender', '女')->findArray();

如果想要其他的比较符号，可以使用三个参数的where方法：
$females = Model::search()->where('age', '!=', 23)->findMany();

度量

要想知道记录的数量，可以使用count方法

$number_of_people = ORM::for_table('person')->count();

过滤结果集

where族方法提供了丰富的过滤机制：
比如调用where('name', 'Fred')会产生 WHERE name = "Fred"
相当于调用whereEqual

想要 WHERE ... IN () 或 WHERE ... NOT IN () 语句, 使用 whereIn 或 whereNotIn 方法。
这两个方法接受两个参数。第一个是字段名，第二个是数组
$people = ORM::for_table('person')->where_in('name', array('Fred', 'Joe', 'John'))->find_many();

原生表达式

如果你需要更复杂的查询，你可以使用whereExpr方法，直接指定SQL表达式片段。
这个方法接受两个参数，一个SQL字符串，另一个（可选）是参数数组，用于绑定到SQL上。如果参数数组提供了，SQL字符串中可以包含?，以待绑定。注意顺序。

这个方法可以和其他的 where* 方法
及其他方法如 orderBy方法。所有使用过的where方法将会用AND连接起来。
$people = ORM::for_table('person')
            ->where('name', 'Fred')
            ->where_raw('(`age` = ? OR `age` = ?)', array(20, 25))
            ->order_by_asc('name')
            ->find_many();

// Creates SQL:
SELECT * FROM `person` WHERE `name` = "Fred" AND (`age` = 20 OR `age` = 25) ORDER BY `name` ASC;
注意这个方法只支持问号作为占位符，不支持带名字的占位符。这是因为Pdo不允许混杂的占位符模式。再次强调，占位符的顺序和参数的顺序需要匹配。

如果你需要更大的灵活性，可以使用excute方法指定整个查询。

数量和偏移

注意此方法不过滤字符，所以不要直接使用用户输入入参。

$people = ORM::for_table('person')->where('gender', 'female')->limit(5)->offset(10)->find_many();

排序

注意此方法不过滤字符，所以不要直接使用用户输入入参。

orderBy()方法接受两个参数，字段名和排序方式。字段名将被括起来。

$people = ORM::for_table('person')->orderBy('gender', 'asc')->orderBy('name', 'DESC')->findMany();

如果需要更复杂的排序方式，也可以直接传一个表达式到这个方法里。

$people = ORM::for_table('person')->orderBy('SOUNDEX(`name`)')->find_many();

分组

注意此方法不过滤字符，所以不要直接使用用户输入入参。

添加 GROUP BY 字句，使用groubBy方法，传入字段名。此方法可以被多次调用，以便多列分组。

$people = ORM::for_table('person')->where('gender', 'female')->group_by('name')->find_many();

使用表达式来分组也是可以的。

$people = ORM::for_table('person')->where('gender', 'female')->groupBy("FROM_UNIXTIME(`time`, '%Y-%m')")->find_many();

Having

当和 GROUP BY 结合查询一些需经过计算的条件时，你可以使用 HAVING 字句。只需要把where替换成having就行。

$people = ORM::for_table('person')->group_by('name')->having_not_like('name', '%bob%')->find_many();

DISTINCT
To add a DISTINCT keyword before the list of result columns in your query, add a call to distinct() to your query chain.

<?php
$distinct_names = ORM::for_table('person')->distinct()->select('name')->find_many();
This will result in the query:

<?php
SELECT DISTINCT `name` FROM `person`;

常用函数
--------

/app_core/function.php 中有一些常用函数：

```php
function _get($name); // 相当于 $_GET[$name]

function _post($name); // 相当于 $_POST[$name]

function _req($name); // 相当于 $_REQUEST[$name]
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

