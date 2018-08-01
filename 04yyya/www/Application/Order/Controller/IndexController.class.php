<?php

namespace Order\Controller;
use Order\Common\MainController;
use Think\Think;

// @className 常规工具
class IndexController extends MainController {

    /**
     * @apiName 获取购买活动的相关信息
     *
     * @apiGetParam {string} token: 通信令牌
     * @apiPostParam {int} tips_id: 要购买的活动ID
     * @apiPostParam {int} piece_id: 要购买的活动分期拼团的ID
     *
     * @apiSuccessResponse{
     *	"tips_id": "7756",
     *	"nickname": "紫嫣",
     *	"mainpic": "http://img.m.yami.ren/20161229/e64be1f25439ec97e7295051e05dd9c2b5cff5ba.jpg",
     *	"title": "测试短信达人和用户",
     *	"price": "0.01",
     *	"times": [
     *		{
     *			"id": "16413",
     *			"tips_id": "7756",
     *			"phase": "1",
     *			"start_time": "1481693400",
     *			"end_time": "1483070040",
     *			"min_num": "1",
     *			"max_num": "3",
     *			"stock": "3",
     *			"start_buy_time": "0",
     *			"stop_buy_time": "1481018040",
     *			"release_time": "1489455970",
     *			"under_time": null,
     *			"limit_num": "0",
     *			"is_finish": "2",
     *			"datetime": "2017-03-14 09:46:31",
     *			"restrict_num": "3",
     *			"count": "0",
     *			"piece": [
     *				{
     *					"id": "4",
     *					"tips_times_id": "16413",
     *					"phase": "1",
     *					"price": "100.00",
     *					"count": "3",
     *					"limit_time": "2",
     *					"status": "1",
     *					"datetime": "2017-03-29 16:10:54"
     *				},
     *				{
     *					"id": "6",
     *					"tips_times_id": "16413",
     *					"phase": "2",
     *					"price": "0.01",
     *					"count": "30",
     *					"limit_time": "2",
     *					"status": "1",
     *					"datetime": "2017-03-29 16:27:02"
     *				}
     *			]
     *		},
     *		{
     *			"id": "16451",
     *			"tips_id": "7756",
     *			"phase": "2",
     *			"start_time": "1484879880",
     *			"end_time": "1484966280",
     *			"min_num": "5",
     *			"max_num": "8",
     *			"stock": "8",
     *			"start_buy_time": "0",
     *			"stop_buy_time": "1484793480",
     *			"release_time": "1489455970",
     *			"under_time": null,
     *			"limit_num": "2",
     *			"is_finish": "2",
     *			"datetime": "2017-03-14 09:46:32",
     *			"restrict_num": "8",
     *			"count": "0",
     *			"piece": []
     *		}
     *	],
     *	"buy_status": "0",
     *	"book_discount": "0.00",
     *	"telephone": "15989117674",
     *	"stop_buy_time": 0,
     *	"buy_price": "0.01"
     *	}
     */
    public function getTips(){
        $tips_id = I('post.tips_id');
        $piece_originator_id = I('post.piece_originator_id','');
        $piece_time = [];
        if(!empty($piece_originator_id)){
            //参团已选择分期
            $piece_time = D('PieceView')->field('piece_id,type_id,type_times_id')->where(['piece_originator_id'=>$piece_originator_id,'A.status'=>1])->group('A.id')->find();
            $tips_id = $piece_time['type_id'];
        }
        //判断活动是否处于可购买状态
        $rs = D('TipsView')->where(['id' => $tips_id, 'status' => 1, 'is_pass' => 1])->find();

        if(empty($rs) || empty($rs['id']))$this->error('您要购买的活动不存在或已经下架！！');
        $allow_buy = M('OrderWares')->join('__ORDER__ a on a.id=order_id')->where(['invite_member_id' => C('DefaultInviteMember'), 'type' => 0, 'ware_id' => $tips_id, 'status' => 1, 'act_status' => ['IN', '0,1,2,3,4,5']])->find();


        //查询出该活动的时间段
        $times = M('TipsTimes')->where(['tips_id' => $tips_id/*,'end_time'=>['GT',time()]*/])->order('phase')->select();
        $can_buy = 0;
        foreach($times as $k => $t){
            if($t['start_buy_time'] < time() && ($t['stock'] > 0 || $t['stock'] == -1 ))$can_buy = 1;
            $times[$k]['restrict_num'] = $t['max_num'];
            $times[$k]['count'] = M('OrderWares')->join('__ORDER__ a on a.id=order_id')->where(['type' => 0, 'ware_id' => $tips_id, 'tips_times_id'=>$t['id'],'status' => 1, 'act_status' => ['IN', '0,1,2,3,4,5']])->count();
            $limit_buy = $times[$k]['limit_buy']= M('OrderWares')->join('__ORDER__ a on a.id=order_id')->where(['type' => 0,'member_id' => session('member.id'), 'ware_id' => $tips_id, 'tips_times_id'=>$t['id'],'status' => 1, 'act_status' => ['IN', '0,1,2,3,4,5']])->count();
            //是否存在拼团
            $times[$k]['piece'] = [];
            $piece = M('Piece')->where(['type_id' => $t['tips_id'],'type_times_id'=>$t['id'],'type'=>0,'status'=>1])->order('price asc')->select();
            if(!empty($piece)){
                foreach($piece as $key=>$val){
                    $times[$k]['piece'][$key]['id'] = $val['id'];
                    $times[$k]['piece'][$key]['price'] = $val['price'];
                    $times[$k]['piece'][$key]['phase'] = $val['phase'];
                    $times[$k]['piece'][$key]['count'] = $val['count'];
                    $times[$k]['piece'][$key]['limit_time'] = $val['limit_time'];
                    $times[$k]['piece'][$key]['save_price'] = ($rs['price']-$val['price']>0)?$rs['price']-$val['price']:0;
//					$times[$k]['piece'][$key]['is_cap'] = $val['is_cap'];
                    $times[$k]['piece'][$key]['limit_num'] = $val['limit_num'];
                    if( $val['count'] <= $t['stock']){
                        if($t['limit_num']>0){
                            if($val['limit_num']>0){
                                if(empty($piece_originator_id)){
                                    $limit_num = min($t['stock'],$val['count'],$val['limit_num'],$t['limit_num'],($t['limit_num']-$limit_buy));
                                }else{
                                    $PieceOrderView = new \Member\Model\PieceOrderViewModel();
                                    $buied_num = $PieceOrderView->where(['piece_originator_id'=>$piece_originator_id,'act_status'=>['IN',[1,2,3,4]]])->group('A.id')->count();
                                    if($val['is_cap'] == 1){
                                        $limit_num = min($t['stock'],$val['count'],$val['limit_num'],$t['limit_num'],($val['count']-$buied_num),($t['limit_num']-$limit_buy));
                                    }else{
                                        $limit_num = min($t['stock'],$val['count'],$val['limit_num'],$t['limit_num'],($t['limit_num']-$limit_buy));
                                    }

                                }
                            }else{
                                if(empty($piece_originator_id)){
                                    $limit_num = min($t['stock'],$val['count']);
                                }else{
                                    $PieceOrderView = new \Member\Model\PieceOrderViewModel();
                                    $buied_num = $PieceOrderView->where(['piece_originator_id'=>$piece_originator_id,'act_status'=>['IN',[1,2,3,4]]])->group('A.id')->count();
                                    if($val['is_cap'] == 1){
                                        $limit_num = min($t['stock'],$val['count'],($val['count']-$buied_num));
                                    }else{
                                        $limit_num = min($t['stock'],$val['count']);
                                    }

                                }
                            }

                        }else{
                            if($val['limit_num']>0){
                                if(empty($piece_originator_id)){
                                    $limit_num = min($t['stock'],$val['count'],$val['limit_num'],($val['limit_num']-$limit_buy));
                                }else{
                                    $PieceOrderView = new \Member\Model\PieceOrderViewModel();
                                    $buied_num = $PieceOrderView->where(['piece_originator_id'=>$piece_originator_id,'act_status'=>['IN',[1,2,3,4]]])->group('A.id')->count();
                                    if($val['is_cap'] == 1){
                                        $limit_num = min($t['stock'],$val['count'],$val['limit_num'],($val['count']-$buied_num),($val['limit_num']-$limit_buy));
                                    }else{
                                        $limit_num = min($t['stock'],$val['count'],$val['limit_num'],($val['limit_num']-$limit_buy));
                                    }

                                }
                            }else{
                                if(empty($piece_originator_id)){
                                    $limit_num = min($t['stock'],$val['count']);
                                }else{
                                    $PieceOrderView = new \Member\Model\PieceOrderViewModel();
                                    $buied_num = $PieceOrderView->where(['piece_originator_id'=>$piece_originator_id,'act_status'=>['IN',[1,2,3,4]]])->group('A.id')->count();
                                    if($val['is_cap'] == 1){
                                        $limit_num = min($t['stock'],$val['count'],($val['count']-$buied_num));
                                    }else{
                                        $limit_num = min($t['stock'],$val['count']);
                                    }

                                }
                            }

                        }
                        $times[$k]['piece'][$key]['limit_num'] = $limit_num;
                        $times[$k]['piece'][$key]['can_buy'] = 1;
                    }else{
                        $times[$k]['piece'][$key]['can_buy'] = 0;
                    }
                }
            }
        }
        if(!$can_buy && !(session('?invite') && session('invite.member_id') == C('DefaultInviteMember') && $allow_buy)){
            $this->error('活动尚未开始!');
        }

        $time = time();

        //查询是否有折扣价格
//		$mp = M('marketing')->where([
//			'type' => 0,
//			'type_id' => $tips_id,
//			'start_time' => ['LT', $time],
//			'end_time' => ['GT', $time],
//		])->order('price asc')->find();
//		$price = isset($mp['price']) ? $mp['price'] : $rs['price'];

        $price = $rs['price'];
        //会员标签ID
        $member_tags = M('MemberTag')->where(['member_id' => session('member.id')])->getField('tag_id', true);
        if(empty($member_tags))$member_tags = [];
        //活动标签
        $tips_tags = M('TipsTag')->where(['tips_id' => $tips_id])->getField('tag_id', true);
        if(empty($tips_tags))$tips_tags = [];

        //临时处理: 被分享用户奖励优惠券
        if($tips_id == 9116 && session('?invite')){
            //判断是否已经领取该优惠券
            $coupon_rs = M('MemberCoupon')->where(['member_id' => session('member.id'), 'coupon_id' => 1550])->find();
            $coupon_rs1 = M('MemberCoupon')->where(['invitecode' => session('invite.code'), 'coupon_id' => 1550])->find();
            if(empty($coupon_rs) && empty($coupon_rs1)){
                //查询出所有已购买用户且未分享的用户邀请码
                $sql = "Select a.member_id as 'member_id',c.invitecode as 'invitecode' from ym_order a
					join ym_order_wares b on a.id=b.order_id
					join ym_member c on a.member_id=c.id
					where a.member_coupon_id=0 and b.type=0 and b.ware_id={$tips_id} and a.act_status in (1,2,3,4) and a.status=1
					and a.member_id not in (select `invite_member_id` from ym_order A join ym_order_wares B on A.id=B.order_id where B.type=0 and B.ware_id={$tips_id} and A.status=1 and invite_member_id is not null)";
                $coupon_rs = M()->query($sql);
                foreach($coupon_rs as $row){
                    if(session('invite.member_id') == $row['member_id'] && session('invite.code') == $row['invitecode']){
                        //记录优惠券
                        M('MemberCoupon')->where(['member_id' => ['exp', 'is null'], 'coupon_id' => 1550])->limit(1)->save([
                            'member_id' => session('member.id'),
                            'channel' => $this->channel,
                            'invitecode' => session('invite.code')
                        ]);
                    }
                }
            }
        }
        //临时处理结束

        //查询可使用的优惠券
        $where = [
            'member_id' => session('member.id'),
            'used_time' => 0,
            'start_time' => ['LT', $time],
            'end_time' => ['GT', $time],
            'min_amount' => ['ELT', $price]
        ];
        $coupons = D('MemberCouponView')->where($where)->order('value desc')->select();
        $coupon = [];
        ob_start();
        foreach($coupons as $r){
            //验证优惠券标签
            $allow = 0;
            if($r['member_tags'] == '*' || !empty(array_intersect($member_tags, explode(',', $r['member_tags']))))$allow ++;
            if($r['tips_tags'] == '*' || !empty(array_intersect($tips_tags, explode(',', $r['tips_tags']))))$allow ++;
            if($allow == 2){
                $coupon = $r;
                break;
            }
        }

        $data = [
            'tips_id' => $tips_id,
            'nickname' => $rs['member_nickname'],
            'mainpic' => thumb($rs['path'], 1),
            'title' => $rs['title'],
            'price' => $price,
            'times' => $times,
            'piece_time' => $piece_time,//开团的ID
            'buy_status' => $rs['buy_status'],
            'book_discount' => $rs['discount'],
            'telephone' => session('member.telephone'),
            'stop_buy_time' => 0
        ];

        $data['buy_price'] = $price;
//		if(!empty($mp)){
//			if($mp['allow_coupon'] == 1){
//				$data['allow_coupon'] = 1;
//				if(!empty($coupon)){
//					$data['coupon'] = array(
//						'id' => $coupon['id'],
//						'type' => $coupon['type'],
//						'value' => $coupon['value'],
//						'content' => $coupon['content']
//					);
//				}
//			}
//		}else{
        if(!empty($coupon)){
            $data['coupon'] = [
                'id' => $coupon['id'],
                'type' => $coupon['type'],
                'value' => $coupon['value'],
                'content' => $coupon['content']
            ];
        }
//		}

        $this->ajaxReturn($data);
    }

