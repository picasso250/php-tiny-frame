<?php

/**
 * @author ryan
 */
class Controller
{
    public $viewRoot;
    
    public function renderView($tpl)
    {
        include $tpl;
    }
}
