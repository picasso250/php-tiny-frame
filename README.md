php-tiny-frame
==============

Small framework for website write in PHP.

一个简单的 PHP 路由框架，完全在一个文件内完成。

简明教程
--------------

首先，配置路由。在 `index.php` 中配置路由，使用 rules 字段。调用 `run()` 方法运行程序, 参数是路由规则。

```php
require 'php-tiny-fram/ptf/autoload.php';
run([
    ['GET', '%^/$%', function () {
        echo 'hello';
    }],
]);
```

每条路由规则由四个基本元素构成：

1. 请求方法（如果传 null 则匹配所有）
2. 网址规则（一个正则表达式，可以使用具名分组）
3. 回调函数
4. 预绑定事件

如果没有找到对应的路由规则，则映射到run的第二个函数(如果有的话);

简明教程
--------------

以豆瓣小组为例，我们分析一下网址：

`/group/topic/35708257/`

当用户访问这个网址的时候，我们希望服务器执行我们写的特定代码。这个功能就叫做路由。在 ptf 中，你需要这样配置路由规则:

```php
['GET', '%^/group/topic/(?<id>\d+)$%', $func]
```

路由规则中的 `(?<id>\d+)` 代表参数，将会以关联数组的形式传递给回调函数.

### 404 页面

```
run([
    ['GET', '%^/$%', $func],
], function() {
	header()
	echo '404 page';
});
```
