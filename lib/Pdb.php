<?php
!defined('IN_PTF') && exit('ILLEGAL EXECUTION');

/**
 * Pdb
 * 用于操作PDO数据库，支持SAE（非wrapper，支持主从分离）
 *
 * @file    Pdb
 * @author  ryan <cumt.xiaochi@gmail.com>
 * @created Jul 17, 2012 10:32:14 AM
 * @version 0.9
 * todo 给表名加上反引号
 */

class Pdb {

    const MASTER = 1;
    const SLAVE = 2;
    
    private static $ms = self::MASTER;
    private static $config = null;
    
    private static $dbs = null; // db slave
    private static $dbm = null; // db master

    public static function setConfig($config) {
        if (!empty(self::$config)) { // 只准运行一次
            throw new Exception('db can be set config only once');
        }
        self::$config = $config;
    }

    public static function instance($ms = self::MASTER) {
        
        if ($ms == self::MASTER) {
            if (self::$dbm === null) {
                self::$dbm = new PdoHelper(self::$config, PdoHelper::MASTER);
            }
        } else {
            if (self::$dbs === null) {
                self::$dbs = new PdoHelper(self::$config, PdoHelper::SLAVE);
            }
        }
    }
    
    public static function fetchAll($fields, $tables, $conds=array(), $orders=array(), $tail='') {
        if (empty(self::$dbs)) {
            self::instance(self::SLAVE);
        }
        return self::$dbs->fetchAll($fields, $tables, $conds, $orders, $tail);
    }

    public static function fetchRow($fields, $tables, $conds=array(), $tail='') { // why there is $orders ????
        if (empty(self::$dbs)) {
            self::instance(self::SLAVE);
        }
        return self::$dbs->fetchRow($fields, $tables, $conds, $tail);
    }

    public static function exists($tables, $conds=array(), $tail='') {
        if (empty(self::$dbs)) {
            self::instance(self::SLAVE);
        }
        return self::$dbs->exists($tables, $conds, $tail);
    }

    public static function count($tables, $conds=array()) {
        if (empty(self::$dbs)) {
            self::instance(self::SLAVE);
        }
        return self::$dbs->count($tables, $conds);
    }

    public static function insert($arr, $table, $tail='') {
        if (empty(self::$dbm)) {
            self::instance(self::MASTER);
        }
        return self::$dbm->insert($arr, $table, $tail);
    }

    public static function lastInsertId() {
        if (empty(self::$dbm)) {
            self::instance();
        }
        return self::$dbm->lastInsertId();
    }
    
    public static function del($table, $conds) {
        if (empty(self::$dbm)) {
            self::instance();
        }
        return self::$dbm->del($table, $conds);
    }

    public static function update($arr, $table, $conds=array(), $tail='') {
        if (empty(self::$dbm)) {
            self::instance();
        }
        return self::$dbm->update($arr, $table, $conds, $tail);
    }

    

    public static function getLog() {
        $ret = array();
        if (self::$dbm) {
            $ret['MASTER'] = self::$dbm->getLog();
        }
        if (self::$dbs) {
            $ret['SLAVE'] = self::$dbs->getLog();
        }
        return $ret;
    }

    public function close() {
        if (self::$dbm) {
            self::$dbm->close();
            self::$dbm = null;
        }
        if (self::$dbs) {
            self::$dbs->close();
            self::$dbs = null;
        }
    }
}

class PdoHelper {

    const MASTER = 1;
    const SLAVE = 2;

    private $db = null;
    private $sql_history = array();

    function __construct($config, $ms = self::MASTER) {
        extract($config); // 安全？
        if ($ms == self::SLAVE && isset($dsn_s)) {
            $dsn = $dsn_s;
        }
        $this->db = new PDO($dsn, $username, $pwd);
        $this->db->exec('SET character_set_connection=UTF8, character_set_results=UTF8, character_set_client=binary'); // unpredictable
    }

    private function prepare($sql) {
        $this->sql_history[] = $sql;
        return $this->db->prepare($sql); // use & ??
    }

    // 已废弃
    private static function usefulValues($arr) {
        return array_filter($arr, function ($v) {
            return !($v === false);
        });
    }
    private static function precomposite($para) {
        if (is_array($para))
            $para = implode(',', $para);
        return $para;
    }

