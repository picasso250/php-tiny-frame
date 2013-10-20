<?php

namespace ptf\lib;

/**
 * Description of Paginate
 * 翻页
 *
 * @file    Paginate
 * @author  ryan <cumt.xiaochi@gmail.com>
 * @created Jul 24, 2012 11:56:27 AM
 * @version 1.4
 *
 * Usage:
 * $paging = new Paginate($per_page, $total);
 * $paging->setCurPage(isset($_GET['p']) ? $_GET['p'] : 1);
 * $offset = $paging->offset();
 */

class Paginate {

    private $total = null;  // 总条目数
    private $perPage = 10;  // 每页的条目数
    private $offset = null; // 
    private $viewNum = 5;  // 每个网页显示的最多的翻页格子

    private $start = 1; // 第一页
    private $end; // 最后一页

	private $previousHalfNum; // 前半翻页格子的个数
    private $nextHalfNum; // 后半翻页格子的个数

    public function __construct($perPage=10, $total=0, $offset=0) {
        if ($offset >= $total) $offset = 0;
        $this->perPage = $perPage;
        $this->total = $total;
        $this->offset = $offset;

        // assume that all informations are given
        $this->start = 1;
        $this->end = ceil($total / $perPage);
        // floor($this->total / $this->perPage - 0.00001) + 1;

        $this->t_half = ($this->viewNum - 1) / 2;
        $this->previousHalfNum = floor($t_half);
        $this->nextHalfNum = ceil($t_half);

        $this->showStart = $this->curPage - $this->viewNum;
        $this->showEnd = $this->curPage + $this->viewNum;

    }

    public function setTotal($total) {
        $this->total = $total;
    }

    public function setPerPage($perPage) {
        $this->perPage = $perPage;
    }

    public function setOffset($offset) {
        $this->offset = $offset;
    }

    public function getPageNumbers() {
        return range(max($this->showStart, $this->start), min($this->showEnd, $this->end));
    }

    public function hasPrevious() {
        return $this->curPage > $this->start;
    }

    public function hasNext() {
        return $this->curPage < $this->end;
    }

    public function setViewNum($num) {
        $this->viewNum = $num;
    }
    
    public function isOmitStart() {
        return $this->showStart > $this->start;
    }
    
    public function isOmitEnd() {
        return $this->showEnd < $this->end;
    }

    public function setCurPage($page) 
    {
        $this->curPage = $page;
        $this->offset = $this->perPage * ($page - 1);
        if ($this->offset > $this->total) {
            $this->offset = 0;
        }
    }
    
    public function curPage() {
        return $this->offset / $this->perPage + 1;
    }
    
    public function getEnd() {
        return $this->end;
    }

    public function hrefPrefix($pageKey = 'p')
    {
        $q = $_SERVER['QUERY_STRING'];
        $parse_str($q, $arr);
        unset($arr[$pageKey]);
        return http_build_query($arr);
    }

}
