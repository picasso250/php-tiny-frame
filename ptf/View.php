<?php

class View
{
    public function make($name)
    {
        $code = self::parseFile("app/views/$name.blade.php");
        ob_start();
        eval($code);
        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }

    private static function processLine($line)
    {
        return $line;
    }
    private static function parseFile($file, $sections = [])
    {
        $ret = [];
        $sectionArrays = []
        $f = fopen($file, 'r');
        while (($line = fgets($f)) !== false) {
            $line  = trim($line);
            if (strpos($line, '@') === 0) {
                preg_match('/@(\w+)\((.+)\)/', $line, $matches);
                $params = eval("[ $matches[2] ]");
                $ret[] = call_user_func_array(get_called_class().'::do'.ucfirst($matches[1]), $params);
            } elseif (self::$currentSection) {
                $sectionArrays[self::$currentSection][] = self::processLine($line);
            } else {
                $ret[] = self::processLine($line);
            }
        }
        if (self::$parent) {
            $parent = self::$parent;
            self::$parent = null;
            return self::parseFile($parent, $sections);
        }
        return implode('', $ret);
    }

    private static function doStop($name)
    {
        self::$currentSection = null;
    }
    private static function doSection($name)
    {
        self::$currentSection = $name;
        return '';
    }
    private static function doExtends($name)
    {
        self::$parent = $name;
        return '';
    }
    private static function doYield($name, $default = '')
    {
        return isset(self::$sections[$name]) ? self::$sections[$name] : $default;
    }
}
