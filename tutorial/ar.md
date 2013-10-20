
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

这样之后，我们在 `dao` 文件夹下新建一个文件：`CommentDao.php`

```php
class CommentDao extends IdDao 
{
}
```

在 `entity` 文件夹下新建一个文件： `Comment.php`

```php
class Comment extends IdEntity
{
}
```

然后，我们想当然的在 `controller` 里这么调用：

```php
public function topic()
{
    $topicModel = new TopicModel;
    $topic = $topicModel->findOne($topicId);
    $comments = $topic->getComments();
}
```

`$comments` 就是一个 array，里面装满了 Comment 对象。

为了让我们想当然的代码工作，我们还得填写 `Topic::comments()` 方法。

这个方法是这样的：

```php
class Topic extends IdEntity 
{
    public function getComments()
    {
        $commentDao = new CommentDao;
        return $commentDao->where('topic_id', $this->id)->findMany(); // 这就是见证奇迹的代码
    }
}
```

好了，现在我们的代码已经可以正常工作了。
不过，你肯定很好奇见证奇迹的代码是如何工作的。我来一步步的讲解一下吧。

```php
$data =
    $personDao        // 这是一个Dao，专门用来获取数据库中的数据。
    ->where('key', $value) // 指定搜索条件
    ->findMany()           // 使用 `Searcher::findMany()` 方法获取数据
```

ORM
------

**ORM 配置**

```php
PdoWrapper::config('mysql:host=localhost;dbname=my_database');
PdoWrapper::config('username', 'database_user');
PdoWrapper::config('password', 'top_secret');
```

还可以用config()方法设置一些其他的选项。

```php
PdoWrapper::config('选项名', '选项值');
```

还可以一次性传多个键值对：

```php
PdoWrapper::config(array(
    '选项名1' => '选项值1',
    '选项名2' => '选项值2',
    '等等' => '其他'
));
```

`getConfig()` 可以用来获取选项值

```php
$isLoggingEnabled = PdoWrapper::getConfig('logging');
PdoWrapper::config('logging', false);
// 全速循环，会产生超多sql，乃至你想要屏蔽日志
PdoWrapper::config('logging', $isLoggingEnabled);
```

有时候，有些数据库适配器允许指定自己的配置，比如mysql的中文，我们就可以这样：

```php
PdoWrapper::config('driver_options', array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'));
```

实际上，可以用 `getDb()` 方法获取 Pdo 对象，也可以直接用 `setDb()` 注入 Pdo 对象。当然，但愿你用不着这个功能。

**查询**

查询接口采用了“连贯接口”的设计模式，也就是通常所说的链式调用。

首先需要我们建立模型，假如表名为 `person` ，则我们建立的模型为

```php
//有主键的表，要继承 IdModel 类。
class Person extends IdModel {} 
```

当我们建立了对应的模型之后，就可以进行查询了：

```php
Person::search()->where('name', 'Fred Bloggs')->findOne();
```

如果是想要找到对应某一个主键的一行记录。

```php
Person::findOne(5);
```

上述语句找到了主键id为5的记录。

注意， `Model` 为是所有模型的基类。 `IdModel` 是指有主键的。

**查找多个**

如果方法链以 `findMany()` 结尾，即是寻找多个。

找到所有记录：

```php
$people = Person::search()->findMany();
```

找到性别为女的记录

```php
$females = Person::search()->where('gender', '女')->findMany();
```

默认返回的是一个Model对象，如果想要返回关联数组，则

```php
$females = Person::search()->where('gender', '女')->findArray();
```

如果想要其他的比较符号，可以使用三个参数的where方法：

```php
$people = Person::search()->where('age', '!=', 23)->findMany();
```

**度量**

要想知道记录的数量，可以使用count方法

```php
$number_of_people = Person::search()->count();
```

**过滤结果集**

where族方法提供了丰富的过滤机制：
比如调用 `where('name', 'Fred')` 会产生 `WHERE name = "Fred"` ，
相当于调用 `whereEqual()`

想要使用 `WHERE ... IN ()` 或 `WHERE ... NOT IN ()` 语句, 使用 `whereIn()` 或 `whereNotIn()` 方法。
这两个方法接受两个参数。第一个是字段名，第二个是数组

```php
$people = Person::search()->whereIn('name', array('Fred', 'Joe', 'John'))->findMany();
```

**原生表达式**

如果你需要更复杂的查询，你可以使用 `whereExpr()` 方法，直接指定SQL表达式片段。
这个方法接受两个参数，一个SQL字符串，另一个（可选）是参数数组，用于绑定到SQL上。如果提供了参数数组，SQL字符串中可以包含 `?` ，以待绑定。注意参数的顺序，是顺次和 `?` 绑定的。

这个方法可以和其他的 where 族方法及其他方法如 `orderBy()` 方法混用。所有使用过的where方法的语句将会用 `AND` 连接起来。

```php
$people = Person::search()
            ->where('name', 'Fred')
            ->whereRaw('`age` = ? OR `age` = ?', array(20, 25))
            ->orderBy('name', 'asc')
            ->findMany();

// Creates SQL:
SELECT * FROM `person` WHERE `name` = "Fred" AND (`age` = 20 OR `age` = 25) ORDER BY `name` ASC;
```

注意这个方法只支持问号 `?` 作为占位符，不支持带名字的占位符。这是因为 Pdo 不允许混杂的占位符模式。再次强调，占位符的顺序和参数的顺序需要匹配。

如果你需要更大的灵活性，可以使用 `excute()` 方法指定整个查询。

