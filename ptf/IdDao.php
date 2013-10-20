<?php

namespace ptf;

/**
 * 数据库访问基类
 * 
 * @author ryan
 */
class IdDao extends Searcher
{
    protected $table;
    protected $pkey;
    protected $id;

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
     * 返回键名
     * @return string
     */
    public function pkey()
    {
        $defaultPrimaryKey = 'id';
        if (isset($this->pkey)) {
            return $this->pkey;
        } else {
            return $defaultPrimaryKey;
        }
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
     * 更新
     * @param \ptf\IdEntity $entity
     * @return int
     */
    public function update(IdEntity $entity)
    {
        $set = $entity->dirtyArray();
        if ($set) {
            return PdoWrapper::update($this->table, $set, "`{$this->pkey}`=?", array($entity->id()));
        }
        return 0;
    }

    /**
     * 删除
     * @param \ptf\IdEntity $entity
     * @return type
     */
    public function delete(IdEntity $entity)
    {
        return PdoWrapper::delete($this->table(), "`{$this->pkey}`=?", array($entity->id()));
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
