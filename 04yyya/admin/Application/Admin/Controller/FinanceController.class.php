<?php
namespace Admin\Controller;
use Admin\Controller\MainController;

class FinanceController extends MainController {

	Protected $pagename = '财务管理';

	public function settlement(){
		$this->actname = '结算管理';
		$type = I('get.type', 0);//0-活动 1-商品
		$title = I('get.title');
		$page = I('get.page', 1);
		$page_count = 30;

		if($type == 0){
//			$sql = $this->m2('OrderWares')->field(['tips_times_id'])->join('__ORDER__ a on a.id=order_id')->where(['status' => 1,'type'=>0, 'act_status' => ['IN', '1,2,3,4'], 'tips_times_id' => ['EXP', 'IS NOT NULL'], 'tips_times_id' => ['NEQ', ''], 'oldid' => ['EXP', 'is null']])->buildSql();
			$sql = $this->m2('OrderWares')->field(['tips_times_id'])->join('__ORDER__ a on a.id=order_id')->where(['status' => 1,'type'=>0, 'act_status' => ['IN', '1,2,3,4'], 'tips_times_id' => ['EXP', 'IS NOT NULL'], 'tips_times_id' => ['NEQ', '']])->buildSql();
			$where = ['stop_buy_time' => ['LT', time()], 'title' => ['NOTLIKE','%测试%'], 'price' => ['GT', 0], 'status' => 1, 'is_pass' => 1, 'times_id' => ['EXP', "in ({$sql})"]];
			if(!empty($title))$where['title'] = ['LIKE', '%'. $title .'%'];
			$rs = D('SettlementTipsView')->where($where)->page($page, $page_count)->order('start_time desc')->select();
			$page_sum = D('SettlementTipsView')->where(['stop_buy_time' => ['LT', time()], 'title' => ['NOTLIKE','%测试%'], 'price' => ['GT', 0], 'status' => 1, 'is_pass' => 1, 'times_id' => ['EXP', "in ({$sql})"]])->count();
		}else{
			$sql = $this->m2('OrderWares')->field(['ware_id'])->join('__ORDER__ a on a.id=order_id')->where(['status' => 1,'type'=>1, 'act_status' => ['IN', '1,2,3,4'], 'oldid' => ['EXP', 'is null']])->buildSql();
			$where = ['title' => ['NOTLIKE','%测试%'], 'price' => ['GT', 0], 'status' => 1, 'is_pass' => 1, 'id' => ['EXP', "in ({$sql})"]];
			if(!empty($title))$where['title'] = ['LIKE', '%'. $title .'%'];
			$rs = D('SettlementGoodsView')->where($where)->page($page, $page_count)->order('id desc')->select();
			$page_sum = D('SettlementGoodsView')->where(['status' => 1, 'title' => ['NOTLIKE','%测试%'], 'price' => ['GT', 0], 'is_pass' => 1, 'id' => ['EXP', "in ({$sql})"]])->count();
		}

		$datas['datas'] = [];
		$pay_type = ['支付宝','微信APP','微信WEB'];
		foreach($rs as $row){
			$data = [];
			if($type == 0){
				$data['code'] = 'A' . $row['id'] . '-' . $row['times_id'];
				$data['sid'] = $row['times_id'];
				$data['start_time'] = date('Y-m-d H:i', $row['start_time']);
			}else{
				$data['code'] = 'B' . $row['id'];
				$data['sid'] = $row['id'];
			}

			$data['id'] = $row['id'];
			$data['member_id'] = $row['member_id'];
			$data['title'] = $row['title'];
			$data['price'] = $row['price'];
			$data['type'] = $type;
			$data['nickname'] = $row['nickname'];
			$data['telephone'] = $row['telephone'];

			//实收款数
			$times_sql = $type==0?"and `tips_times_id`='{$row['times_id']}'":'';
			$sql = "
				Select a.price as 'price',c.type as 'type',a.channel as 'channel',count(a.id) as 'count' from __ORDER__ a
				join __ORDER_WARES__ b on a.id=b.order_id
				join __ORDER_PAY__ c on a.id=c.order_id
				where b.`type`='{$type}' and `ware_id`='{$row['id']}' {$times_sql} and `status`=1 and `act_status` in (1,2,3,4)
				group by a.id
			";
			$_rs = $this->m2()->query($sql);
			$sum = $count = 0;
			$income = [];
			foreach($_rs as $r){
				if(in_array($r['channel'], [7,8,9]))
					$str = '有饭';
				else
					$str = '吖咪';
				$str .= $pay_type[$r['type']];
				$income[$str] += (float)$r['price'];
				$sum += (float)$r['price'];
				$count += (int)$r['count'];
			}
			$arr = [];
			foreach($income as $k => $r){
				$arr[] = $k . ':' . $r;
			}
			$data['income'] = '￥' . $sum . '<br><small>['. join('|', $arr) .']</small>';
			$data['count'] = $count;

			//应收款数
			$data['money'] = $row['price'] * $count;

			//已结算金额
			if($type == 0)
				$data['settlemented'] = $this->m2('Settlement')->where(['type' => 0, 'type_id' => $row['times_id']])->sum('amount');
			else
				$data['settlemented'] = $this->m2('Settlement')->where(['type' => 1, 'type_id' => $row['id']])->sum('amount');
			if(empty($data['settlemented']))$data['settlemented'] = 0;
			//未结算金额
			$data['settlement'] = $data['money'] - $data['settlemented'];

			$datas['datas'][] = $data;
		}

		$datas['operations'] = [
			'查看订单' => [
				'style' => 'default',
				'fun' => 'showOrders(%sid, %type, this)',
				'condition' => '%count>0'
			],
			'手动结算' => [
				'style' => 'success',
				'fun' => 'settle_Manual(%sid, %type)',
				'condition' => '%settlement>0'
			],
			'自动结算' => [
				'style' => 'secondary',
				'fun' => 'setSettle(%sid, %type)',
				'condition' => '%settlement>0'
			],
		];
		$datas['pages'] = [
			'sum' => $page_sum,
			'count' => $page_count
		];

		$datas['lang'] = [
			'code' => '商品代码',
		];
		if($type == 0)$datas['lang']['start_time'] = '活动时间';

		$datas['lang']['title'] = ['商品标题', '', '15%'];
		$datas['lang']['nickname'] = 'HOST昵称';
		$datas['lang']['telephone'] = 'HOST手机号';
		$datas['lang']['price'] = ['单价','￥%*%'];
		$datas['lang']['income'] = '已收款';
		$datas['lang']['money'] = ['应收款','￥%*%'];
		$datas['lang']['settlement'] = ['未结算','￥%*%'];
		$datas['lang']['settlemented'] = ['已结算','￥%*%'];

		$this->assign($datas);
		$this->view();
	}

