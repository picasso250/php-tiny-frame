<?php
!defined('IN_PTF') && exit('ILLEGAL EXECUTION');
/**
 * Description of Model
 *
 * @author ryan
 */
class Model {
    
    protected $id = null;
    protected $info = null;
    
    function __construct($para) {
        if (is_array($para) && isset($para['id'])) {
            $this->id = $para['id'];
            $this->info = $para;
        } else {
            $this->id = $para;
        }
    }
    
    protected function selfCond() {
        return array('id=?' => $this->id);
    }

    protected function arg2id($arg)
    {
        if (is_numeric($arg)) {
            return $arg;
        } elseif ($arg instanceof self) {
            return $self->id;
        } else {
            throw new Exception("not good arg");
        }
    }

    protected function arg2obj($arg)
    {
        if (is_numeric($arg)) {
            return new self($arg);
        } elseif ($arg instanceof self) {
            return $arg;
        } else {
            throw new Exception('not good arg');
        }
    }
}

?>
