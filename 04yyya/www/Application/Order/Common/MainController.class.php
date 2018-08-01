<?php

namespace Order\Common;
use Common\Controller\MController;

class MainController extends MController {

	Private $allowPage = ['pay/wx_notify', 'pay/alipay_notify'];
	/**
	 * 订单模块通用入口
	 */
	Public function __construct(){
		parent::__construct();
		//判断是否已登录
		if(!session('?member')){
			define('IS_LOGIN', false);
			if(!in_array(strtolower(CONTROLLER_NAME . '/*'), $this->allowPage) && !in_array(strtolower(CONTROLLER_NAME . '/' . ACTION_NAME), $this->allowPage))
				$this->error('尚未登录，无法访问！');
		}else{
			define('IS_LOGIN', true);
		}
	}
}