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
    
    public function __construct($class)
    {
        $this->class = $class;
        $this->table = $class::table();
    }

    public function filterBy($exp, $value)
    {
        if (is_a($value, 'BasicModel'))
            $value = $value->id;

        $relationMap = $this->class::relationMap();
        $tableDotKey = preg_match('/\b(\w+)\.(\w+)\b/', $exp, $matches); // table.key = ?
        $tableDotId = isset($relationMap[$exp]);
        
        if ($tableDotKey) {
            $ref = $matches[1];
            $refKey = $matches[2];
            $refTable = $relationMap[$ref];
            $this->conds["$refTable.$refKey=?"] = $value;
        } elseif ($tableDotId) {
            $refTable = $relationMap[$exp];
            $this->conds["$refTable.id=?"] = $value;
        } else {
            $this->conds[$exp] = $value;
        }
        if ($tableDotKey || $tableDotId)
            $this->conds["$this->table.$ref=$refTable.id"] = null;
        return $this;
    }

    public function orderBy($exp)
    {
        $this->orders[] = "$this->table.$exp";
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