    /**
     * @apiName 获取购买商品的相关信息
     *
     * @apiGetParam {string} token: 通信令牌
     * @apiPostParam {int} goods_id: 要购买的商品ID
     *
     * @apiSuccessResponse
     * {
     * 		"mainpic": "http://yummy194.cn/uploads/20151117/564af95fb70ce.jpg",
     * 		"title": "吖咪×好色派 你见过如此好色的沙拉吗？【午餐】法式尼斯吞拿鱼沙拉 佐 魔芥酱(吖咪价39元",
     * 		"price": "45.00",
     * 		"address": [
     * 			{
     * 				"id": "503",
     * 				"member_id": "9982",
     * 				"address": "广州市建设六马路47号201",
     * 				"zipcode": "0",
     * 				"linkman": "陈小燕",
     * 				"telephone": "18029266389",
     * 				"is_default": "1",
     * 				"datetime": "2016-01-23 15:48:01",
     * 				"area_id": "2095",
     * 				"area_name": "天河",
     * 				"city_id": "224",
     * 				"city_name": "广州",
     * 				"province_id": "19",
     * 				"province_name": "广东"
     * 			},
     * 			{
     * 				"id": "504",
     * 				"member_id": "9982",
     * 				"address": "广州市天河区广和路10号富景花园101室",
     * 				"zipcode": "0",
     * 				"linkman": "杨智慧",
     * 				"telephone": "18988833926",
     * 				"is_default": "1",
     * 				"datetime": "2016-01-23 15:48:06",
     * 				"area_id": "2095",
     * 				"area_name": "天河",
     * 				"city_id": "224",
     * 				"city_name": "广州",
     * 				"province_id": "19",
     * 				"province_name": "广东"
     * 			}
     * 		],
     * 		"tel": "020-11223344",
     * 		"stocks": "0",
     * 		"shipping": "0.00",
     * 		"buy_price": "45.00"
     * 	}
     */
    public function getGoods(){
        $goods_id = I('post.goods_id');
        $ispiece = I('post.ispiece', 0);
        //判断活动是否处于可购买状态
        $goods = new \Goods\Model\GoodsViewModel();
        $rs = $goods->where(['id' => $goods_id, 'status' => 1, 'is_pass' => 1])->find();

        if(empty($rs) || empty($rs['id']))$this->error('您要购买的商品不存在或已经下架！');

        //查询该会员的默认收货地址
        $address = D('MemberAddressView')->where(['member_id' => session('member.id'), 'is_default' => 1])->find();

        $time = time();
        //查询是否有折扣价格
//		$mp = M('marketing')->where([
//			'type' => 1,
//			'type_id' => $goods_id,
//			'start_time' => ['LT', $time],
//			'end_time' => ['GT', $time]
//		])->order('price asc')->find();
//		$price = isset($mp['price']) ? $mp['price'] : $rs['price'];
        $price = $rs['price'];
        //查询是否有拼桌价格
        if($ispiece == 1){
//			$pp = M('GoodsPiece')->where(['goods_id' => $goods_id])->find();
            $pp = M('Piece')->where(['type_id' => $goods_id, 'type' => 1])->find();
        }

        //会员标签ID
        $member_tags = M('MemberTag')->where(['member_id' => session('member.id')])->getField('tag_id', true);
        if(empty($member_tags))$member_tags = [];
        //商品标签
        $goods_tags = M('goodsTag')->where(['goods_id' => $goods_id])->getField('tag_id', true);
        if(empty($goods_tags))$goods_tags = [];

        //查询可使用的优惠券
        $where = [
            'member_id' => session('member.id'),
            'used_time' => 0,
            'start_time' => ['LT', $time],
            'end_time' => ['GT', $time],
            'min_amount' => ['ELT', $price]
        ];
        $coupons = D('MemberCouponView')->where($where)->order('value desc')->select();
        $coupon = [];
        foreach($coupons as $r){
            //验证优惠券标签
            $allow = 0;
            if($r['member_tags'] == '*' || !empty(array_intersect($member_tags, explode(',', $r['member_tags']))))$allow ++;
            if($r['goods_tags'] == '*' || !empty(array_intersect($goods_tags, explode(',', $r['goods_tags']))))$allow ++;
            if($allow == 2){
                $coupon = $r;
                break;
            }
        }

        $data = [
            'mainpic' => thumb($rs['path'], 1),
            'title' => $rs['title'],
            'price' => $price,
            'address' => $address,
            'stocks' => $rs['stocks'],
            'shipping' => $rs['shipping'],
            'id' => $rs['id'],
            'piece' => $pp,
            'limit_num' => $rs['limit_num']
        ];

        $data['buy_price'] = $price;
//		if($mp && $mp['allow_coupon'] == 1){
//			$data['allow_coupon'] = 1;
        if(!empty($coupon)){
            $data['coupon'] = [
                'id' => $coupon['id'],
                'type' => $coupon['type'],
                'value' => $coupon['value'],
                'content' => $coupon['content'],
                'name' => $coupon['name']
            ];
        }
//		}elseif($mp && $mp['allow_coupon'] == 0){
//            unset($data['coupon']);
//        }

        $this->ajaxReturn($data);
    }

