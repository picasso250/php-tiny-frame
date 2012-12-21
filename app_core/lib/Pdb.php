<?php

/**
 * Pdb
 * 用于操作PDO数据库，支持SAE（非wrapper，支持主从分离）
 *
 * @file    Pdb
 * @author  ryan <cumt.xiaochi@gmail.com>
 * @created Jul 17, 2012 10:32:14 AM
 * @version 1.1
 */

class Pdb 
{
    const MASTER = 1;
    const SLAVE = 2;
    
    private static $ms = self::MASTER; // master or slave
    private static $forceMaster = false;
    private static $config = null;
    
    private static $dbs = null; // db slave
    private static $dbm = null; // db master

    public static function setConfig($config)
    {
        if (empty($config)) {
            throw new Exception('config array empty');
        }

        $config = array_merge(
            array(
                'host' => 'localhost',
                'username' => 'root',
            ),
            $config
        );
        if (!empty(self::$config)) { // 只准运行一次
            throw new Exception('db can be set config only once');
        }

        if (isset($config['force']) && $config['force'] === 'master') {
            self::$forceMaster = true;
        }

        // username and password
        if (isset($config['username'])) {
            $username = $config['username'];
        } else {
            throw new Exception('config no username');
        }
        if (isset($config['password'])) {
            $password = $config['password'];
        }
        if (isset($config['pwd'])) {
            $password = $config['pwd'];
        }
        if (!isset($config['password']) && !isset($config['pwd'])) {
            throw new Exception('config no password');
        }

        // first look for dsn
        if (isset($config['dsn'])) {
            $dsn = $config['dsn'];
            if (isset($config['dsn_s'])) {
                $dsn_s = $config['dsn_s'];
            }
        }

        // then look for master or slave or no
        if (isset($config['host'])) {
            $conf['host'] = $config['host'];
        }
        if (isset($config['port'])) {
            $conf['port'] = $config['port'];
        }
        if (isset($config['dbname'])) {
            $conf['dbname'] = $config['dbname'];
        }
        if (isset($config['master'])) {
            $master = array_merge($conf, $config['master']);
        } else {
            $master = $conf;
        }
        if (isset($config['slave'])) {
            $slave = array_merge($conf, $config['slave']);
        } else {
            $slave = $conf;
        }
        $dsn = self::makeDsn($master);
        $dsn_s = self::makeDsn($slave);

        $config = compact('dsn', 'dsn_s', 'username', 'password');
        self::$config = $config;

    }

    private static function makeDsn($arg) 
    {
        $arr = array();
        foreach ($arg as $key => $value) {
            $arr[] = $key . '=' . $value;
        }
        return 'mysql:' . implode(';', $arr);
    }

    public static function instance($ms = self::MASTER) 
    {
        
        if ($ms == self::MASTER || self::$forceMaster) {
            if (self::$dbm === null) {
                self::$dbm = new PdoHelper(self::$config, PdoHelper::MASTER);
                if (self::$forceMaster)
                    self::$dbs = self::$dbm;
            }
        } elseif (self::$dbs === null) {
            self::$dbs = new PdoHelper(self::$config, PdoHelper::SLAVE);
        }
    }
    
    public static function fetchAll($fields, $tables, $conds=array(), $orders=array(), $tail='') 
    {
        if (empty(self::$dbs))
            self::instance(self::SLAVE);
        return self::$dbs->fetchAll($fields, $tables, $conds, $orders, $tail);
    }

    public static function fetchRow($fields, $tables, $conds=array(), $tail='') 
    {
        if (empty(self::$dbs))
            self::instance(self::SLAVE);
        return self::$dbs->fetchRow($fields, $tables, $conds, $tail);
    }

    public static function exists($tables, $conds=array(), $tail='') {
        if (empty(self::$dbs))
            self::instance(self::SLAVE);
        return self::$dbs->exists($tables, $conds, $tail);
    }

