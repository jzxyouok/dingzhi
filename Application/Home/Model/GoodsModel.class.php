<?php

namespace Home\Model;
use Think\Model;

class GoodsModel extends Model {

    function getList() {
        $data = $this->field('goods_id,goods_name,shop_price,goods_thumb as image')
            ->where(array('is_delete'=>0,'is_on_sale'=>1))
            ->order('sort_order desc,goods_id desc')->select();
        return $data;
    }

    function detail($id) {
        $data = $this->field('goods_id,goods_name,shop_price,goods_desc,goods_img as image')
            ->find($id);
        return $data;
    }
}