	//显示订单列表
	public function showOrders(){
		$goods_id = I('post.goods_id');
		$type = I('post.type', 0);
		$times_id = I('post.times_id');

		if($type == 0){
			$sql = "
				Select a.id as 'id',a.sn as 'sn',a.price as 'price',f.value as 'coupon_value',count(a.id) as 'count',d.telephone as 'telephone',c.type as 'pay_type',a.channel as 'channel',c.trade_no as 'trade_no',a.create_time as 'create_time' from __ORDER__ a
				join __ORDER_WARES__ b on a.id=b.order_id
				join __ORDER_PAY__ c on a.id=c.order_id
				join __MEMBER__ d on a.member_id=d.id
				left join __MEMBER_COUPON__ e on e.id=a.member_coupon_id
				left join __COUPON__ f on e.coupon_id=f.id
				where b.`type`='{$type}' and `tips_times_id`='{$times_id}' and a.`status`=1 and `act_status` in (1,2,3,4)
				group by a.id
			";
		}else{
			$sql = "
				Select a.id as 'id',a.sn as 'sn',a.price as 'price',f.value as 'coupon_value',count(a.id) as 'count',d.telephone as 'telephone',c.type as 'pay_type',a.channel as 'channel',c.trade_no as 'trade_no',a.create_time as 'create_time' from __ORDER__ a
				join __ORDER_WARES__ b on a.id=b.order_id
				join __ORDER_PAY__ c on a.id=c.order_id
				join __MEMBER__ d on a.member_id=d.id
				left join __MEMBER_COUPON__ e on e.id=a.member_coupon_id
				left join __COUPON__ f on e.coupon_id=f.id
				where b.`type`='{$type}' and `ware_id`='{$goods_id}' and a.`status`=1 and `act_status` in (1,2,3,4)
				group by a.id
			";
		}
		$rs = $this->m2()->query($sql);

		$data = [];
		$channel = C('CHANNEL');
		foreach($rs as $row){
			$row['create_time'] = date('Y-m-d H:i:s', $row['create_time']);
			$row['pay_type'] = ['支付宝', '微信APP', '微信WEB'][$row['pay_type']];
			$row['channel'] = $channel[$row['channel']];
			$row['coupon_value'] = $row['coupon_value']?:'';
			$data[] = $row;
		}

		$this->ajaxReturn($data);
	}

