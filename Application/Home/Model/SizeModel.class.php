<?php

namespace Home\Model;
use Think\Model;

class SizeModel extends Model {

    protected $tableName = 'user_size';

    protected $_auto = array (
        array('status','1'),  // 新增的时候把status字段设置为1
        array('add_time','time',1,'function'), // 对add_time字段在新增的时候写入当前时间戳
        array('update_time','time',2,'function'), // 对update_time字段在更新的时候写入当前时间戳
    );

    protected $_validate = array(
        array('jingwei','require','请输入颈围'),
        array('xiongwei','require','请输入胸围'),
        array('yaowei','require','请输入腰围'),
        array('tunwei','require','请输入臀围'),
        array('jiankuan','require','请输入肩宽'),
        array('yichang','require','请输入衣长'),
        array('xiuchang','require','请输入袖长'),
        array('biwei','require','请输入臂围'),
        array('wanwei','require','请输入腕围'),
    );

    function getList() {
        $data = $this->field('*')
            ->where(array('is_delete'=>0,'is_on_sale'=>1))
            ->order('sort_order desc,goods_id desc')->select();
        return $data;
    }

    function detail($id) {
        $data = $this->field('*')->find($id);
        return $data;
    }
}