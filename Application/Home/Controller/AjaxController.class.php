<?php
namespace Home\Controller;

class AjaxController extends HomeController {

    protected function  _initialize() {
        parent::_initialize();
    }

    //城市列表
    function cityList() {
        $pro_id = I('pro_id');
        $province_list = M('region')->field('region_id as val,region_name as name')->where(array('region_type'=>2,'parent_id'=>$pro_id))->select();
        if ($province_list) {
            $this->_success('', $province_list);
        } else {
            $this->_error();
        }
    }
    //门店列表
    function agencyList() {
        $city_id = I('city_id');
        $agency_list = M('agency')->field('agency_id as val,agency_name as name')->where(array('city'=>$city_id))->select();
        if ($agency_list) {
            $this->_success('', $agency_list);
        } else {
            $this->_error();
        }
    }
    //经销商列表
    function dealerList() {
        $agency_id = I('agency_id');
        $dealer_list = M('dealer')->field('dealer_id as val,dealer_name as name')->where(array('agency'=>$agency_id))->select();
        if ($dealer_list) {
            $this->_success('', $dealer_list);
        } else {
            $this->_error();
        }
    }

}