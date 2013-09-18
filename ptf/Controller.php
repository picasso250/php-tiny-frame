<?php

namespace ptf;

/**
 * @author ryan
 */
class Controller
{
    public $viewRoot;

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
