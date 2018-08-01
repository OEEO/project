<?php

namespace Member\Controller;
use Member\Common\MainController;

// @className 常规工具
class ProfitController extends MainController {

	public function getProfit() {
		$goods_content = M('Goods')->alias('a')
									->join('__PICS__ as b ON a.pic_id = b.id')
									->join('__PROFIT_LIST__ AS c ON a.id = c.goods_id')
									->where('a.is_pass = 1')
									->field('a.id,title,b.path,price,c.share_money')
									->order('c.id DESC')
									->select();
		foreach($goods_content as $key=>$value) {
			$goods_content[$key]['path'] = thumb($goods_content[$key]['path'], 1);
		}
		$this->ajaxReturn($goods_content);
	}

	public function getShareMoney() {
		$goods_id = I('post.goods_id');
		$share_money = M('ProfitList')->where(['goods_id' => $goods_id])
									->getfield('share_money');
		$this->ajaxReturn($share_money);
	}

	public function getProfitOrder() {
		$member_id = session('member.id');
		/*
		$order_id = M('Order')->field('id')
								->where("invite_member_id != member_id and invite_member_id=".$member_id." and act_status != 0")
								->select();
		foreach ($order_id as $key=>$value) {
			$order_str .= $order_id[$key]['id'].',';
		}
		if ($order_id) {
			$order_str = substr($order_str,0,strlen($order_str)-1);
			$goods = M('OrderWares')->alias('a')
									->join('__PROFIT_LIST__ as b ON a.ware_id = b.goods_id')
									->join('__GOODS__ as c ON a.ware_id = c.id')
									->join("__ORDER_PAY__ as d ON d.order_id = a.order_id")
									->join('LEFT JOIN __PROFIT_BALANCE__ as e ON a.order_id=e.order_id')
									->field('a.ware_id,b.share_money,c.title,d.success_pay_time,e.reason,unix_timestamp(e.datetime),e.datetime')
									->where("a.order_id in (".$order_str.")")
									->where(['b.status' => 1])
									->distinct(true)
									->order('d.success_pay_time DESC')
//									->order('unix_timestamp(e.datetime) DESC')
									->select();

		}
		*/
		$goods = M('Order')->alias('a')
						->join('__ORDER_WARES__ as b on a.id=b.order_id')
						->join('__PROFIT_LIST__ as c on b.ware_id=c.goods_id')
						->join('__GOODS__ as d on b.ware_id=d.id')
						->join('__ORDER_PAY__ as e on e.order_id=a.id')
						->join('LEFT JOIN __PROFIT_BALANCE__ as f on a.id=f.order_id')
						->join('LEFT JOIN __ORDER_REFUND__ as g on a.id=g.order_id')
						->field('b.ware_id,(c.share_money * count(b.ware_id)) as share_money,d.title,e.success_pay_time,f.reason,unix_timestamp(f.datetime),f.datetime,a.act_status,g.refund_num,count(b.ware_id) as buy_num,c.share_money as unit_share_money')
						->where("a.invite_member_id != a.member_id and a.invite_member_id=".$member_id." and a.act_status != 0")
						->where(['c.status' => 1])
//						->distinct(true)
						->order('e.success_pay_time DESC')
						->group('a.id')
						->select();
		if (!$goods) $goods = [
			'no_rs' => '暂无数据'
		];
		$this->ajaxReturn($goods);
	}

	public function getShareImg() {
		$member_id = session('member.id');
		$goods_id = I('goods_id');
		$invitecode = M('Member')->where(['id' => $member_id])->getfield('invitecode');
//		$this->ajaxReturn('http://phph.sc2yun.com/can2.html');
		$data['src1'] = 'http://api.test.yummy194.cn/Member/Verify/qrcode?goods_id='.$goods_id.'&invitecode='.$invitecode;
		$goods = M('Goods')->alias('a')
							->join('__PICS__ as b ON a.pic_id = b.id')
							->where(['a.id' => $goods_id])
							->field('path,title,price')
							->find();
		$data['src2'] = 'http://api.test.yummy194.cn/Member/Verify/curl2?mainpic='.$goods['path'];
//		$data['src2'] = $mainpic;
		$data['title'] = $goods['title'];
		$data['price'] = $goods['price'];
		$this->ajaxReturn($data);
	}

