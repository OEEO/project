<?php
namespace Admin\Controller;
use Admin\Controller\MainController;

class AdminController extends MainController {
	
	Protected $pagename = '管理员';
	
	/**
	 * 首页
	 */
	Public function index(){
		$this->actname = '列表';
		
		$admin = D('UserView');
		$datas['datas'] = $admin->select();
		
		$datas['operations'] = array(
				'查看操作日志' => 'showLogs(%id)',
				'切换管理组' => "changeGroups(%id,%group_id)",
				'修改密码' => "resetPassword(%id)",
				'删除账户' => "dataDelete(%id)"
		);
		$datas['pages'] = array(
				'sum' => $admin->count(),
                'count' => $admin->count()
		);
			
		$datas['lang'] = array(
            'id' => '会员ID',
            'username' => '管理员名称',
            'email' => '企业邮箱',
            'telephone' => '联系电话',
            'groupname' => '管理员组',
            'datetime' => '注册时间'
		);
		
		$datas['groups'] = $this->m1('group')->select();
		$this->assign($datas);
		$this->view();
	}
	
	/**
	 * 管理员添加
	 */
	Public function add(){
		if(IS_AJAX){
			$data['username'] = I('post.username');
			$data['password'] = encrypt(I('post.password'));
			$data['group_id'] = I('post.group_id');
            $data['email'] = I('post.email');
            $data['telephone'] = I('post.telephone');
			
			$user = D('user');
			if(!$user->create($data)){
				$this->error($user->getError());
			}

			$user->add();
			$this->success('添加成功！');
			exit;
		}
		$this->error('非法访问！');
	}
	
	/**
	 * 查看操作日志
	 */
	Public function showlogs(){
		if(IS_AJAX && IS_POST){
			$id = I('post.id');
			$starttime = I('post.starttime', null);
			$endtime = I('post.endtime', null);
			
// 			$map = array();
// 			$map[] = 'user_id=' . $id;
			
// 			if(!empty($starttime))$map[] = "datetime >= " . strtotime($starttime);
// 			if(!empty($endtime))$map[] = "datetime =< " . strtotime($endtime);

			$map = array('user_id' => $id);
			if(!empty($starttime))$map['datetime'] = array('EGT', $starttime);
			if(!empty($endtime))$map['datetime'] = array('ELT', $endtime);
			
			$rs = D('ActLogView')->where($map)->limit(1000)->order('datetime desc')->select();
			$this->success($rs);
		}
	}
	
	/**
	 * 修改密码
	 */
	Public function resetPass(){
		if(IS_AJAX){
			$data['password'] = encrypt(I('post.password'));
			$data['id'] = I('post.id');
			D('user')->save($data);
			$this->success('修改成功！');
			exit;
		}
		$this->error('非法访问！');
	}
	
	/**
	 * 切换管理组
	 */
	Public function changeGroup(){
		if(IS_AJAX){
			$data['group_id'] = I('post.group_id');
			$data['id'] = I('post.id');
			D('user')->save($data);
			if($data['id'] == session('admin.id')){
				session('admin.group_id', $data['group_id']);
			}
			$this->success('修改成功！');
			exit;
		}
		$this->error('非法访问！');
	}
	
	/**
	 * 删除
	 */
	Public function delete(){
		if(IS_AJAX){
			$data['id'] = I('post.id');
			if($data['id'] == session('admin.id')){
				$this->error('无法删除自己！');
			}
			/*D('user')->where($data)->delete();*/
            $this->m1('ActLog')->where(['user_id'=>$data['id']])->delete();
            $this->m1('LoginLog')->where(['user_id'=>$data['id']])->delete();
            $this->m1('User')->where(['id'=>$data['id']])->delete();
			$this->success('删除成功！');
			exit;
		}
		$this->error('非法访问！');
	}
	
	/**
	 * 管理组列表
	 */
	Public function group(){
		//AJAX获取权限列表
		if(IS_AJAX){
			$group_id = I('post.group_id');
			$rs = $this->m1('authority')->where(array('group_id' => $group_id, 'allow_pass' => 1))->select();
			$data = array();
			foreach($rs as $row){
				$data[] = $row['framework_id'];
			}
			$this->ajaxReturn($data);
		}
		
		$this->actname = "管理组列表";
		$group = $this->m1('group');
		$datas['datas'] = $group->page(I('get.page'), C('table.listnum'))->select();
		
		$datas['operations'] = array(
				'修改组' => "dataModify(%id, '%name')",
				'删除组' => "dataDelete(%id)"
		);
		$datas['pages'] = array(
				'sum' => $group->count()
		);
			
		$datas['lang'] = array(
				'id' => '组ID',
				'name' => '管理组名称',
				'datetime' => '创建时间'
		);
		
		$rs = $this->m1('framework')->select();
		foreach($rs as $row){
			if($row['type'] == 1)
				$datas['framework'][$row['id']]['name'] = $row['name'];
			else
				$datas['framework'][$row['pid']]['sub'][] = array(
					'name' => $row['name'],
					'type' => $row['type'],
					'id' => $row['id']
				);
		}
		
		$this->assign($datas);
		$this->view();
	}
	
	/**
	 * 添加管理员组
	 */
	Public function addGroup(){
		$name = I('post.name');
		$framework = I('post.framework');
		if(empty($name) || empty($framework)){
			$this->error('请将资料填写完整！');
			exit;
		}
		
		$group_id = $this->m1('group')->add(array('name' => $name));
		if(is_numeric($group_id)){
			$dataList = array();
			foreach($framework as $val){
				$dataList[] = array(
					'group_id' => $group_id,
					'framework_id' => $val,
					'allow_pass' => 1
				);
			}
			if($this->m1('authority')->addAll($dataList)){
				$this->success('管理组添加成功！');
				exit;
			}
		}
		$this->error('管理组添加失败！');
	}
	
	/**
	 * 修改管理员组
	 */
	Public function modifyGroup(){
		$name = I('post.name');
		$framework = I('post.framework');
		$group_id = I('post.group_id');
		if(empty($name) || empty($framework) || empty($group_id)){
			$this->error('请将资料填写完整！');
			exit;
		}
		
		$data = array(
			'id' => $group_id,
			'name' => $name
		);
		
		if(is_numeric($this->m1('group')->save($data))){
			$authority = $this->m1('authority');
			$authority->where(array('group_id' => $group_id))->delete();
			$dataList = array();
			foreach($framework as $val){
				if(empty($val))continue;
				$dataList[] = [
					'group_id' => $group_id,
					'framework_id' => $val,
					'allow_pass' => 1
				];
			}
			if($authority->addAll($dataList)){
				$this->success('管理组修改成功！');
				exit;
			}
		}
		$this->error('管理组修改失败！');
	}
	
	/**
	 * 删除管理组
	 */
	Public function deleteGroup(){
		$group_id = I('post.id');
		if(session('admin.group_id') == $group_id){
			$this->error('不能删除自己所属的权限组！');
		}
		$rs = array();
		//删除权限
		$rs[] = $this->m1('authority')->where(array('group_id' => $group_id))->delete();
		//删除管理员
		$rs[] = $this->m1('user')->where(array('group_id' => $group_id))->delete();
		//删除组
		$rs[] = $this->m1('group')->where(array('id' => $group_id))->delete();

		$this->success('删除成功！');
	}

	
}


