<?php

namespace Member\Common;
use Common\Controller\MController;

class MainController extends MController {
	
	//不需要登录的页面
	Private $allowPage = ['index/mergeaccount', 'index/auth', 'index/register', 'index/sendsms', 'mypic/tobase64', 'index/autologin', 'coupon/wxinto', 'index/invitecode', 'coupon/getcoupon', 'member/setdevice', 'index/img', 'index/gettoken'];

	//2016密码登录权的账号
	Protected $tels = [];

	/**
	 * 会员模块通用入口
	 */
	Public function __construct(){
		parent::__construct();

		//判断是否已登录
		if(!session('?member')){
			define('IS_LOGIN', false);
			$this->tels = M('MemberInfo')->join('__MEMBER__ on id=member_id')->where(['is_white' => 1])->getField('telephone', true);
			if(!in_array(strtolower(CONTROLLER_NAME . '/*'), $this->allowPage) && !in_array(strtolower(CONTROLLER_NAME . '/' . ACTION_NAME), $this->allowPage))
				$this->error('尚未登录，无法访问！');
		}else{
			define('IS_LOGIN', true);
		}
	}
}