<?php

namespace ptf;

/**
 * 实体基类
 * 
 * represents a row in a table
 * @author ryan
 */
class IdEntity
{
    protected $model; // model object, for now, it is dao
    protected $row = array(); // data
    protected $id;

    protected $dirty = array();

    /**
     * 获取对应的Dao
     * @return IdDao
     */
    public static function dao()
    {
        $classname = get_called_class().'Dao';
        return new $classname;
    }

    /**
     * 工厂函数，获取一个实例
     * @param IdDao $model
     * @param array $row
     * @return IdEntity
     */
    public static function make($model, array $row)
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

    /**
     * 获取主键的值
     * @return int
     */
    public function id()
    {
        $pkey = $this->model->pkey();
        if (isset($this->row[$pkey])) {
            return $this->row[$pkey];
        }
        return null;
    }

    /**
     * 保存
     * @return \ptf\IdEntity
     */
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

    /**
     * 获取写脏的值，键值对数组
     * @return array
     */
    public function dirtyArray()
    {
        $set = array();
        foreach ($this->dirty as $key) {
            $set[$key] = $this->row[$key];
        }
        return $set;
    }

    /**
     * 标记为干净的
     */
    public function clean()
    {
        $this->dirty = array();
        return $this;
    }

    /**
     * 从数据库删除这条记录
     * @return type
     */
    public function delete()
    {
        return $this->model->delete($this);
    }

    /**
     * 转化为数组
     * @return type
     */
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

    /**
     * 获取对应的属性
     * @param type $name
     * @return null
     */
    public function get($name)
    {
        if (isset($this->row) && isset($this->row[$name])) {
            return $this->row[$name];
        }
        return null;
    }

    /**
     * 设置对应的属性
     * @return \ptf\IdEntity
     */
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
    
    /**
     * 同时设置多个属性
     * 貌似大家都叫populate来着
     * @param array $arr
     * @return \ptf\IdEntity
     */
    public function setMulti(array $arr)
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

    /**
     * 填充数据
     * 或许这个方法叫做populate
     * @param array $data
     */
    public function fillWith(array $data)
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
