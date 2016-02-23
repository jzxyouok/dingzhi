<?php

namespace Home\Model;
use Think\Model;

class OrderModel extends Model {

    protected $tableName = 'order_info';

    protected $_auto = array (
//        array('status','1'),  // 新增的时候把status字段设置为1
//        array('add_time','time',1,'function'), // 对add_time字段在新增的时候写入当前时间戳
//        array('update_time','time',2,'function'), // 对update_time字段在更新的时候写入当前时间戳
    );

    protected $_validate = array(
//        array('wanwei','require','请输入腕围'),
    );

    //TODO 翻页
    function getList($user_id) {
        $data = array();
        $order_list = $this->field('order_id,order_sn,order_status,shipping_status,pay_status,order_amount,invoice_no,dealer_id')
            ->where(array('user_id'=>$user_id, 'is_delete'=>0))
            ->order('order_id desc')->select();
        $OrderGoods = M('order_goods');
        foreach ($order_list as $order_row) {
            $goods_info = $OrderGoods->where(array('order_id'=>$order_row['order_id']))->select();
            if (empty($goods_info)) continue;
            $total_goods_number = 0;
            $goods_list = array();
            foreach ($goods_info as $goods_row) {
                $total_goods_number += $goods_row['goods_number'];
                $temp = array(
                    'goods_name'=>$goods_row['goods_name'],
                    'goods_price'=>$goods_row['goods_price'],
                );
                if ($goods_row['is_custom']) {
                    $temp['goods_thumb'] = './assets/img/dingzhi17.jpg';
                } else {
                    $temp['goods_thumb'] = M('goods')->getFieldByGoodsId($goods_row['goods_id'],'goods_thumb');
                }
                $goods_list[] = $temp;
            }
            $order_status = order_status($order_row);
            //TODO 是否已取消，是否已评论（评论订单还是商品？）
//            if ($order_status == 'finished') {
//                $comment = M('comment')->where(array('id_value'=>,'user_id'=>$user_id,'comment_type'=>0))->getField('content');
//            }
            //TODO 加上经销商信息
            $data[] = array_merge($order_row, array(
                'status'=>$order_status,
                'goods_list'=>$goods_list,
                'total_goods_number'=>$total_goods_number,
            ));
        }
        return $data;
    }

    function payDetail($order_sn) {
        $data = $this->field('order_sn,user_id,pay_status,order_amount,goods_name')
            ->join('__ORDER_GOODS__ ON __ORDER_GOODS__.order_id = __ORDER_INFO__.order_id')
            ->where(array('order_sn'=>$order_sn))->find();
        return $data;
    }

    function update($order_sn, $data) {
        //TODO 判断经销商是否存在
        return $this->where(array('order_sn'=>$order_sn))->save($data);
    }
}