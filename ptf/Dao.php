<?php

namespace ptf;

/**
 * 数据库访问基类
 * 
 * @author ryan
 */
class Dao extends Searcher
{
    protected $table;

    /**
     * 新建一个实体
     * @return IdEntity
     */
    public function create()
    {
        return $this->makeEntity(array());
    }

    protected function makeEntity($row)
    {
        preg_match('/^(\w+)Dao$/', get_called_class(), $matches);
        $classname = $matches[1];
        return $classname::make($this, $row);
    }

    /**
     * 返回表名
     * @return string
     */
    public function table()
    {
        return $this->table;
    }

    /**
     * 插入
     * @param \ptf\IdEntity $entity
     * @return type
     */
    public function insert(IdEntity $entity)
    {
        PdoWrapper::insert($this->table(), $entity->toArray());
        return PdoWrapper::lastInsertId();
    }

    /**
     * 获取现在的时间，依mysql表示方法
     * @return string
     */
    public function now()
    {
        return date('Y-m-d H:i:s', time());
    }
}
