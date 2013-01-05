<?php

/**
 *
 * @author  ryan <cumt.xiaochi@gmail.com>
 */
class Topic extends BasicModel 
{
    public function comments()
    {
        return Comment::search()->filterBy('topic', $this)->find();
    }
}
