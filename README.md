php-tiny-frame
==============

Very small framework for website write in PHP.

## System Requirements

PHP >= 5.4

## Hello World Tutorial

```php
require 'php-tiny-fram/ptf/autoload.php';

$routers = [
    ['GET', '%^/$%', function () {
        echo 'hello';
    }],
];
run($routers);
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



