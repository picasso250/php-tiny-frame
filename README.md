php-tiny-frame
==============

Small framework for website write in PHP.

一个简单的 PHP 路由框架，完全在一个文件内完成。

简明教程
--------------

首先，配置路由。在 `index.php` 中配置路由，使用 rules 字段。然后就可以调用 run 方法运行程序。

```php
$app = Application();
$app->rules = array(
    ['GET', '%^/$%', 'Index', 'index'], // 规则 1
    ['GET', '%^/role/(?<id>\d+)$%', 'Role', 'view'], // 规则 2
);
$app->run();
```

每条路由规则由四个基本元素构成：

1. 请求方法（如果传 null 则匹配所有）
2. 网址规则（一个正则表达式，可以使用具名分组）
3. 控制器类（name映射到nameController）
4. 控制器方法（name映射到nameAction）

控制器将会到对应 `controller` 目录下的文件，如上述规则2对应的文件是 `controller/RoleController.php`，对应的控制器方法是 `viewAction`。

如果没有找到对应的路由规则，则映射到 `IndexController::code404Action()`。

文件结构
---------------

根目录下的文件夹有如下：

* controller

 控制器。


简明教程
--------------

以豆瓣小组为例，我们分析一下网址：

`/group/topic/35708257/`

当用户访问这个网址的时候，我们希望服务器执行我们写的特定代码。这个功能就叫做路由。在 ptf 中，你需要这样配置路由规则:

```php
['GET', '%^/group/topic/(?<id>\d+)$%', 'Group', 'viewTopic']
```

这样就新建了一个路由规则，用户访问指定的网址时，服务器将会加载 `GroupController` 类，并调用 `viewTopicAction` 方法。路由规则中的 `(?<id>\d+)` 代表参数，将会以关联数组的形式传递给 `viewTopicAction(['id' => $id])` 方法。