    /**
     * @apiName 提交购买信息并创建订单
     *
     * @apiGetParam {string} token: 通信令牌
     * @apiPostParam {int} goods_id: 要购买的商品ID(商品众筹活动三选一)
     * @apiPostParam {int} tips_id: 要购买的活动ID(商品众筹活动三选一)
     * @apiPostParam {int} raise_id: 要购买的众筹ID(商品众筹活动三选一)
     * @apiPostParam {int} num: 要购买的数量(必填)
     * @apiPostParam {int} times_id: 要购买的时间段id(活动众筹下必填)
     * @apiPostParam {int} address_id: 选择的收货地址id(商品下必填)
     * @apiPostParam {string} attr_ids: 要购买的商品属性ID(商品下选填, 多个属性逗号隔开)
     * @apiPostParam {int} coupon_id: 选择的优惠券id
     * @apiPostParam {string} context: 用户留言备注
     * @apiPostParam {string} weixincode: 微信号
     * @apiPostParam {int} member_privilege_id: 会员领取特权ID
     * @apiPostParam {int} type_piece_id: 购买拼团ID
     * @apiPostParam {int} piece_originator_id: 邀请参团团长的ID
     * @apiPostParam {int} is_book: 是否包场（0-否（默认），1-是）
     * @apiPostParam {int} oper_read: 是否确认协议（0-否，1-是（默认））
     * @apiPostParam {int} from: 从哪个渠道过来的，0--朋友圈 1--微信好友 2--微信群
     *
     * @apiSuccessResponse
     * {
     * 		"info": [
     * 			"order_id" : 1111  //创建的订单id
     * 		],
     * 		"status": 1,
     * 		"url": "",
     * 	}
     * @apiErrorResponse
     * {
     * 		"info": "失败原因",
     * 		"status": 0,
     * 		"url": "",
     * 	}
     */
    Public function create(){
        $goods_id = I('post.goods_id');
        $tips_id = I('post.tips_id');
        $raise_id = I('post.raise_id');
        $num = (int)I('post.num', 1);
        $times_id = I('post.times_id');
        $address_id = I('post.address_id');
        $type_piece_id = I('post.type_piece_id','');
        $piece_originator_id = I('post.piece_originator_id','');
        //$attr_ids = I('post.attr_ids');
        $coupon_id = I('post.coupon_id', '');
        $context = I('post.context', '');
        $member_id = session('member.id');
        $is_book = I('post.is_book', 0);
        $weixincode = I('post.weixincode','');
        $member_privilege_id = I('post.member_privilege_id','');
        $oper_read = I('post.oper_read',1);
        $from = I('post.from');
        $platform = I('post.platform');
        $openid = M('MemberView')->where(['id' => $member_id, 'type' => $this->openidType])->getField('openid');

        if(empty($openid) && strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') > 0){
            $this->error('open_id_is_null');
        }

//        if(empty($openid) && strpos($_SERVER['HTTP_USER_AGENT']) > 0){
//            $this->error('open_id_is_null');
//        }
        //查询出会员标签用于对比优惠券,是否可用
        $member_tags = M('MemberTag')->where(['member_id' => session('member.id')])->getField('id', true);

        if(session('?invite'))$invite_member_id = session('invite.member_id');
        if(empty($member_tags))$member_tags = [];
        //判断是否为活动订单
        if(!empty($tips_id) && !empty($times_id) && empty($type_piece_id) && empty($piece_originator_id)) {
            $this->createTips($tips_id, $times_id, $member_tags, $num, $context, $is_book, $coupon_id,$invite_member_id,$from,$platform);
            //判断是否为商品订单
        }elseif(!empty($goods_id) && !empty($address_id) && empty($type_piece_id) && empty($piece_originator_id)){
            $this->createGoods($goods_id, $address_id, $member_tags, $num, $context, $coupon_id,$invite_member_id,$from,$platform);
            //判断是否为众筹订单
        }elseif(!empty($raise_id) && !empty($times_id) && empty($type_piece_id) && empty($piece_originator_id)){
            $this->createRaise($raise_id, $times_id, $address_id, $context,$weixincode,$member_privilege_id,$oper_read,$invite_member_id,$from,$platform,$num);
        }elseif(!empty($type_piece_id) || !empty($piece_originator_id)){
            //判断是否为拼团订单
            if(!empty($type_piece_id)){//开团订单
                $piece = M('Piece')->where(['id'=>$type_piece_id,'status'=>1])->find();
            }elseif(!empty($piece_originator_id)){//参团订单
                $piece = D('PieceView')->where(['piece_originator_id'=>$piece_originator_id,'piece_act_status'=>['IN',[1,2]],'piece_status'=>1,'status'=>1])->find();

            }
            if(empty($piece)) $this->error('不存在该拼团！');

            if($piece['type'] == 0 ){
                if(!empty($tips_id) && !empty($times_id)){
                    if($tips_id != $piece['type_id'])$this->error('该拼团对应的活动ID不正确');
                    if($times_id != $piece['type_times_id'])$this->error('该拼团对应的活动分期ID不正确！');
                }
            }elseif($piece['type'] == 1){
                if(!empty($goods_id)){
                    if($tips_id != $piece['type_id'])$this->error('该拼团对应的商品ID不正确');
                }
            }elseif($piece['type'] == 2){
                if(!empty($raise_id) && !empty($times_id)){
                    if($tips_id != $piece['type_id'])$this->error('该拼团对应的众筹ID不正确');
                    if($times_id != $piece['type_times_id'])$this->error('该拼团对应的众筹分期ID不正确！');
                }
            }

            $this->createPiece($piece,$piece_originator_id,$address_id,$oper_read,$weixincode, $member_tags, $num, $context, $coupon_id,$is_book);
        }
        else{
            $this->error('非法访问!');
        }
    }

