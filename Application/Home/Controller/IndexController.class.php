<?php
namespace Home\Controller;

class IndexController extends HomeController {

    protected function  _initialize() {
        parent::_initialize();
    }

    function index() {
        //TODO 翻页
        $this->assign('list', D('goods')->getList());
        $this->assign('title', '服装定制');
        $this->display();
    }
}