	//结算
	Public function settleManual(){
		$id = I('post.id');
		$type = I('post.type');

		if($type == 0){
			$times = $this->m2('TipsTimes')->field(['tips_id', 'start_time'])->where(['id' => $id])->find();
			$tips_id = $times['tips_id'];
			$rs = $this->m2('tips')->field(['member_id', 'title', 'price'])->where(['id' => $tips_id])->find();
			$num = $this->m2('OrderWares')->join('__ORDER__ a on a.id=order_id')->where(['status' => 1,'tips_times_id' => $id,'act_status' => ['IN', '1,2,3,4']])->count();
		}elseif($type == 1){
			$rs = $this->m2('goods')->field(['member_id', 'title', 'price'])->where(['id' => $id])->find();
			$num = $this->m2('OrderWares')->join('__ORDER__ a on a.id=order_id')->where(['status' => 1,'type' => 1,'ware_id' => $id,'act_status' => ['IN', '1,2,3,4']])->count();
		}
		if(empty($rs))$this->error('要结算的商品不存在!');

		//银行卡
		$_rs = $this->m2('MemberBank')->field(['ym_member_bank.id' => 'id', 'ym_member_bank.name' => 'name', 'a.name' => 'bankname', 'number'])->join('__BANK__ a on a.id=bank_id')->where(['member_id' => $rs['member_id'], 'status' => 1])->find();
		if(empty($_rs))$this->error('请先添加达人银行卡!');
		//已结算
		$m = $this->m2('Settlement')->where(['type' => 0, 'type_id' => $id])->sum('amount');
		if(!session('?admin'))$this->error('请重新登录刷新页面！');

		if(!empty($_POST['money'])){
			$money = (float)$_POST['money'];
			if($money <= 0 || $money > $rs['price'] * $num - $m)$this->error('请填写正确的金额!');
			$this->m2('Settlement')->add([
				'pay_type' => 0,
				'pay_id' => $_rs['id'],
				'type' => $type,
				'type_id' => $id,
				'amount' => $money,
				'originator_id' => session('admin.id'),
				'sn' => createCode(20),
			]);
			//获取会员手机号
			$telephone = $this->m2('member')->where(['id' => $rs['member_id']])->getField('telephone');
//			if(isset($times)){
//				$start_time = date('Y-m-d H:i', $times['start_time']);
//				$content = "您所发布的活动『{$rs['title']}』({$start_time})已有{$num}位用户参与，菜金合计". ($rs['price'] * $num) ."元，";
//			}else{
//				$content = "您所发布的商品『{$rs['title']}』已有{$num}份被卖出，菜金合计". ($rs['price'] * $num) ."元，";
//			}
//			if($_rs['name'] == '支付宝' || $_rs['name'] == '微信钱包'){
//				$content .= "已存入您账号为{$_rs['number']}的{$_rs['name']}{$money}元，请笑纳。如有疑问请咨询小助理!";
//			}else{
//				//银行卡尾号
//				$number = substr($_rs['number'], -4);
//				$content .= "已存入您尾号{$number}的{$_rs['name']}卡{$money}元，请笑纳。如有疑问请咨询小助理!";
//			}
//			$data_1 = [
//				'telephone'=>$telephone,
//				'content'	=>$content,
//			];
//			$this->ajaxReturn($data_1);
//			sms_send($telephone, $content);



			//2016-12-29
			if(isset($times)){//活动
				$start_time = date('Y-m-d H:i', $times['start_time']);
				if($_rs['name'] == '支付宝' || $_rs['name'] == '微信钱包'){
					$params = array(
						'title' => $rs['title'],
						'start_time' => $start_time,
						'num' => (string)$num,
						'price_num' => (string)($rs['price'] * $num),
						'number' => (string)$_rs['number'],
						'name' => $_rs['name'],
						'money' => (string)$money,
					);
					smsSend($telephone,'SMS_36875001',$params);
				}else{
					//银行卡尾号
					$number = substr($_rs['number'], -4);
					$params = array(
						'title' => $rs['title'],
						'start_time' => $start_time,
						'num' => (string)$num,
						'price_num' => (string)($rs['price'] * $num),
						'number' => (string)$number,
						'name' => $_rs['name'],
						'money' =>(string)$money,
					);
					smsSend($telephone,'SMS_36500034',$params);
				}
			}else{//商品
				if($_rs['name'] == '支付宝' || $_rs['name'] == '微信钱包'){
					$params = array(
						'title' => $rs['title'],
						'num' => (string)$num,
						'price_num' => (string)($rs['price'] * $num),
						'number' => (string)$_rs['number'],
						'name' => $_rs['name'],
						'money' => (string)$money,
					);
					smsSend($telephone,'SMS_36855001',$params);
				}else{
					//银行卡尾号
					$number = substr($_rs['number'], -4);
					$params = array(
						'title' => $rs['title'],
						'num' => (string)$num,
						'price_num' => (string)($rs['price'] * $num),
						'number' => (string)$number,
						'name' => $_rs['name'],
						'money' =>(string) $money,
					);
					smsSend($telephone,'SMS_36570037',$params);
				}
			}
			$this->success('结算成功!');
			exit;
		}
//		if(!empty($_POST['telephone']) &&!empty($_POST['message'])){
//			sms_send($_POST['telephone'], $_POST['message']);
//			$this->success('结算成功!');
//			exit;
//
//		}

		$data = [
			'settlement' => $rs['price'] * $num,
			'settlemented' => $m?:0,
			'bankname' => $_rs['bankname'],
			'name' => $_rs['name'],
			'number' => $_rs['number']
		];
		$this->ajaxReturn($data);
	}

