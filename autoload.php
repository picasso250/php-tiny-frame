<?php

require __DIR__.'/ptf/lib.php';
spl_autoload_register(function ($name) {
	$f = __DIR__.'/ptf/'.str_replace('\\', '/', $name).'.php';
    if (is_file($f)) {
        require $f;
    }
});
