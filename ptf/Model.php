<?php

namespace ptf;

/**
 * @author ryan
 */
class Model
{
    protected static $table;
    
    protected $row;
    protected $dirty = array();

    public function __construct()
    {
    }

    public static function create()
    {
        $self = get_called_class();
        return new $self();
    }

    public static function fromArray($arr)
    {
        $self = get_called_class();
        $o = new $self();
        $o->$row = $row;
        return $o;
    }

    public function toArray()
    {
        $num_args = func_num_args();
        if ($num_args == 0) {
            return $this->row;
        }
        
        $names = func_get_args();
        foreach ($names as $name) {
            $ret[$name] = $this->row[$name];
        }
        return $ret;
    }

    public static function table()
    {
        $self = get_called_class();
        if (isset($self::$table))
            return $self::$table;
        else
            return camel2under($self); // camal to underscore
    }

    public static function fetchOne($sql, $args = array())
    {
        $row = PdoWrapper::fetchRow($sql, $args);
        if ($row === false) {
            return null;
        } else {
            return static::fromArray($row);
        }
    }

    public static function fetchMany($sql, $args = array())
    {
        $rows = PdoWrapper::fetchAll($sql, $args);
        if ($rows === false) {
            return null;
        }
        $ret = array();
        foreach ($rows as $key => $value) {
            $ret[$key] = static::fromArray($value);
        }
        return $ret;
    }

    public static function execute($sql, $args = array())
    {
        return PdoWrapper::execute($sql, $args);
    }

    public function get($name)
    {
        if (isset($this->row) && isset($this->row[$name])) {
            return $this->row[$name];
        }
        return null;
    }

    public function set()
    {
        $num_args = func_num_args();
        if ($num_args == 1) {
            $arr = func_get_arg(0);
            if (is_array($arr)) {
                foreach ($arr as $key => $value) {
                    $this->_set($key, $value);
                }
            }
        } elseif ($num_args == 2) {
            $key = func_get_arg(0);
            $value = func_get_arg(1);
            $this->_set($key, $value);
        }
    }

    public function _set($key, $value)
    {
        $this->row[$key] = $value;
        $this->dirty[] = $key;
    }

    public static function search()
    {
        return new Searcher(get_called_class());
    }

    public function fillWith($data)
    {
        $this->row = $data;
    }

    public function __set($key, $value)
    {
        $this->_set($key, $value);
    }

    public function __get($key)
    {
        return $this->get($key);
    }
}