	//结算
	Public function settleAutomatic(){
		$id = I('post.id');
		$type = I('post.type');

		if($type == 0){
			$times = $this->m2('TipsTimes')->field(['tips_id', 'start_time'])->where(['id' => $id])->find();
			$tips_id = $times['tips_id'];
			$rs = $this->m2('tips')->field(['member_id', 'title', 'price'])->where(['id' => $tips_id])->find();
			$num = $this->m2('OrderWares')->join('__ORDER__ a on a.id=order_id')->where(['status' => 1,'type' => 0,'tips_times_id' => $id,'act_status' => ['IN', '1,2,3,4']])->count();
		}elseif($type == 1){
			$rs = $this->m2('goods')->field(['member_id', 'title', 'price'])->where(['id' => $id])->find();
			$num = $this->m2('OrderWares')->join('__ORDER__ a on a.id=order_id')->where(['status' => 1,'type' => 1,'ware_id' => $id,'act_status' => ['IN', '1,2,3,4']])->count();
		}
		if(empty($rs))$this->error('要结算的商品不存在!');

		//支付宝账号
		$_rs = $this->m2('MemberPayway')->where(['member_id' => $rs['member_id'], 'status' => 1])->find();
		if(empty($_rs))$this->error('请先添加达人支付宝账号!');
		//已结算
		$m = $this->m2('Settlement')->where(['type' => 0, 'type_id' => $id])->sum('amount');
		if(!session('?admin'))$this->error('请重新登录刷新页面！');
		if(!empty($_POST['money'])){
			$money = (float)$_POST['money'];
			if($money <= 0 || $money > $rs['price'] * $num - $m)$this->error('请填写正确的金额!');

			$s_id = $this->m2('Settlement')->add([
				'pay_type' =>1,
				'pay_id' => $_rs['id'],
				'sn' => createCode(20),
				'type' => $type,
				'type_id' => $id,
				'originator_id' => session('admin.id'),
				'amount' => $money
			]);
			if($s_id>0){
				$this->m2('MemberApply')->add([
					'channel' => 10,
					'type' => 3,
					'type_id' => $s_id,
				]);
			}else{
				$this->error('生成不了提现数据，请找技术！');
			}
			$this->success('结算成功!');
			exit;
		}
		$data = [
			'settlement' => $rs['price'] * $num,
			'settlemented' => $m?:0,
			'bankname' => ['支付宝','微信APP','微信公众号'][$_rs['type']],
			'type' => $_rs['type'],
			'name' => $_rs['name'],
			'number' => $_rs['code']
		];
		$this->ajaxReturn($data);
	}

