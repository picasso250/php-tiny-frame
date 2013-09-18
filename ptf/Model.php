<?php

namespace ptf;

/**
 * @author ryan
 */
class Model
{
    protected static $table;
    
    protected $row;
    
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
        return $this->row();
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

    public function __get($name) 
    {
        return $this->get($name);
    }

    public function get($name)
    {
        if (isset($this->row) && isset($this->row[$name])) {
            return $this->row[$name];
        }
        return null;
    }

    public static function search()
    {
        return new Searcher(get_called_class());
    }

    public function fillWith($data)
    {
        $this->row = $data;
    }
}
