<?php

namespace Home\Model;
use Think\Model;

class CartModel extends Model {

    //获取购物车数据
    function getList($user_id, $goods_detail = false) {
        $cart_data = $this->where(array('user_id'=>$user_id))->select();
        $total_money = 0;
        foreach ($cart_data as &$row) {
            $total_money += $row['goods_price'] * $row['goods_number'];

            if ($goods_detail) {//如果要同时获取商品详情
                if ($row['is_custom']) {
                    $row['goods_thumb'] = './assets/img/dingzhi17.jpg';
                } else {
                    $row['goods_thumb'] = M('goods')->getFieldByGoodsId($row['goods_id'],'goods_thumb');
                }
            }
        }
        $return['cart_data'] = $cart_data;
        $return['total_money'] = $total_money;
        return $return;
    }

    //获取库存信息，返回键值对“购物车ID”=>“数量”
    function stock($user_id) {
        return $this->where(array('user_id'=>$user_id,'is_custom'=>0))->getField('rec_id,goods_number');
    }

    function clear($user_id) {
        $this->where(array('user_id'=>$user_id))->delete();
    }

    //返回修改后的数量
    function update($id, $inc = 1) {
        $this->where(array('rec_id'=>$id))->setInc('goods_number', $inc);
        return $this->where(array('rec_id'=>$id))->getField('goods_number');
    }

    function del($id) {
        return $this->where(array('rec_id'=>$id))->delete();
    }
}