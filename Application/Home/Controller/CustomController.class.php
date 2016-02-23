<?php
namespace Home\Controller;

class CustomController extends HomeController {

    protected function  _initialize() {
        parent::_initialize();
    }

    //面料页面
    function material() {
        //筛选和分页
        $material_list = M('material')->select();
        $this->assign('material_list', $material_list);
        $this->assign('title','选面料');
        $this->display();
    }

    //定制页面
    function design(){
        $material_id = I('get.material_id/d');
        $custom_id = I('get.custom_id/d');
        if (empty($material_id) && empty($custom_id))
            redirect(U('Home/Custom/material','',true,true));
        $material_info = M('material')->find($material_id);
        if (empty($material_info))
            $this->_error('数据错误');

        if ($custom_id) {
            //从购物车进来修改的
            $custom_info = M('custom')->find($custom_id);
            if (empty($custom_info))
                $this->_back();
            $this->assign('custom_id',$custom_id);
            $this->assign($custom_info);
        } else {
            //通过正常流程进来的
            $this->assign('material_id',$material_id);
        }
        $this->assign('title','定制');
        $this->assign('material_info',$material_info);
        $this->display();
    }

    //保存定制选项并购买
    function buy() {
        $this->_checkAuth();
        $data = array(
            'status'=>'1',
            'user_id'=>$this->_userid,
            'material_id'=>I('post.material_id/d'),
            'neckline'=>I('post.neckline/d'),
            'sleeve'=>I('post.sleeve/d'),
            'type'=>I('post.type/d'),
            'pocket'=>I('post.pocket/d'),
            'placket'=>I('post.placket/d'),
            'lap'=>I('post.lap/d'),
            'neckline_color'=>I('post.neckline_color/d'),
            'sign_text'=>I('post.sign_text'),
            'sign_family'=>I('post.sign_family/d'),
            'sign_color'=>I('post.sign_color/d'),
            'sign_location'=>I('post.sign_location/d'),
        );
        $custom_id = M('custom')->add($data);
        cookie('custom_id', $custom_id);
        redirect(U('Home/Size/index'));
    }

    //修改定制（通常用于从购物车那边过来修改）
    function update() {
        $this->_checkAuth();
        $custom_id = I('post.custom_id/d');
        $Custom = M('custom');
        $custom_row = $Custom->where(array('custom_id'=>$custom_id,'user_id'=>$this->_userid))->find();
        if (empty($custom_row))
            $this->_error('数据错误');
        $data = array(
            'neckline'=>I('post.neckline/d'),
            'sleeve'=>I('post.sleeve/d'),
            'type'=>I('post.type/d'),
            'pocket'=>I('post.pocket/d'),
            'placket'=>I('post.placket/d'),
            'lap'=>I('post.lap/d'),
            'neckline_color'=>I('post.neckline_color/d'),
            'sign_text'=>I('post.sign_text'),
            'sign_family'=>I('post.sign_family/d'),
            'sign_color'=>I('post.sign_color/d'),
            'sign_location'=>I('post.sign_location/d'),
        );
        $Custom->where(array('custom_id'=>$custom_id,'user_id'=>$this->_userid))->save($data);
        redirect(U('Home/Cart/index'));
    }
}