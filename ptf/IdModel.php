<?php

namespace ptf;

/**
 * @author ryan
 */
class IdModel extends Model
{
    protected static $pkey;

    protected $id;

    public static function create()
    {
        return IdEntity::make($this, array());
    }

    public function findOne($id)
    {
        if ($id === null) {
            return null;
        }
        
        $table = $this->table();
        $pkey = $this->pkey();
        $sql = "SELECT * FROM `$table` WHERE `$pkey`=?";
        $row = PdoWrapper::fetchRow($sql, array($id));
        return IdEntity::make($this, $row);
    }

    public static function fetchMany($sql, $args = array())
    {
        $rows = PdoWrapper::fetchAll($sql, $args);
        if ($rows === false) {
            return array();
        }

        $ret = array();
        $pkey = $this->pkey();
        foreach ($rows as $key => $value) {
            $ret[$value[$pkey]] = IdEntity::make($this, $value);
        }
        return $ret;
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
        if (isset($this->id) && $this->id) {
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
