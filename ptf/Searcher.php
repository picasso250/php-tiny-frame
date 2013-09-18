<?php

namespace ptf;

use \Pdo;

/**
 * @author ryan
 */
class Searcher
{
    private $class = null;

    protected $count;
    protected $columns;
    protected $table;
    protected $alias;
    protected $wheres;
    protected $havings;
    protected $groupbys;
    protected $orderbys;
    protected $distinct;
    protected $limit;
    protected $offset;
    
    public function __construct($class)
    {
        $this->class = $class;
        $this->table = $class::table();
        $this->initBuilds();
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
     */
    public function findOne() {
        $this->limit(1);
        list($sql, $values) = $this->buildSelectSql();
        $statement = $this->execute($sql, $values);
        $data = $statement->fetch(Pdo::FETCH_ASOCC);
        if ($data) {
            $class = $this->class;
            return $class::fromArray($data);
        }
        return null;
    }

    /**
     * 拉取多行数据
     * 
     * 对应 select * from t
     */
    public function findMany() {
        list($sql, $values) = $this->buildSelectSql();
        $statement = $this->execute($sql, $values);

        $rows = array();
        while (($row = $statement->fetch(Pdo::FETCH_ASOCC)) !== false) {
            $class = $this->class;
            $rows[] = $class::fromArray($row);
        }
        return $rows;
    }

    public function makeEntity($row)
    {
        $o = new $this->class;
        $o->fillWith($row);
        return $o;
    }

    public function count() 
    {
        $this->limit(1);
        $this->count = true;
        list($sql, $values) = $this->buildSelectSql();
        $statement = $this->execute($sql, $values);
        $data = $statement->fetch(Pdo::FETCH_NUM);
        if ($data) {
            return $data[0];
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

    private function buildTable() {
        if ($this->table) {
            $t = self::backQuoteWord($this->table);
        } else {
            $t = self::backQuoteWord(static::table());
        }
        if ($this->as) {
            $t .= ' AS ' . self::backQuoteWord($this->as);
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

    private function buildOrderBy() {
        if ($this->orderbys) {
            return 'ORDER BY ' . implode(',', $this->orderbys);
        }
        return '';
    }

    private function buildGroupBy() {
        if ($this->groupbys) {
            return 'GROUP BY ' . implode(',', $this->groupbys);
        }
        return '';
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
        $this->table = null;
        $this->as = null;
        $this->wheres = array();
        $this->havings = array();
        $this->groupbys = array();
        $this->orderbys = array();
        $this->distinct = false;
        $this->limit = null;
        $this->offset = null;
    }
}
