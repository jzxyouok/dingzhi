<?php
namespace Home\Controller;

class AddressController extends HomeController {

    protected function  _initialize() {
        parent::_initialize();
        $this->_checkAuth();
    }

    //地址列表
    function index() {
        $address_data = M('address')->where(array('userid'=>$this->_userid))
            ->order('is_default desc,id desc')->select();
        foreach ($address_data as &$row) {
            $sql='SELECT region_name FROM ecs_region WHERE region_id="'.$row['pro_id'].'" UNION SELECT region_name FROM ecs_region WHERE region_id="'.$row['city_id'].'" UNION SELECT region_name FROM ecs_region WHERE region_id="'.$row['area_id'].'"';
            $area_data=M()->query($sql);
            $area_data[2]['region_name']=(!empty($area_data[2]['region_name']))?$area_data[2]['region_name']:"";
            $row['address']=$area_data[0]['region_name'].$area_data[1]['region_name'].$area_data[2]['region_name'].$row['remark'];
        }
        $this->assign('address_list',$address_data);
        $this->assign('title','收货地址');
        $this->display();
    }

    //选择某个地址
    function choose() {
        $id = I('post.id/d');
        if (M('address')->where(array('id'=>$id,'userid'=>$this->_userid))->find()) {
            cookie('address_id', $id);
        }
        //TODO 设为默认？
        $this->_success('',array('redirect'=>U('Home/Cart/order')));
//        redirect(U('Home/Cart/order'));
    }

    //添加页面
    function add() {
        if (IS_POST) {
            $data = array(
                'userid'=>$this->_userid,
                'name'=>I('post.name'),
                'phone'=>I('post.phone'),
                'pro_id'=>I('post.pro_id/d'),
                'city_id'=>I('post.city_id/d'),
                'area_id'=>0,
                'remark'=>I('post.remark'),
                'zipcode'=>I('post.zipcode'),
                'is_default'=>0,
            );
            if (empty($data['name']) || empty($data['phone']) || empty($data['remark']))
                $this->_error('请输入必要的数据');
            $result = M('address')->add($data);
            if ($result) {
                redirect(U('Home/Address/index'));
            } else {
                $this->_error('添加失败');
            }
        } else {
            $this->assign('title', '添加收货地址');
            $this->display();
        }
    }

    function delete() {
        if (M('address')->where(array('id'=>I('post.id'),'userid'=>$this->_userid))->delete()) {
            $this->_success('删除成功');
        } else {
            $this->_error('删除失败');
        }
    }

}