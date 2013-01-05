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
            ->filterBy('created > ?', '2012.01.01 00:00:00')
            ->filterBy('user.name LIKE ?' '%name%');
        $totalItems = $searcher->count();
        $itemsPerPage = 10;
        $startIndex = 0;
        $searcher = $searcher->limit($itemsPerPage)->offset($startIndex)
            ->orderBy('id DESC');
        $items = $searcher->find();
        $itemCount = count($items);

        render_view($this->view, compact('items', 'totalItems'));
    }
}
