<?php
namespace Member\Controller;
use Member\Common\MainController;

// @className 我的订单
Class OrderController extends MainController {
	
	/**
	 * @apiName 获取订单列表
	 *
	 * @apiGetParam {string} token: 通信令牌
	 * @apiGetParam {int} page: 页数
	 * @apiPostParam {int} type: 0-活动订单(默认) 1-商品订单 2-众筹订单
     * @apiPostParam {int} act_status: 订单状态(0-未支付 1-已支付未发货（未参加） 2-已支付已发货（已参加） 3-已发货未确认 4-已完成 5-已申请退款 6-已退款完成 7-已取消 8-第三方退款申请中)
	 * @apiSuccessResponse
	 * [
	 *     {
	 *         "type": "0",
	 *         "ware_id": "4001",
	 *         "server_status": "0",
	 *         "check_code": "0",
	 *         "goods_attr_ids": null,
	 *         "count": "4",
	 *         "id": "30281",
	 *         "sn": "20151223125405279",
	 *         "member_id": "9982",
	 *         "price": "99.00",
	 *         "act_status": "1",
	 *         "postage": "0",
	 *         "comment_id": null,
	 *         "status": "1",
	 *         "is_piece": "1",
	 *         "start_time": "1451268000",
	 *         "end_time": "1451275200",
	 *         "title": "吖咪生活美学│走进动物的世界，把童真留给自己",
	 *         "path": "uploads/20151125/5655860d2a726.jpg",
	 *         "catname": "已售罄",
	 *         "limit_time" => "1454818702", //支付时限
     *         "is_free" => "0", // 不是免费
	 *     },
	 *     {
	 *         "type": "1",
	 *         "ware_id": "53",
	 *         "server_status": "0",
	 *         "check_code": "0",
	 *         "goods_attr_ids": null,
	 *         "count": "1",
	 *         "id": "30280",
	 *         "sn": "20151223120105227",
	 *         "member_id": "9982",
	 *         "price": "0.00",
	 *         "act_status": "1",
	 *         "is_piece": "1",
	 *         "postage": "0",
	 *         "comment_id": null,
	 *         "status": "1",
	 *         "start_time": null,
	 *         "end_time": null,
	 *         "title": "吖咪×【龙润鲜花饼】新花young玫瑰酥皮鲜花饼礼盒",
	 *         "path": "uploads/member/18565765105/热火一_5523852fe49b4.jpg",
	 *         "catname": "其他",
	 *         "limit_time" => "1454818702", //支付时限
     *         "is_free" => "1", // 免费，暂时定义为抽奖福利
	 *     }
	 * ]
	 */
	Public function index(){
        $act_status = I('post.act_status', null);
		$type = I('post.type', 0);
		$member_id = session('member.id');
		$page = I('get.page', 1);

        $where = ['member_id' => $member_id, 'status' => 1, 'type' => $type];
        if(is_numeric($act_status))$where['act_status'] = $act_status;
        if ($act_status == 8) {
            $where['act_status'] = ['IN', array(5,6,8)];
            $where['member_id'] = ['EQ', $member_id];
            $where['status'] = ['EQ', 1];
        } elseif (!isset($act_status)) {
            $where['act_status'] = ['IN', array(0,1,2,3,4,7)];
            $where['member_id'] = ['EQ', $member_id];
            $where['status'] = ['EQ', 1];
        }
		$wares=D('OrderWareView')->where($where)->group('order_id')->order('id desc')->page($page, 5)->select();

		if(empty($wares))$this->ajaxReturn([]);
		else if($wares[0]['type'] == 0){
			$ids = [];
			foreach($wares as $row){
				$ids[] = $row['wid'];
			}
		}
		$datas = [];
		foreach($wares as $key => $row){
			$pay_type = M('OrderPay')->where(['order_id' => $row['id']])->getField('type');
			$data = [
				'id' => $row['id'],
				'type' => $row['type'],
				'count' => $row['count'],
				'sn' => $row['sn'],
				'price' => $row['is_free'] ? 0 : ($row['price']?:'0'),
				'act_status' => $row['act_status'],
				'title' => !empty($row['order_pid'])?$row['title'].'【二次支付】':$row['title'],
				'catname' => $row['catname'],
				'pay_type' => !empty($pay_type) && $row['act_status']==0 ? $pay_type : '',
                'is_free' => $row['is_free'],
                'create_time' => $row['create_time']
			];
			//拼团订单
			$data['is_piece'] = 0;
//			$order_piece = M('OrderPiece')->where(['order_id'=>$row['id']])->count();
            $order_piece = D('MemberPieceLiteView')->where(['A.order_id' => $row['id']])->find();
			if(!empty($order_piece)){
				$data['is_piece'] = 1;
				$data['piece_info'] = $order_piece;
			}
			if($row['type']==0){
				$data['path'] = thumb($row['path'], 1);
				$check_code = M('OrderWares')->field(['id', 'check_code', 'server_status'])->where(['order_id' => $row['id']])->select();
				foreach($check_code as $r) {
					$data['check_code'][] = [
						'code' => $r['check_code'],
						'status' => $r['server_status']
					];
				}
				//支付时限
				if($row['limit_time'] > 0)$data['limit_time'] = $row['limit_time'] + $row['create_time'];
			}
			elseif($row['type']==1){
				$data['title'] = $row['goods_title'];
				$data['path'] = thumb($row['goods_path'], 1);
				$data['catname'] = $row['goods_catname'];
				$data['postage'] = $row['postage'];
				if (!empty($order_piece)) {

                }
				//支付时限
				if($row['goods_limit_time'] > 0)$data['limit_time'] = $row['goods_limit_time'] + $row['create_time'];
			}
			elseif($row['type']==2) {
				$rs = D('RaiseView')->where(['id' => $row['ware_id'],'times_id'=>$row['tips_times_id'] ])->find();
				$data['title'] = !empty($row['order_pid'])?$rs['title'].'【二次支付】':$rs['title'];
				$data['raise_times_title'] = $rs['raise_times_title'];
				$data['path'] = thumb($rs['path'], 1);
				$data['catname'] = $rs['catname'];
				$data['order_pid'] = empty($row['order_pid'])?'':$row['order_pid'];
				$data['is_lottery'] = $rs['type']; // 是否抽奖的订单
				$data['end_time'] = $rs['end_time'];
				//支付时限
				if ($rs['limit_time'] > 0) $data['limit_time'] =!empty($row['order_pid'])?($row['limit_pay_time']):($rs['limit_time'] + $row['create_time']);
			}
			$datas[] = $data;
		}

		$this->ajaxReturn($datas);
	}


	public function findUnpaid() {
		$member_id = session('member.id');
		$datas['member_id'] = $member_id;

		$where['B.member_id'] = $member_id;
		$where['B.status'] = '1';
		$where['B.act_status'] = '0';
	//	$where['B.id'] = 'A.order_id';
		$wares=D('FindUnpaidView')->where($where)->order('id desc')->select();
		$datas['order_id'] = $wares;

		$this->ajaxReturn($datas);
	}

	public function findTemporaryTel () {

		$member_id = session('member.id');
//		$tel = D('FindTemporaryTelView')->getField('tel');
//		$mem_tel = D('FindTemporaryTelView')->where(['B.id' => $member_id])->getField('telephone');
//		$where['B.id'] = $member_id;
//		$where['A.tel'] = ['B.telephone']; 
		$tel = D('FindTemporaryTelView')->select();
		$where['id'] = $member_id;
		$mem_tel = D('FindMemberTelView')->where($where)->getField('telephone');
		$datas['mem'] = $member_id;
		$datas['mem_tel'] = $mem_tel;
		$datas['tel'] = $tel;
		$this->ajaxReturn($datas);
	}


	public function findTemporaryBuy() {
		$raise_id = I('post.raise_id');
		$member_id = session('member.id');
		$datas['raise_id'] = $raise_id;
		$this->ajaxReturn($datas);
	}

	/**
	 * @apiName 获取订单详细资料
	 *
	 * @apiGetParam {string} token: 通信令牌
	 * @apiPostParam {int} order_id: order表的ID
	 * @apiSuccessResponse
	 * {
	 * 	"sn": "lL90xoOh6w7FCI4KNi",
	 * 	"create_time": "1453888702",
	 * 	"total": "28.00",
	 * 	"context": "",
	 * 	"act_status": "1",
	 * 	"count": 2,
	 * 	"order_pid": 256555,//该众筹订单的父ID
	 * 	"pay_price": 5265.00,//该众筹订单该付的金额
	 * 	"prepay": 55.00,//该众筹的预付金额
	 * 	"retainage": 555.00,//该众筹的尾款
	 * 	"weixincode": "sfdgffd",//用户的微信号
	 * 	"order_cid": "56566",//该众筹预约订单的子订单ID
	 * 	"step": "1",//众筹方式分为两种(一种是全额，step=0 一种是预付，这个分为两个订单  )
	 *              //进入->预付第一阶段支付-》头款订单
	 *              //step=1-头款未支付 step=2-头款已支付,尾款未生成.(此时未有尾款订单ID) step=3-尾款生成，此时已有（尾款的ID，是order_cid ） step=7-尾款已生成,但是时间已超时,订单已关闭，不能支付。step=4-尾款支付完成（整个预付方式众筹订单完成）
	 *              //【进入->预付第二阶段支付-》尾款订单	step=5- 尾款订单已生成，未支付step=6- 尾款支付完成（整个预付方式众筹订单完成）】
	 * 	"telephone": "18664861856",
	 * 	"type": 0,
	 * 	"id": "4192",
	 * 	"title": "吖咪餐厅| 抹柒与味蕾零距离对话",
	 * 	"path": "tips/20160118/569c6530bf356.jpg",
	 *  "price": "14.00",
	 * 	"times_id": "12383",
	 * 	"start_time": "1454155200",
	 * 	"end_time": "1454162400",
	 * 	"address": "",
	 * 	"tel": "",
	 * 	"nickname": "撒拉亭",
	 * 	"catname": "餐厅",
	 * 	"headpic": "/uploads/member/13922215619/5660fcd70a51f.jpg",
	 * 	"cityname": "广州",
	 * 	"check_code": [
	 * 		{
	 * 			"code": "83253511",
	 * 			"status": 0,
	 * 			"nickname": "紫嫣"
	 * 		},
	 * 		{
	 * 			"code": "64324543",
	 * 			"status": 0,
	 * 			"nickname": "紫嫣"
	 * 		}
	 * 	],
	 * 		"coupon_price": {
	 * 		"type": "1",
	 * 		"value": "1.00"
	 * 	},
	 *  "limit_time" => 1454818702, //支付时限
     *  "lottery" => { // 当lottery为""时，表示没有
     *      "id": "1",
     *      "lucky_num": "0098",
     *      "member_id": "",
     *      "time": "2017-08-01 18:28:29", // 创建时间
     *      "type": "1", // 1——付费抽奖, 0——分享抽奖
     *      "lucky_status": "0", // 中奖状态，0——未抽奖，-1——未中奖，1——中奖
     *      "url": "跳转连接" // 抽奖细则按钮跳转连接
     *  }
	 * }
	 */
	Public function getDetail(){
		$order_id = I('post.order_id');

		if(empty($order_id))$this->error('非法访问!');

		$order = M('Order')->where(['id' => $order_id])->find();
		$piece_originator_id = D('PieceOrderView')->where(['B.id' => $order_id])->getField('piece_originator_id');
		$orderWare = M('OrderWares')->where(['order_id' => $order_id])->select();
		$pay_type = M('OrderPay')->where(['order_id' => $order_id])->getField('type');
		$order_address = M('MemberAddress')->where(['id' => $order['member_address_id']])->find();
		$address = D('CityView')->where(['district_id'=>$order_address['citys_id']])->find();
		if(empty($order) || empty($orderWare))$this->error('订单不存在!');
		$data = [
			'sn' => $order['sn'],
			'create_time' => $order['create_time'],
			'total' => $order['price']?:'0',
			'context' => empty($order['context']) ? '' : $order['context'],
			'act_status' => $order['act_status'],
			'count' => count($orderWare),
			'comment_id' => $order['comment_id'],
			'pay_type' => !empty($pay_type) && $order['act_status']==0 ? $pay_type : '',
			'piece_originator_id' => !empty($piece_originator_id) ? $piece_originator_id : '',
			'invite_nickname' => '',
			];

		//获取邀请人的昵称
		if(!empty($order['invite_member_id'])){
			$data['invite_nickname'] = M('Member')->where(['id'=>$order['invite_member_id']])->getField('nickname');
		}
		if($orderWare[0]['type'] == 0){
			$data['type'] = 0;
			$tips = D('TipsView')->where(['id' => $orderWare[0]['ware_id'], 'times_id' => $orderWare[0]['tips_times_id']])->find();
			$tips['path'] = thumb($tips['path'], 1);
			$tips['headpic'] = thumb($tips['headpic'], 2);
			$data = array_merge($data, $tips);
			//查询消费码及其状态
			foreach($orderWare as $r){
				if(!empty($r['inviter_id'])){
					$nickname = M('Member')->where(['id' => $r['inviter_id']])->getField('nickname');
				}
				$data['check_code'][] = [
					'code' => $r['check_code'],
					'status' => $r['server_status'] > 0 ? 1 : 0,
					'nickname' => $nickname,
				];
			}
			if($tips['limit_time'] > 0)$data['limit_time'] = $order['create_time'] + $tips['limit_time'];
		}
		elseif($orderWare[0]['type'] == 1){
			$data['type'] = 1;
			$data['postage'] = $order['postage']?:'0';
			//查出商品信息
			$goods = D('GoodsView')->where(['id' => $orderWare[0]['ware_id']])->find();
			$goods['path'] = thumb($goods['path'], 1);
			//查出收货地址
			$address = D('MemberAddressView')->where(['id' => $order['member_address_id']])->find();
			if(empty($address))$address = [];
			$data = array_merge($data, $goods, $address);
			$data['goods_id'] = $goods['id'];
			if($goods['limit_time'] > 0)$data['limit_time'] = $order['create_time'] + $goods['limit_time'];
		}
		elseif($orderWare[0]['type'] == 2){
			// TODO 获取订单相关的抽奖码

		    $data['type'] = 2;
		    $data['ware_type'] = 2;
		    $order_wares_count = count($orderWare);
			$raise = D('RaiseView')->where(['id' => $orderWare[0]['ware_id'], 'times_id' => $orderWare[0]['tips_times_id']])->find();
			$raise['weixincode']=M('MemberInfo')->where(['member_id'=>$order['member_id']])->getField('weixincode');
			$raise['path'] = thumb($raise['path'], 1);
			$raise['content'] = utf8_substr(trim(preg_replace(['/\[img.+?\]/', '/\&\w+?;/', '/[\r\n\t]/'], '', strip_tags($raise['content']))), 0, 200);
			$raise['title'] = !empty($order['order_pid'])?$raise['title'].'【二次支付】':$raise['title'];
			$raise['pay_price'] = !empty($order['order_pid'])
                                    ?$raise['price']-$raise['prepay']
                                    :($raise['prepay']>0)
                                        ?$raise['prepay']
                                        :$raise['price'];

			$raise['pay_price'] = $raise['pay_price'] * $order_wares_count; //
            $raise['order_wares_count'] = $order_wares_count;

			if($raise['prepay']>0){
//				$raise['retainage'] = preg_replace('/^(\d+?)(\d{2})$/', '$1.$2', (int)(($raise['price'] - $raise['prepay']) * 100));
//				$raise['retainage'] = printf('%.2f', (float)(floor($tmp*100)/100));
//				$raise['retainage'] = $raise['price'] - $raise['prepay'];
				$raise['retainage'] = Number_format(($raise['price'] - $raise['prepay']) * $order_wares_count, 2, '.','');
				if(!empty($order['order_pid'])){//尾款订单0.01
					if($order['act_status']==0){//尾款未支付
						$raise['step'] = 5;
					}else{//尾款已支付
						$raise['step'] = 6;
					}
				}else{//头款订单
					if(in_array($order['act_status'], [1,2,3,4])){//头款已支付
						$order_cid=M('Order')->field('id,act_status,order_pid,status')->where(['order_pid'=>$order_id])->find();
						if(!empty($order_cid)){//生成尾款订单
							if($order_cid['act_status']==0 && $order_cid['status']==1){//尾款未支付
								$raise['step'] = 3;
								$raise['order_cid'] = $order_cid['id'];
//							}elseif($order_cid['status']==2){//不能支付二次订单（订单已关闭）
//								$raise['step'] = 7;
//								$raise['order_cid'] = $order_cid['id'];
							}else{
								$raise['step'] = 4;
							}
						}else{
							$raise['step'] = 2;//尾款未生成，预款已支付
						}
					}else{//头款未支付
						$raise['step'] = 1;
					}
				}
				$raise['prepay'] = $raise['prepay'] * $order_wares_count;
			}else{
				$raise['retainage'] = '';
				$raise['step'] = 0;
			}

			$raise['order_pid'] = $order['order_pid'];
			$raise['address'] = $address['province_name'].$address['province_alt'].$address['city_name'].$address['city_alt'].$address['district_name'].$address['district_alt'].$order_address['address'];
			//获取类目
//			$times = M('RaiseTimes')->where(['raise_id' => $orderWare[0]['ware_id']])->select();
//			$_rs = M('OrderWares')->join('__ORDER__ a on order_id=a.id')->where(['type' => 2, 'ware_id' => $orderWare[0]['ware_id'], 'status' => 1, 'act_status' => ['in', '1,2,3,4']])->select();
//			$raise['totaled'] = $raise['count'] = 0;
//			$orders = [];
//			foreach($_rs as $row){
//				if(!isset($orders[$row['tips_times_id']]['total'])){
//					$orders[$row['tips_times_id']]['total'] = 0;
//					$orders[$row['tips_times_id']]['count'] = 0;
//				}
//				$orders[$row['tips_times_id']]['total'] += $row['price'];
//				$orders[$row['tips_times_id']]['count'] ++;
//			}
//			foreach($times as $t) {
//				$raise['totaled'] += $orders[$t['id']]['total'] ?: 0;
//				$count = $orders[$t['id']]['count'] ?: 0;
//				$raise['count'] += $count;
//			}

			$rs = D('RaiseOrderWaresView')->where(['A.type' => 2, 'A.ware_id' => $orderWare[0]['ware_id'], 'B.status' => 1, 'B.act_status' => ['in', '1,2,3,4'],'order_pid'=>['EXP', 'IS NULL']])->group('A.id')->select();
			$raise['totaled'] = $raise['count'] = 0;
			foreach($rs as $row){
				$raise['totaled'] += $row['raise_times_price'];
				$raise['count'] ++;
			}

			if ($raise['id'] == 51) {
			    $raise['totaled'] += 38640;
			    $raise['count'] += 280;
            } elseif ($raise['id'] == 94) {
                $raise['totaled'] += 998;
			    $raise['count'] += 2;
            }


            $gids = M('raise_goods')->where(['rid' => $raise['id']])->getField('gid', true);
            if (!empty($gids)) {
                $raiseGoodsSelled = $this->getJoinGoodsOrder($gids);

                if (!empty($raiseGoodsSelled)) {
                    $raise['totaled'] += $raiseGoodsSelled['total_selled'];
                    $raise['count'] += $raiseGoodsSelled['total_count'];
                }
            }


			$data = array_merge($data, $raise);
			if($raise['limit_time'] > 0)$data['limit_time'] = !empty($order['order_pid'])?($order['limit_pay_time']):($order['create_time'] + $raise['limit_time']);

			$luckyItem = M('RaiseLucky')->where(['raise_times_id' => $raise['times_id'], 'member_id' => session('member.id'), 'order_id' => $order_id])->find();
            !empty($luckyItem) && ($luckyItem['lucky_num'] = str_pad($luckyItem['lucky_num'], 6, '0', STR_PAD_LEFT));

            if (!empty($luckyItem) && $luckyItem['lucky_status'] == 0) {
                // 未开始
                $luckyItem['url'] = 'http://'. DOMAIN .'/?page=choice-lotteryRule';
            } else if (!empty($luckyItem)) {
                // 已开奖
                $luckyItem['url'] = 'http://'. DOMAIN .'/?page=choice-lotteryResult&raise_id=' . $raise['id'] . '&times_id=' . $raise['times_id'];
            }

            $data['lottery'] = $luckyItem;

			$this->put($data);
		}

		//查询优惠金额
		if(!empty($order['member_coupon_id'])){
			$data['coupon'] = D('MemberCouponView')->field(['type','value'])->where(['id' => $order['member_coupon_id']])->find();
		}

		$comment = M('MemberComment')->where(['id'=>$order['comment_id']])->find();
		if(!empty($comment)){
			$comment['pics'] = M('Pics')->where(['group_id'=>$comment['pics_group_id']])->getField('path', true);
			foreach($comment['pics'] as $key=>$val){
				$comment['pics'][$key]=thumb($val,5);
			}
			$data['comment'] = $comment;
		}else{
			$data['comment'] = [];
		}

		$this->ajaxReturn($data);
    }
    
    private function getJoinGoodsOrder($gids) {
        $selled = D('Goods/OrderView')->where(['type' => 1, 'ware_id' => ['in', join(',', $gids)], 'act_status'=> ['IN', '1,2,3,4'], 'status' => 1])->select();
        $data['total_selled'] = 0;
        $data['total_count'] = count($selled);
        foreach ($selled as $item) {
            $data['total_selled'] += $item['price'];
        }
        return $data;
    }

	/**
	 * @apiName 取消订单操作
	 *
	 * @apiGetParam {string} token: 通信令牌
	 * @apiPostParam {int} order_id: 要操作的订单ID
	 * @apiSuccessResponse
	 * {
	 *    "info" : "操作成功!",
	 *    "status" : 1,
	 *    "url" : ""
	 * }
	 * @apiErrorResponse
	 * {
	 *    "info" : "错误原因",
	 *    "status" : 0,
	 *    "url" : ""
	 * }
	 */
	Public function cancel(){
		$order_id = I('post.order_id');
		if(empty($order_id))$this->error('非法访问!');
		$order = M('order')->where(['member_id' => session('member.id'), 'status' => 1, 'act_status' => 0, 'id' => $order_id])->save(['act_status' => 7]);
		if($order > 0){
			$this->cancelOrder($order_id);
			$this->success('操作成功!');
		}else{
			$this->error('请先确保要退款的订单处于未支付状态!');
		}
	}

	/**
	 * @apiName 退款申请操作
	 *
	 * @apiGetParam {string} token: 通信令牌
	 * @apiPostParam {int} order_id: 要操作的订单ID
	 * @apiPostParam {string} context: 退款原因
	 * @apiPostParam {int} pic_id: 退款证据图片id(可忽略)
	 *
	 * @apiSuccessResponse
	 * {
	 *    "info" : "操作成功!",
	 *    "status" : 1,
	 *    "url" : ""
	 * }
	 * @apiErrorResponse
	 * {
	 *    "info" : "错误原因",
	 *    "status" : 0,
	 *    "url" : ""
	 * }
	 */
	Public function refund(){
		$order_id = I('post.order_id');
		$context = I('post.context');
		$pic_id = I('post.pic_id');
		if(empty($order_id))$this->error('非法访问!');
		if(empty($context))$this->error('请提供退款原因!');
		$order = M('order')->where(['member_id' => session('member.id'), 'status' => 1, 'act_status' => 1, 'id' => $order_id])->find();
		if(empty($order))$this->error('您申请退款的订单不存在或不属于未参加状态!');
		//查询退款申请表中是否已有未处理记录
		$count = M('OrderRefund')->where(['order_id' => $order_id, 'is_allow' => 0])->count();
		if($count > 0)$this->error('退款申请已经提交,请耐心等待!');
		$data = ['order_id' => $order_id, 'money' => $order['price'], 'cause' => $context];
		if(!empty($pic_id))$data['pic_id'] = $pic_id;
		//退款申请入库
		M('OrderRefund')->add($data);
		//将订单设置为退款状态
		M('order')->where(['id' => $order_id])->save(['act_status' => 5]);
		$this->success('操作成功!');
	}

	/**
	 * @apiName 取消退款操作
	 *
	 * @apiGetParam {string} token: 通信令牌
	 * @apiPostParam {int} order_id: 要操作的订单ID
	 *
	 * @apiSuccessResponse
	 * {
	 *    "info" : "操作成功!",
	 *    "status" : 1,
	 *    "url" : ""
	 * }
	 * @apiErrorResponse
	 * {
	 *    "info" : "错误原因",
	 *    "status" : 0,
	 *    "url" : ""
	 * }
	 */
	Public function cancelRefund(){
		$order_id = I('post.order_id');
		if(empty($order_id))$this->error('非法访问!');

		$order = M('order')->where(['member_id' => session('member.id'), 'status' => 1, 'act_status' => 5, 'id' => $order_id])->find();
		if(empty($order))$this->error('您要取消退款的订单不存在或不属于退款中状态!');

		//删除退款申请
		M('OrderRefund')->where(['order_id' => $order_id, 'is_allow' => 0])->delete();
		//将订单设置为未参加状态
		M('order')->where(['id' => $order_id])->save(['act_status' => 1]);

		$this->success('操作成功!');
	}

	/**
	 * @apiName 核验会员消费码
	 *
	 * @apiGetParam {string} token: 通信令牌
	 * @apiPostParam {int} code: 要核验的会员消费码(多个消费码用逗号隔开)
	 *
	 * @apiSuccessResponse
	 * {
	 *    "info" : "核验成功!",
	 *    "status" : 1,
	 *    "url" : ""
	 * }
	 * @apiErrorResponse
	 * {
	 *    "info" : "错误原因",
	 *    "status" : 0,
	 *    "url" : ""
	 * }
	 */
	Public function checkCode(){
		$code = I('post.code');
		if(!preg_match('/^\d{8}(,{0,1}\d{8})*$/', $code)){
			$this->error('消费码格式不正确!');
		}
		$rs = D('GetOrderWareView')->where(['member_id' => session('member.id'), 'check_code' => ['IN', $code]])->find();
		if(empty($rs)){
			$this->error('消费码不存在!');
		}
		if($rs['server_status'] == 1)$this->error('该消费码已经核验过了!');
		$ids = [];
		foreach($rs as $row){
			$ids[] = $row['id'];
		}
		session_write_close();
		//取消超时时间
		set_time_limit(0);
		//开始进入循环长连接
		$time = 0;
		while(!\Common\Util\Cache::getInstance()->get('checkCode_' . $code)){
			usleep(500000);
			$time += 0.5;
			if($time > 30){
				$this->ajaxReturn(['status' => 2]);
			}
		}
		\Common\Util\Cache::getInstance()->rm('checkCode_' . $code);
		$this->success('核验成功!');
	}

	/**
	 * @apiName 获取订单的物流跟踪信息
	 *
	 * @apiGetParam {string} token: 通信令牌
	 * @apiPostParam {int} order_id: 订单ID
	 *
	 * @apiSuccessResponse
	 * {
	 * 	"name": "中通速递",
	 * 	"path": "http://xxxxxxx.jpg",
	 * 	"state": "已签收",
	 * 	"number": "405787357890",
	 * 	"traces": [
	 * 		{
	 * 			"AcceptTime": "2016-07-01 15:02:56",
	 * 			"AcceptStation": "昆山 的 瑞菇中通 已收件"
	 * 		},
	 * 		{
	 * 			"AcceptTime": "2016-07-01 23:11:39",
	 * 			"AcceptStation": "快件离开 昆山 已发往 广州中转部"
	 * 		},
	 * 		{
	 * 			"AcceptTime": "2016-07-03 04:26:50",
	 * 			"AcceptStation": "快件已到达 广州中心 上一站是 昆山"
	 * 		},
	 * 		{
	 * 			"AcceptTime": "2016-07-03 04:45:37",
	 * 			"AcceptStation": "快件离开 广州中心 已发往 广州东圃"
	 * 		},
	 * 		{
	 * 			"AcceptTime": "2016-07-03 05:01:22",
	 * 			"AcceptStation": "快件已到达 广州东圃 上一站是 广州中心"
	 * 		},
	 * 		{
	 * 			"AcceptTime": "2016-07-03 10:24:35",
	 * 			"AcceptStation": "广州东圃 的 丁建朝康乐新村 正在派件"
	 * 		},
	 * 		{
	 * 			"AcceptTime": "2016-07-03 13:53:19",
	 * 			"AcceptStation": "广州东圃的派件已签收，感谢您使用中通快递！"
	 * 		}
	 * 	]
	 * }
	 */
	Public function getLogistics(){
		$order_id = I('post.order_id');
		$rs = D('OrderLogisticsView')->where(['id' => $order_id, 'member_id' => session('member.id')])->find();

		if(empty($rs))$this->error('该订单不存在或不属于已发货状态!');

		$requestData= "{'OrderCode':'{$rs['sn']}','ShipperCode':'{$rs['logkey']}','LogisticCode':'{$rs['number']}'}";

		$datas = [
			'EBusinessID' => '1260852',
			'RequestType' => '1002',
			'RequestData' => urlencode($requestData),
			'DataType' => '2',
		];
		$datas['DataSign'] = urlencode(base64_encode(md5($requestData.'b6d982a6-a52a-4a54-ad0b-3b3a73c5e84b')));

		$data = curl_post('http://api.kdniao.cc/Ebusiness/EbusinessOrderHandle.aspx', $datas);
		$data = json_decode($data, true);

		$data = [
			'path' => thumb($rs['path'], 1),
			'name' => $rs['name'],
			'number' => $rs['number'],
			'state' => ['未知','未知','在途中','已签收','问题件'][(int)$data['State']?:0],
			'traces' => array_reverse($data['Traces'])
		];

		$this->ajaxReturn($data);
	}

	/**
	 * @apiName 确认收货
	 *
	 * @apiGetParam {string} token: 通信令牌
	 * @apiPostParam {int} order_id: 订单ID
	 *
	 * @apiSuccessResponse
	 * {
	 *    "info" : "确认成功!",
	 *    "status" : 1,
	 *    "url" : ""
	 * }
	 * @apiErrorResponse
	 * {
	 *    "info" : "错误原因",
	 *    "status" : 0,
	 *    "url" : ""
	 * }
	 */
	Public function ConfirmReceipt(){
		$order_id = I('post.order_id');
		$rs = M('order')->where(['id' => $order_id, 'member_id' => session('member.id'), 'act_status' => 2])->find();
		if(empty($rs))$this->error('该订单不存在或不属于已发货状态!');
		M('order')->where(['id' => $order_id])->save(['act_status' => 3]);
		$this->success('确认成功!');
	}

}