    //创建活动订单
    private function createTips($tips_id, $times_id, $member_tags, $num, $context, $is_book, $coupon_id,$invite_member_id, $target, $platform){
        $member_id = session('member.id');
        if(empty($member_id))$this->error('请登录账号！');
        $rs = D('TipsView')->where(['id' => $tips_id, 'times_id' => $times_id])->find();
        \Think\Log::write('开团信息1：'.json_encode($rs));
        //给times表加行锁避免超卖
        if($rs['limit_time'] > 0)M('TipsTimes')->where(['id' => $times_id])->lock(true)->select();
        $allow_buy = M('OrderWares')->join('__ORDER__ a on a.id=order_id')->where(['invite_member_id' => C('DefaultInviteMember'), 'tips_times_id' => $times_id, 'status' => 1, 'act_status' => ['IN', '0,1,2,3,4,5']])->find();
        if($rs['start_buy_time'] > time() && !(session('?invite') && session('invite.member_id') == C('DefaultInviteMember') && empty($allow_buy) ))$this->error('尚未开始售卖,无法购买!');
        if($rs['stop_buy_time'] < time())$this->error('该活动已结束购买');

        $model = M();
        $model->startTrans();//开启事务
        $buy_num = M('OrderWares')->join('__ORDER__ a on a.id=order_id')->where(['tips_times_id' => $times_id, 'member_id' => $member_id, 'status' => 1, 'act_status' => ['IN', '0,1,2,3,4,5']])->count();
        //执行你想进行的操作, 最后返回操作结果 result
        if ($rs['stock'] <= 0 && $rs['stock'] != -1 ) {
            $model->rollback();//回滚
            $this->error('该活动的所选期数已售罄!');
        } elseif ($rs['stock'] < $num && $rs['stock'] != -1) {
            $model->rollback();//回滚
            $this->error('所选期数剩余数量不足!');
        } elseif ($rs['limit_num'] > 0 && $buy_num + $num > $rs['limit_num'] && $rs['stock'] != -1) {
            $model->rollback();//回滚
            $this->error('所选期数限制每人购买'. $rs['limit_num'] .'份');
        } elseif ($num < 1) {
            $model->rollback();//回滚
            $this->error('非法提交!');
        } elseif ($rs['lowest_num'] > $num && $rs['lowest_num']>0 && $rs['stock'] != -1) {
            $model->rollback();//回滚
            $this->error('所选期数最少购买为'.$rs['lowest_num'].'位');
        }elseif($rs['status'] != 1){
            $model->rollback();//回滚
            $this->error('该活动尚未开放');
        }

        if($rs['buy_status'] == 2)$is_book = 1;
        elseif($rs['buy_status'] == 0)$is_book = 0;
        //查询订单价格
        $time = time();
        //查询是否有折扣价格
//			$mp = M('marketing')->lock(true)->where([
//				'type' => 0,
//				'type_id' => $tips_id,
//				'start_time' => ['LT', $time],
//				'end_time' => ['GT', $time],
//				'num' => ['EGT', $num]
//			])->order('price asc')->find();

        //拼团价格
        if(!empty($type_piece_id)){
            $price = M('Piece')->where(['id'=>$type_piece_id])->getField('price');
        }else{
            $price = $rs['price'];
        }
//			if (!empty($mp['price'])) {
//				$price = $mp['price'];
//				M('marketing')->where(array('id' => $mp['id']))->setDec('num', $num);
//			}

        $sum_price = $price * $num;

        //查询是否包场
        if($is_book == 1 && $rs['discount']>0){
            $sum_price = $sum_price * ( $rs['discount'] / 100 );
        }
        if($is_book && $rs['min_num'] > $num){
            $this->error('包场或定制不得低于最低参与数!');
        }

        //查询优惠券
        $tips_tags = M('TipsTag')->where(['tips_id' => $tips_id])->getField('tag_id', true);
        if(empty($tips_tags))$tips_tags = [];
        if(!empty($coupon_id)){
            $coupon = D('MemberCouponView')->where(['id' => $coupon_id])->find();
            if ($coupon['member_id'] != $member_id) $this->error('优惠券不属于你,不能使用!');
            if ($coupon['start_time'] > $time) $this->error('优惠券还没有开始,不能使用!');
            if ($coupon['end_time'] < $time) $this->error('优惠券已过期,不能使用!');
            if ($coupon['min_amount'] > $sum_price) $this->error('优惠券不到最低限额,不能使用!');
            if ($coupon['used_time'] > 0) $this->error('优惠券已使用,不能再次使用!');

            //验证优惠券标签
            if($coupon['member_tags'] != '*' && empty(array_intersect($member_tags, explode(',', $coupon['member_tags']))))$this->error('优惠券不能使用!');
            if($coupon['tips_tags'] != '*' && empty(array_intersect($tips_tags, explode(',', $coupon['tips_tags']))))$this->error('优惠券不能使用!');

            $value = (float)$coupon['value'];
            if ($coupon['type'] == 0) {
                $sum_price = $sum_price - $value;
            } elseif ($coupon['type'] == 1) {
                $sum_price = $sum_price * $value / 100;
            }
            $sum_price = $sum_price > 0 ? $sum_price : 0;

            M('MemberCoupon')->where(['id' => $coupon_id])->save(['used_time' => time()]);
        }
        $data = [
            'sn' => createCode(18),
            'member_id' => session('member.id'),
            'price' => $sum_price,
            'act_status' => 0,
            'member_coupon_id' => $coupon_id,
            'invite_member_id' => $invite_member_id,
            'create_time' => $time,
            'limit_pay_time' => $time + $rs['limit_time'],
            'context' => $context,
            'is_book' => $is_book,
            'channel' => $this->channel
        ];

        $order_id = M('order')->add($data);
        if (!$order_id) {
            $model->rollback();//回滚
            $this->error('订单创建失败!');
        }

        // 添加分享记录
        if (!empty($invite_member_id) && !empty($target)) {
            $shareItem = M('MemberShare')->where(['member_id' => $invite_member_id, 'item_id' => $tips_id, 'type' => 0, 'target' => $target, 'platform' => $platform])->find();
            !empty($shareItem) && M('MemberJoinshare')->add(array('member_id' => $member_id, 'order_id' => $order_id, 'share_id' => $shareItem['id']));
        }

        //快照数据[转换数据表tips_menu->tips_menus(2016-11-18)]
        $menus = M('TipsMenus')->where(['tips_id'=>$tips_id])->select();
        if(!empty($menus)){
            foreach($menus as $m_rs){
                if(empty($m_rs['pid'])){
                    $new_menu_data[$m_rs['id']]['food_type'] = $m_rs['name'];
                }else{
                    foreach($new_menu_data as $key =>$m_val){
                        if($key == $m_rs['pid']){
                            $new_menu_data[$key]['food_name_arr'][] = $m_rs['name'];
                        }
                    }
                }
            }
            foreach($new_menu_data as  $me_rs){
                $me_rs['food_name']= implode(',',$me_rs['food_name_arr']);
                unset($me_rs['food_name_arr']);
                $data_menu[] = $me_rs;
            }
            $data['menu'] = $data_menu;
        }else{
            $data['menu'] = [];
        }
        $snapshot = array(
            'tips_id'=>$tips_id,
            'tips_title'=>$rs['title'],
            'tips_edges'=>$rs['edges'],
            'tips_phase'=>$rs['phase'],
            'tips_category'=>$rs['catname'],
            'tips_address'=>$rs['address'],
            'tips_price'=>$rs['price'],
            'tips_menu'=>$data['menu'],
            //'check_code'=>$code,
            //'购买数量'=>$num,
            'is_book'=>$is_book,
            'member_coupon_id'=>$coupon_id,
//                'marketing_price'=>$mp['price'],
//                'marketing_id'=>$mp['id'],
            //'最终支付价格'=>$sum_price,
            //'member_nickname'=>$rs['member_nickname'],
            'datetime'=>time(),
        );

        $code = createCode(8);
        for ($i = 0; $i < $num; $i++) {
            //判断是否是包场，不是包场则将验证码区分开
            //if($rs['restrict_num'] > $num)$code = createCode(8);
            if($is_book == 0)$code = createCode(8);

            $result = M('OrderWares')->add(array(
                'order_id' => $order_id,
                'type' => 0,
                'ware_id' => $tips_id,
                'price' => $price,
                'marketing_id' => !empty($mp['id']) ? $mp['id'] : null,
                'check_code' => $code,
                'tips_times_id' => $times_id,
                'snapshot' => json_encode($snapshot)
            ));
            if (!$result) {
                $model->rollback();//回滚
                $this->error('订单商品插入失败!');
            }
        }

        //当活动为预约制活动，不减库存
        if(in_array(76,$tips_tags) == false){
            //下单减库存
            if($rs['limit_time'] > 0){
                if($is_book == 0)
                    M('TipsTimes')->where(['id' => $times_id])->setDec('stock', $num);
                else
                    M('TipsTimes')->where(['id' => $times_id])->save(['stock' => 0]);
            }
        }
        //开团团长保存
        if(empty($piece_originator_id) &&  !empty($type_piece_id)){
            $data_piece = [
                'member_id'=> $member_id,
                'piece_id'=> $type_piece_id,
                'end_time'=> date('Y-m-d H:i:s',strtotime('+2 days')),
            ];

            \Think\Log::write('开团信息：'.json_encode($data_piece));
            $piece_originator_id = M('OrderPiece')->add($data_piece);
        }
        //参团会员保存团长ID
        if(!empty($piece_originator_id) || (!empty($type_piece_id) && !empty($piece_originator_id))){
            M('order')->where(['id'=>$order_id])->save(['order_piece_id'=>$piece_originator_id]);
        }
        $model->commit();//事务提交

        //判断核销微信卡券
        /*if(!empty($coupon_id)){
            $accessToken = getAccessToken();
            $coupon_data = M('member_coupon')->field('coupon_id,sn')->where('id='.$coupon_id)->find();
            $wx_sn = M('coupon')->where('id='.$coupon_data['coupon_id'])->getField('wx_sn');
            if(!empty($wx_sn)){
                $sn = $coupon_data['sn'];
                $post_data = array('card_id'=>$wx_sn,'code'=>$sn);
                $rs = $this->curl_post('https://api.weixin.qq.com/card/code/get?access_token=' . $accessToken, $post_data);
            }
        }*/


        //记录订单快照信息
        $this->SaveSnapshotLogs((int)$order_id,3,$this->framework_id());
        $this->success(['order_id' => (int)$order_id,'limit_pay_time'=>$data['limit_pay_time']]);
    }

