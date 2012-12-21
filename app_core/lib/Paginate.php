<?php

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
    private $vernier = null; // 游标

    function __construct($per_page=10, $total=0, $offset=0) {
        if ($offset >= $total) $offset = 0;
        $this->perPage = $per_page;
        $this->total = $total;
        $this->offset = $offset;
    }

    public function setTotal($total) {
        $this->total = $total;
    }

    public function setPerPage($per_page) {
        $this->perPage = $per_page;
    }

    public function setOffset($offset) {
        $this->offset = $offset;
    }

    public function hasPrevious() {
        return $this->offset > 0;
    }

    public function hasNext() {
        return ($this->offset + $this->perPage) < $this->total; // ????
    }

    public function previousOffset() {
        $prev = $this->offset - $this->perPage;
        return ($prev < 0)? 0 : $prev;
    }

    public function nextOffset() {
        $next = $this->offset + $this->perPage;
        return ($next > $this->total-1)? $this->total-1 : $next;
    }
    
    public function setViewNum($num) {
        $this->viewNum = $num;
    }
    
    private function vernier() {
        if ($this->vernier) 
            return $this->vernier;
        $minPage = 1;
        $maxPage = $this->maxPage();
        $curPage = $this->curPage();
        $half = $this->viewNum / 2; // 游标的一半长度
        $min = (int)($curPage - $half);
        $max = (int)($curPage + $half);
        
        // 1. 当游标比整个尺子还大时
        if ($this->viewNum >= $maxPage)
            return array(
                'reachStart' => false,
                'reachEnd' => false,
                'min' => $minPage,
                'max' => $maxPage,
            );
        
        $vernier = array(
            'reachStart' => true,
            'reachEnd' => true,
            'min' => $min,
            'max' => $max,
        );
        
        // 2.a. 左边超出
        if ($min <= $minPage) {
            $vernier['reachStart'] = false;
            $min = $minPage;
            $max = $min + $this->viewNum - 1;
        }
        
        // 2.b. 右边超出
        if ($max >= $maxPage) {
            $vernier['reachEnd'] = false;
            $max = $maxPage;
            $min = $max - $this->viewNum;
        }
        
        $vernier['min'] = $min;
        $vernier['max'] = $max;
        $this->vernier = $vernier;
        return $vernier;
    }

    public function viewMin() {
        $v = $this->vernier();
        return (int) $v['min'];
    }
    
    public function reachStart() {
        $v = $this->vernier();
        return $v['reachStart'];
    }
    
    public function viewMax() {
        $v = $this->vernier();
        return (int) $v['max'];
    }
    
    public function reachEnd() {
        $v = $this->vernier();
        return $v['reachEnd'];
    }
    
    public function isCurrent($page) {
        return $page === $this->curPage();
    }
    
    public function offset($i = -1) {
        if ($i === -1) {
            return $this->offset;
        } else {
            return $this->offset + $i * $this->perPage;
        }
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
    
    public function maxPage() {
        return floor($this->total / $this->perPage - 0.00001) + 1;
    }

    public function hrefPrefix()
    {
        $q = $_SERVER['QUERY_STRING'];
        $arr = explode('&', $q);
        $arr = array_filter($arr, function ($e) {
            return strpos($e, 'p=') !== 0;
        });
        return implode('&amp;', $arr) . '&amp;p=';
    }

    public function startCanShow()
    {
        return $this->viewMin() === 1;
    }

    public function endCanShow()
    {
        return $this->viewMax() === $this->maxPage();
    }
}
