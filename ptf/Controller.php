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
    public $app;

    private $vars = array();
    private $lazies = array('names' => array(), 'values' => array());

    private $scripts = array();
    private $styles = array();

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
            } elseif (is_string($args)) {
                $name = $args;
                return $this->_param($name, null);
            }
        } elseif ($num_args == 2) {
            $name = func_get_arg(0);
            $default = func_get_arg(1);
            return $this->_param($name, $default);
        } else {
            return $_REQUEST;
        }
    }

    public function paramFile($name)
    {
        if (isset($_FILES[$name])) {
            return $_FILES[$name];
        }
        return null;
    }

    private function _param($key, $default = null)
    {
        return isset($_REQUEST[$key]) ? $_REQUEST[$key] : $default;
    }

    public function layout($tpl)
    {
        $this->layout = $tpl;
    }
    
    public function renderView($tpl)
    {
        if ($this->layout) {
            $this->view = $tpl;
            include "$this->view_root/$this->layout.phtml";
            $this->layout = null;
        } else {
            include "$this->view_root/$tpl.phtml";
        }
    }

    public function yieldView()
    {
        include "$this->view_root/$this->view.phtml";
    }

    public function renderBlock($tpl, $data = array())
    {
        foreach ($data as $key => $value) {
            $this->{$key} = $value;
        }
        include "$this->view_root/$tpl.phtml";
    }

    public function addScript($js)
    {
        $this->scripts[] = $js;
    }

    public function addStyle($css)
    {
        $this->styles[] = $css;
    }

    public function redirect($url)
    {
        header('Location: '.$url);
        exit;
    }

    public function ip()
    {
        return $_SERVER['REMOTE_ADDR'];
    }

    
}