    //创建商品订单
    private function createGoods($goods_id, $address_id, $member_tags, $num, $context, $coupon_id,$invite_member_id){
        $member_id = session('member.id');
        if(empty($member_id))$this->error('请登录账号！');
        $rs = D('GoodsView')->where(['id' => $goods_id])->find();

        $model = M();
        $model->startTrans();//开启事务
        //给GOODS表加行锁
        if($rs['limit_time'] > 0)M('Goods')->where(['id' => $goods_id])->lock(true)->select();

        //执行你想进行的操作, 最后返回操作结果 result
        if ($rs['stocks'] <= 0) {
            $model->rollback();//回滚
            $this->error('该商品已售罄!');
        } elseif ($rs['stocks'] < $num) {
            $model->rollback();//回滚
            $this->error('该商品剩余库存不足!');
        } elseif ($num < 1) {
            $model->rollback();//回滚
            $this->error('非法提交!');
        }elseif($rs['status'] != 1 && $rs['status'] != 1){
            $model->rollback();//回滚
            $this->error('该商品尚未开放购买');
        } elseif($rs['limit_num'] > 0 && $rs['limit_num'] < $num) {
            $model->rollback();
            $this->error('每人限购' . $rs['limit_num'] . '份');
        }

        //查询订单价格
        $time = time();
        //查询是否有折扣价格
        $mp = M('marketing')->lock(true)->where([
            'type' => 1,
            'type_id' => $goods_id,
            'start_time' => ['LT', $time],
            'end_time' => ['GT', $time],
            'num' => ['EGT', $num]
        ])->order('price asc')->find();
        $price = $rs['price'];
        if (!empty($mp['price'])) {
            $price = $mp['price'];
            M('marketing')->where(['id' => $mp['id']])->setDec('num', $num);
        }

        $sum_price = $price * $num;

        //加入邮费
        if($rs['shipping'] > 0)$sum_price += $rs['shipping'];

        //查询优惠券
        if(!empty($coupon_id)){
            $coupon = D('MemberCouponView')->where(['id' => $coupon_id])->find();
            if ($coupon['member_id'] != $member_id) $this->error('优惠券不属于你,不能使用!');
            if ($coupon['start_time'] > $time) $this->error('优惠券还没有开始,不能使用!');
            if ($coupon['end_time'] < $time) $this->error('优惠券已过期,不能使用!');
            if ($coupon['min_amount'] > $sum_price) $this->error('优惠券不到最低限额,不能使用!');
            if ($coupon['used_time'] > 0) $this->error('优惠券已使用,不能再次使用!');

            //验证优惠券标签
            if($coupon['member_tags'] != '*' && empty(array_intersect($member_tags, explode(',', $coupon['member_tags']))))$this->error('优惠券不能使用!');
            $goods_tags = M('GoodsTag')->where(['goods_id' => $goods_id])->getField('id', true);
            if(empty($goods_tags))$goods_tags = [];
            if($coupon['goods_tags'] != '*' && empty(array_intersect($goods_tags, explode(',', $coupon['goods_tags']))))$this->error('优惠券不能使用!');

            $value = (float)$coupon['value'];
            if ($coupon['type'] == 0) {
                $sum_price = $sum_price - $value;
            } elseif ($coupon['type'] == 1) {
                $sum_price = $sum_price * $value / 100;
            }
            $sum_price = $sum_price > 0 ? $sum_price : 0;

            M('MemberCoupon')->where(['id' => $coupon_id])->save(['used_time' => time()]);
        }

        $data = [
            'sn' => createCode(18),
            'member_id' => session('member.id'),
            'price' => $sum_price,
            'act_status' => 0,
            'member_coupon_id' => $coupon_id,
            'invite_member_id' => $invite_member_id,
            'create_time' => $time,
            'limit_pay_time' => $time + $rs['limit_time'],
            'member_address_id' => $address_id,
            'postage' => $rs['shipping'],
            'context' => $context,
            'channel' => $this->channel
        ];

//		if(session('?invite'))$data['invite_member_id'] = session('invite.member_id');
        $order_id = M('order')->add($data);
        if (!$order_id) {
            $model->rollback();//回滚
            $this->error('订单创建失败!');
        }

        //快照数据
        $address = M('MemberAddress')->where(['id'=>$address_id])->find();
        $snapshot = [
            'goods_id'=>$goods_id,
            'goods_title'=>$rs['title'],
            'goods_edge'=>$rs['edge'],
            'goods_category'=>$rs['catname'],
            'goods_price'=>$rs['price'],
            'member_address'=>$address,
            'member_coupon_id'=>$coupon_id,
            'marketing_price'=>$mp['price'],
            'marketing_id'=>$mp['id'],
            'datetime'=>time()
        ];

        for ($i = 0; $i < $num; $i++) {
            $result = M('OrderWares')->add([
                'order_id' => $order_id,
                'type' => 1,
                'ware_id' => $goods_id,
                'price' => $price,
                'marketing_id' => !empty($mp['id']) ? $mp['id'] : null,
                'snapshot' => json_encode($snapshot)
            ]);
            if (!$result) {
                $model->rollback();//回滚
                $this->error('订单商品插入失败!');
            }
        }

        //下单减库存
        if($rs['limit_time'] > 0)M('Goods')->where(['id' => $goods_id])->setDec('stocks', $num);

        $model->commit();//事务提交

        //记录订单快照信息
        $this->SaveSnapshotLogs((int)$order_id,3,$this->framework_id());
        $this->success(['order_id' => (int)$order_id,'limit_pay_time'=>$data['limit_pay_time']]);
    }

