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

    public function table()
    {
        return $this->table;
    }

    public function pkey()
    {
        $defaultPrimaryKey = 'id';
        if (isset($this->pkey))
            return $this->pkey;
        else 
            return $defaultPrimaryKey;
    }

    public function insert(IdEntity $entity)
    {
        PdoWrapper::insert($this->table(), $entity->toArray());
        return PdoWrapper::lastInsertId();
    }

    public function update(IdEntity $entity)
    {
        $set = $entity->dirtyArray();
        if ($set) {
            return PdoWrapper::update($this->table(), $set, "`{$this->pkey()}`=?", array($entity->id()));
        }
        return 0;
    }

    public function delete()
    {
        return PdoWrapper::delete($this->table(), "`{$this->pkey()}`=?", array($entity->id()));
    }

    public function now()
    {
        return date('Y-m-d H:i:s', time());
    }
}
