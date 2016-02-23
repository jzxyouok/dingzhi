<?php
namespace Home\Controller;

class GoodsController extends HomeController {

    protected function  _initialize() {
        parent::_initialize();
    }

    //商品详情页面
    function detail(){
        $goods_id = I('get.id/d');
        if (empty($goods_id)) $this->_back();
        $data = D(CONTROLLER_NAME)->detail($goods_id);
        if (empty($data)) $this->_back();
        $this->assign($data);
        $this->assign('title', '服装定制');
        $this->display();
    }

    //加入购物车
    function buy() {
        $this->_checkAuth();
        $goods_id = I('get.id/d');
        //TODO 判断是否存在，判断库存
        if (empty($goods_id)) {
            $this->_error('请选择商品');
        };

        $Cart = D('Cart');
        $cart_info = $Cart->where(array('user_id'=>$this->_userid,'goods_id'=>$goods_id))->getField('rec_id');
        if ($cart_info['rec_id']) {
            //本来就存在的话只是加数量
            $Cart->update($cart_info['rec_id']);
        }
        cookie('goods_id', $goods_id);

        $this->_success('', array('redirect'=>U('Home/Size/index','',true,true)));
    }
}