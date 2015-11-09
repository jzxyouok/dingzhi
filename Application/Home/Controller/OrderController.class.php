<?php
namespace Home\Controller;

class OrderController extends HomeController {

    protected function  _initialize() {
        parent::_initialize();
        $this->_checkAuth();
    }

    function index() {
        $list = D('order')->getList($this->_userid);
        $this->assign('list', $list);
        $this->assign('title', '查看订单');
        $this->display();
    }

    function detail() {
        $order_sn = I('order_sn');
        $this->assign('order_sn',$order_sn);
        $this->display();
    }

    //生成订单并显示经销商，然后准备付款
    function insert() {
        //检测库存是否足够
        $Cart = D('cart');
        $cart_info = $Cart->getList($this->_userid);
        if (empty($cart_info['cart_data'])) {
            $this->_error('购物车里没有商品！');
        }
        $stock_info = check_stock($Cart->stock($this->_userid));
        //库存不足
        if($stock_info['error'] != 0){
            $this->_error($stock_info['goods_name'].'的库存只有'.$stock_info['goods_number'].'，不足'.$stock_info['cart_number']);
        }

        $address_id=cookie('address_id');
        if (empty($address_id)) {
            $this->_error('请选择送货地址');
        }
        $address_data = M('address')->find($address_id);
        if ($address_data['userid'] != $this->_userid) {
            $this->_error('请选择送货地址');
        }

        //发票抬头
        $inv_payee=I('invoice');
        //获取提交的订单信息
        $order_sn=get_order_sn();
        $shipping_id=(isset($_REQUEST['shipping_id']))?$_REQUEST['shipping_id']:2;//顺丰速运
        $shipping_name="快递";
        $pay_id=2;
        $pay_name='微信支付';

        $goods_amount=0;//商品总金额，也等于订单金额
        $return_data=array();
        $Goods = M('goods');
        $Custom = M('custom');
        $Material = M('material');
        foreach($cart_info['cart_data'] as $cart_row){
            $goods_id = $cart_row['goods_id'];
            $goods_number = $cart_row['goods_number'];
            if($goods_number>0){
                if ($cart_row['is_custom']) {
                    $material_id = $Custom->getFieldByCustomId($cart_row['goods_id'],'material_id');
                    $material_price = $Material->getFieldByMaterialId($material_id, 'price');
                    $data = array(
                        'goods_name'=>$cart_row['goods_name'],
                        'shop_price'=>$material_price,
                        'market_price'=>$material_price,
                    );
                } else {
                    $data=$Goods->where(array('goods_id'=>$goods_id))->find();
                    //更新库存
                    $Goods->where(array('goods_id'=>$goods_id))->setField('goods_number', intval($data['goods_number'])-intval($goods_number));
                }
                empty($data['goods_sn']) && $data['goods_sn'] = '';
                $goods_amount += $data['shop_price']*$goods_number;
                $return_data[]=array(
                    'goods_id'=>$goods_id,
                    'num'=>$goods_number,
                    'name'=>$data['goods_name'],
                    'shop_price'=>$data['shop_price'],
                    'market_price'=>$data['market_price'],
                    'goods_sn'=>$data['goods_sn'],
                    'is_custom'=>$cart_row['is_custom'],
                    'size_id'=>$cart_row['size_id'],
                );
            }
        }
        //添加订单
        $order_id=M('order_info')->add(array(
            'order_sn'=>$order_sn,
            'user_id'=>$this->_userid,
            'order_status'=>0,
            'shipping_status'=>0,
            'pay_status'=>0,
            'consignee'=>$address_data['name'],
            'country'=>1,
            'province'=>$address_data['pro_id'],
            'city'=>$address_data['city_id'],
            'district'=>$address_data['area_id'],
            'address'=>$address_data['remark'],
            'zipcode'=>$address_data['zipcode'],
            'tel'=>$address_data['phone'],
            'mobile'=>$address_data['phone'],
            'shipping_id'=>$shipping_id,
            'shipping_name'=>$shipping_name,
            'pay_id'=>$pay_id,
            'pay_name'=>$pay_name,
            'how_oos'=>'等待所有商品备齐后再发',
            'add_time'=>time(),
            'goods_amount'=>$goods_amount,
            'order_amount'=>$goods_amount,
            'inv_payee'=>$inv_payee,
            'tax'=>0,
        ));
        if($order_id!==false){
            $OrderGoods = M('order_goods');
            $order_goods_data = array();
            foreach($return_data as $key=>$value){
                $order_goods_data[] = array(
                    'order_id'=>$order_id,
                    'goods_id'=>$return_data[$key]['goods_id'],
                    'goods_name'=>$return_data[$key]['name'],
                    'goods_sn'=>$return_data[$key]['goods_sn'],
                    'goods_number'=>$return_data[$key]['num'],
                    'market_price'=>$return_data[$key]['market_price'],
                    'goods_price'=>$return_data[$key]['shop_price'],
                    'goods_attr'=>'',
                    'is_real'=>1,
                    'is_custom'=>$value['is_custom'],
                    'size_id'=>$value['size_id'],
                );
            }
            $OrderGoods->addAll($order_goods_data);
        }
        //清空购物车
        $Cart->clear();
        $this->_success('',array('orderId'=>$order_id,'redirect'=>U('Home/Order/readyToPay',array('order_sn'=>$order_sn),true,true)));
    }

