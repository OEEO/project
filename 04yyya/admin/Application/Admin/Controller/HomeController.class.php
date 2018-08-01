<?php
namespace Admin\Controller;
use Admin\Controller\MainController;

class HomeController extends MainController {
    protected $pagename = '首页管理';

	public function index() {
		$this->redirect('/Admin/Home/getHomeList');
	}

    public function add() {
		$id = I('post.id');
		$type = I('post.type');

		if($type == 0) {
			$row = $this->m2('home')->where(['t_id' => $id, 'type' => $type])->select();
		
		}else{
			$row = $this->m2('home')->where(['r_id' => $id, 'type' => $type])->select();
		}

		if (!empty($row)) {
			$this->json(-1, "你已经添加过了");
			return;
		}

		if($type == 0){
			// 活动
			$data = [
				't_id' => $id,
				'type' => $type
			];
		}else{
			// 众筹
			$data = [
				'r_id' => $id,
				'type' => $type
			];
		}

		$this->m2('home')->add($data);
		$this->json(1, "添加成功");
	}

	public function removeItem() {
		$id = I('post.id');
		$type = I('post.type');

		if($type == 1) {
			$rs = $this->m2('home')->where(["r_id" => $id, "type" => $type])->delete();
		} else {
			$rs = $this->m2('home')->where(["t_id" => $id, "type" => $type])->delete();
		}

		if($rs == 0) {
			$this->json(-1, '删除不成功', $rs);
		} else {
			$this->json(1, '删除成功');
		}
	}

	public function shift() {
		$id = I('post.id');
		$type = I('post.type');
		$action = I('post.action');

		if($type == 1) {
			$c = 'r_id';
		} else {
			$c = 't_id';
		}
		$weight = $this->m2('home')->where([$c => $id, 'type' => $type])->getField('weight');

		if($action === 'shift_up') {
			$weight = $weight + 1;
		} else {
			$weight = $weight - 1;
		}

		$this->m2('home')->where([$c => $id, 'type' => $type])->save(['weight' => $weight]);
		$this->json(1, '上移成功');
	}

	protected function json($status, $info = "", $data = "") {
		return $this->ajaxReturn(array(
			"status" => $status,
			"info" => $info,
			"data" => $data
		));
	}
}