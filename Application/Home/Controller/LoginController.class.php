<?php
namespace Home\Controller;

class LoginController extends HomeController {

    protected function  _initialize() {
        parent::_initialize();
    }

    function index(){
        redirect(U('Home/Login/login'));
    }

    function login() {
        if (IS_POST) {
            $username = I('post.username');
            $referer = I('referer','','htmlspecialchars_decode');
            $password = md5(I('post.password'));
            $Users = M('users');
            $user_id = $Users->where(array('mobile_phone'=>$username,'password'=>$password))->getField('user_id');
            if (empty($user_id)) {
                $user_id = $Users->where(array('user_name'=>$username,'password'=>$password))->getField('user_id');
                if (empty($user_id)) {
                    $this->_error('帐号或密码错误');
                }
            }
            $this->_login($user_id);
            $redirect = $referer ? $referer : U('Home/Index/index','',true,true);
            redirect($redirect);
        } else {
            $referer = I('server.HTTP_REFERER');
            $redirect = $referer ? $referer : U('Home/Index/index','',true,true);
            if ($this->_userid) {
                redirect($redirect);
            }
            $this->assign('referer', $redirect);
            $this->assign('title', '会员登录');
            $this->display();
        }
    }

    //注册
    function register() {
        if (IS_POST) {
            $password = I('post.password');
            $username = I('post.username');
            $mobile_phone = I('post.mobile');
            if (strlen($username) < 3)
                $this->_error('会员名长度至少3位');
            if (strlen($password) < 6)
                $this->_error('密码长度至少6位');
            if (empty($password) || $password != I('post.pwd'))
                $this->_error('请输入相同的密码');
            if (strlen($mobile_phone) != 11)
                $this->_error('请输入正确的手机号');
            $Users = M('users');
            if ($Users->where(array('user_name'=>$username))->find())
                $this->_error('该会员名已被注册');
            if ($Users->where(array('mobile_phone'=>$mobile_phone))->find())
                $this->_error('该手机号已被注册');
            $data = array(
                'user_name'=>$username,
                'mobile_phone'=>$mobile_phone,
                'password'=>md5($password),
                'reg_time'=>NOW_TIME,
                'alias'=>'',
                'msn'=>'',
                'qq'=>'',
                'office_phone'=>'',
                'home_phone'=>'',
                'credit_line'=>0,
            );
            $user_id = $Users->add($data);
            if ($user_id) {
                $this->_login($user_id);
                redirect(U('Home/Index/index','',true,true));
            } else {
                $this->_error('注册失败，请重新再试');
            }
        } else {
            if ($this->_userid) {
                redirect(U('Home/Index/index','',true,true));
            }
            $this->assign('title', '会员注册');
            $this->display();
        }
    }

    private function _login($user_id) {
        cookie('user_id', $user_id, 3600);//TODO 名称改为token；id要加密；修改时长
    }
}