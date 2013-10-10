<?php

namespace ptf;

/**
 * 实体基类
 * 
 * represents a row in a tabel
 * @author ryan
 */
class IdEntity
{
    protected $model; // model object, for now, it is dao
    protected $row = array(); // data
    protected $id = 0;

    protected $dirty = array();

    public static function dao()
    {
        $classname = get_called_class().'Dao';
        return new $classname;
    }

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
        $o->row = $arr;
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
            $this->id = $this->row[$this->model->pkey()] = $this->model->insert($this);
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
        return $this->model->delete($this);
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
                $this->setMulti($arr);
            }
        } elseif ($num_args == 2) {
            $key = func_get_arg(0);
            $value = func_get_arg(1);
            $this->_set($key, $value);
        }
        return $this;
    }
    
    public function setMulti($arr)
    {
        foreach ($arr as $key => $value) {
            $this->_set($key, $value);
        }
        return $this;
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
        return $value;
    }

    public function __get($key)
    {
        return $this->get($key);
    }
}