    //创建众筹订单
    private function createRaise($raise_id, $times_id, $address_id, $context,$weixincode,$member_privilege_id,$oper_read,$invite_member_id,$target,$platform,$num = 1){
        $member_id = session('member.id');
        if(empty($member_id))$this->error('请登录账号！');
        //给times表加行锁避免超卖
        M('RaiseTimes')->where(['id' => $times_id])->lock(true)->select();
        $rs = D('RaiseView')->where(['id' => $raise_id, 'times_id' => $times_id,'status'=>1])->find();
        $buy_count = M('OrderWares')->join('__ORDER__ a on order_id=a.id')->where(['type' => 2, 'a.member_id' =>$member_id, 'ware_id' => $raise_id,'tips_times_id' => $times_id, 'status' => 1, 'act_status' => ['in', '0,1,2,3,4']])->count();
        if(empty($rs))$this->error('非法提交!');
        if($oper_read == 0)$this->error('未确认协议，请确认协议再下单!');
        $is_use_privilege = false;
        //检查特权是否使用
        if(!empty($member_privilege_id)&&$rs['start_time']>time()){
            $privilege = M('MemberPrivilege')->where(['id'=>$member_privilege_id])->find();
            //if($privilege['order_id']!=null)	$this->error('该特权已经使用了！');
            //以下是检查特权有没有超售
            $privilege_id = $privilege['privilege_id'];
            //$pri_buy_count = M('MemberPrivilege')->where(['privilege_id'=>$privilege_id,'order_id'=>['EXP','IS NOT NULL']])->count();
            $privilege_info = M('Privilege')->where(['id'=>$privilege_id])->find();
            if($privilege_info['number']<$num&&$privilege_info['number']>=0){
                $this->error('该优先众筹的所选项目已售罄!你可以在众筹正式上线时认筹');
            }
            $is_use_privilege=true;

        }
        $model = M();
        $model->startTrans();//开启事务
        //执行你想进行的操作, 最后返回操作结果 result
        if ($is_use_privilege==false && $num>$rs['stock']&&$rs['stock'] >= 0 ) { //应该为 $num>rs['stock']&&$rs['stock'] >= 0 
            $model->rollback();//回滚
            $this->error('该众筹的所选项目已售罄!');
        }elseif($rs['start_time']>time() && empty($member_privilege_id)){
            $model->rollback();//回滚
            $this->error('该众筹尚未开放');
        }elseif ($rs['limit_num'] > 0 && $buy_count >= $rs['limit_num']) {
            $model->rollback();//回滚
            $this->error('限制购买次数为'.$rs["limit_num"].'，您已下单'.$buy_count.'次了，不能再购买了');
        }elseif ($rs['end_time']<time()) {
            $model->rollback();//回滚
            $this->error('该众筹已结束购买');
        }


        //保存用户信息
        M('MemberInfo')->where(['member_id'=>$member_id])->save(['weixincode'=>$weixincode]);
        //查询订单价格
        $time = time();
        // 单价，如果是预付款，则为预付款
        $singlePrice = !empty($rs['prepay']) && $rs['prepay']>0?$rs['prepay']:$rs['price'];
        //全额/预付头款
        $data = [
            'sn' => createCode(18),
            'member_id' => $member_id,
            'price' => $singlePrice * $num,
            'act_status' => 0,
            'create_time' => $time,
            'limit_pay_time' => $time + $rs['limit_time'],
            'invite_member_id' => $invite_member_id,
            'channel' => $this->channel
        ];
        if(!empty($address_id))$data['member_address_id'] = $address_id;
        if(!empty($context))$data['context'] = $context;
//		if(session('?invite'))$data['invite_member_id'] = session('invite.member_id');
        $order_id = M('order')->add($data);

        if (!$order_id) {
            $model->rollback();//回滚
            $this->error('订单创建失败!');
        }

        // TODO
        // 添加众筹购买份数

        // 添加分享记录
        if (!empty($invite_member_id)) {
            $shareItem = M('MemberShare')->where(['member_id' => $invite_member_id, 'item_id' => $raise_id, 'type' => 3, 'target' => $target, 'platform' => $platform])->find();
            !empty($shareItem) && M('MemberJoinshare')->add(array('member_id' => $member_id, 'order_id' => $order_id, 'share_id' => $shareItem['id']));
        }

        //快照数据
        $snapshot = [
            'raise_id'=>$raise_id,
            'raise_times_id'=>$times_id,
            'raise_title'=>$rs['title'],
            'raise_content'=>$rs['content'],
            'raise_category'=>$rs['catname'],
            'raise_introduction'=>$rs['introduction'],
            'raise_total'=>$rs['total'],
            'raise_price'=>$rs['price'],
            'raise_prepay'=>$rs['prepay'],
            'raise_type'=>$rs['prepay']>0 ? '预付方式' : '全额方式',
            'raise_act_pay'=> $singlePrice,
            'datetime'=>$address_id,
            'datetime'=>time(),
        ];

        $code = createCode(8);

        // 添加order_wares
        for ($i = 0; $i < $num; $i++) {
            $result = M('OrderWares')->add(array(
                'order_id' => $order_id,
                'type' => 2,
                'ware_id' => $raise_id,
                'price' => $singlePrice,
                'check_code' => $code,
                'tips_times_id' => $times_id,
                'snapshot' => json_encode($snapshot)
            ));
            if (!$result) {
                $model->rollback();//回滚
                $this->error('订单商品插入失败!');
            }
        }

        //下单减库存
      //  if(!$is_use_privilege){
           
     //   }else{ //使用特权
        if(!empty($member_privilege_id) && $rs['start_time']>time()){
                M('MemberPrivilege')->where(['id' => $member_privilege_id])->save(['order_id'=>$order_id]);
                if($privilege_info['number']>=0){
                    M('Privilege')->where(['id'=>$privilege_id])->setDec('number', $num);
                }
            }
            if($rs['stock'] >=0){M('RaiseTimes')->where(['id' => $times_id])->setDec('stock', $num);}
       // }
        
        $model->commit();//事务提交

        //记录订单快照信息
        $this->SaveSnapshotLogs((int)$order_id,3,$this->framework_id());
        $this->success(['order_id' => (int)$order_id,'limit_pay_time'=>$data['limit_pay_time']]);

    }

    //创建拼团订单
    private function createPiece($piece,$piece_originator_id,$address_id,$oper_read,$weixincode, $member_tags, $num, $context, $coupon_id,$is_book){
        $member_id = session('member.id');
        if(empty($member_id))$this->error('请登录账号！');

        if($piece['type'] == 0){
            $this->createTipsPiece($piece,$piece_originator_id,$address_id,$oper_read,$weixincode, $member_tags, $num, $context, $coupon_id,$is_book,$member_id);
        } elseif ($piece['type'] == 1) {
            $this->createGoodsPiece($piece,$piece_originator_id,$address_id,$oper_read,$weixincode, $member_tags, $num, $context, $coupon_id,$is_book,$member_id);
        }

        //判断核销微信卡券
        /*if(!empty($coupon_id)){
            $accessToken = getAccessToken();
            $coupon_data = M('member_coupon')->field('coupon_id,sn')->where('id='.$coupon_id)->find();
            $wx_sn = M('coupon')->where('id='.$coupon_data['coupon_id'])->getField('wx_sn');
            if(!empty($wx_sn)){
                $sn = $coupon_data['sn'];
                $post_data = array('card_id'=>$wx_sn,'code'=>$sn);
                $rs = $this->curl_post('https://api.weixin.qq.com/card/code/get?access_token=' . $accessToken, $post_data);
            }
        }*/



    }