**数量和偏移**

注意此方法不过滤字符，所以不要直接使用用户输入入参。

```php
$people = Person::search()->where('gender', 'female')->limit(5)->offset(10)->findMany();
```

**排序**

注意此方法不过滤字符，所以不要直接使用用户输入入参。

orderBy()方法接受两个参数，字段名和排序方式。字段名将被括起来。

```php
$people = Person::search()->orderBy('gender', 'asc')->orderBy('name', 'DESC')->findMany();
```

如果需要更复杂的排序方式，也可以直接传一个表达式到这个方法里。

```php
$people = Person::search()->orderBy('SOUNDEX(`name`)')->findMany();
```

**分组**

注意此方法不过滤字符，所以不要直接使用用户输入入参。

添加 GROUP BY 字句，使用groubBy方法，传入字段名。此方法可以被多次调用，以便多列分组。

```php
$people = Person::search()->where('gender', 'female')->groupBy('name')->findMany();
```

使用表达式来分组也是可以的。

```php
$people = Person::search()->where('gender', 'female')->groupBy("FROM_UNIXTIME(`time`, '%Y-%m')")->findMany();
```

**Having**

当和 GROUP BY 结合查询一些需经过计算的条件时，你可以使用 HAVING 字句。只需要把where替换成having就行。

```php
$people = Person::search()->groupBy('name')->having_not_like('name', '%bob%')->findMany();
```

**DISTINCT**

```php
$distinct_names = Person::search()->distinct()->select('name')->findMany();
```

生成的sql语句如下：

```sql
SELECT DISTINCT `name` FROM `person`;
```

**连接**

ptf有一族连接函数：
`join()`, `leftJoin()`, `rightJoin()`, `fullJoin()`.

每个方法都有相同的入参。

前两个参数是必须的。第一个参数是要连接的表名，第二个参数是连接条件。推荐使用三元数组指定连接条件：字段名、操作符、字段名。表名和字段名会被自动括起来。

```php
$results = Person::search()
    ->join('person_profile', array('person.id', '=', 'person_profile.person_id'))
    ->findMany();
```

用字符串作为链接条件也是可以的。

```php
// 不推荐，因为字段名不会被反引用
$results = Person::search()
    ->join('person_profile', 'person.id = person_profile.person_id')
    ->findMany();
```

如果想为被连接的表指定别名，在表名处传入一个长度为1的数组，键为原表名，值为别名。这在连接自身的时候会非常有用。

连接方法还有第三个可选参数，可以是数组或者字符串，指定要选择的列。和 columns 方法参数一致。

```php
$results = Person::search()
    ->tableAlias('p1')
    ->column('p1.*')
    ->column('p2.name', 'parent_name')
    ->join(array('person' => 'p2'), array('p1.parent', '=', 'p2.id'))
    ->findMany();
```

**原生查询**

如果你想完成更复杂的查询，你可以指定整个sql表达式。excute方法需要一个字符串和（可选的）一个参数数组。字符串可以要送问号占位符，也可以用名称占位。如果你想要获取数据，那么使用 `fetchMany()` 和 `fetchOne()` 方法。

```php
$people = Model::fetchMany('SELECT p.* FROM person p JOIN role r ON p.role_id = r.id WHERE r.name = :role', array('role' => 'janitor'));
```

返回的依然是包含这个类的实例的数组。

**模型**

**从对象中获取数据**

取得了对象（记录）之后，你就可以通过访问对象的属性来获取数据了。可以使用get方法，或者直接访问属性。

```php
$person = ORM::for_table('person')->findOne(5);

// 以下两种方式等同
$name = $person->get('name');
$name = $person->name;
```

你也可以通过 `toArray()` 方法将数据转化为数组。

`toArray()` 方法拿字段名作为（可选的）参数，如果提供字段名（一个或多个），那么只有指定的字段才会被返回。

```php
$person = ORM::for_table('person')->create();

$person->first_name = 'Fred';
$person->surname = 'Bloggs';
$person->age = 50;

// 返回 array('first_name' => 'Fred', 'surname' => 'Bloggs', 'age' => 50)
$data = $person->as_array();

// 返回 array('first_name' => 'Fred', 'age' => 50)
$data = $person->as_array('first_name', 'age');
```

**更新记录**

改变对象的属性，然后调用对象的save方法。
改变对象属性可以用set方法，也可以直接对属性赋值。给set方法传数组参数也可以一次改动多处属性。

```php
$person = ORM::for_table('person')->findOne(5);

// 以下两种形式等同
$person->set('name', 'Bob Smith');
$person->age = 20;

// 下面和两次赋值等同
$person->set(array(
    'name' => 'Bob Smith',
    'age'  => 20
));

// 与数据库同步
$person->save();
```

**包含表达式的属性**

通过setExpr方法可以设置SQL表达式。

```php
$person = Person::findOne(5);
$person->set('name', 'Bob Smith');
$person->age = 20;
$person->set_expr('updated', 'NOW()');
$person->save();
```

创建新记录
首先，需要创建一个空对象，然后像平常一样给属性赋值，最后保存之。

```php
$person = Person::create();

$person->name = 'Joe Bloggs';
$person->age = 40;

$person->save();
```

保存对象之后，调用id方法能够得到数据库给它的自增id的值。

删除记录
只要简单调用delete方法就可以从数据库中删除对象。

```php
$person = Person::findOne(5);
$person->delete();
```

如果想删除不止一条记录，调用searcher的delete方法。

```php
$person = Person::search()
    ->whereEqual('zipcode', 55555)
    ->delete();
```