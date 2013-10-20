<?php

namespace ptf;

/**
 * 数据库访问基类
 * 
 * @author ryan
 */
class IdDao extends Dao
{
    protected $pkey;

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


}
