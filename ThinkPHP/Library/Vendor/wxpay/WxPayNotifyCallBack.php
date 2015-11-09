<?php
use Think\Log;
/**
 * 自己写的类库，不属于微信官方的。注意处理Log类。
 */
class PayNotifyCallBack extends WxPayNotify
{
    //查询订单
    public function Queryorder($transaction_id)
    {
        $input = new \WxPayOrderQuery();
        $input->SetTransaction_id($transaction_id);
        $result = \WxPayApi::orderQuery($input);
        Log::write("query:" . json_encode($result), Log::INFO);
        if(array_key_exists("return_code", $result)
            && array_key_exists("result_code", $result)
            && $result["return_code"] == "SUCCESS"
            && $result["result_code"] == "SUCCESS")
        {
            return true;
        }
        return false;
    }

    //重写回调处理函数
    public function NotifyProcess($data, &$msg)
    {
        Log::write("call back:" . json_encode($data), Log::INFO);
        $notfiyOutput = array();

        if(!array_key_exists("transaction_id", $data)){
            $msg = "输入参数不正确";
            return false;
        }
        //查询订单，判断订单真实性
        if(!$this->Queryorder($data["transaction_id"])){
            $msg = "订单查询失败";
            return false;
        }

        ////业务逻辑
        //查询当前订单是否已更新支付信息
        $OrderInfo = M('order_info');
        $order_info = $OrderInfo->where(array('order_sn'=>$data["out_trade_no"]))->find();
        if (empty($order_info)) {
            $msg = "订单不存在";
            return false;
        }
        if($order_info['pay_status']==0){//如果未支付
            //更新订单信息
            $result = $OrderInfo->where(array('order_sn'=>$data["out_trade_no"]))->save(array(
                'order_status'=>1,
                'pay_status'=>2,
                'pay_time'=>NOW_TIME,
            ));
            /*$user_id=$order_info['user_id'];
            //生成待评价记录
            $sql='SELECT goods_id FROM ecs_order_goods WHERE order_id=(SELECT order_id FROM ecs_order_info WHERE order_sn="'.$data['out_trade_no'].'")';
            $goods_id_data=$db->getAll($sql);
            foreach($goods_id_data as $key=>$value){
                //检测ecs_feedback_status（待评价列表里是否有该用户和货品的id的记录，没有则添加一条）
                $sql='SELECT 1 FROM ecs_feedback_status WHERE goods_id="'.$goods_id_data[$key]['goods_id'].'" AND userid="'.$user_id.'"';
                $check=$db->getRow($sql);
                if(empty($check)){
                    $sql='INSERT INTO ecs_feedback_status (userid,goods_id,`status`) VALUES ("'.$user_id.'","'.$goods_id_data[$key]['goods_id'].'",0)';
                    $db->query($sql);
                }
            }*/
            $day_timestamp = strtotime(date('Y-m-d'));
            $OrderStats = M('order_stats');
            do {
                $order_stats = $OrderStats->where(array('date'=>$day_timestamp))->find();
                if (empty($order_stats)) {
                    $OrderStats->add(array(
                        'date'=>$day_timestamp,
                        'order_count'=>1,
                        'max_count'=>M('shop_config')->where(array('code'=>'max_order_count'))->getField('value'),
                    ));
                    break;
                } elseif ($order_stats['order_count'] < $order_stats['max_count']) {
                    $OrderStats->where(array('date'=>$day_timestamp))->setInc('order_count');
                    break;
                } else {//如果订单数已满
                    $day_timestamp += 86400;
                }
            } while(true);
        } else {
            $result = true;
        }

        return $result;
    }
}