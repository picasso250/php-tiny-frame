<?php

namespace ptf;

use \PDO;

/**
 * represents a row in a tabel
 * @author ryan
 */
class IdEntity
{
    protected $model; // model object
    protected $row = array(); // data
    protected $id = 0;

    protected $dirty = array();

    public static function make($model, $row)
    {
        $classname = get_called_class();
        $o = new $classname;
        $o->model = $model;
        if ($row) {
            $o->row = $row;
            $o->id = $row[$model->pkey()];
        }
        return $o;
    }

    public static function fromArray($arr)
    {
        $o = new self;
        $o->row = $row;
        $o->id = $arr[$this->model->pkey()];
        return $o;
    }

    public function id()
    {
        $pkey = $this->model->pkey();
        if (!isset($this->row[$pkey])) {
            return 0;
        }
        return $this->row[$pkey];
    }

    public function save()
    {
        if ($this->id()) {
            $this->model->update($this);
        } else {
            $this->row[$this->model->pkey()] = $this->model->insert($this);
        }
        $this->clean();
        return $this;
    }

    public function dirtyArray()
    {
        $set = array();
        foreach ($this->dirty as $key) {
            $set[$key] = $this->row[$key];
        }
        return $set;
    }

    public function clean()
    {
        $this->dirty = array();
    }

    public function delete()
    {
        return $this->model->delete($this->id());
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