    /**
     * 活动拼团
     * @param $piece
     * @param $piece_originator_id
     * @param $address_id
     * @param $oper_read
     * @param $weixincode
     * @param $member_tags
     * @param $num
     * @param $context
     * @param $coupon_id
     * @param $is_book
     * @param $member_id
     */
    private function createTipsPiece($piece,$piece_originator_id,$address_id,$oper_read,$weixincode, $member_tags, $num, $context, $coupon_id,$is_book,$member_id) {
        $tips_id = $piece['type_id'];
        $times_id = $piece['type_times_id'];
        $rs = D('TipsView')->where(['id' => $tips_id, 'times_id' => $times_id,'status'=>1,'is_pass'=>1])->find();
        //给times表加行锁避免超卖
        if($rs['limit_time'] > 0)M('TipsTimes')->where(['id' => $times_id])->lock(true)->select();
        $allow_buy = M('OrderWares')->join('__ORDER__ a on a.id=order_id')->where([ 'tips_times_id' => $times_id, 'status' => 1, 'act_status' => ['IN', '0,1,2,3,4,5']])->find();
        if($rs['start_buy_time'] > time()  && empty($allow_buy) )$this->error('尚未开始售卖,无法购买!');
        if($rs['stop_buy_time'] < time())$this->error('该活动已结束购买!');

        //开团限制操作
        if(empty($piece_originator_id)){
            $p_rs = D('OrderPieceView')->where(['member_id'=>$member_id,'piece_id'=>$piece['id'],'piece_act_status'=>['IN','0,1'],'piece_status'=>1,'end_time'=>['gt',time()],'D.act_status' => ['IN', '0,1,2,3,4,5'],'order_status'=>1])->find();
            if(!empty($p_rs))$this->error('该拼团您已经开过了，去继续支付或者查看拼团详情');
            if($rs['stock'] < $piece['count'])$this->error('库存不足，不能发起拼团');
        }else{
            $p_rs = D('OrderPieceView')->where(['piece_originator_id'=>$piece_originator_id,'piece_status'=>1,'D.act_status' => ['IN', '0,1,2,3,4,5']])->find();
            if($p_rs['end_time'] <time())$this->error('该活动拼团已结束购买');
            if($p_rs['act_status'] == 3)$this->error('该拼团已成团，不能购买');
            if($p_rs['act_status'] == 8)$this->error('该拼团已过期，不能购买');
            if($p_rs['act_status'] == 9)$this->error('该拼团已取消，不能购买');

        }
        $model = M();
        $model->startTrans();//开启事务
        $buy_num = M('OrderWares')->join('__ORDER__ a on a.id=order_id')->where(['tips_times_id' => $times_id, 'member_id' => $member_id, 'status' => 1, 'act_status' => ['IN', '0,1,2,3,4,5']])->count();

        //执行你想进行的操作, 最后返回操作结果 result
        if ($rs['stock'] <= 0) {
            $model->rollback();//回滚
            $this->error('该活动的所选期数已售罄!');
        } elseif ($rs['stock'] < $num ) {
            $model->rollback();//回滚
            $this->error('所选期数剩余数量不足!');
        } elseif ($rs['limit_num'] > 0 && ($buy_num + $num) > $rs['limit_num']) {
            $model->rollback();//回滚
            $this->error('所选期数限制每人购买'. $rs['limit_num'] .'份!');
        } elseif ($num < 1) {
            $model->rollback();//回滚
            $this->error('非法提交!');
        }elseif($rs['status'] != 1){
            $model->rollback();//回滚
            $this->error('该活动尚未开放');
        }

        if($rs['buy_status'] == 2)$is_book = 1;
        elseif($rs['buy_status'] == 0)$is_book = 0;
        //查询订单价格
        $time = time();
        //拼团价格
        $price =$piece['price'];
        $sum_price = $price * $num;

        //查询是否包场
        if($is_book == 1 && $rs['discount']>0){
            $sum_price = $sum_price * ( $rs['discount'] / 100 );
        }
        if($is_book && $rs['min_num'] > $num){
            $this->error('包场或定制不得低于最低参与数!');
        }
        //查询优惠券
        if(!empty($coupon_id)){
            $coupon = D('MemberCouponView')->where(['id' => $coupon_id])->find();
            if ($coupon['member_id'] != $member_id) $this->error('优惠券不属于你,不能使用!');
            if ($coupon['start_time'] > $time) $this->error('优惠券还没有开始,不能使用!');
            if ($coupon['end_time'] < $time) $this->error('优惠券已过期,不能使用!');
            if ($coupon['min_amount'] > $sum_price) $this->error('优惠券不到最低限额,不能使用!');
            if ($coupon['used_time'] > 0) $this->error('优惠券已使用,不能再次使用!');

            //验证优惠券标签
            if($coupon['member_tags'] != '*' && empty(array_intersect($member_tags, explode(',', $coupon['member_tags']))))$this->error('优惠券不能使用!');
            $tips_tags = M('TipsTag')->where(['tips_id' => $tips_id])->getField('tag_id', true);
            if(empty($tips_tags))$tips_tags = [];
            if($coupon['tips_tags'] != '*' && empty(array_intersect($tips_tags, explode(',', $coupon['tips_tags']))))$this->error('优惠券不能使用!');

            $value = (float)$coupon['value'];
            if ($coupon['type'] == 0) {
                $sum_price = $sum_price - $value;
            } elseif ($coupon['type'] == 1) {
                $sum_price = $sum_price * $value / 100;
            }
            $sum_price = $sum_price > 0 ? $sum_price : 0;

            M('MemberCoupon')->where(['id' => $coupon_id])->save(['used_time' => time()]);
        }
        $data = [
            'sn' => createCode(18),
            'member_id' => session('member.id'),
            'price' => $sum_price,
            'act_status' => 0,
            'member_coupon_id' => $coupon_id,
            'create_time' => $time,
            'limit_pay_time' => $time + $rs['limit_time'],
            'context' => $context,
            'is_book' => $is_book,
            'channel' => $this->channel
        ];
        $order_id = M('order')->add($data);
        if (!$order_id) {
            $model->rollback();//回滚
            $this->error('订单创建失败!');
        }

        //快照数据[转换数据表tips_menu->tips_menus(2016-11-18)]
        $menus = M('TipsMenus')->where(['tips_id'=>$tips_id])->select();
        if(!empty($menus)){
            foreach($menus as $m_rs){
                if(empty($m_rs['pid'])){
                    $new_menu_data[$m_rs['id']]['food_type'] = $m_rs['name'];
                }else{
                    foreach($new_menu_data as $key =>$m_val){
                        if($key == $m_rs['pid']){
                            $new_menu_data[$key]['food_name_arr'][] = $m_rs['name'];
                        }
                    }
                }
            }
            foreach($new_menu_data as  $me_rs){
                $me_rs['food_name']= implode(',',$me_rs['food_name_arr']);
                unset($me_rs['food_name_arr']);
                $data_menu[] = $me_rs;
            }
            $data['menu'] = $data_menu;
        }else{
            $data['menu'] = [];
        }
        $snapshot = array(
            'tips_id'=>$tips_id,
            'tips_title'=>$rs['title'],
            'tips_edges'=>$rs['edges'],
            'tips_phase'=>$rs['phase'],
            'tips_category'=>$rs['catname'],
            'tips_address'=>$rs['address'],
            'tips_price'=>$rs['price'],
            'tips_menu'=>$data['menu'],
            'is_book'=>$is_book,
            'member_coupon_id'=>$coupon_id,
            'datetime'=>time(),
        );

        $code = createCode(8);
        for ($i = 0; $i < $num; $i++) {
            $result = M('OrderWares')->add(array(
                'order_id' => $order_id,
                'type' => 0,
                'ware_id' => $tips_id,
                'price' => $price,
                'marketing_id' => !empty($mp['id']) ? $mp['id'] : null,
                'check_code' => $code,
                'tips_times_id' => $times_id,
                'snapshot' => json_encode($snapshot)
            ));

            if (!$result) {
                $model->rollback();//回滚
                $this->error('订单商品插入失败!');
            }

        }

        //下单减库存
        if($rs['limit_time'] > 0){
            if($is_book == 0)
                M('TipsTimes')->where(['id' => $times_id])->setDec('stock', $num);
            else
                M('TipsTimes')->where(['id' => $times_id])->save(['stock' => 0]);
        }
        //参团会员
        if(!empty($piece_originator_id)){
            M('order_piece')->add(['piece_originator_id'=>$piece_originator_id,'order_id'=>(int)$order_id]);
            $is_member_piece = 1;
        }
        //开团团长保存
        if(empty($piece_originator_id) && !empty($piece)){
            $data_piece = [
                'member_id'=> $member_id,
                'piece_id'=> $piece['id'],
                'act_status'=> 0,
                'end_time'=> time() + (3600*(int)$piece['limit_time']),
            ];

            \Think\Log::write('开团信息：'.json_encode($data_piece));
            $piece_originator_id = M('MemberPiece')->add($data_piece);
            M('order_piece')->add(['piece_originator_id'=>$piece_originator_id,'order_id'=>(int)$order_id]);
            $is_member_piece = 0;
        }
        $model->commit();//事务提交

        //记录订单快照信息
        $this->SaveSnapshotLogs((int)$order_id,3,$this->framework_id());
        $this->success(['order_id' => (int)$order_id,'limit_pay_time'=>$data['limit_pay_time'],'piece_originator_id'=>$piece_originator_id,'surplus_num'=>(($piece['count']-$buy_num)>0?(int)($piece['count']-$buy_num):0),'is_member_piece'=>$is_member_piece]);
    }

