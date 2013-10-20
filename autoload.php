<?php
spl_autoload_register(function ($name) {
    if (preg_match('/^ptf\b/', $name)) {
        require __DIR__.'/'.str_replace('\\', '/', $name).'.php';
    }
});
