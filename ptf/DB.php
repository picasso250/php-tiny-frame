<?php

class DB
{
    public $lastSql = '';
    public $pdo;

    public function __construct($dsn, $username, $password)
    {
        $pdo = new Pdo($dsn, $username, $password, array(Pdo::MYSQL_ATTR_INIT_COMMAND => 'set names utf8'));
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo = $pdo;
    }

    function execute($sql, $values = array())
    {
        if (is_int(key($values))) {
            $param_arr = array_map(function($e){return "'$e'";}, $values);
            array_unshift($param_arr, str_replace('?', '%s', $sql));
            $this->lastSql = call_user_func_array('sprintf', $param_arr);
        } else {
            $print_sql = $sql;
            foreach ($values as $k => $v) {
                $print_sql = str_replace(':'.$k, "'$v'", $print_sql);
            }
            $this->lastSql = $print_sql;
        }
        // echo "$this->lastSql\n";

        $stmt = $this->pdo->prepare($sql);
        if (!$stmt->execute($values)) {
            foreach ($stmt->errorInfo() as $key => $value) {
                echo "$key: $value\n";
            }
            throw new Exception("bad sql", 1);
        }
        return $stmt;
    }
    public function update($table, $set, $where)
    {
        $func = function ($field) {
            return "$field=?";
        };
        $join = function ($kvs) use ($func) {
            return implode(',', array_map($func, array_keys($kvs)));
        };
        $set_str = $join($set);
        $where_str = $join($where);
        $sql = "update $table set $set_str where $where_str";
        return $this->execute($sql, array_merge(array_values($set), array_values($where)));
    }
    public function insert($table, $values)
    {
        $keys = array_keys($values);
        $columns = implode(',', $keys);
        $value_str = implode(',', array_map(function($field){
            return ":$field";
        }, $keys));
        $sql = "insert into $table ($columns)values($value_str)";
        return $this->execute($sql, $values);
    }
    public function __call($name, $args)
    {
        return call_user_func_array(array($this->pdo, $name), $args);
    }
    public function queryAll($sql, $values=array())
    {
        $stmt = $this->execute($sql, $values);
        return $stmt->fetchAll(Pdo::FETCH_ASSOC);
    }
    public function queryRow($sql, $values=array())
    {
        $stmt = $this->execute($sql, $values);
        return $stmt->fetch(Pdo::FETCH_ASSOC);
    }
}