    /**
     * 商品拼团
     * @param $piece
     * @param $piece_originator_id
     * @param $address_id
     * @param $oper_read
     * @param $weixincode
     * @param $member_tags
     * @param $num
     * @param $context
     * @param $coupon_id
     * @param $is_book
     * @param $member_id
     */
    private function createGoodsPiece($piece,$piece_originator_id,$address_id,$oper_read,$weixincode, $member_tags, $num, $context, $coupon_id,$is_book,$member_id) {
        $goods_id = $piece['type_id']; // 商品Id
        $rs = D('GoodsView')->where(['id' => $goods_id])->find();

        if (empty($address_id)) {
            $this->error('商品邮购，必须填写地址');
        }

        //开团限制操作
        if(empty($piece_originator_id)){
            $p_rs = D('OrderPieceView')->where(['member_id'=>$member_id,'piece_id'=>$piece['id'],'piece_act_status'=>['IN','0,1,2,3'],'piece_status'=>1,'D.act_status' => ['IN', '0,1,2,3,4,5'],'D.status'=>1])->find();
            if($rs['stocks'] < $piece['count'])$this->error('库存不足，不能发起拼团');
            if(!empty($p_rs))$this->error('该拼团您已经开过了，去继续支付或者查看拼团详情');
        }else{
            $p_rs = D('OrderPieceView')->where(['piece_originator_id'=>$piece_originator_id,'piece_status'=>1,'D.act_status' => ['IN', '0,1,2,3,4'], 'D.status' => 1])->select();
            $p_count = count($p_rs);

            foreach($p_rs as $item) {
                if ($item['order_member_id'] == $member_id) {
                    $this->error('你已经参加过这个团购了');
                    return;
                }
            }
            unset($item);

            $p_rs = $p_rs[0];
            if($p_rs['end_time'] <time())$this->error('该活动拼团已结束购买');
            if($p_rs['act_status'] == 3 || ($p_rs['is_cap'] == 1 && $p_count >= $p_rs['count']))$this->error('该拼团已成团，不能购买');
            if($p_rs['act_status'] == 8)$this->error('该拼团已过期，不能购买');
            if($p_rs['act_status'] == 9)$this->error('该拼团已取消，不能购买');
            if($p_rs['limit_num'] >= 1 && $p_rs['limit_num'] < $num)$this->error('每人限购' . $p_rs['limit_num'] . '份');
        }

        $model = M();
        $model->startTrans();//开启事务
        //给GOODS表加行锁
        if($rs['limit_time'] > 0)M('Goods')->where(['id' => $goods_id])->lock(true)->select();

        //执行你想进行的操作, 最后返回操作结果 result
        if ($rs['stocks'] <= 0) {
            $model->rollback();//回滚
            $this->error('该商品已售罄!');
        } elseif ($rs['stocks'] < $num) {
            $model->rollback();//回滚
            $this->error('该商品剩余库存不足!');
        } elseif ($num < 1) {
            $model->rollback();//回滚
            $this->error('非法提交!');
        }elseif($rs['status'] != 1 && $rs['status'] != 1){
            $model->rollback();//回滚
            $this->error('该商品尚未开放购买');
        }

        //查询订单价格
        $time = time();
        //查询是否有折扣价格
        $mp = M('marketing')->lock(true)->where([
            'type' => 1,
            'type_id' => $goods_id,
            'start_time' => ['LT', $time],
            'end_time' => ['GT', $time],
            'num' => ['EGT', $num]
        ])->order('price asc')->find();
        $price = $piece['price']; //$rs['price']; // 拼团价格
        if (!empty($mp['price'])) {
            $price = $mp['price'];
            M('marketing')->where(['id' => $mp['id']])->setDec('num', $num);
        }

        $sum_price = $price * $num;

        //加入邮费
        if($rs['shipping'] > 0)$sum_price += $rs['shipping'];

        //查询优惠券
        if(!empty($coupon_id)){
            $coupon = D('MemberCouponView')->where(['id' => $coupon_id])->find();
            if ($coupon['member_id'] != $member_id) $this->error('优惠券不属于你,不能使用!');
            if ($coupon['start_time'] > $time) $this->error('优惠券还没有开始,不能使用!');
            if ($coupon['end_time'] < $time) $this->error('优惠券已过期,不能使用!');
            if ($coupon['min_amount'] > $sum_price) $this->error('优惠券不到最低限额,不能使用!');
            if ($coupon['used_time'] > 0) $this->error('优惠券已使用,不能再次使用!');

            //验证优惠券标签
            if($coupon['member_tags'] != '*' && empty(array_intersect($member_tags, explode(',', $coupon['member_tags']))))$this->error('优惠券不能使用!');
            $goods_tags = M('GoodsTag')->where(['goods_id' => $goods_id])->getField('id', true);
            if(empty($goods_tags))$goods_tags = [];
            if($coupon['goods_tags'] != '*' && empty(array_intersect($goods_tags, explode(',', $coupon['goods_tags']))))$this->error('优惠券不能使用!');

            $value = (float)$coupon['value'];
            if ($coupon['type'] == 0) {
                $sum_price = $sum_price - $value;
            } elseif ($coupon['type'] == 1) {
                $sum_price = $sum_price * $value / 100;
            }
            $sum_price = $sum_price > 0 ? $sum_price : 0;

            M('MemberCoupon')->where(['id' => $coupon_id])->save(['used_time' => time()]);
        }

        $data = [
            'sn' => createCode(18),
            'member_id' => session('member.id'),
            'price' => $sum_price,
            'act_status' => 0,
            'member_coupon_id' => $coupon_id,
//            'invite_member_id' => $invite_member_id,
            'create_time' => $time,
            'limit_pay_time' => $time + $rs['limit_time'],
            'member_address_id' => $address_id,
            'postage' => $rs['shipping'],
            'context' => $context,
            'channel' => $this->channel
        ];

//		if(session('?invite'))$data['invite_member_id'] = session('invite.member_id');
        $order_id = M('order')->add($data);
        if (!$order_id) {
            $model->rollback();//回滚
            $this->error('订单创建失败!');
        }

        //快照数据
        $address = M('MemberAddress')->where(['id'=>$address_id])->find();
        $snapshot = [
            'goods_id'=>$goods_id,
            'goods_title'=>$rs['title'],
            'goods_edge'=>$rs['edge'],
            'goods_category'=>$rs['catname'],
            'goods_price'=>$rs['price'],
            'member_address'=>$address,
            'member_coupon_id'=>$coupon_id,
            'marketing_price'=>$mp['price'],
            'marketing_id'=>$mp['id'],
            'datetime'=>time()
        ];

        for ($i = 0; $i < $num; $i++) {
            $result = M('OrderWares')->add([
                'order_id' => $order_id,
                'type' => 1,
                'ware_id' => $goods_id,
                'price' => $price,
                'marketing_id' => !empty($mp['id']) ? $mp['id'] : null,
                'snapshot' => json_encode($snapshot)
            ]);
            if (!$result) {
                $model->rollback();//回滚
                $this->error('订单商品插入失败!');
            }
        }

        //下单减库存
        if($rs['limit_time'] > 0)M('Goods')->where(['id' => $goods_id])->setDec('stocks', $num);

        //参团会员
        if(!empty($piece_originator_id)){
            M('order_piece')->add(['piece_originator_id'=>$piece_originator_id,'order_id'=>(int)$order_id]);
            $is_member_piece = 1;
        }
        //开团团长保存
        if(empty($piece_originator_id) && !empty($piece)){
            $data_piece = [
                'member_id'=> $member_id,
                'piece_id'=> $piece['id'],
                'act_status'=> 0,
                'end_time'=> time() + (3600*(int)$piece['limit_time']),
            ];

            \Think\Log::write('开团信息：'.json_encode($data_piece));
            $piece_originator_id = M('MemberPiece')->add($data_piece);
            M('order_piece')->add(['piece_originator_id'=>$piece_originator_id,'order_id'=>(int)$order_id]);
            $is_member_piece = 0;
        }

        $model->commit();//事务提交
        //记录订单快照信息
        $this->SaveSnapshotLogs((int)$order_id,3,$this->framework_id());
        $this->success(['order_id' => (int)$order_id,'limit_pay_time'=>$data['limit_pay_time'],'piece_originator_id'=>$piece_originator_id,'is_member_piece'=>$is_member_piece]);
    }
}