<?php

namespace ptf;

use \PDO;

/**
 * @author ryan
 */
class Searcher
{
    protected $count;
    protected $columns;
    protected $table;
    protected $alias;
    protected $joins;
    protected $wheres;
    protected $havings;
    protected $groupbys;
    protected $orderbys;
    protected $distinct;
    protected $limit;
    protected $offset;
    
    public function __construct()
    {
        $this->initBuilds();
    }

    public function columns()
    {
        $num_args = func_num_args();
        if ($num_args == 1) {
            foreach (func_get_arg(0) as $key => $value) {
                if (is_int($key)) {
                    $this->columns[] = self::backQuote($value);
                } else {
                    $this->columns[] = "`$key` AS `$value`";
                }
            }
        }
    }

    public function column()
    {
        $num_args = func_num_args();
        if ($num_args == 1) {
            $this->columns[] = self::backQuote(func_get_arg(0));
        } elseif ($num_args == 2) {
            $column = self::backQuote(func_get_arg(0));
            $alias = self::backQuoteWord(func_get_arg(1));
            $this->columns[] = "$column AS $alias";
        }
    }

    public function from($table)
    {
        if (is_string($table)) {
            $this->table_ = $table;
        } elseif (is_array($table)) {
            $this->table_ = key($table);
            $this->alias = reset($table);
        }
        return $this;
    }

    public function alias($alias)
    {
        $this->alias = "`$alias`";
    }

    /**
     * 对应sql中的where
     * 
     * @example
     *     where('username', 'Jack') // WHERE `username` = 'Jack'
     *     where(array('id' => 3)) // WHERE `id` = '3'
     *     where(array('id' => array(3, 7, 11))) // WHERE `id` in ('3', '7', '11')
     *     where('id', 'not in', array(3, 7, 11)) // WHERE `id` not in ('3', '7', '11')
     *     where(array('(id=3 or id=4)', 'status' => 1)) // WHERE (id=3 or id=4) and `status` = '1'
     */
    public function where()
    {
        $arg_num = func_num_args();

        if ($arg_num == 1) { // where(array(key=>value,...))
            $a = func_get_arg(0);

            // expression
            if (is_string($a)) {
                $this->wheres[] = array($a, array());
                return $this;
            }

            // 解析where数组
            foreach ($a as $key => $value) {
                if (is_int($key)) {
                    // 支持直接使用sql表达式，以及带参数的sql表达式
                    if (is_string($value)) {
                        $this->whereExpr($value);
                    } elseif (is_array($value)) {
                        $this->whereExpr($value[0], $value[1]);
                    }
                } else {
                    // 支持=和in操作符
                    if (is_array($value)) {
                        $this->whereIn($key, $value);
                    } else {
                        $this->whereEqual($key, $value);
                    }
                }
            }
        } elseif ($arg_num == 2) {
            $key = (func_get_arg(0));
            $value = func_get_arg(1);
            // 支持=和in操作符
            if (is_array($value)) {
                $this->whereIn($key, $value);
            } else {
                $this->whereEqual($key, $value);
            }
        } elseif ($arg_num == 3) {
            $key = self::backQuote(func_get_arg(0));
            $operator = func_get_arg(1);
            $value = func_get_arg(2);
            if (is_array($value)) {
                $placeholder = array_map(function ($e) {return '?';}, $value);
                $placeholder = '(' . implode(', ', $placeholder) . ')';
            }
            if (!is_array($value)) {
                $value = array($value);
            }
            $this->wheres[] = array("$key $operator $placeholder", $value);
        }
        return $this;
    }

    /**
     * 对应 sql 中的 where sql表达式
     * 
     * @example
     *     where('id=? or id=?', array('3', '4')) // WHERE WHERE (id=3 or id=4)
     */
    public function whereExpr($expr, $values = array()) 
    {
        $this->wheres[] = array("($expr)", $values);
        return $this;
    }

    // where id=3
    public function whereEqual($key, $value)
    {
        $key = self::backQuote($key);
        if (is_object($value)) {
            $value = $value->{$value::pkey()};
        }
        $this->wheres[] = array("$key = ?", array($value));
    }

    // where id in (3, 4, 5)
    public function whereIn($key, $value)
    {
        return $this->whereOpArray($key, 'IN', $value);
    }

