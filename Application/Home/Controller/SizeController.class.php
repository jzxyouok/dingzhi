<?php
namespace Home\Controller;

class SizeController extends HomeController {

    protected function  _initialize() {
        parent::_initialize();
        $this->_checkAuth();
    }

    //尺寸首页
    function index(){
        $this->assign('title', '添加尺寸');
        $this->display();
    }

    //客户尺寸列表
    function myList(){
        $list = M('user_size')->field('size_id,name,sex,height,weight')
            ->where(array('user_id'=>$this->_userid,'status'=>1))->select();
        $this->assign('list', $list);
        $this->assign('title', '我的尺码');
        $this->display();
    }

    //添加客户尺寸页面
    function add() {
        $name = I('post.name/s');
        $sex = I('post.sex');
        $height = I('post.height/d');
        $weight = I('post.weight/f');
        $sex = $sex == 'male' ? 1 : ($sex == 'female' ? 2 : 0);
        if (empty($name)) $this->_error('请输入姓名');
        if (empty($sex)) $this->_error('请输入性别');
        if (empty($height)) $this->_error('请输入身高');
        if (empty($weight)) $this->_error('请输入体重');
        cookie('name', $name);
        cookie('sex', $sex);
        cookie('height', $height);
        cookie('weight', $weight);
        $this->assign('data', array(
            'name'=>$name,
            'sex'=>$sex,
            'height'=>$height,
            'weight'=>$weight
        ));
        $this->assign('title', '编辑尺寸');
        $this->display();
    }

    //添加客户尺寸动作
    function insert() {
        $data = $_POST;
        $data['user_id'] = $this->_userid;
        $data['name'] = cookie('name');
        $data['sex'] = cookie('sex');
        $data['height'] = cookie('height');
        $data['weight'] = cookie('weight');
        if (empty($data['name']) || empty($data['sex']) || empty($data['height']) || empty($data['weight']))
            redirect(U("Home/Size/index"));
        $Size = D('Size');
        $result = $Size->create($data);
        if (!$result) {
            $this->_error($Size->getError(), 'Size_add');
        }
        $size_id = $Size->add();
        if ($size_id) {
            cookie('name', null);
            cookie('sex', null);
            cookie('height', null);
            cookie('weight', null);
            redirect(U("Home/Cart/add",array('size_id'=>$size_id),true,true));
        } else {
            $this->_error('添加失败，请重试。', 'Size_add');
        }
    }

    //编辑客户尺寸页面
    function edit() {
        $row = M('user_size')->find(I('get.size_id'));
        if ($row['user_id'] != $this->_userid)
            $this->_error('数据错误', 'Size_add');
        $this->assign('data', $row);
        $this->assign('title', '编辑尺寸');
        $this->display('Size_add');
    }

    //编辑客户尺寸动作
    function update() {
        $size_id = I('post.size_id/i');
        $Size = D('Size');
        $size_row = $Size->find($size_id);
        if ($size_row['user_id'] != $this->_userid) {
            $this->_back();
        }
        $data = $_POST;
        $data['update_time'] = NOW_TIME;
        $result = $Size->create($data);
        if (!$result) {
            $this->_error($Size->getError(), 'Size_add');
        }
        $result = $Size->save();
        if ($result) {
            redirect(U('Home/Size/myList'));
        } else {
            $this->_error('修改失败，请重试。', 'Size_add');
        }
    }
}