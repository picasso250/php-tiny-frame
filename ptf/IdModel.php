<?php

namespace ptf;

/**
 * @author ryan
 */
class IdModel extends Searcher
{
    protected $pkey;

    protected $id;

    public function create()
    {
        return $this->makeEntity(array());
    }

    protected function makeEntity($row)
    {
        return IdEntity::make($this, $row);
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

    public function save(IdEntity $entity)
    {
        if ($entity->id()) {
            $this->update($entity);
        } else {
            $this->insert($entity);
        }
        $entity->clean();
        return $this;
    }

    public function insert($entity)
    {
        PdoWrapper::insert($this->table(), $entity->toArray());
        return PdoWrapper::lastInsertId();
    }

    public function update($entity)
    {
        $set = $entity->dirtyArray();
        if ($set) {
            $where = array($this->pkey(), $entity->id());
            return PdoWrapper::update($this->tabel(), $set, $where);
        }
        return 0;
    }

    public function delete()
    {
        $where = array($this->pkey(), $entity->id());
        return PdoWrapper::delete($this->table(), $where);
    }
}
