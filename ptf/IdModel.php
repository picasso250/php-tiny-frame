<?php

namespace ptf;

use \PDO;

/**
 * @author ryan
 */
class IdModel extends Model
{
    protected static $pkey;

    protected $id;

    public static function create()
    {
        $self = get_called_class();
        return new $self();
    }

    public function findOne($id)
    {
        if ($id === null) {
            return null;
        }
        
        var_dump(get_called_class());
        $table = self::table();
        $pkey = self::pkey();
        $sql = "SELECT * FROM `$table` WHERE `$pkey`=?";
        $row = PdoWrapper::fetchRow($sql, array($id), PDO::FETCH_ASSOC);
        return static::fromArray($row);
    }

    public static function fetchMany($sql, $args = array())
    {
        $rows = PdoWrapper::fetchAll($sql, $args);
        if ($rows === false) {
            return null;
        }
        $ret = array();
        $pkey = static::pkey();
        foreach ($rows as $key => $value) {
            $ret[$value[$pkey]] = static::fromArray($value);
        }
        return $ret;
    }

    public static function fromArray($arr)
    {
        $o = parent::fromArray($arr);
        $o->id = $arr[static::pkey()];
        return $o;
    }

    public static function pkey()
    {
        $defaultPrimaryKey = 'id';
        if (isset(static::$pkey))
            return static::$pkey;
        else 
            return $defaultPrimaryKey;
    }

    public function id()
    {
        return $this->row[static::pkey()];
    }

    public function save()
    {
        if (isset($this->id) && $this->id) {
            $this->update();
        } else {
            $this->insert();
        }
        $thsi->dirty = array();
        return $this;
    }

    public function insert()
    {
        PdoWrapper::insert(static::table(), $this->row);
        return PdoWrapper::lastInsertId();
    }

    public function update()
    {
        $set = $this->dirtyArray();
        if ($set) {
            $where = array(static::pkey(), $this->id);
            return PdoWrapper::update(static::tabel(), $set, $where);
        }
        return 0;
    }

    public function dirtyArray()
    {
        $set = array();
        foreach ($this->dirty as $key => $value) {
            $set[$key] = $this->row[$key];
        }
        return $set;
    }

    public function delete()
    {
        $where = array(static::pkey(), $this->id);
        return PdoWrapper::delete(static::table(), $where);
    }
}