    // where id not in (3, 4, 5)
    public function whereNotIn($key, $value)
    {
        return $this->whereOpArray($key, 'NOT IN', $value);
    }

    protected function whereOpArray($key, $op, $arr)
    {
        $key = self::backQuote($key);
        if (is_array($value)) {
            $placeholder = array_map(function ($e) {return '?';}, $value);
            $placeholder = implode(', ', $placeholder);
        }

        if (!is_array($value)) {
            $value = array($value);
        }

        $this->wheres[] = array("$key $op ($placeholder)", $value);
        return $this;
    }

    /**
     * 对应 SQL 中的 HAVING
     * 
     * @example
     *     where('username', 'Jack') // WHERE `username` = 'Jack'
     *     where(array('id' => 3)) // WHERE `id` = '3'
     *     where(array('id' => array(3, 7, 11))) // WHERE `id` in ('3', '7', '11')
     *     where('id', 'not in', array(3, 7, 11)) // WHERE `id` not in ('3', '7', '11')
     *     where(array('(id=3 or id=4)', 'status' => 1)) // WHERE (id=3 or id=4) and `status` = '1'
     */
    public function having()
    {
        $arg_num = func_num_args();

        if ($arg_num == 1) { // where(array(key=>value,...))
            $a = func_get_arg(0);

            // expression
            if (is_string($a)) {
                $this->havings[] = array($a, array());
                return $this;
            }

            // 解析where数组
            foreach ($a as $key => $value) {
                if (is_int($key)) {
                    // 支持直接使用sql表达式，以及带参数的sql表达式
                    if (is_string($value)) {
                        $this->havingExpr($value);
                    } elseif (is_array($value)) {
                        $this->havingExpr($value[0], $value[1]);
                    }
                } else {
                    // 支持=和in操作符
                    if (is_array($value)) {
                        $this->havingIn($key, $value);
                    } else {
                        $this->havingEqual($key, $value);
                    }
                }
            }
        } elseif ($arg_num == 2) {
            $key = (func_get_arg(0));
            $value = func_get_arg(1);
            // 支持=和in操作符
            if (is_array($value)) {
                $this->havingIn($key, $value);
            } else {
                $this->havingEqual($key, $value);
            }
        } elseif ($arg_num == 3) {
            $key = self::backQuote(func_get_arg(0));
            $operator = func_get_arg(1);
            $value = func_get_arg(2);
            if (is_array($value)) {
                $placeholder = array_map(function ($e) {return '?';}, $value);
                $placeholder = '(' . implode(', ', $placeholder) . ')';
            }
            if (!is_array($value)) {
                $value = array($value);
            }
            $this->havings[] = array("$key $operator $placeholder", $value);
        }
        return $this;
    }

    /**
     * 对应 sql 中的 having sql表达式
     * 
     * @example
     *     where('id=? or id=?', array('3', '4')) // WHERE WHERE (id=3 or id=4)
     */
    public function havingExpr($expr, $values = array()) 
    {
        $this->havings[] = array("($expr)", $values);
        return $this;
    }

    // where id=3
    public function havingEqual($key, $value)
    {
        $key = self::backQuote($key);
        if (is_object($value)) {
            $value = $value->{$value::pkey()};
        }
        $this->havings[] = array("$key = ?", array($value));
    }

    // having id in (3, 4, 5)
    public function havingIn($key, $value)
    {
        return $this->havingOpArray($key, 'IN', $value);
    }

    // having id not in (3, 4, 5)
    public function havingNotIn($key, $value)
    {
        return $this->havingOpArray($key, 'NOT IN', $value);
    }

    protected function havingOpArray($key, $op, $arr)
    {
        $key = self::backQuote($key);
        if (is_array($value)) {
            $placeholder = array_map(function ($e) {return '?';}, $value);
            $placeholder = implode(', ', $placeholder);
        }

        if (!is_array($value)) {
            $value = array($value);
        }

        $this->havings[] = array("$key $op ($placeholder)", $value);
        return $this;
    }

    public function orderBy($exp)
    {
        $arg_num = func_num_args();
        if ($arg_num == 1) {
            $a = func_get_arg(0);
            if (is_array($a)) {
                foreach ($a as $key => $value) {
                    $key = self::backQuote($key);
                    $this->orderbys[] = "$key $value";
                }
            } elseif (is_string($a)) {
                $this->orderbys[] = $a;
            }
        } elseif ($arg_num == 2) {
            $field = self::backQuote(func_get_arg(0));
            $sort = strtoupper(func_get_arg(1));
            $this->orderbys[] = "$field $sort";
        }
        return $this;
    }

