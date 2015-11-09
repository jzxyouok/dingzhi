<?php

/* 订单状态 */
define('OS_UNCONFIRMED',            0); // 未确认
define('OS_CONFIRMED',              1); // 已确认
define('OS_CANCELED',               2); // 已取消
define('OS_INVALID',                3); // 无效
define('OS_RETURNED',               4); // 退货
define('OS_SPLITED',                5); // 已分单
define('OS_SPLITING_PART',          6); // 部分分单

/* 配送状态 */
define('SS_UNSHIPPED',              0); // 未发货
define('SS_SHIPPED',                1); // 已发货
define('SS_RECEIVED',               2); // 已收货
define('SS_PREPARING',              3); // 备货中
define('SS_SHIPPED_PART',           4); // 已发货(部分商品)
define('SS_SHIPPED_ING',            5); // 发货中(处理分单)
define('OS_SHIPPED_PART',           6); // 已发货(部分商品)

/* 支付状态 */
define('PS_UNPAYED',                0); // 未付款
define('PS_PAYING',                 1); // 付款中
define('PS_PAYED',                  2); // 已付款

function order_status($order_info) {
    if (in_array($order_info['order_status'],array(OS_CONFIRMED, OS_SPLITED)) &&
        in_array($order_info['shipping_status'],array(SS_SHIPPED, SS_RECEIVED)) &&
        in_array($order_info['pay_status'],array(PS_PAYED, PS_PAYING))) {
        //已完成
        $status = 'finished';
    } elseif (in_array($order_info['order_status'],array(OS_CONFIRMED)) &&
        in_array($order_info['shipping_status'],array(SS_SHIPPED, SS_RECEIVED))) {
        //已发货
        $status = 'shipped';
    } elseif (in_array($order_info['order_status'],array(OS_CONFIRMED, OS_SPLITED, OS_SPLITING_PART)) &&
        in_array($order_info['shipping_status'],array(SS_UNSHIPPED, SS_PREPARING, SS_SHIPPED_ING)) &&
        in_array($order_info['pay_status'],array(PS_PAYED, PS_PAYING))) {
        //待发货（已支付）
        $status = 'await_ship';
    } elseif (in_array($order_info['order_status'],array(OS_UNCONFIRMED, OS_CONFIRMED)) &&
        in_array($order_info['shipping_status'],array(SS_UNSHIPPED)) &&
        in_array($order_info['pay_status'],array(PS_UNPAYED))) {
        //待支付
        $status = 'unprocessed';
    } else {
        $status = 'error';
    }
    return $status;
}

//字符串截取
function str_cut($sourcestr, $cutlength, $suffix = '...') {
    $str_length = strlen($sourcestr);
    if ($str_length <= $cutlength) {
        return $sourcestr;
    }
    $returnstr = '';
    $n = $i = $noc = 0;
    while ($n < $str_length) {
        $t = ord($sourcestr[$n]);
        if ($t == 9 || $t == 10 || (32 <= $t && $t <= 126)) {
            $i = 1;
            $n++;
            $noc++;
        } elseif (194 <= $t && $t <= 223) {
            $i = 2;
            $n += 2;
            $noc += 2;
        } elseif (224 <= $t && $t <= 239) {
            $i = 3;
            $n += 3;
            $noc += 2;
        } elseif (240 <= $t && $t <= 247) {
            $i = 4;
            $n += 4;
            $noc += 2;
        } elseif (248 <= $t && $t <= 251) {
            $i = 5;
            $n += 5;
            $noc += 2;
        } elseif ($t == 252 || $t == 253) {
            $i = 6;
            $n += 6;
            $noc += 2;
        } else {
            $n++;
        }
        if ($noc >= $cutlength) {
            break;
        }
    }
    if ($noc > $cutlength) {
        $n -= $i;
    }
    $returnstr = substr($sourcestr, 0, $n);


    if (substr($sourcestr, $n, 6)) {
        $returnstr = $returnstr . $suffix; //超过长度时在尾处加上省略号
    }
    return $returnstr;
}

//检查库存
function check_stock($arr) {
    foreach ($arr AS $key => $val) {
        $val = intval($val);
        if ($val <= 0 || !is_numeric($key))
        {
            continue;
        }
        $sql = "SELECT g.goods_name, g.goods_number ".
            "FROM " .C('DB_PREFIX'). "goods AS g, ".
            C('DB_PREFIX'). "cart AS c ".
            "WHERE g.goods_id = c.goods_id AND c.rec_id = '$key'";
        $data = M()->query($sql);
        if ($data[0]['goods_number'] < $val)
        {
            return array(
                'error'=>1,
                'goods_name'=>$data[0]['goods_name'],
                'goods_number'=>$data[0]['goods_number'],
                'cart_number'=>$val,
            );
        }
    }
    return array(
        'error'=>0,
    );
}

/*
 * 生成订单号
 */
function get_order_sn(){
    return date('ymdHis').mt_rand(1000,9999);
}