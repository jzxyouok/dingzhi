<?php
namespace Home\Controller;
use Think\Log;

class PayController extends HomeController {

    protected function  _initialize() {
        parent::_initialize();
    }

    //异步回调
    function notify() {
        /*$GLOBALS['HTTP_RAW_POST_DATA'] = '<xml><appid><![CDATA[wx3f5b4ffef2f4b48e]]></appid>
<bank_type><![CDATA[CFT]]></bank_type>
<cash_fee><![CDATA[1]]></cash_fee>
<fee_type><![CDATA[CNY]]></fee_type>
<is_subscribe><![CDATA[Y]]></is_subscribe>
<mch_id><![CDATA[1255379201]]></mch_id>
<nonce_str><![CDATA[a1wkusm1m6vdv8guxodyytugng2kxojb]]></nonce_str>
<openid><![CDATA[ocB-MjkYsAtGP0PSHmKC4FlYv-8I]]></openid>
<out_trade_no><![CDATA[201510181153238842]]></out_trade_no>
<result_code><![CDATA[SUCCESS]]></result_code>
<return_code><![CDATA[SUCCESS]]></return_code>
<sign><![CDATA[05F36707D9495536E2A49A37886A25B1]]></sign>
<time_end><![CDATA[20151018211521]]></time_end>
<total_fee>1</total_fee>
<trade_type><![CDATA[JSAPI]]></trade_type>
<transaction_id><![CDATA[1006240670201510181248186849]]></transaction_id>
</xml>';*/
        vendor('wxpay.lib.WxPay#Api');
        vendor('wxpay.lib.WxPay#Notify');//这里一定要用#，否则vendor第一个参数相同时不会再导入
//        vendor('wxpay.WxPay','','.JsApiPay.php');
        vendor('wxpay.WxPayNotifyCallBack');//这个自定义文件要根据实际修改

        Log::write("wxpay notify", Log::INFO);
        $notify = new \PayNotifyCallback();
        $notify->Handle(false);
    }
}
