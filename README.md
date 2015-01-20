php-tiny-frame
==============

Very small framework for website write in PHP.

## System Requirements

* PHP >= 5.4
* URL rewriting so that all requests are handled by index.php, you can just do `php -S 0.0.0.0:80`

## Example

Hello World

```php
require 'php-tiny-fram/ptf/autoload.php';

$routers = [
    ['GET', '%^/$%', function () {
        echo 'hello';
    }],
];
run($routers);
```

Named parameters

```php
$routers = [
    ['GET', '%^/user/(?<name>\d+)$%', function ($params) {
        echo 'hello ', $params['name'];
    }],
];
```

echo json

```php
$routers = [
    ['GET', '%^/api/v2/post/$%', function ($params) {
        echo_json([1, 2, 3]);
    }],
];
```

## Routers

You can pass the routers to `run()`.

```php
function run($rules, $page404 = null)
```

`$rules` is an array of rule. Each rule has 3 elements:

1. http method
2. url rule, using regex.
3. a function to call when url matches
4. (optional) event of before

If none of the rule matched, it will call the function `$page404`.

```php
run([
    ['GET', '%^/$%', $func],
], function() {
	echo '404 page'; // already send 404 header
});
```

All the callbacks of url rule recieve a prameter.

`function callback($params)`

which `$params` is an array, of 3rd parameter of the `preg_match()`.

## Regex to match URL ##

- You can use `%` instead of `/` as the deliminate as `/` is always used in the pattern.
- Your use a named group such as `%^/topic/(?<topic_id>\d+)$%`, which You can recieve it in the `$params` as associate array of callback function.

## services ##

register a service

```php
Service('db', new Pdo('mysql:dbname=xc', 'root', ''));

// later

$db = Service('db');
```

## events ##

Every rule has 4th element to specify before event.

```php
run([
    ['GET', '%^/$%', $func, function() {
    	if (!is_login()) {
    		return false; // return false makes the routing stop
    	}
    }],
]);
```

## Views ##

```php
render('index.html', ['data' => $data]);
```

or render with the layout

```php
render('index.html', ['data' => $data], 'layout.html');
```

where layout.html can render index.html as `$_inner_`.

```php
<!-- file: layout.html -->
<html><head></head><body>
<?php include $_inner_; ?>
</body></html>
```

## License ##

(MIT License)

Copyright (c) 2010 Chris O'Hara cohara87@gmail.com

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