	public function takeMoney(){
		$this->actname = '提现管理';
		$page = I('get.page',1);
		$pageSize = 20;

		$condition = [];

		$is_pass = I('get.is_pass',null);

		if($is_pass!==null)$condition['A.is_pass'] = $is_pass;

		$this->assign('is_pass', $is_pass);

		$condition['A.type'] = 3;
		$datas['datas'] = D('TakeMoneyView')->where($condition)->page($page,$pageSize)->select();
//		print_r(D('TakeMoneyView')->getLastSql());
//		print_r($datas);
//		exit;
		foreach($datas['datas'] as $key=>$row){
			$datas['datas'][$key]['path'] = pathFormat($row['path']);
			if($row['is_pass']==0)$datas['datas'][$key]['_is_pass'] = '未操作';
			if($row['is_pass']==1)$datas['datas'][$key]['_is_pass'] = '提款成功';
			if($row['is_pass']==2)$datas['datas'][$key]['_is_pass'] = '提款失败';
			$datas['datas'][$key]['update_time'] = !empty($row['update_time'])?date('Y-m-d H:i',$row['update_time']):'';
			$datas['datas'][$key]['originator_nickname'] = $row['originator_id']? $this->m1('user')->where(['id'=>$row['originator_id']])->getField('username'):'';
			$datas['datas'][$key]['handle_nickname'] = $row['handle_id']? $this->m1('user')->where(['id'=>$row['handle_id']])->getField('username'):'';
		}

		$datas['operations'] = [
			'同意提现' => [
				'style' => 'success',
				'fun' => 'confirm_pay(%id,%settlement_id,1)',
				'condition' => '%is_pass == 0'
			],
			'拒接提现' => [
				'style' => 'danger',
				'fun' => 'confirm_pay(%id,%settlement_id,0)',
				'condition' => '%is_pass == 0'
			]
		];
		$datas['pages'] = [
			'sum' => D('TakeMoneyView')->where($condition)->count(),
			'count' => $pageSize
		];

		//img.test.yami.ren    img.yummy.com
		$datas['lang'] = [
			'id' => '申请ID',
			'nickname' => '达人昵称',
			'telephone' => '达人手机号',
			'amount' => '提现金额',
			'content' => '备注',
			'originator_nickname' => '提现管理员',
			'handle_nickname' => '处理提现管理员',
			'_is_pass' => '是否通过',
			'update_time' => '更新时间',
			'refusal_reason' => '失败原因'
		];

		$this->assign($datas);
		$this->view();
	}