    public function fetchRow($fields, $tables, $conds=array(), $orders=array(), $tail='') { // why there is $orders ????
        $fields = self::precomposite($fields);
        $tables = self::precomposite($tables);
        $where = $conds? "WHERE ".implode(' AND ', array_keys($conds)) : '';
        $sm = $this->prepare("SELECT $fields FROM $tables $where $tail LIMIT 1");
        self::bindValues($sm, array_values($conds));
        if (!$sm->execute()) {
            d($this->getLog());
            d($sm->errorInfo());
            throw new Exception();
        }
        return $sm->fetch(PDO::FETCH_ASSOC);
    }

    public function exists($tables, $conds=array(), $tail='') { // why there is $orders ????
        $tables = self::precomposite($tables);
        $where = $conds? "WHERE ".implode(' AND ', array_keys($conds)) : '';
        $sm = $this->prepare("SELECT count(*) FROM $tables $where $tail LIMIT 1");
        self::bindValues($sm, array_values($conds));
        if (!$sm->execute()) {
            d($this->getLog());
            d($sm->errorInfo());
            throw new Exception();
        }
        return reset($sm->fetch(PDO::FETCH_NUM)) == 1;
    }

    public function count($tables, $conds=array()) {
        $tables = self::precomposite($tables);
        $where = $conds? "WHERE ".implode(' AND ', array_keys($conds)) : '';
        $sm = $this->prepare("SELECT count(*) FROM $tables $where");
        self::bindValues($sm, array_values($conds));
        if (!$sm->execute()) {
            d($this->getLog());
            throw new Exception;
        }
        return reset($sm->fetch(PDO::FETCH_NUM));
    }

    private static function valueParaList($arr) {
        return implode(',', array_map(function ($name, $value) {
            if ($value === false || $value === null) {
                return $name;
            } else {
                return $name.'=?';
            }
        }, array_keys($arr), array_values($arr)));
    }

    private static function bindValues(&$sm, $arr) {
        $i = 1;
        foreach ($arr as $v) {
            if ($v === false || $v === null)
                continue;
            $sm->bindValue($i, $v);
            $i++;
        }
    }

    public function insert($arr, $table_name, $tail='') {
        $para_list = self::valueParaList($arr);
        $sm = $this->prepare("INSERT INTO $table_name SET $para_list $tail");
        self::bindValues($sm, array_values($arr));
        $r = $sm->execute();
        if (!$r) {
            d($this);
            d($this->getLog());
            d($sm->errorInfo());
            throw new Exception();
        }
    }

    public function lastInsertId() {
        return $this->db->lastInsertId();
    }

    public function del($table, $conds) {
        $conds_str = implode(' AND ', array_keys($conds));
        $sm = $this->prepare("DELETE FROM $table WHERE $conds_str");
        self::bindValues($sm, $conds);
        $r = $sm->execute();
        if (!$r) {
            throw new Exception();
        }
        return $r;
    }

    public function update($arr, $table, $conds = array(), $tail = '') {
        $para_list = self::valueParaList($arr);
        $conds_str = implode(' AND ', array_keys($conds));
        $where = $conds_str? "WHERE $conds_str" : '';
        $sm = $this->prepare("UPDATE $table SET $para_list $where $tail");
        self::bindValues($sm, array_merge(array_values($arr), array_values($conds)));
        if (!$sm->execute()) {
            d($this->getLog());
            d($sm->errorInfo());
            throw new Exception();
        }
    }

    public function fetchAll($fields, $tables, $conds=array(), $orders=array(), $tail='') {
        if (is_array($fields)) // precomposite
            $fields = implode(',', $fields);
        if (is_array($tables))
            $tables = implode (',', $tables);
        $cond_arr = $conds;
        if (is_array($cond_arr)) { // ????
            $conds = implode(' AND ', array_keys($cond_arr));
        }
        $where = $conds? "WHERE $conds" : '';
        if (is_array($orders))
            $orders = implode (',', $orders);
        $orders = $orders? "ORDER BY $orders" : '';
        $sm = $this->prepare("SELECT $fields FROM $tables $where $orders $tail");
        self::bindValues($sm, array_values($cond_arr));
        if ($sm->execute()) {
            $ret = array();
            while ($row = $sm->fetch(PDO::FETCH_ASSOC)) {
                $ret[] = $row;
            }
            return $ret;
        } else {
            d($this->getLog());
            d($sm->errorInfo());
            throw new Exception();
        }
    }

    public function getLog() {
        return $this->sql_history;
    }

    public function close() {
        $this->db = null;
    }
}

?>
