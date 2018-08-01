<?php

namespace Admin\Behavior;

class ActionBehavior {
	public function run(&$params){
		if(session('?admin')){
			$framework_id = D('FrameworkView')->where(array(
				'p_sign' => strtolower(CONTROLLER_NAME),
				'sign' => strtolower(ACTION_NAME)
			))->getField('id');
			if(!empty($framework_id)){
				$get = json_encode(I('get.'));
				$post = json_encode(I('post.'));
				M('ActLog', 'admin_', 'DB1')->add(array(
					'user_id' => session('admin.id'),
					'framework_id' => $framework_id,
					'get' => substr($get, 0, 190) . '...',
					'post' => substr($post, 0, 190) . '...'
				));
			}
		}
	}
}