	public function getTodayProfit() {
		$member_id = session('member.id');
		$beginToday = mktime(0,0,0,date('m'),date('d'),date('Y'));
		/*
		$order_id = M('Order')->field('id')
							->where("invite_member_id != member_id and invite_member_id=".$member_id." and act_status != 0")
							->select();
		foreach ($order_id as $key=>$value) {
			$order_str .= $order_id[$key]['id'].',';
		}
		if ($order_id) {
			$order_str = substr($order_str,0,strlen($order_str)-1);
			$money = M('OrderWares')->alias('a')
									->join('__PROFIT_LIST__ as b ON a.ware_id = b.goods_id')
									->join('__GOODS__ as c ON a.ware_id = c.id')
									->join("__ORDER_PAY__ as d ON d.order_id = a.order_id")
									->field('b.share_money')
									->where("a.order_id in (".$order_str.")")
									->where(['b.status' => 1])
									->where(['d.success_pay_time' => ['gt', $beginToday]])
									->distinct(true)
									->order('d.success_pay_time DESC')
									->select();			
		}
		*/
		$money = M('Order')->alias('a')
						->join('__ORDER_WARES__ as b on a.id=b.order_id')
						->join('__PROFIT_LIST__ as c on b.ware_id=c.goods_id')
						->join('__GOODS__ as d on b.ware_id=d.id')
						->join('__ORDER_PAY__ as e on e.order_id=a.id')
						->join('LEFT JOIN __PROFIT_BALANCE__ as f on a.id=f.order_id')
						->join('LEFT JOIN __ORDER_REFUND__ as g on a.id=g.order_id')
						->field('(c.share_money * count(b.ware_id)) as share_money')
						->where("a.invite_member_id != a.member_id and a.invite_member_id=".$member_id." and a.act_status != 0")
						->where(['c.status' => 1])
						->where(['e.success_pay_time' => ['gt', $beginToday]])
//						->distinct(true)
						->order('e.success_pay_time DESC')
						->group('a.id')
						->select();
		if (!$money) $money = [
			'no_rs' => '暂无数据'
		];
		$this->ajaxReturn($money);
	}

	public function getCustomer() {
		$member_id = session('member.id');
		$order = M('Order')->alias('a')
							->where("a.invite_member_id != a.member_id and a.invite_member_id=".$member_id." and a.act_status != 0")
							->join('__ORDER_WARES__ as b on a.id=b.order_id')
							->join('__MEMBER__ as c on a.member_id=c.id')
							->join('LEFT JOIN __PICS__ as d on c.pic_id=d.id')
							->join('__GOODS__ as e on b.ware_id=e.id')
		//					->where(['b.type' => 1])
							->field('c.nickname,d.path,e.title,sum(b.price) as price,count(c.nickname) as `count`,a.datetime')
							->group("c.nickname,e.title")
							->select();
		$visit = M('ProfitVisit')->alias('a')
								->where("a.invite_member_id=".$member_id)
								->join('__MEMBER__ as b on a.visitor_id=b.id')
								->join('LEFT JOIN __PICS__ as c on b.pic_id=c.id')
								->join('__GOODS__ as d on a.project_id=d.id')
								->field('b.nickname,c.path,d.title,0.00 as price,0 as count,a.datetime')
								->select();
		$data = array_merge($order,$visit);
		foreach($data as $key => $value) {
			if ($value['count'] == 0) {
				foreach ($data as $k => $v) {
					if ($value['nickname'] == $v['nickname'] && $value['title'] == $v['title'] && $v['count'] != 0) {
						unset($data[$key]);
					}
				}
			}
		}
		array_multisort(array_column($data,'datetime'),SORT_DESC,$data);
		$this->ajaxReturn($data);
	}

	public function CustomerVisit() {
		$invitecode = I('post.invitecode');
		$invite_member_id = M('Member')->where(["invitecode" => $invitecode])->getfield('id');
		$visitor_id = session('member.id');
		if ($invite_member_id == $visitor_id) $this->ajaxReturn('邀请人与访问用户是同一人');
		$type = I('post.type');
		$project_id = I('post.project_id');


		$data['invite_member_id'] = $invite_member_id;
		$data['visitor_id'] = $visitor_id;
		$data['type'] = $type;
		$data['project_id'] = $project_id;

		$rs = M('ProfitVisit')->where($data)->find();
		if ($rs) $this->ajaxReturn('已存在数据');
		$visit = M('ProfitVisit')->add($data);
		$this->success($data);
//		$this->ajaxReturn($data);
	}

	public function getCanWithdraw() {
		$member_id = session('member.id');
		$profit = M('Profit')->where(["member_id" => $member_id])->getfield('profit_money');
		$data['profit'] = $profit;
		if (!$profit) $data = [
			'no_rs' => '暂无数据'
		];
		$this->ajaxReturn($data);
	}

	public function sendWithdraw() {
		$member_id = session('member.id');
		$money = I('post.money');
		$realname = I('post.realname');
		$alipay = I('post.alipay');

		$data['price'] = $money;
		$data['realname'] = $realname;
		$data['payaccount'] = $alipay;
		$data['member_id'] = $member_id;
		$data['way'] = 1;
		$data['start_time'] = time();
		$data['is_balance'] = 0;

		M('ProfitWithdrawBalance')->add($data);
		$old_money = M('Profit')->where(['member_id' => $member_id])->getfield('profit_money');
		$new_money = $old_money - $money;
		$data2['profit_money'] = $new_money;
		M('Profit')->where(['member_id' => $member_id])->data($data2)->save();
		$this->ajaxReturn($data);
	}

	public function getWithdrawList() {
		$member_id = session('member.id');
		$data['member_id'] = $member_id;
//		$data['is_balance'] = 1;
		$rs = M('ProfitWithdrawBalance')->where(['member_id' => $member_id])->order('id DESC')->select();
		$this->ajaxReturn($rs);
	}

	public function checkWeekend() {
		if((date('w') == 6) || (date('w') == 0)){
			$this->ajaxReturn('1');
		} else {
			$this->ajaxReturn('0');
		}
	}
}
