<?php

namespace ptf;

/**
 * 
 * get set 可以设置懒加载的服务
 * @author ryan
 */
class Controller
{
    public $viewRoot;

    private $vars;
    private $lazies = array('names' => array(), 'values' => array());

    public function __get($key)
    {
        if (array_key_exists($key, $this->vars)) {
            return $this->vars[$key];
        } elseif (array_key_exists($key, $this->lazies['names'])) {
            if (!array_key_exists($key, $this->lazies['values'])) {
                $this->lazies['values'][$key] = $this->lazies['names'][$key]();
            }
            return $this->lazies['values'][$key];
        }
        return null;
    }

    public function __set($key, $value)
    {
        if (is_callable($value)) {
            $this->$lazies['names'][$key] = $value;
        } else {
            $this->$vars[$key] = $value;
        }
    }

    protected function params()
    {
        $args = func_get_args();
        if ($args) {
            $ret = array();
            foreach ($args as $name) {
                $ret[] = isset($_REQUEST[$name]) ? $_REQUEST[$name] : null;
            }
            if (func_num_args() == 1) {
                return reset($ret);
            }
            return $ret;
        }
        return $_REQUEST;
    }
    
    public function renderView($tpl)
    {
        include $tpl;
    }
}
