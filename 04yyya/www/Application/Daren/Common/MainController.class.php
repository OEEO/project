<?php
namespace Daren\Common;
use Common\Controller\MController;

Class MainController extends MController {
	
	//不需要登录的页面
	Private $allowPage = [];
	
	/**
	 * 会员模块通用入口
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
			//判断是否是达人
			if(!session('?daren')){
				$info = D('DarenView')->where(['member_id' => session('member.id'), 'tag_id' => 18])->find();
				if(empty($info))$this->error('你不是达人，无法进入达人服务中心！');
				session('daren', $info);
			}
		}
	}

}