	public function confirmTakeMoney_old(){
		$id = I('post.id');
		$member_wealth_id = I('post.member_wealth_id');
		$allow = I('post.allow');

		if(IS_AJAX){
			$member_wealth_log_id = $this->m2('MemberApply')->where(['id'=>$id])->getField('type_id');
			//获取提现金额数量
			$quantity = $this->m2('MemberWealthLog')->where(['id'=>$member_wealth_log_id])->getField('quantity');
			//获取提款人ID
			$member_id = $this->m2('MemberWealth')->where(['id'=>$member_wealth_id])->getField('member_id');
			if($allow == 0){
				//申请表修改
				$this->m2('MemberApply')->data(['id'=>$id,'is_pass'=>2,'refusal_reason'=>'账号不正确','update_time'=>time()])->save();
				//财富日志添加记录
				$this->m2('MemberWealthLog')->data(['member_wealth_id'=>$member_wealth_id,'type'=>'tixian','quantity'=>abs($quantity),'content'=>'提款失败'])->add();
				$this->m2('MemberWealth')->where(['id'=>$member_wealth_id])->setinc('quantity',abs($quantity));
				$this->push_Message($member_id,array(),'',null,'你申请的提现操作（金额为'.abs($quantity).'）失败，原因是账号不正确');
			}elseif($allow == 1){
				//申请表修改
				$this->m2('MemberApply')->data(['id'=>$id,'is_pass'=>1,'update_time'=>time()])->save();
				$this->push_Message($member_id,array(),'',null,'你申请的提现操作（金额为'.abs($quantity).'）已成功');
			}
			$this->success('操作成功');
		}else{
			$this->error('非法访问');
		}
	}

