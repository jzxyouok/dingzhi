<?php
namespace Home\Controller;

class CartController extends HomeController {

    protected function  _initialize() {
        parent::_initialize();
        $this->_checkAuth();
    }

    function index() {
        $this->assign(D('cart')->getList($this->_userid, true));
        $this->assign('title','购物车');
        $this->display();
    }

    //选择尺寸并添加到购物车
    function add() {
        $size_id = I('size_id/d');
        $goods_id = I('cookie.goods_id/d');
        $custom_id = I('cookie.custom_id/d');
        //TODO 输入检验

//        $num=(!empty($_SESSION['shopping'][$goods_id]))?$_SESSION['shopping'][$goods_id]:0;
//        if(isset($_REQUEST['is_delete'])&&$_REQUEST['is_delete']==1){//删除
//            $num=0;
//        }else{
//            if(isset($_REQUEST['type'])&&$_REQUEST['type']=="del"){//减少
//                $num=intval($num)-1;
//            }else{//新增
//                $num=intval($num)+1;
//            }
//        }
//        $num=($num>0)?$num:0;
        $num = 1;
        if ($goods_id) {
            //普通商品
            $Cart = D('Cart');
            $cart_info = $Cart->where(array('user_id'=>$this->_userid,'goods_id'=>$goods_id))->find();
            if ($cart_info) {
                //本来就存在的话
                $num = $cart_info['goods_number'];
            }
            $goods_number = M('goods')->where(array('goods_id'=>$goods_id))->getField('goods_number');
            //检测库存是否足够
            if(intval($num)>intval($goods_number)){
                $this->_error('商品库存不足！');
            }
            $goods_info = M('goods')->find($goods_id);
            $data = array(
                'user_id'=>$this->_userid,
                'is_custom'=>0,
                'goods_id'=>$goods_id,
                'goods_name'=>$goods_info['goods_name'],
                'market_price'=>$goods_info['market_price'],
                'goods_price'=>$goods_info['shop_price'],
                'goods_number'=>$num,
                'goods_attr'=>'',
                'is_real'=>$goods_info['is_real'],
                'extension_code'=>$goods_info['extension_code'],
                'size_id'=>$size_id,
            );
            $result = $Cart->add($data);
            $result && cookie('goods_id',null);
        } elseif ($custom_id) {
            //定制服装
            $material_id = M('custom')->getFieldById($custom_id,'material_id');
            $price = M('material')->getFieldById($material_id,'price');
            if (empty($price))
                $this->_error('该商品数据有误！');
            $data = array(
                'user_id'=>$this->_userid,
                'is_custom'=>1,
                'goods_id'=>$custom_id,
                'goods_name'=>'服装定制',
                'market_price'=>$price,
                'goods_price'=>$price,
                'goods_number'=>$num,
                'goods_attr'=>'',
                'is_real'=>1,
                'extension_code'=>'',
                'size_id'=>$size_id,
            );
            $result = M('cart')->add($data);
            $result && cookie('custom_id',null);
        } else {
            $this->_error('请重新购买商品');
        }

        if (empty($result)) {
            $this->_error('系统错误，请稍后再试');
        } else {
            if (IS_AJAX) {
                $this->_success('', array('redirect'=>U('Home/Cart/index','',true,true)));
            } else {
                redirect(U("Home/Cart/index"));
            }
        }
    }

    //删除
    function delete() {
        $id = I('post.id/d');
        $Cart = D('cart');
        if (empty($id) || !$Cart->where(array('rec_id'=>$id,'user_id'=>$this->_userid))->find())
            $this->_error('数据错误', 'Cart_index');
        $result = $Cart->del($id);
        if ($result) {
            $this->_success();
        } else {
            $this->_error('删除失败', 'Cart_index');
        }
    }

    //修改数量
    function update() {
        $id = I('post.id/d');
        $inc = I('post.inc/d');
        $Cart = D('cart');
        if (empty($id) || !$Cart->where(array('rec_id'=>$id,'user_id'=>$this->_userid))->find())
            $this->_error('数据错误', 'Cart_index');
        $number = $Cart->update($id, $inc ? 1 : -1);
        $this->_success('', array('num'=>$number));
    }

    //结算中心页面
    function order() {
        $cart_data = D('cart')->getList($this->_userid);
        if ($cart_data['total_money'] <= 0) {
            $this->_error('购物车数据错误');
        }
        $this->assign($cart_data);
        //=======公共数据========
        //快递方式
//        $express_type=array('id'=>1,'name'=>'快递');
//        $this->assign('express_type',$express_type);
        //配送地址（如果没有没有传输address_id过来就选取默认地址本）
//        if(isset($_REQUEST['address_id'])&&!empty($_REQUEST['address_id'])){
//            $sql='SELECT * FROM ecs_address WHERE id="'.intval($_REQUEST['address_id']).'" AND userid="'.$_SESSION['uid'].'"';
//        }else{
//            $sql='SELECT * FROM ecs_address WHERE userid="'.$_SESSION['uid'].'" AND is_default=1 LIMIT 0,1';
//        }
        $address_data = M('address')->find(cookie('address_id'));
        if($address_data){
            $sql='SELECT region_name FROM ecs_region WHERE region_id="'.$address_data['pro_id'].'" UNION SELECT region_name FROM ecs_region WHERE region_id="'.$address_data['city_id'].'" UNION SELECT region_name FROM ecs_region WHERE region_id="'.$address_data['area_id'].'"';
            $area_data=M()->query($sql);
            $area_data[2]['region_name']=(!empty($area_data[2]['region_name']))?$area_data[2]['region_name']:"";
            $address_data['address']=$area_data[0]['region_name'].$area_data[1]['region_name'].$area_data[2]['region_name'].$address_data['remark'];
            $this->assign('address_data',$address_data);
        }

        //购物车数据
        /*
        if(isset($_REQUEST['iserror'])&&$_REQUEST['iserror']==1)$error_msg='订单数据不能为空!';
        if(isset($_REQUEST['iserror'])&&$_REQUEST['iserror']==2)$error_msg='商品库存不足!';
        $this->assign('error_msg',$error_msg);*/

        //TODO 预计送达时间
        $this->assign('arrival_time','大后天');
        $this->assign('title','订单确认支付');
        $this->display();
    }

}