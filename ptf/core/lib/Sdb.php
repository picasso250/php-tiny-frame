<?php

/**
* xxx
* former Pdb is heavy, because it has to be compatiable with former files
* and then, I have not been useing Modle layer
* Now since I have used ORM, it's better to write a more light Sql Db class
*/
class Sdb
{
    private static $db = null;
    private static $config = null;
    private static $log = array();

    public static function setConfig($config = array())
    {
        $defaultConfig = array(
            'host' => 'localhost',
            'username' => 'root',
        );
        $config = array_merge($defaultConfig, $config);

        // username and password
            $username = $config['username'];
            $password = $config['password'];

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

    public static function getDb()
    {
        if (self::$db !== null)
            return self::$db;
        $config = self::$config;
        return self::$db =  new PDO($config['dsn'], $config['username'], $config['password']);
    }

    // $conds : string
    // $conds : string => [para,...]
    public static function fetch($fields, $tables, $conds = '', $orderbys = array(), $tail = '')
    {
        if (is_array($fields))
            $fields = implode(',', $fields);
        if (is_array($tables))
            $tables = implode(',', $tables);
        if (is_array($conds)) {
            $paras = reset($conds);
            $conds = reset(array_keys($conds));
        }
        $orderby = implode(',', $orderbys);

        $whereStr = $conds ? "WHERE $conds" : '';
        $orderStr = $orderby ? "ORDER BY $orderby" : '';
        $sql = "SELECT $fields FROM $tables $whereStr $orderStr $tail";

        $db = self::getDb();
        $s = $db->prepare($sql);
        if (isset($paras)) {
            $i = 0;
            foreach ($para as $value) {
                $i++;
                $s->bindValue($i, $value);
            }
        }
        if (!$s->execute()) {
            throw new Exception("db execute error", 1);
        }

        self::addLog($sql, isset($paras) ? $paras : null);
        
        $ret = array();
        while ($row = $sm->fetch(PDO::FETCH_ASSOC)) {
            if (count($row) == 1) {
                $row = reset($row);
            }
            $ret[] = $row;
        }
        return $ret;
    }

    public static function fetchRow($fields, $tables, $conds = '', $orderbys = array(), $tail = '')
    {
        $arr = self::fetch($fields, $tables, $conds, $orderbys, $tail . ' LIMIT 1');
        return $arr ? reset($arr) : false;
    }

    private static addLog($sql, $paras = null)
    {
        if ($paras) {
            $sql = array('sql' => $sql, 'paras' => $paras);
        }
        self::$log[] = $sql;
    }

    public static log()
    {
        return self::$log;
    }
}
