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
        $self = get_called_class();
        return new $self();
    }

    public function findOne($id)
    {
        $table = static::table();
        $pkey = static::pkey();
        $sql = "select from `$table` where `$pkey`=?";
        $row = self::fetchRow($sql, array($id), Pdo::FETCH_ASSOC);
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
        $o->row = $row[static::pkey()];
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

    public function set()
    {
        $num = func_num_args();
        if ($num == 1 && $args = func_get_arg(0) && is_array($args)) {
            foreach ($args as $key => $value) {
                $this->row[$key] = $value;
                $this->dirty[$key] = true;
            }
        } elseif ($num == 2) {
            $key = func_get_arg(0);
            $value = func_get_arg(1);
            $this->row[$key] = $value;
        }
    }

    public function save()
    {
        if (isset($this->id)) {
            $this->insert();
        } else {
            $this->update();
        }
    }

    public function insert()
    {
        PdoWrapper::insert(static::tabel(), $this->row);
        return PdoWrapper::lastInsert();
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
