<?php

namespace ptf;

/**
 * 
 * get set 可以设置懒加载的服务
 * @author ryan
 */
class Controller
{
    public $view_root;
    public $config;

    private $vars = array();
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
            $this->lazies['names'][$key] = $value;
        } else {
            $this->vars[$key] = $value;
        }
    }

    protected function param()
    {
        $num_args = func_num_args();
        if ($num_args == 1) {
            $args = func_get_arg(0);
            if (is_array($args)) {
                $ret = array();
                foreach ($args as $a => $b) {
                    if (is_int($a)) {
                        $name = $b;
                        $default = null;
                    } else {
                        $name = $a;
                        $default = $b;
                    }
                    $ret[$name] = $this->_param($name, $default);
                }
                return $ret;
            }
        } elseif ($num_args == 2) {
            $name = func_get_arg(0);
            $default = func_get_arg(1);
            $this->_param($name, $default);
        } else {
            return $_REQUEST;
        }
    }

    private function _param($key, $default = null)
    {
        return isset($_REQUEST[$key]) ? $_REQUEST[$key] : $default;
    }
    
    public function renderView($tpl)
    {
        include "$this->view_root/$tpl.phtml";
    }
}
