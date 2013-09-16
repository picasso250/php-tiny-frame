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
