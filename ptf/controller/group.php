<?php
/**
* group
*/
class GroupController extends BasicController
{
    public function topic()
    {
        $topic = new Topic($topicId);
    }
}
