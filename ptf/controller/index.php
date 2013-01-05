<?php
/**
 * @author  ryan <cumt.xiaochi@gmail.com>
 */

/**
* 
*/
class IndexController extends BasicController
{
    function __construct()
    {
        super();
        
        $searcher = Topic::search()
            ->filterBy('title LIKE ?', '%keyword%')
            ->filterBy('created > ?', '2012.01.01 00:00:00');
        $totalItems = $searcher->count();
        $itemsPerPage = 10;
        $startIndex = 0;
        $searcher = $searcher->limit($itemsPerPage)->offset($startIndex);
        $items = $searcher->find();
        $itemCount = count($items);
    }
}
