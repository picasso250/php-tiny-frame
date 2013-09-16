<?php

/**
 * @author ryan
 */
class PdoWrapper
{
    protected static $instance;
    protected static $config = array(
        'debug' => false,
    );

    /**
     * 配置
     * 
     * 支持的传参方式
     * key, value
     * 或
     * array(key=>value,...)
     */
    public function static config()
    {
        $num = func_num_args();
        if ($num == 1) {
            foreach (func_get_arg(0) as $key => $value) {
                self::$config[$key] = $value;
            }
        } elseif ($num == 2) {
            self::$config[func_get_arg(0)] = func_get_arg(1);
        }
    }

    protected function __construct()
    {
    }

    protected static function update()
    {
        $self = get_called_class();
        return new $self();
    }
}
