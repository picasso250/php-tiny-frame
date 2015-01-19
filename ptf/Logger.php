<?php

class Logger
{
	public function __construct($file)
	{
		$this->file = $file;
	}

	public function __call($level, $args)
	{
		$tpl = '%s %s '.array_shift($args)."\n";
		array_unshift($args, strtoupper($level));
		array_unshift($args, date('c'));
		array_unshift($args, $tpl);
		$line = call_user_func_array('sprintf', $args);
		error_log($line, 3, $this->file);
	}
}
