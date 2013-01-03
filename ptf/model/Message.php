<?php
!defined('IN_PTF') && exit('ILLEGAL EXECUTION');

include_once Pf::model('Model');

/**
 *
 * @author  ryan <cumt.xiaochi@gmail.com>
 */
class Message extends Model {

    public static $table = 'msg';
    
    public static function post($name, $text, $email = '') {
        $arr = compact('name', 'text', 'email');
        $arr['time=NOW()'] = null;
        Pdb::insert($arr, self::$table);
        $id = Pdb::lastInsertId();
        return new self(compact('id', 'name', 'text', 'email'));
    }
    
    public function getInfo() {
        if (!empty($this->info)) return $this->info;
        $this->info = Pdb::fetchRow('*', self::$table, $this->selfCond());
        return $this->info;
    }

    public static function listM($conds=array()) {
        extract(self::defaultConds($conds));
        $orders = 'id DESC';
        $tail = "LIMIT $limit OFFSET $offset";
        return array_map(function ($info) {
            return new Message($info);
        }, Pdb::fetchAll('*', self::$table, array(), $orders, $tail));
    }
    
    public static function count() {
        return Pdb::count(self::$table);
    }

    private static function defaultConds($conds) {
        return array_merge(array(
            'limit' => 10,
            'offset' => 0,
        ), $conds);
    }

    public function __get($name) {
        if ($name == 'id') return $this->id;
        if (empty($this->info)) {
            $this->info = $this->getInfo();
        }
        return $this->info[$name];
    }

}

?>