	/*
     * 确认提现
     * author:cherry
     * date:2017-04-24
     * */
	public function confirmTakeMoney(){
		$id = I('post.id');
		$settlement_id = I('post.settlement_id');
		$allow = I('post.allow');
		$refusal_reason = I('post.reason','账号不正确');
		$remark = I('post.remark','');
		if(!session('?admin'))$this->error('请重新登录刷新页面！');
		$row = $this->m2('Settlement')->where(['id'=>$settlement_id])->find();
		if(empty($row))$this->error('非法访问');

		if(IS_AJAX){
			if($allow == 0){
				//申请表修改
				$this->m2('MemberApply')->data(['id'=>$id,'is_pass'=>2,'refusal_reason'=>$refusal_reason,'update_time'=>time()])->save();
				$this->m2('Settlement')->data(['id'=>$settlement_id,'status'=>2])->save();
//				$this->push_Message($member_id,array(),'',null,'你申请的提现操作（金额为'.abs($quantity).'）失败，原因是账号不正确');
			}elseif($allow == 1){

				$rs = D('TakeMoneyView')->where(['A.type'=>3 , 'settlement_id'=>$settlement_id,'F.type'=>0,'F.status'=>1])->find();
				if(empty($rs)) $this->error('该达人的信息不完善');
				//支付宝账号
				$_rs = $this->m2('MemberPayway')->where(['member_id' => $rs['member_id'], 'status' => 1])->find();

				//获取会员手机号
				$telephone = $this->m2('member')->where(['id' => $rs['member_id']])->getField('telephone');

				if($rs['s_type'] == 0){
					$times = $this->m2('TipsTimes')->field(['tips_id', 'start_time'])->where(['id' => $rs['times_id']])->find();
					$tips_id = $times['tips_id'];
					$tips_rs = $this->m2('tips')->field(['member_id', 'title', 'price'])->where(['id' => $tips_id])->find();
					$num = $this->m2('OrderWares')->join('__ORDER__ a on a.id=order_id')->where(['status' => 1,'tips_times_id' => $rs['times_id'],'act_status' => ['IN', '1,2,3,4']])->count();
				}elseif($rs['s_type'] == 0){
					$tips_rs = $this->m2('goods')->field(['member_id', 'title', 'price'])->where(['id' => $rs['times_id']])->find();
					$num = $this->m2('OrderWares')->join('__ORDER__ a on a.id=order_id')->where(['status' => 1,'type' => 1,'ware_id' => $rs['times_id'],'act_status' => ['IN', '1,2,3,4']])->count();
				}
				if(empty($rs))$this->error('要结算的商品不存在!');
				//申请表修改
				$rs['remark'] = $remark;
				$r = $this->alipayto($rs);
				$result = json_decode($r);
				if($result->alipay_fund_trans_toaccount_transfer_response->code == 10000 && $result->alipay_fund_trans_toaccount_transfer_response->msg == 'Success' ){

					$this->m2('MemberApply')->data(['id'=>$id,'is_pass'=>1,'update_time'=>time()])->save();
					$this->m2('Settlement')->data(['id'=>$settlement_id,'status'=>1,'content'=>$remark,'handle_id'=>session('admin.id')])->save();
					$this->m2('SettlementPay')->data(['trade_no' => $result->alipay_fund_trans_toaccount_transfer_response->order_id,'status'=>$result->alipay_fund_trans_toaccount_transfer_response->code])->where(['settlement_id'=>$settlement_id])->save();

					if(isset($times)){//活动
						$start_time = date('Y-m-d H:i', $times['start_time']);
						if($_rs['type'] ==0){
							$params = array(
								'title' => $tips_rs['title'],
								'start_time' => $start_time,
								'num' => $num,
								'price_num' => (string)($tips_rs['price'] * $num),
								'number' => $_rs['code'],
								'name' => $_rs['name'],
								'money' => $rs['amount'],
							);
							smsSend($telephone,'SMS_36875001',$params);
						}else{
							//银行卡尾号
							$number = substr($_rs['code'], -4);
							$params = array(
								'title' => $rs['title'],
								'start_time' => $start_time,
								'num' => (string)$num,
								'price_num' => (string)($tips_rs['price'] * $num),
								'number' => (string)$number,
								'name' => $_rs['name'],
								'money' => (string)$rs['amount'],
							);
							smsSend($telephone,'SMS_36500034',$params);
						}
					}else{//商品
						if($_rs['type'] == 0){
							$params = array(
								'title' => $rs['title'],
								'num' => (string)$num,
								'price_num' => (string)($tips_rs['price'] * $num),
								'number' => $_rs['code'],
								'name' => $_rs['name'],
								'money' => (string)$rs['amount'],
							);
							smsSend($telephone,'SMS_36855001',$params);
						}else{
							//银行卡尾号
							$number = substr($_rs['code'], -4);
							$params = array(
								'title' => $rs['title'],
								'num' => (string)$num,
								'price_num' => (string)($tips_rs['price'] * $num),
								'number' => $number,
								'name' => $_rs['name'],
								'money' => (string)$rs['amount'],
							);
							smsSend($telephone,'SMS_36570037',$params);
						}
					}
					//发送邮件
					$email = $this->m1('user')->where(['id'=>$row['originator_id']])->getField('email');
					if(!empty($email) && preg_match('/^([a-zA-Z0-9]+[_|\_|\.]?)*[a-zA-Z0-9]+@([a-zA-Z0-9]+[_|\_|\.]?)*[a-zA-Z0-9]+\.[a-zA-Z]{2,3}$/',$email)){

						$ststus = $this->sendmail($email, '转账申请已通过通知', '你的转账申请:达人姓名：'.$rs['nickname'].',金额为'.$rs['amount'].'元，已同意，有问题请发送邮件给'.session('admin.email'));

						if($ststus != 1){
							$this->success('已转账成功，但邮件出现bug,如有需要，找技术');
						}
					}
				}else{
					$this->m2('SettlementPay')->data(['status'=>$result->alipay_fund_trans_toaccount_transfer_response->code])->where(['settlement_id'=>$settlement_id])->save();
					$this->error($result->alipay_fund_trans_toaccount_transfer_response->sub_msg);
				}
			}
			$this->success('操作成功');
		}else{
			$this->error('非法访问');
		}
	}