    //准备支付页面，显示经销商信息
    function readyToPay() {
        $order_sn = I('order_sn');
        $order_info = M('order_info')->where(array('order_sn'=>$order_sn))->find();
        if (empty($order_info) || $order_info['user_id'] != $this->_userid) {
            $this->_error('该订单不存在');
        }
        $this->assign('order_amount', $order_info['order_amount']);

        $province_list = M('region')->field('region_id,region_name')->where(array('region_type'=>1,'parent_id'=>1))->select();
        $this->assign('province_list', $province_list);
        $this->assign('title', '支付');
        $this->display();
    }

    //支付
    function pay() {
        $order_sn = I('get.order_sn');//订单号要用GET提交过来
        $dealer_id = I('get.dealer/d');
        $agency_id = I('get.agency/d');
        if (empty($dealer_id)) {
            $this->_error('请选择经销商', 'order_readyToPay');
        }

        vendor('wxpay.lib.WxPay#Api');
        vendor('wxpay.WxPay#JsApiPay');

        $notify_url = C('HOST').'/wxpay_notify.php';
        $callback_url = U('Home/Order/index','',true,true);
        //微信支付
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        if (strpos($user_agent, 'MicroMessenger') === false) {
            // 非微信浏览器禁止浏览
            // 这里交给获取Openid时候判断
        } else {
            // 微信浏览器，允许访问 获取版本号
            preg_match('/.*?(MicroMessenger\/([0-9.]+))\s*/', $user_agent, $matches);
            if($matches[2] < "5.0"){
                echo '<script>alert("仅支持微信5.0以上版本");</script>';
                die;
            }
        }

        $order_data = D('order')->payDetail($order_sn);
        if (empty($order_data) || $order_data['user_id'] != $this->_userid) {
            $this->_error('这不是有效的订单');
        }
        if ($order_data['pay_status'] != 0) {
            $this->_error('该订单已经支付过了');
        }
        $body = $order_data['goods_name']; //商品简介
        empty($body) && $body = '服装定制';
        $out_trade_no = $order_data['order_sn'];//订单号
        $total_fee=$order_data['order_amount'];//订单金额
        $total_fee=0.01;//TODO 订单金额

        //①、获取用户openid
        $tools = new \JsApiPay();
        $openId = $tools->GetOpenid();//这里会有跳转行为，因此提交的参数要用get方式

        //支付逻辑

        //②、统一下单
        $input = new \WxPayUnifiedOrder();
        $input->SetBody($body);
        $input->SetOut_trade_no($out_trade_no);
        $input->SetTotal_fee($total_fee*100);
        $input->SetTime_start(date("YmdHis"));
        $input->SetTime_expire(date("YmdHis", time() + 600));
        $input->SetNotify_url($notify_url);
        $input->SetTrade_type("JSAPI");
        $input->SetOpenid($openId);
        $order = \WxPayApi::unifiedOrder($input);
        if ($order['return_code'] == 'FAIL') {
            $this->_error($order['return_msg']);
        }
        if ($order['result_code'] == 'FAIL') {
            $this->_error($order['err_code_des']);
        }
        $jsApiParameters = $tools->GetJsApiParameters($order);
        //获取共享收货地址js函数参数
        $editAddress = $tools->GetEditAddressParameters();

        //③、在支持成功回调通知中处理成功之后的事宜，见 notify.php
        /**
         * 注意：
         * 1、当你的回调地址不可访问的时候，回调通知会失败，可以通过查询订单来确认支付是否成功
         * 2、jsapi支付时需要填入用户openid，WxPay.JsApiPay.php中有获取openid流程 （文档可以参考微信公众平台“网页授权接口”，
         * 参考http://mp.weixin.qq.com/wiki/17/c0f37d5704f0b64713d5d2c37b468d75.html）
         */
        $this->assign('jsApiParameters',$jsApiParameters);
        $this->assign('editAddress',$editAddress);
        $this->assign('callback_url',$callback_url);
        $this->assign('title',$body);

        //修改$dealer_id，放在最后是为了减少重定向以后又执行一次
        if (!D('order')->update($order_sn, array('dealer_id'=>$dealer_id,'agency_id'=>$agency_id))) {
            $this->_error('经销商保存失败', 'order_readyToPay');
        }
        $this->display();

    }

    //评论
    function comment() {
        //TODO 到底是评价订单还是商品？
        if (IS_POST) {
            $order_sn = I('post.order_sn');
            $content = I('post.content');
            if (empty($content))
                $this->_error('请输入评论内容');
            if (empty($order_sn) || M('order_info')->where(array('order_sn'=>$order_sn))->getField('user_id') != $this->_userid)
                $this->_error('订单不存在');
            $data = array(
                'comment_type'=>0,//0是商品
                'email'=>'',
                'user_name'=>'',
                'content'=>$content,
                'add_time'=>NOW_TIME,
                'ip_address'=>'',
                'status'=>'0',
                'user_id'=>$this->_userid,
            );
            $result = M('comment')->add($data);
            if ($result) {
                redirect(U('Home/Order/index'));
            } else {
                $this->_error('评论提交失败');
            }
        } else {
            $order_sn = I('get.order_sn');
            if (empty($order_sn) || !M('order_info')->where(array('order_sn'=>$order_sn))->find())
                $this->_error('订单不存在');
            $this->assign('order_sn', $order_sn);
            $this->assign('title', '评价');
            $this->display();
        }
    }

    //TODO 取消订单
    function cancel() {}
}
