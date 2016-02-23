<?php
namespace Home\Controller;
use Think\Controller;

class HomeController extends Controller {

    protected $_userid = 0;

    protected function  _initialize() {
        $this->_userid = I('cookie.user_id/d');
//        $this->_userid = 1;//TODO del
    }

    protected function _checkAuth() {
        if (!empty($this->_userid)) {
            return;
        }
        if (IS_AJAX) {
            $this->_return('请先登录',0,array('redirect'=>U('Home/Login/index','',true,true)));
        } else {
            redirect(U('Home/Login/login','',true,true));
        }
    }

    protected function _back() {
        $referer = I('server.HTTP_REFERER');
        $redirect = $referer ? htmlspecialchars_decode($referer) : U('Home/Index/index','',true,true);
        redirect($redirect);
    }
    protected function _success($msg = '', $data = array()) {
        empty($msg) && $msg = '操作成功';
        $this->_return($msg, 0, $data);
    }
    protected function _error($msg = '操作失败', $temple_name = '') {
        if (IS_AJAX) {
            $this->_return($msg, 1);
        } else {
            $this->assign('msg', $msg);
            $this->display($temple_name);
            exit;
        }
    }
    protected function _return($msg = '', $error = 0, $data = array()) {
        $res = array(
            'error'  => $error,
            'msg' => $msg,
            'data'    => $data
        );
        $this->ajaxReturn($res);
    }
}