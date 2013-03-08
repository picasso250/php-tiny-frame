<?php

/**
 *
 * @author  ryan <cumt.xiaochi@gmail.com>
 */
class Comment extends BasicModel 
{
    protected $relationMap = array('topic' => 'topic');
}
