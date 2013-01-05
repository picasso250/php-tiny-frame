<?php

/**
 * @author ryan
 */

// get_called_class() can be replaced by static::

class Searcher
{
    private $table = null;
    private $class = null;
    private $conds = array();
    private $orders = array();
    private $limit = null;
    private $offset = 0;
    
    public function __construct($class, $table)
    {
        $this->table = $table;
        $this->class = $class;
    }

    public function filterBy($exp, $value)
    {
        $this->conds[$exp] = $value;
        return $this;
    }

    public function limit()
    {
        if (!func_num_args())
            return $this->limit;
        $this->limit = func_get_arg(0);
        return $this;
    }

    public function offset()
    {
        if (!func_num_args())
            return $this->offset;
        $this->offset = func_get_arg(0);
        return $this;
    }

    public function find()
    {
        $field = "$this->table.id";
        $limitStr = $this->limit ? "LIMIT $this->limit" : '';
        $tail = "$limitStr OFFSET $this->offset";
        $ids = Pdb::fetchAll($field, $this->table, $this->conds, $this->orders, $tail);
        return array_map(function ($id) use($this->class) {
            return new $this->class($id);
        }, $ids);
    }
}