	/*
     * 支付宝调用接口
     * */
	private function alipayto($param){
		if(isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'admin.m.yami.ren') === false && isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'admin.yummy.com') === false) {
//		$parameter = [
//			"app_id" => C('ALIPAY.appid)'),
//			"method" => 'alipay.fund.trans.toaccount.transfer',
//			"biz_content" => '{"out_biz_no":"'.$param['sn'].'","payee_type":"ALIPAY_LOGONID","payee_account":"'.$param['code'].'","amount":"'.$param['amount'].'","payer_show_name":"吖咪网络","payee_real_name":"'.$param['pay_realname'].'","remark":"'.$param['remark'].'"}',
//			"sign_type"	=> 'RSA2',
//			"timestamp"	=> date('Y-m-d H:i:s'),
//			"version" => '1.0'
//		];
		}else{
			$parameter = [
				"app_id" => '2016072800107742',
				"method" => 'alipay.fund.trans.toaccount.transfer',
				"biz_content" => '{"out_biz_no":"'.$param['sn'].'","payee_type":"ALIPAY_LOGONID","payee_account":"xbifhn9630@sandbox.com","amount":"'.$param['amount'].'","payer_show_name":"吖咪网络","payee_real_name":"","remark":"'.$param['remark'].'"}',
				"sign_type"	=> 'RSA2',
				"timestamp"	=> date('Y-m-d H:i:s'),
				"version" => '1.0'
			];

		}
		ksort($parameter);
		$code = [];
		$signcode = [];
		foreach($parameter as $k => $v){
			if(!empty($v)){
				$code[] = trim($k) . '=' . urlencode($v);
				$signcode[] = trim($k) . '=' . $v;
			}
		}
		$data = implode('&', $code);
		$da = $this->m2('SettlementPay')->where(['settlement_id'=>$param['settlement_id'],'type'=>0])->find();
		if(!empty($da)){
			$this->m2('SettlementPay')->data([
				'settlement_id' => $param['settlement_id'],
				'context' => $data
			])->where(['settlement_id'=>$param['settlement_id'],'type'=>0])->save();
		}else{
			$this->m2('SettlementPay')->add([
				'settlement_id' => $param['settlement_id'],
				'type' => 0,
				'context' => $data
			]);
		}
		$private_key = file_get_contents(COMMON_PATH . 'Util/cacert/alipay_test_private.pem');
//		$private_key = file_get_contents(C('ALIPAY.private_key_path'));
		$pi_key = openssl_get_privatekey($private_key);//这个函数可用来判断私钥是否是可用的，可用返回资源id Resource id
		openssl_sign(implode('&', $signcode), $sign, $pi_key, OPENSSL_ALGO_SHA256);

		$sign = base64_encode($sign);
		$url = 'https://openapi.alipaydev.com/gateway.do?' . $data . '&sign=' . urlencode($sign);

		\Think\Log::write('手机号->'.$param['telephone'].'提现支付URL->'.$url.'-->'.date('Y-m-d H:i:s'));
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_FAILONERROR, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_HTTPHEADER, ['content-type:application/x-www-form-urlencoded;charset=utf-8']);

		$rs = curl_exec($ch);
		$httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		if (curl_errno($ch)) {
			var_dump(curl_error($ch));
		}
		curl_close($ch);
		\Think\Log::write('手机号->'.$param['telephone'].'提现支付状态->'.$httpStatusCode.'-->'.date('Y-m-d H:i:s'));
		\Think\Log::write('提现信息->'.$rs.'-->'.date('Y-m-d H:i:s'));
		return $rs ;
	}

}