    public function limit()
    {
        if (!func_num_args())
            return $this->limit;
        $this->limit = func_get_arg(0);
        return $this;
    }

    public function join($table, $on, $columns = null)
    {
        return $this->_join('JOIN', $table, $on, $columns);
    }

    public function leftJoin($table, $on, $columns = null)
    {
        return $this->_join('LEFT JOIN', $table, $on, $columns);
    }

    public function rightJoin($table, $on, $columns = null)
    {
        return $this->_join('RIGHT JOIN', $table, $on, $columns);
    }

    public function fullJoin($table, $on, $columns = null)
    {
        return $this->_join('OUT JOIN', $table, $on, $columns);
    }

    private function _join($method, $table, $on, $columns = null)
    {
        if (is_string($table)) {
            $ti = $table;
            $table = "`$table`";
        } elseif (is_array($table)) {
            $a = reset($table);
            $ti = key($table);
            $table = "`$ti` AS `$a`";
        }

        if (is_array($on)) {
            $n = count($on);
            if ($n == 2) {
                $c1 = self::backQuote($on[0]);
                $c2 = self::backQuote($on[1]);
                $on = "$c1 = $c2";
            } elseif ($n == 3) {
                $c1 = self::backQuote($on[0]);
                $c2 = self::backQuote($on[2]);
                $on = "$c1 $on[1] $c2";
            }
        }

        if ($columns) {
            $this->joinColumns($columns, $ti);
        }
        $join = "$method $table ON $on";
        $this->joins[] = $join;
        return $this;
    }

    protected function joinColumns($columns, $table)
    {
        if (empty($this->columns)) {
            $this->columns[] = "`$this->table`.*";
        }
        if (is_string($columns) && $columns != '*') {
            if ($columns == '*') {
                $this->columns[] = "`$table`.*";
            } else {
                $this->columns[] = $this->joinColumn($columns, $table);
            }
        } elseif (is_array($columns)) {
            foreach ($columns as $key => $value) {
                if (is_int($key)) {
                    $this->columns[] = $this->joinColumn($value);
                } else {
                    $name = $this->joinColumn($key);
                    $this->columns[] = "$name AS `$value`";
                }
            }
        }
    }

    protected function joinColumn($column, $table)
    {
        $column = self::backQuoteWord($column);
        if (strpos($column, '.') === false) {
            return "`$table`.$column";
        } else {
            return $column;
        }
    }

    public function offset()
    {
        if (!func_num_args())
            return $this->offset;
        $this->offset = func_get_arg(0);
        return $this;
    }

    /**
     * 拉取一行数据
     * 
     * 对应select * from t limit 1
     * @return 找到返回对象，找不到返回null
     */
    public function findOne($id = null) {
        if ($id === null) {
            $this->limit(1);
            list($sql, $values) = $this->buildSelectSql();
        } else {
            $table = $this->table();
            $pkey = $this->pkey();
            $sql = "SELECT * FROM `$table` WHERE `$pkey`=?";
            $values = array($id);
        }
        $statement = $this->execute($sql, $values);
        $data = $statement->fetch(PDO::FETCH_ASSOC);
        if ($data) {
            return $this->makeEntity($data);
        }
        return null;
    }

    /**
     * 拉取多行数据
     * 
     * 对应 select * from t
     * @return 找到返回数组，包含目标对象，如无数据，返回空
     */
    public function findMany($sql = null, $args = array()) {
        $ret = array();
        if ($sql === null) {
            foreach ($this->findArray() ?: array() as $key => $value) {
                $o = $this->makeEntity($value);
                $ret[$o->id()] = $o;
            }
        } else {
            $rows = PdoWrapper::fetchAll($sql, $args);
            if ($rows === false) {
                return array();
            }

            $ret = array();
            $pkey = $this->pkey();
            foreach ($rows as $key => $value) {
                $ret[$value[$pkey]] = $this->makeEntity($value);
            }
        }
        return $ret;
    }

    public function findArray() {
        list($sql, $values) = $this->buildSelectSql();
        return PdoWrapper::fetchAll($sql, $values);
    }