    public static function count($tables, $conds=array()) {
        if (empty(self::$dbs))
            self::instance(self::SLAVE);
        return (int) self::$dbs->count($tables, $conds);
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
    
    public static function del($table, $conds = null) {
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

    public static function exec($sql = '')
    {
        if (empty(self::$dbm)) {
            self::instance();
        }
        return self::$dbm->exec($sql);
    }

    public static function log()
    {
        $ret = array('forceMaster' => self::$forceMaster);
        if (self::$dbm) {
            $masterLog = self::$dbm->getLog();
            $ret['MASTER'] = $masterLog;
        }
        if (self::$dbs) {
            $slaveLog = self::$dbs->getLog();
            $ret['SLAVE'] = $slaveLog;
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

/**
 * it is for PDO engine to manipulate database
 */
class PdoHelper {

    const MASTER = 1;
    const SLAVE = 2;

    private $db = null;
    private $sql_history = array();

    function __construct($config, $ms = self::MASTER) {
        if (empty($config)) {
            throw new \Exception('config emtpy');
        }
        extract($config); // 安全？
        if ($ms == self::SLAVE && isset($dsn_s) && $dsn_s) {
            $dsn = $dsn_s;
        }
        $this->db = new PDO($dsn, $username, $password);
        $this->db->exec('SET character_set_connection=UTF8, character_set_results=UTF8, character_set_client=binary'); // unpredictable
    }

    private function prepare($sql) {
        $this->sql_history[] = $sql;
        return $this->db->prepare($sql); // use & ??
    }

    // 已废弃
    // private static function usefulValues($arr) {
    //     return array_filter($arr, function ($v) {
    //         return !($v === false);
    //     });
    // }
    private static function precomposite($para) {
        if (is_array($para))
            $para = implode(',', $para);
        return $para;
    }

    public function fetchRow($fields, $tables, $conds = array(), $orders = array(), $tail='') { // why there is $orders ????
        if ($conds === null) 
            $conds = array();
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

        // if only one, then chop
        $ret = $sm->fetch(PDO::FETCH_ASSOC);
        if ($ret === false)
            return false;
        if (count($ret) === 1) {
            $ret = reset($ret);
        }
        return $ret;
    }

    public function exists($tables, $conds = array(), $tail='') { // why there is $orders ????
        if ($conds === null)
            $conds = array();
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

    public function count($tables, $conds = array()) {
        if ($conds === null)
            $conds = array();
        if (!is_array($conds)) {
            d($conds);
            throw new Exception('conds not array');
        }
        $tables = self::precomposite($tables);
        $where = $conds? "WHERE ".implode(' AND ', array_keys($conds)) : '';
        $sm = $this->prepare("SELECT count(*) FROM $tables $where");
        self::bindValues($sm, array_values($conds));
        if (!$sm->execute()) {
            d($this->getLog());
            d($sm->errorInfo());
            throw new Exception;
        }
        return (int) reset($sm->fetch(PDO::FETCH_NUM));
    }

    private static function valueParaList($arr) {
        return implode(', ', array_map(function ($name, $value) {
            $find = strpos($name, '=');
            if ($value === false || $value === null || $find !== false) {
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
            if (is_object($v)) {
                d($v);
                throw new Exception("para can not convert to string");
            }
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
            d($this->getLog());
            d($sm->errorInfo());
            throw new Exception();
        }
    }

    public function lastInsertId() {
        return $this->db->lastInsertId();
    }

    public function del($table, $conds) {
        if (empty($conds))
            $conds_str = '';
        else
            $conds_str = implode(' AND ', array_keys($conds));
        $where = $conds_str ? "WHERE $conds_str" : '';
        $sm = $this->prepare("DELETE FROM $table $where");
        if ($conds)
            self::bindValues($sm, $conds);
        $r = $sm->execute();
        if (!$r) {
            d($sm->errorInfo());
            throw new Exception();
        }
        return $r;
    }

    public function update($arr, $table, $conds = array(), $tail = '') {
        if ($conds === null)
            $conds = array();
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

    public function fetchAll($fields, $tables, $conds = array(), $orders=array(), $tail='') 
    {
        // if ret arr length is 1, then simplify it?
        if (is_array($fields)) // precomposite
            $fields = implode(',', $fields);

        // maybe we don't need this...
        if (preg_match('/[\(\)]|AS/', $fields)) {
            $where_verb = "HAVING";
        } else {
            $where_verb = "WHERE";
        }
        if (is_array($tables))
            $tables = implode (',', $tables);
        if ($conds === null)
            $conds = array();
        $cond_arr = $conds;
        if (is_array($cond_arr)) { // ????
            $conds = implode(' AND ', array_keys($cond_arr));
        }
        $where = $conds? "$where_verb $conds" : '';
        if (is_array($orders))
            $orders = implode (',', $orders);
        $orders = $orders? "ORDER BY $orders" : '';
        $sm = $this->prepare("SELECT $fields FROM $tables $where $orders $tail");
        self::bindValues($sm, array_values($cond_arr));
        if ($sm->execute()) {
            $ret = array();
            while ($row = $sm->fetch(PDO::FETCH_ASSOC)) {
                if (count($row) == 1) {
                    // will it ok?
                    $row = reset($row);
                }
                $ret[] = $row;
            }
            return $ret;
        } else {
            d($this->getLog());
            d($cond_arr);
            d($sm->errorInfo());
            throw new Exception();
        }
    }

    public function exec($sql)
    {
        $this->db->exec($sql);
    }

    public function getLog() {
        return $this->sql_history;
    }

    public function close() {
        $this->db = null;
    }
}