    public function count() 
    {
        $this->limit(1);
        $this->count = true;
        list($sql, $values) = $this->buildSelectSql();
        $statement = $this->execute($sql, $values);
        $data = $statement->fetch(PDO::FETCH_NUM);
        if ($data) {
            return (int) ($data[0]);
        }
        return null;
    }

    public function groupBy()
    {
        $arg_num = func_num_args();
        if ($arg_num == 1) {
            $args = func_get_arg(0);
            if (is_array($args)) {
                foreach ($a as $key => $value) {
                    $this->groupbys[] = self::backQuote($value);
                }
            }
        } elseif ($arg_num > 1) {
            $args = func_get_args();
            foreach ($a as $key => $value) {
                if (preg_match('/^\w+$/', $value)) {
                    $this->groupbys[] = self::backQuote($value);
                } else {
                    $this->groupbys[] = $value;
                }
            }
        }
        return $this;
    }

    public function update($set)
    {
        if ($set) {
            list($whereStr, $whereVals) = $this->buildWhere();
            return PdoWrapper::update($this->table, $set, $whereStr, $whereVals);
        }
        return 0;
    }

    public function delete()
    {
        list($whereStr, $whereVals) = $this->buildWhere();
        return PdoWrapper::delete($this->table, $whereStr, $whereVals);
    }

    protected function buildSelectSql() {
        $field = $this->buildColumns();
        $table = $this->buildTable();
        list($where, $whereVals) = $this->buildWhere();
        list($having, $havingVals) = $this->buildHaving();
        $join = implode(' ', $this->joins);
        $groupBy = implode(', ', $this->groupbys);
        $orderBy = implode(', ', $this->orderbys);
        $limit = $this->limit === null ? '' : " LIMIT $this->limit";
        $offset = $this->offset ? " OFFSET $this->offset" : '';
        $sql = "SELECT"
                . ($this->distinct ? ' DISTINCT' : '')
                . " $field FROM $table"
                . ($join ? " $join" : '')
                . ($where ? " $where" : '')
                . ($groupBy ? " GROUP BY $groupBy" : '')
                . ($having ? " $having" : '')
                . ($orderBy ? " ORDER BY $orderBy" : '')
                . $limit . $offset;
        $values = array_merge($whereVals, $havingVals);
        return array($sql, $values);
    }

    private function buildColumns()
    {
        if ($this->count) {
            $field = 'COUNT(*)';
        } elseif (empty($this->columns)) {
            $field = '*';
        } else {
            $field = implode(', ', $this->columns);
        }
        return $field;
    }

    private function buildTable() {
        $t = self::backQuoteWord($this->table_);
        if ($this->alias) {
            $t .= " AS `$this->alias`";
        }
        return $t;
    }

    private function buildWhere() {
        if ($this->wheres) {
            list($str, $values) = self::buildPredicates($this->wheres);
            return array('WHERE ' . $str, $values);
        }
        return array('', array());
    }

    private function buildHaving() {
        if ($this->havings) {
            list($str, $values) = self::buildPredicates($this->havings);
            return array('HAVING ' . $str, $values);
        }
        return array('', array());
    }

    private static function buildPredicates($raws) {
        $strs = array();
        $values = array();
        foreach ($raws as $kv) {
            $strs[] = $str = $kv[0];
            $vals = $kv[1];
            $values = array_merge($values, $vals);
        }
        $str = implode(' AND ', $strs);
        return array($str, $values);
    }

    public static function backQuote($key) {
        if (strpos($key, '.')) {
            $arr = explode('.', $key);
            return self::backQuoteWord($arr[0]) . '.' . self::backQuoteWord($arr[1]);
        } else {
            return self::backQuoteWord($key);
        }
    }

    public static function backQuoteWord($key) {
        if (strpos($key, '`') === false && $key != '*') {
            return "`$key`";
        }
        return $key;
    }

    public function execute($sql, $args = array())
    {
        $r = PdoWrapper::execute($sql, $args);
        $this->initBuilds();
        return $r;
    }

    /**
     * 重置搜索条件
     */
    protected function initBuilds()
    {
        $this->count = false;
        $this->columns = array();
        $this->table_ = $this->table;
        $this->alias = null;
        $this->joins = array();
        $this->wheres = array();
        $this->havings = array();
        $this->groupbys = array();
        $this->orderbys = array();
        $this->distinct = false;
        $this->limit = null;
        $this->offset = null;
    }
}
