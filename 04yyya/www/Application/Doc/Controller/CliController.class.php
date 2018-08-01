<?php
namespace Doc\Controller;
use Think\Controller;

Class CliController extends Controller {

	Public function __construct(){
		parent::__construct();
		//验证密码
		$key = I('get.key');
		if($key != C('CliKey')){
			$this->error('非法访问！');
		}
	}

	/**
	 * 推送消息
	 * @param $member_id 推送的目标会员ID
	 * @param $context 推送消息内容
	 * @param int|string|null $origin_id 推送来源会员ID(number-来源会员ID, 'wx'-发送到微信公众号, 'sms'-发送到会员短信上, 'email'-发送到会员邮箱)
	 * @param int $type 消息关联资源(0-普通消息 1-评论回复通知 2-达人动态通知 3-订单动态通知 4-活动推送消息 5-专题推送消息)
	 * @param int $type_id 关联的活动ID、专题ID、订单ID、食报ID
	 * @param int $sendtime 发送时间
	 * @param int $channel 渠道 0-吖咪 1-我有饭
	 */
	Protected function pushMessage($member_id, $context, $origin_id = null, $type = 0, $type_id = 0, $sendtime = 0, $channel = 0){
		if(abslength($context) > 250 || empty($context)){
			\Think\Log::write($context . ' Length:' . abslength($context));
			$this->error('消息不能为空或太长,推送失败!');
		}

		$data = [
			'type' => $type,
			'type_id' => $type_id,
			'content' => $context,
			'sendtime' => $sendtime
		];

		if(is_string($origin_id)){
			$send_way = explode('|', $origin_id);
			$rs = M('member')->where(['id' => $member_id])->find();
			if(in_array('sms', $send_way)){
				$data['sms_send'] = 1 + $channel;
				$sms = 0;
				if($sendtime < time())
					$sms = sms_send($rs['telephone'], $context, false, $channel);
			}
		}elseif(is_numeric($origin_id))$data['member_id'] = $origin_id;

		$message_id = M('message')->add($data);

		M('MemberMessage')->add([
			'member_id' => $member_id,
			'message_id' => $message_id,
			'is_sms' => !empty($sms) ? 1 + $channel : 0,
			'is_wx' => !empty($wx) ? 1 + $channel: 0
		]);

		return true;
	}

	/**
	 * 推送消息(阿里云短信)
	 * @param $member_id 推送的目标会员ID
	 * @param $param 参数
	 * @param $code_key 短信模板代码
	 * @param int|string|null $origin_id 推送来源会员ID(number-来源会员ID, 'wx'-发送到微信公众号, 'sms'-发送到会员短信上, 'email'-发送到会员邮箱)
	 * @param string $site_message
	 * @param int $type 消息关联资源(0-普通消息 1-评论回复通知 2-达人动态通知 3-订单动态通知 4-活动推送消息 5-专题推送消息)
	 * @param int $type_id 关联的活动ID、专题ID、订单ID、食报ID
	 * @param int $sendtime 发送时间
	 * @param int $channel 渠道 0-吖咪 1-我有饭
	 */
	Protected function push_Message($member_id, $param=array(),$code_key='', $origin_id = null,$site_message= null, $type = 0, $type_id = 0, $sendtime = 0, $channel = 0){
//		if(abslength($context) > 250 || empty($context)){
//			\Think\Log::write($context . ' Length:' . abslength($context));
//			$this->error('消息不能为空或太长,推送失败!');
//		}
		$o = 'openid';
		if($channel){
			$o = 'yf_openid';
		}
		$code_config  = C('DX_SMS.'.$code_key);
		if(!empty($param) && !empty($code_key)){
			$replace_params = $code_params =array();
			foreach($code_config['params'] as $val ){
				$code_params[] = '${'.$val.'}';
			}
			foreach($param as $replace_val ){
				$replace_params[] = $replace_val;
			}
			$content = str_replace($code_params,$replace_params,$code_config['content']);
		}elseif(!empty($code_key)){
			$content = $code_config['content'];
		}elseif(empty($code_key) && empty($param) && !empty($site_message)){//发送站内信息
			$content =$site_message;
		}
		\Think\Log::write($code_config['content'] );
		$data = [
			'type' => $type,
			'type_id' => $type_id,
			'code_type' => $code_key,
			'params' => json_encode($param),
			'content' => $content,
			'sendtime' => $sendtime
		];

		if(is_string($origin_id)){
			$send_way = explode('|', $origin_id);
			$rs = M('member')->where(['id' => $member_id])->find();

			if(in_array('wx', $send_way)){
                $data['wx_send'] = 1 + $channel;
                $wx = 0;
                $openidInfo = M('Openid')->where(['member_id' => $member_id, 'type' => 1 + $channel])->find();
                if($sendtime < time() && !empty($openidInfo['openid'])){
                    if(!empty($code_config['wxtemplate'])){
                        foreach ($param['wxtemplate'] as $key => $val) {

                            if ($key !== 'first' && $key !== 'remark') {
                                $WXcode_params[$key] = [
                                    'value' => $val,
                                    'color' => '#173177'
                                ];
                            } else {
                                $WXcode_params[$key] = [
                                    'value' => $val,
                                    'color' => '#000'
                                ];
                            }

                        }
                        if (!empty($param['$url'])) {
                            $url = $param['$url'];
                        } else {
                            $url = 'http://' . DOMAIN . '/'.'?page=orderDetail&order_id='.$type_id;
                        }
                        $jsonData = [
                            'touser'=>$openidInfo['openid'],
                            'template_id'=>$code_config['wxtemplate']['template_id'],
                            'url'=> $url,
                            'topcolor'=>'#FF0000',
                            'data'=>$WXcode_params
                        ];

                        \Think\Log::write('微信发送信息内容=>'.json_encode($jsonData));
                        $returnData = curl_post('https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=' . getAccessToken(), json_encode($jsonData));
                        \Think\Log::write('微信返回内容=>'.json_encode($returnData));
                    }
                }
			}
			if(in_array('sms', $send_way)){
				$data['sms_send'] = 1 + $channel;
				$sms = 0;
				if($sendtime < time()){
                    $smsParam = [];
                    foreach($param as $key => $val) {
                        if (in_array($key, $code_config['params']) && $key !== 'wxtemplate' && $key !== '$url') {
                            // 表示是短信的模板
                            $smsParam[$key] = $param[$key];
                        }
                    }
					$status = smsSend($rs['telephone'], $code_key, $smsParam, $channel);
					if($status == 1){
						$sms = 1;
					}else{
						$sms = 0;
					}
				}

			}
			if(in_array('ios', $send_way)){
				$data['ios_push'] = 1 + $channel;

				if($sendtime < time()){
					$devicetoken = M('MemberDevice')->where(['member_id' => $member_id, 'channel' => $channel])->order('id desc')->getField('device');
					\Think\Log::write('IOS的设备号123为=>'.$devicetoken);
					if(!empty($devicetoken)){
						\Think\Log::write('IOS的设备号为=>'.$devicetoken);
						$dt = [
							'devicetoken' => $devicetoken,
							'msg' => $content,
							'channel' => $channel
						];
					}
				}
			}
		}elseif(is_numeric($origin_id))$data['member_id'] = $origin_id;

		$message_id = M('message')->add($data);

		$msg_id = M('MemberMessage')->add([
			'member_id' => $member_id,
			'message_id' => $message_id,
			'is_sms' => !empty($sms) ? 1 + $channel : 0,
			'is_wx' => !empty($wx) ? 1 + $channel: 0,
		]);

		if(!empty($dt)){
			$dt['msg_id'] = $msg_id;
			getRedis()->rPush(str_replace('.', '', DOMAIN) . '_app_push', json_encode($dt));
		}
		return true;
	}


	/**
	 * 记录活动，商品，众筹，订单更改
	 * @param $type_id 活动，商品，众筹，订单ID
	 * @param $type  类型 （0-活动，1-商品 2-众筹 3-订单）
	 * @param $framework_id  框架ID
	 */
	Protected function  SaveSnapshotLogs($type_id,$type,$framework_id=''){
		$context = [];

		switch($type){
			case 0:
				$context = M('tips')->join('__TIPS_SUB__ ON tips_id=id')->where(['id' => $type_id])->find();
				if(empty($context))return false;
				$context['times'] = M('TipsTimes')->where(['tips_id' => $type_id])->select();
				$context['times']['Piece'] = [];
				if(!empty($context['times'])){
					foreach($context['times'] as $val){
						$context['times']['Piece'] = M('Piece')->where(['type_id'=>$val['tips_id'],'type_times_id'=>$val['id'],'type'=>$type])->select();
					}
				}
				$context['menu'] = M('TipsMenu')->where(['tips_id' => $type_id])->select();

				//环境地址
				$context['space'] = M('space')->where(['id' => $context['space_id']])->find();
				//tip主图
				$main_pic = M('pics')->field('path')->where(['id' => $context['pic_id']])->getField('path');
				if(!empty($main_pic)){
					$context['main_path'] = thumb($main_pic);
				}
				//tip图组
				$context['pics_group'] = M('pics')->field('id,path')->where(['group_id'=>$context['pics_group_id']])->select();
				if(!empty($context['pics_group'])){
					foreach($context['pics_group'] as $key => $row){
						$context['pics_group'][$key]['path'] = thumb($row['path']);
					}
				}else{
					$context['pics_group'] = [];
				}
				//tip环境图
				$context['environment_pics_group'] = M('pics')->field('id,path')->where(['group_id'=>$context['environment_pics_group_id']])->select();
				if(!empty($context['environment_pics_group'])){
					foreach($context['environment_pics_group'] as $key => $row){
						$context['environment_pics_group'][$key]['path'] = thumb($row['path']);
					}
				}else{
					$context['environment_pics_group'] = [];
				}
				//tip菜单图
				$context['menu_pics_group'] = M('pics')->field('id,path')->where(['group_id'=>$context['menu_pics_group_id']])->select();
				if(!empty($context['menu_pics_group'])){
					foreach($context['menu_pics_group'] as $key => $row){
						$context['menu_pics_group'][$key]['path'] = thumb($row['path']);
					}
				}else{
					$context['menu_pics_group'] = [];
				}
				break;
			case 1:
				$context = M('Goods')->join('__GOODS_SUB__ ON goods_id=id')->where(['id' => $type_id])->find();
				if(empty($context))return false;
				$context['attr'] = M('GoodsAttr')->where(['goods_id' => $type_id])->select();
				$context['Piece'] = M('Piece')->where(['type_id'=>$context['id'],'type'=>$type])->select();
				$context['tag'] = M('GoodsTag')->where(['goods_id' => $type_id])->select();
				$context['fight_groups'] = M('GoodsPrice')->where(['goods_id' => $type_id])->select();
				//tip主图
				$main_pic = M('pics')->field('path')->where(['id' => $context['pic_id']])->getField('path');
				if(!empty($main_pic)){
					$context['main_path'] = thumb($main_pic);
				}
				//tip图组
				$context['pics_group'] = M('pics')->field('id,path')->where(['group_id'=>$context['pics_group_id']])->select();
				if(!empty($context['pics_group'])){
					foreach($context['pics_group'] as $key => $row){
						$context['pics_group'][$key]['path'] = thumb($row['path']);
					}
				}else{
					$context['pics_group'] = [];
				}
				break;
			case 2:
				$context = M('Raise')->where(['id' => $type_id])->find();
				if(empty($context))return false;
				$context['times'] = M('RaiseTimes')->where(['raise_id' => $type_id])->select();
				$context['times']['Piece'] = [];
				if(!empty($context['times'])){
					foreach($context['times'] as $val){
						$context['times']['Piece'] = M('Piece')->where(['type_id'=>$val['raise_id'],'type_times_id'=>$val['id'],'type'=>$type])->select();
					}
				}
				$context['tag'] = M('RaiseTag')->where(['raise_id' => $type_id])->select();
				break;
			case 3:
				$context = M('Order')->where(['id' => $type_id])->find();
				if(!empty($context)){
					$context['Wares'] = M('OrderWares')->where(['order_id' => $type_id])->select();
					$context['OrderPay'] = M('OrderPay')->where(['order_id' => $type_id])->select();
					$context['OrderRefund'] = M('OrderRefund')->where(['order_id' => $type_id])->find();
					switch($context['Wares'][0]['type']){
						case 0:
							$context['tips'] = M('tips')->join('__TIPS_SUB__ ON tips_id=id')->where(['id' => $context['Wares'][0]['ware_id']])->find();
							if(empty($context['tips']))return false;
							$context['tips']['times'] = M('TipsTimes')->where(['id' => $context['Wares'][0]['tips_times_id']])->find();
							$context['tips']['times']['Piece'] = [];
							if(!empty($context['tips']['times'])){
								$context['tips']['times']['Piece'] = M('Piece')->where(['type_id'=>$context['tips']['times']['tips_id'],'type_times_id'=>$context['tips']['times']['id'],'type'=>0])->select();
							}
							$context['tips']['menu'] = M('TipsMenu')->where(['tips_id' => $context['Wares'][0]['ware_id']])->select();

							//环境地址
							$context['tips']['space'] = M('space')->where(['id' => $context['tips']['space_id']])->find();
							//tip主图
							$main_pic = M('pics')->field('path')->where(['id' => $context['tips']['pic_id']])->getField('path');
							if(!empty($main_pic)){
								$context['tips']['main_path'] = thumb($main_pic);
							}
							//tip图组
							$context['tips']['pics_group'] = M('pics')->field('id,path')->where(['group_id'=>$context['tips']['pics_group_id']])->select();
							if(!empty($context['tips']['pics_group'])){
								foreach($context['tips']['pics_group'] as $key => $row){
									$context['tips']['pics_group'][$key]['path'] = thumb($row['path']);
								}
							}else{
								$context['tips']['pics_group'] = [];
							}
							//tip环境图
							$context['tips']['environment_pics_group'] = M('pics')->field('id,path')->where(['group_id'=>$context['tips']['environment_pics_group_id']])->select();
							if(!empty($context['tips']['environment_pics_group'])){
								foreach($context['tips']['environment_pics_group'] as $key => $row){
									$context['tips']['environment_pics_group'][$key]['path'] = thumb($row['path']);
								}
							}else{
								$context['tips']['environment_pics_group'] = [];
							}
							//tip菜单图
							$context['tips']['menu_pics_group'] = M('pics')->field('id,path')->where(['group_id'=>$context['tips']['menu_pics_group_id']])->select();
							if(!empty($context['tips']['menu_pics_group'])){
								foreach($context['tips']['menu_pics_group'] as $key => $row){
									$context['tips']['menu_pics_group'][$key]['path'] = thumb($row['path']);
								}
							}else{
								$context['tips']['menu_pics_group'] = [];
							}
							break;
						case 1:
							$context['goods'] = M('Goods')->join('__GOODS_SUB__ ON goods_id=id')->where(['id' => $context['Wares'][0]['ware_id']])->find();
							if(empty($context['goods']))return false;
							$context['goods']['attr'] = M('GoodsAttr')->where(['goods_id' => $context['Wares'][0]['ware_id']])->select();
							$context['goods']['Piece'] = M('Piece')->where(['type_id'=>$context['id'],'type'=>1])->select();
							$context['goods']['tag'] = M('GoodsTag')->where(['goods_id' => $context['Wares'][0]['ware_id']])->select();
							$context['goods']['fight_groups'] = M('GoodsPiece')->where(['goods_id' => $context['Wares'][0]['ware_id']])->find();

							//goods主图
							$main_pic = M('pics')->field('path')->where(['id' => $context['goods']['pic_id']])->getField('path');
							if(!empty($main_pic)){
								$context['main_path'] = thumb($main_pic);
							}
							//goods图组
							$context['pics_group'] = M('pics')->field('id,path')->where(['group_id'=>$context['goods']['pics_group_id']])->select();
							if(!empty($context['pics_group'])){
								foreach($context['pics_group'] as $key => $row){
									$context['pics_group'][$key]['path'] = thumb($row['path']);
								}
							}else{
								$context['pics_group'] = [];
							}
							break;
						case 2:
							$context['raise'] = M('Raise')->where(['id' => $context['Wares'][0]['ware_id']])->find();
							if(empty($context['raise']))return false;
							$context['raise']['times'] = M('RaiseTimes')->where(['id' => $context['Wares'][0]['tips_times_id']])->find();
							$context['raise']['times']['times']['Piece'] = [];
							if(!empty($context['raise']['times'])){
								$context['raise']['times']['Piece'] = M('Piece')->where(['type_id'=>$context['raise']['times']['raise_id'],'type_times_id'=>$context['raise']['times']['id'],'type'=>2])->select();
							}
							$context['raise']['tag'] = M('RaiseTag')->where(['raise_id' => $context['Wares'][0]['ware_id']])->select();
							//raise主图
							$main_pic = M('pics')->field('path')->where(['id' => $context['raise']['pic_id']])->getField('path');
							if(!empty($main_pic)){
								$context['main_path'] = thumb($main_pic);
							}
							break;
					}
				}else{
					return false;
				}
				break;
		}

		$data = [
			'type_id' => $type_id,
			'framework_id' => $framework_id?$framework_id:null,
			'type' => $type,
			'context' => json_encode($context),
		];
		M('SnapshotLogs')->add($data);

	}

	//检查订单是否超时,每1分钟自动执行一次
	/*Public function order(){
		//查询出所有未支付订单
		$rs = M('order')->where(['act_status' => 0, 'status' => 1])->select();
		$log = [];

		foreach($rs as $row){
			//判断订单是否超时
            $orderWares = M('OrderWares')->where(['order_id' => $row['id']])->select();
			if(empty($orderWares))continue;

            //如果是活动，则找到该活动的结束时间
            if($orderWares[0]['type'] == 0){
                $tips_end_time = M('tips_times')->where(['id'=>$orderWares[0]['tips_times_id']])->getField('end_time')?:0;
				$tips = M('tips')->field(['limit_time'])->where(['id' => $orderWares[0]['ware_id']])->find();
				$limit_time = !empty($tips)?$tips['limit_time']:0;
            }elseif($orderWares[0]['type'] == 1){
                $tips_end_time = 0;
				$limit_time = M('goods')->where(['id' => $orderWares[0]['ware_id']])->getField('limit_time');
            }elseif($orderWares[0]['type'] == 2){
				$raise_time = M('raise')->where(['id'=>$orderWares[0]['ware_id']])->find();
				$tips_end_time = !empty($raise_time)?$raise_time['end_time']:0;

				if(!empty($row['order_pid'])){
					$limit_time = time();
				}else{
					$limit_time = !empty($raise_time)?$raise_time['limit_time']:0;

				}
			}
			if(!empty($limit_time))$limit_time += $row['create_time'];
			$log[] = "关闭超时订单限制时间:ID:{$row['id']} =>".$limit_time;

            //如果该活动已经结束，关闭该活动的未支付订单,或支付超时订单
			if(($orderWares[0]['type'] == 0 && $tips_end_time < time()) || ($limit_time > 0 && $limit_time < time())){
				$order = $row;
				$order_id = $row['id'];

				//若有消耗折扣,将折扣恢复
				foreach($orderWares as $r){
					if(is_numeric($r['marketing_id'])){
						M('marketing')->where(['id' => $r['marketing_id']])->setInc('num');
					}
				}
				//若有使用优惠券,将优惠券还原
				if(is_numeric($order['member_coupon_id'])){
					M('MemberCoupon')->where(['id' => $order['member_coupon_id']])->save(['used_time' => 0]);
				}
				//将订单设置为关闭

				if(empty($row['order_pid']) && $orderWares[0]['type'] != 2) {
					M('order')->where(['id' => $order_id])->save(['status' => 2]);
				}
				//恢复库存
				if($limit_time > 0){
					$num = count($orderWares);
					if($orderWares[0]['type'] == 0){
						//判断是否为包场活动
						if($order['is_book']){
							M('TipsTimes')->where(['id' => $orderWares[0]['tips_times_id']])->setField('stock', ['exp', 'max_num']);
						}else{
							M('TipsTimes')->where(['id' => $orderWares[0]['tips_times_id']])->setInc('stock', $num);
						}
					}elseif($orderWares[0]['type'] == 1){
						M('goods')->where(['id' => $orderWares[0]['ware_id']])->setInc('stocks', $num);
					}elseif($orderWares[0]['type'] == 2){
						//二次订单超时，释放库存之外，还要把预付款的状态改变(后台操作退款时才释放库存)
						if(!empty($row['order_pid'])){
//							$order_price =  M('Order')->where(['id'=>$row['order_pid']])->getField('price');
//							$pid_refund = [
//								'money' => $order_price,
//								'order_id' => $row['order_pid'],
//							];
//							M('Order')->where(['id'=>$row['order_pid']])->save(['act_status'=>5]);
//							$refund_id = M('OrderRefund')->data($pid_refund)->add();
//							$log[] = M('OrderRefund')->getLastSql();
//							$log[] = "关闭超时订单:ID:{$row['id']}，父ID：{$row['order_pid']}订单号：{$row['sn']} => " . date('Y/m/d H:i:s')."生成退款的ID{$refund_id}";
							$log[] = "关闭超时订单:ID:{$row['id']}，父ID：{$row['order_pid']}订单号：{$row['sn']} => " . date('Y/m/d H:i:s');
						}else{
							M('raise_times')->where(['id' => $orderWares[0]['tips_times_id']])->setInc('stock', $num);
						}
					}
				}
				$log[] = "关闭超时订单:ID:{$row['id']}--{$row['sn']} => " . date('Y/m/d H:i:s');
			}
		}
		//活动截止购买后判断是否成局,并发送短信
		$rs = D('TipsView')->where(['is_finish' => 0, 'stop_buy_time' => ['LT', time()-60]])->select();
		if(!empty($rs)){
			foreach($rs as $row){
				//发送的客服微信号
				$serv = C('WX_SERVICE');
				$wx = isset($serv[$row['citys_id']]) ? $serv[$row['citys_id']] : 'yami194';
				$log[] ='wx=>'.$wx;

				//获取该期活动的购买数
				$count = D('OrderTipsView')->where(['tips_times_id' => $row['times_id'], 'act_status' => 1, 'status' => 1])->count();
//				$count = M('Order')->join('__ORDER_WARE__ AS ow ow.order_id = __ORDER__.id')->where(['tips_times_id' => $row['times_id'], 'act_status' => 1, 'status' => 1])-count();
				$log[]= $count;
				$log[]= D('OrderTipsView')->getLastSql();
				if($count == 0){
					//无人购买
					//将该期设置为未成局状态
					M('TipsTimes')->where(['id' => $row['times_id']])->save(['is_finish' => 2]);
					$log[] = "设置未成局活动期数:{$row['id']}-{$row['times_id']} => " . date('Y/m/d H:i:s');
				}elseif($row['min_num'] > $count){
					//未成局
					$order_rs = D('OrderTipsView')->field(['order_id', 'member_id', 'total', 'channel', 'title', 'city_id'])->where(['tips_times_id' => $row['times_id'], 'act_status' => 1, 'status' => 1])->group('A.id')->select();
					$log[]= D('OrderTipsView')->getLastSql();
					foreach($order_rs as $_row){
						//退款订单
						M('order')->where(['id' => $_row['order_id']])->save(['act_status' => 8, 'status' => 2]);
						M('OrderRefund')->add(['order_id' => $_row['order_id'], 'money' => $_row['total'], 'type' => 3]);
						//发消息通知用户
//						if(in_array($_row['channel'], [7,8,9])){
//							$channel = 1;
//							$context = "非常抱歉通知您，您购买的“{$row['title']}”由于未达到成局人数，默认不成局，我有饭会在3个工作日内为您退款。我有饭客服微信号：{$wx}";
//						}else{
//							$channel = 0;
//							$context = "非常抱歉通知您，您购买的“{$row['title']}”由于未达到成局人数，默认不成局，吖咪会在3个工作日内为您退款。客服微信号：{$wx}";
//						}
//						$this->pushMessage($_row['member_id'], $context, 'sms', 3, $_row['order_id'], 0, $channel);

						//2016-12-27
						if(in_array($_row['channel'], [7,8,9])){
							$channel = 1;
							$params = array(
								'title' =>$row['title'],
								'platform' =>'我有饭',
								'wx_number' =>$wx,
							);
						}else{
							$channel = 0;
							$params = array(
								'title' =>$row['title'],
								'platform' =>'吖咪',
								'wx_number' =>$wx,
							);
						}
						$this->push_Message($_row['member_id'], $params,'SMS_36125026', 'sms',null, 3, $_row['order_id'], 0, $channel);

						$log[] = "退款未成局订单:{$_row['order_id']} => " . date('Y/m/d H:i:s');
					}
					//将该期设置为未成局状态
					M('TipsTimes')->where(['id' => $row['times_id']])->save(['is_finish' => 2]);
					//发消息通知host
//					if($wx != 'yami194'){
//						$channel = 1;
//						$context = "非常抱歉通知您，您发布的“{$row['title']}”由于未达到成局人数，默认不成局，我有饭会在3个工作日内退款给食客。我有饭客服微信号：{$wx}";
//					}else{
//						$channel = 0;
//						$context = "非常抱歉通知您，您发布的“{$row['title']}”由于未达到成局人数，默认不成局，吖咪会在3个工作日内退款给食客。客服微信号：{$wx}";
//					}
//					$this->pushMessage($row['member_id'], $context, 'sms', 4, $row['id'], 0, $channel);

					//2016-12-27
					if($wx != 'yami194'){
						$channel = 1;
						$params = array(
							'title' =>$row['title'],
							'wx_number' =>$wx,
							'platform' =>'我有饭',
						);
					}else{
						$channel = 0;
						$params = array(
							'title' =>$row['title'],
							'wx_number' =>$wx,
							'platform' =>'吖咪',
						);
					}
					$this->push_Message($row['member_id'], $params,'SMS_36055220', 'sms',null, 4, $row['id'], 0, $channel);

					$log[] = "设置未成局活动期数:{$row['id']}-{$row['times_id']} => " . date('Y/m/d H:i:s');
				}else{
					//已成局
					$citys = D('CityView')->where(['area_id' => $row['city_id']])->find();
					$address = $citys['city_name'] . $citys['city_alt'] . $citys['area_name'] . $citys['area_alt'] . $row['address'];
					$order_rs = D('OrderTipsView')->field(['order_id', 'member_id', 'total', 'channel', 'title', 'city_id'])->where(['tips_times_id' => $row['times_id'], 'act_status' => 1, 'status' => 1])->group('A.id')->select();
					$host_phone = M('MemberInfo')->where(['member_id' => $row['member_id']])->getField('contact');
					foreach($order_rs as $_row){
						//发消息通知用户
//						if(in_array($_row['channel'], [7,8,9])){
//							$channel = 1;
//							$context = "您购买的『{$row['title']}』已成局，活动时间" . date($row['start_time'], 'Y-m-d H:i') . "，地址：{$address}，host的电话：{$host_phone}，如有疑问请与host联系。我有饭客服微信：{$wx}";
//						}else{
//							$channel = 0;
//							$context = "您购买的『{$row['title']}』已成局，活动时间" . date($row['start_time'], 'Y-m-d H:i') . "，地址：{$address}，主人联系电话：{$host_phone}，如有疑问请与主人联系。客服微信：{$wx}";
//						}
//						$this->pushMessage($_row['member_id'], $context, 'sms', 3, $_row['order_id'], 0, $channel);

						//2016-12-27
						if(in_array($_row['channel'], [7,8,9])){
							$channel = 1;
							$params = [
								'title' => $row['title'],
								'datetime' => date('Y-m-d H:i', $row['start_time']),
								'address' => $address,
								'platform_member_1'=>'Host',
								'telephone' => $host_phone,
								'platform_member'=>'Host',
								'wx_number' => $wx,
							];
//							$context = "您购买的『{$row['title']}』已成局，活动时间" . date($row['start_time'], 'Y-m-d H:i') . "，地址：{$address}，host的电话：{$host_phone}，如有疑问请与host联系。我有饭客服微信：{$wx}";
						}else{
							$channel = 0;
							$params = [
								'title' => $row['title'],
								'datetime' => date('Y-m-d H:i', $row['start_time']),
								'address' => $address,
								'platform_member_1'=>'主人',
								'telephone' => $host_phone,
								'platform_member'=>'主人',
								'wx_number' => $wx,
							];
//							$context = "您购买的『{$row['title']}』已成局，活动时间" . date($row['start_time'], 'Y-m-d H:i') . "，地址：{$address}，主人联系电话：{$host_phone}，如有疑问请与主人联系。客服微信：{$wx}";
						}
						$this->push_Message($_row['member_id'], $params,'SMS_36320171', 'sms',null, 3, $_row['order_id'], 0, $channel);


						$log[] = "发送成局短信:{$_row['order_id']} => " . date('Y/m/d H:i:s');
					}
					//将该期设置为已成局状态
					M('TipsTimes')->where(['id' => $row['times_id']])->save(['is_finish' => 1]);
					//发消息通知host
//					if($wx != 'yami194'){
//						$channel = 1;
//						$context = "您发布的“{$row['title']}”已成局，活动时间：" . date($row['start_time'], 'Y-m-d H:i') . "，已有 {$count} 位报名，请提前准备好迎接食客哦。我有饭客服微信号：{$wx}";
//					}else{
//						$channel = 0;
//						$context = "您发布的“{$row['title']}”已成局，活动时间：" . date($row['start_time'], 'Y-m-d H:i') . "，已有 {$count} 位报名，请提前准备好迎接食客哦。客服微信号：{$wx}";
//					}
//					$this->pushMessage($row['member_id'], $context, 'sms', 4, $row['id'], 0, $channel);

					//2016-12-27
					if($wx != 'yami194'){
						$channel = 1;
						$params = array(
							'title' =>$row['title'],
							'time' =>date ('Y-m-d H:i',$row['start_time']),
							'numberofpeople' =>$count,
							'platform' =>'我有饭',
							'wx_number' =>$wx,
						);
						//$context = "您发布的“{$row['title']}”已成局，活动时间：" . date($row['start_time'], 'Y-m-d H:i') . "，已有 {$count} 位报名，请提前准备好迎接食客哦。我有饭客服微信号：{$wx}";
					}else{
						$channel = 0;
						$params = array(
							'title' =>$row['title'],
							'time' =>date ('Y-m-d H:i',$row['start_time']),
							'numberofpeople' =>$count,
							'platform' =>'吖咪',
							'wx_number' =>$wx,
						);
						//$context = "您发布的“{$row['title']}”已成局，活动时间：" . date($row['start_time'], 'Y-m-d H:i') . "，已有 {$count} 位报名，请提前准备好迎接食客哦。客服微信号：{$wx}";
					}
					$this->push_Message($row['member_id'], $params,'SMS_35955013', 'sms',null, 4, $row['id'], 0, $channel);
					$log[] = "设置成局活动期数:{$row['id']}-{$row['times_id']} => " . date('Y/m/d H:i:s');
				}
			}
		}

		//活动结束后，已支付订单自动验票
		$ids = D('CleanOrderWareView')->where(['act_status'=>1,'end_time'=>['LT',time()],'type'=>0])->group('A.id')->getField('order_id', true);
		if(!empty($ids)){
			M('Order')->where(['id'=>['IN',join(',',$ids)]])->data(['act_status'=>2])->save();
			M('OrderWares')->where(['order_id'=>['IN',join(',',$ids)]])->data(['server_status'=>1])->save();
			//延迟推送消息
			foreach($ids as $row){
				$result = D('OrderTipsView')->where(['A.id'=>$row])->find();
				if(!empty($result)){

					//2016-12-27
					$params = array(
						'title' => $result['title'],
					);
					if(in_array($result['channel'], [7,8,9])){
						$channel = 1;
					}else{
						$channel = 0;
					}
					$this->push_Message($result['member_id'], $params,'SMS_35720209', 'sms',null, 3, $result['order_id'], $result['end_time'] + 3600, $channel);
					$log[] = "自动验票订单:{$result['sn']} => " . date('Y/m/d H:i:s');
				}
			}
		}

		//众筹结束后，发送短信给用户
//		$raise_ids = D('RaiseView')->where(['status'=>1,'end_time'=>['LT',time()]])->group('B.id')->getField('times_id', true);
//		if(!empty($raise_ids)){
//			$result_1 = D('OrderRaiseView')->where(['A.id'=>$row,'tips_times_id'=>['IN',$raise_ids],'type'=>2,'act_status'=>['IN','1,2,3,4']])->group('B.id')->select();
//			foreach($result_1 as $row){
////				$context = "您支持的众筹项目《始于1880年的传奇茶楼 首次众筹》认筹金额已达成目标，项目众筹成功。谢谢您的支持，也希望您能把这个项目告诉更多人。客服微信：yami194（工作时间：9:00-20:00）有问题随时保持联络！";
////				$this->pushMessage($row['member_id'], $context,'SMS_36125296', 'sms', 3, $row['order_id'], $row['end_time'] + 300, 0);
//				//2016-12-27
//				$params=array(
//					'project_name' => '众筹',
//					'title' => $row['title'],
//					'project_name_1' => '众筹',
//					'wx' => 'yami194'
//				);
//				$this->push_Message($row['member_id'],$params,'SMS_36620003', 'sms',null, 3, $row['order_id'], $row['end_time'] + 300, 0);
//				$log[] = "众筹结束，给用户发送短信:{$result_1['member_id']}-{$result_1['order_id']} => " . date('Y/m/d H:i:s');
//			}
//
//		}


		if(!empty($log))echo join("\n", $log) . "\n";
	}
	*/

	Public function goodsOrder() {
        $where['A.act_status'] = ['EQ', 0];
        $where['B.type'] = ['EQ', 1];
        $where['B.server_status'] = ['EQ', 0];
        $where['A.limit_pay_time'] = ['LT', time()];
        $goods_orders = D('GoodsView')->where($where)->group('B.ware_id')->select();
        echo "\n商品订单sql语句： " . D('GoodsView')->getLastSql();

        // 未支付订单，对商品的库存进行恢复
        forEach($goods_orders as $good) {
            M('Goods')->where(['id' => $good['ware_id']])->setInc('stocks', $good['total_count']);
        }
        D('GoodsView')->where($where)->save(['B.server_status' => 1]); // 修改为服务状态

        // 未成团的订单，进行修改，并恢复商品库存、退款处理F
        $unSuccessPiece = D('OrderPieceView')->where(['F.act_status' => 8, 'D.type' => 1, 'F.status' => 1])->group('F.id')->select();
        echo "\n未成团sql: " . D('OrderPieceView')->getLastSql();

        $temp = [];
        forEach($unSuccessPiece as $unSuccessItem) {
            // 设置订单未退款状态，并添加到退款列表中
            echo "\n" . $unSuccessItem['ware_id'];
            if (!empty($temp[$unSuccessItem['ware_id']])) {
                $temp[$unSuccessItem['ware_id']] += 1;
            } else {
                $temp[$unSuccessItem['ware_id']] = 1;
            }
            // 对已付款订单设置为申请退款状态
            M('order')->where(['id' => $unSuccessItem['order_id']])->save(['act_status' => 5, 'status' => 1]);
            $old = M('OrderRefund')->where(['order_id' => $unSuccessItem['order_id']])->find();

            if (empty($old)) {
                M('OrderRefund')->add(['order_id' => $unSuccessItem['order_id'], 'money' => $unSuccessItem['total'], 'type' => 3, 'cause' => '拼团不成功']);
            }
        }
        unset($unSuccessItem);

        forEach($temp as $key => $val) {
            M('Goods')->where(['id' => $key])->setInc('stocks', $val);
        }
        echo 'unsuccess: ' . json_encode($temp);
    }


    /**
     * 发送拼团信息
     * @param $piece_originator_id 团id
     * @param $piece_id 拼团id
     * @param $piece 拼团数据
     * @param $type 类型 1 -- 成功， 0 -- 不成功
     */
    private function sendPieceInfo($piece_originator_id, $piece_id, $piece, $type) {
        if ($type === 1) {
            $act_status = [1,2,3,4];
        } else {
            $act_status = [1,2,3,4,5,8];
        }
        $m = new \Order\Model\OrderPieceViewModel;
        $members = $m->where(['A.id' => $piece_originator_id, 'B.status' => 1, 'B.id' => $piece_id, 'D.status' => 1, 'D.act_status' => ['IN', $act_status]])->field('order_member_id,member_id')->select();
        if ($piece['type'] == 1) {
            // 商品
            $title = M('Goods')->where(['id' => $piece['type_id']])->getField('title');
        } else {
            // 活动
//            $title = M('Tips')
            $title = '活动';
        }

        echo '团员sql: ' .$m->getLastSql() . "\n";
        echo '团员信息：' .json_encode($members) . "\n";

        $params = [
            'title' => $title
        ];
        $smsCode = $type === 1 ? 'SMS_99115059' : 'SMS_99120060'; // 团长短信
        $smsCodeOther  = $type === 1 ? 'SMS_99590011': 'SMS_99130060'; // 团员短信
        foreach ($members as $mem) {
            if ($mem['member_id'] !== $mem['order_member_id']) {
                // 非团长
                $this->push_Message($mem['order_member_id'], $params, $smsCodeOther, 'wx|sms|ios', null, 3, $mem['order_member_id'], 0, 0);
            } else {
                // 团长
                $this->push_Message($mem['order_member_id'], $params, $smsCode, 'wx|sms|ios', null, 3, $mem['order_member_id'], 0, 0);
            }
        }
    }

    public function testSendMessage() {
        $param = [
            'title' => 'test',
            'time' => '1',
            'num' => '1'
        ];
        $this->push_Message(11008, $param, 'SMS_105075029', 'sms', null, 3, 19, 0, 0);
    }


    public function pieceOrderForLimitTime() {
        $curTime = time();
        // 查找剩3小时结束的开团
        $piece_rs = D('OrderPieceGoodsView')->where(['A.act_status' => 1, 'A.status' => 1, 'A.end_time' => ['BETWEEN', ($curTime + 10800 - 30) . ',' . ($curTime + 10800 + 30)], 'F.act_status' => ['IN', [1,2,3,4]]])->group('A.id')->select();

        foreach ($piece_rs as $row) {
            $param = [
                'title' => $row['title'],
                'num' => $row['piece_count'] - $row['order_count'],
                'wxtemplate' => [
                    'first' => '您的匹配商品名拼团还有3小时就要结束啦！还差' . ($row['piece_count'] - $row['order_count']) . '人拼团成功',
                    'keyword1' => $row['nickname'],
                    'keyword2' => $row['title'],
                    'keyword3' => $row['piece_price'],
                    'remark' => '快点击进入拼单页面，分享给好友一起拼团吧！>>'
                ],
                '$url' => 'http://' . DOMAIN . '?page=groupsDetail&groups_id=' . $row['piece_originator_id']
            ];

            $this->push_Message($row['piece_member_id'], $param, 'SMS_105075029', 'sms|wx|ios', null, 3, $row['type_id'], 0, 0);
        }
    }

    public function pieceOrder() {
        //拼团时间截止购买后修改拼团状态
        $piece_rs = M('MemberPiece')->where(['act_status'=>1,'status'=>1,'end_time'=>['LT',time()-2*60]])->select();
        \Think\Log::write('开团信息：'.M('MemberPiece')->getLastSql());
        echo '开团信息：'.M('MemberPiece')->getLastSql() . "\n";
//		\Think\Log::write('开团信息：'.json_encode($piece_rs));
        if(!empty($piece_rs)){
            foreach($piece_rs as $val){
                $mm = new \Member\Model\PieceViewModel;
                $piece = $mm->where(['id'=>$val['piece_id'],'status'=>1])->find();
                \Think\Log::write('开团信息$piece：'.json_encode($piece));
                if(!empty($piece)){
                    $m = new \Order\Model\OrderPieceViewModel;
                    $where['A.id'] = ['EQ', $val['id']];
                    $where['D.status'] = ['EQ', 1];
                    $where['D.act_status'] = ['IN', [1,2,3,4,5]];
                    $order_num =$m->where($where)->count();
                    echo '拼团活动SQL：'.$m->getLastSql() . "\n";
                    \Think\Log::write( '拼团活动SQL：'.$m->getLastSql());
                    \Think\Log::write('拼团活动Order_id：'.json_encode($order_num));
                    if($piece['count']<=$order_num && $piece['is_cap']==0){//上不封顶
                        M('MemberPiece')->where(['id'=>$val['id']])->save(['act_status'=>2]);
                        $this->sendPieceInfo($val['id'], $val['piece_id'], $piece, 1);
                    }elseif($piece['count']<=$order_num && $piece['is_cap']==1){//封顶,已成团
                        M('MemberPiece')->where(['id'=>$val['id']])->save(['act_status'=>3]);
                        $this->sendPieceInfo($val['id'], $val['piece_id'], $piece, 1);
                    }else{
                        // 不成团
                        M('MemberPiece')->where(['id'=>$val['id']])->save(['act_status'=>8,'status'=>2]);
                        $order_ids = M('OrderPiece')->where(['piece_originator_id'=>$val['id']])->getField('order_id',true);
                        if(!empty($order_ids)){
                            M('Order')->where(['id'=>['IN',join(',',$order_ids)], 'act_status' => ['IN', [1,2,3,4]]])->save(['act_status'=>8,'status'=>1]);
                        }
                        $this->sendPieceInfo($val['id'], $val['piece_id'], $piece, 0);
                    }
                }else{
                    M('MemberPiece')->where(['id'=>$val['id']])->save(['act_status'=>8,'status'=>2]);
                    $order_ids = M('OrderPiece')->where(['piece_originator_id'=>$val['id']])->getField('order_id',true);
                    if(!empty($order_ids)){
                        M('Order')->where(['id'=>['IN',join(',',$order_ids)], 'act_status' => ['IN', [1,2,3,4]]])->save(['act_status'=>8,'status'=>1]);
                    }
                    $this->sendPieceInfo($val['id'], $val['piece_id'], $piece, 0);
                }
            }
        }

    }

	//检查订单是否超时,每1分钟自动执行一次
	Public function order(){
	    echo 'order定时任务开始';
		//查询出所有未支付订单
		$rs = M('order')->where(['act_status' => 0, 'status' => 1])->select();
		$log = [];

		foreach($rs as $row){
			//判断订单是否超时
			$orderWares = M('OrderWares')->where(['order_id' => $row['id']])->select();
			if(empty($orderWares))continue;

			//如果是活动，则找到该活动的结束时间
			if($orderWares[0]['type'] == 0){
				$tips_end_time = M('tips_times')->where(['id'=>$orderWares[0]['tips_times_id']])->getField('end_time')?:0;
				$tips = M('tips')->field(['limit_time'])->where(['id' => $orderWares[0]['ware_id']])->find();
				$limit_time = !empty($row['limit_pay_time'])?$row['limit_pay_time']:0;
			}elseif($orderWares[0]['type'] == 1){
				$tips_end_time = 0;
//				$limit_time = M('goods')->where(['id' => $orderWares[0]['ware_id']])->getField('limit_time');
				$limit_time = !empty($row['limit_pay_time'])?$row['limit_pay_time']:0;
			}elseif($orderWares[0]['type'] == 2){
				$raise_time = M('raise')->where(['id'=>$orderWares[0]['ware_id']])->find();
				$tips_end_time = !empty($raise_time)?$raise_time['end_time']:0;

//				if(!empty($row['order_pid'])){
//					$limit_time = time()+3600;
//				}else{
					$limit_time = !empty($row['limit_pay_time'])?$row['limit_pay_time']:0;

//				}
			}
//			if(!empty($limit_time))$limit_time += $row['create_time'];
			$log[] = "关闭超时订单限制时间:ID:{$row['id']} =>".$limit_time;
			$log[] = "关闭超时订单活动限制购买时间:ID:{$row['id']} =>".$tips_end_time;

			if ($orderWares[0]['type'] == 1) {
			    echo '订单号:' . $row['id'];
            }

			//如果该活动已经结束，关闭该活动的未支付订单,或支付超时订单
			if(($orderWares[0]['type'] == 0 && $tips_end_time < time()) || ($limit_time > 0 && $limit_time < time())){
				$order = $row;
				$order_id = $row['id'];

				//若有消耗折扣,将折扣恢复
				foreach($orderWares as $r){
					if(is_numeric($r['marketing_id'])){
						M('marketing')->where(['id' => $r['marketing_id']])->setInc('num');
					}
				}
				//若有使用优惠券,将优惠券还原
				if(is_numeric($order['member_coupon_id'])){
					M('MemberCoupon')->where(['id' => $order['member_coupon_id']])->save(['used_time' => 0]);
				}
				//特权过期
				//$find = M('MemberPrivilege')->where(['order_id'=>$order_id])->find();
				//if(!empty($find)){
				//	M()->execute("Update __MEMBER_PRIVILEGE__ set `order_id`=null,`status`=1 where `id`='{$find['id']}'");
					
				//}
				//拼团过期
//				$piece_originator_id = M('OrderPiece')->where(['order_id'=>$order_id])->getField('piece_originator_id');
//				if(!empty($piece_originator_id)){
//					M()->execute("Update __MEMBER_PIECE__ set `status`=2,`act_status`=8 where `id`='{$piece_originator_id}'");
//				}

				//将订单设置为关闭
//				if(empty($row['order_pid'])) {
					M('order')->where(['id' => $order_id])->save(['status' => 2]);
					//记录订单修改快照信息
					$this->SaveSnapshotLogs($order_id,3);
//				}
				//恢复库存
				if($limit_time > 0){
					$num = count($orderWares);
					if($orderWares[0]['type'] == 0){

						$tips_tags = M('TipsTag')->where(['tips_id' => $orderWares[0]['ware_id']])->getField('tag_id', true);
						//当活动为预约制活动，不减库存
						if(in_array(76,$tips_tags) == false) {
							//判断是否为包场活动
							if ($order['is_book']) {
								M('TipsTimes')->where(['id' => $orderWares[0]['tips_times_id']])->setField('stock', ['exp', 'max_num']);
							} else {
								M('TipsTimes')->where(['id' => $orderWares[0]['tips_times_id']])->setInc('stock', $num);
							}
						}
						//记录活动修改快照信息
						$this->SaveSnapshotLogs($orderWares[0]['ware_id'],0);
					}elseif($orderWares[0]['type'] == 1){
					    echo '商品减库存';
						M('goods')->where(['id' => $orderWares[0]['ware_id']])->setInc('stocks', $num);
						//记录商品修改快照信息
						$this->SaveSnapshotLogs($orderWares[0]['ware_id'],1);
					}elseif($orderWares[0]['type'] == 2){
						//检查特权
						$find = M('MemberPrivilege')->where(['order_id'=>$order_id])->find();
						if(!empty($find)){
							$privilege_id = $find['privilege_id'];
							$privilege_info = M('Privilege')->where(['id'=>$privilege_id])->find();
							if($privilege_info['number']>=0){
								M('Privilege')->where(['id'=>$privilege_id])->setInc('number', $num);
							}
							\Think\Log::write('find数据=>'.json_encode($find));
						}//else{
							$stock = M('raise_times')->where(['id' => $orderWares[0]['tips_times_id']])->getField('stock');
							//二次订单超时，不释放库存
							if(!empty($row['order_pid'])){
	//							$order_price =  M('Order')->where(['id'=>$row['order_pid']])->getField('price');
	//							$pid_refund = [
	//								'money' => $order_price,
	//								'order_id' => $row['order_pid'],
	//							];
	//							M('Order')->where(['id'=>$row['order_pid']])->save(['act_status'=>5]);
	//							$refund_id = M('OrderRefund')->data($pid_refund)->add();
	//							$log[] = M('OrderRefund')->getLastSql();
	//							$log[] = "关闭超时订单:ID:{$row['id']}，父ID：{$row['order_pid']}订单号：{$row['sn']} => " . date('Y/m/d H:i:s')."生成退款的ID{$refund_id}";
								$log[] = "关闭超时订单:ID:{$row['id']}，父ID：{$row['order_pid']}订单号：{$row['sn']} => " . date('Y/m/d H:i:s');
							}else{
								if($stock>=0)M('raise_times')->where(['id' => $orderWares[0]['tips_times_id']])->setInc('stock', $num);
								//记录众筹修改快照信息
								$this->SaveSnapshotLogs($orderWares[0]['ware_id'],2);
							}
						//}
					}
				}
				$log[] = "关闭超时订单:ID:{$row['id']}--{$row['sn']} => " . date('Y/m/d H:i:s');
			}
		}

		// 拼团订单管理
		$this->pieceOrder();

		//活动截止购买后判断是否成局,并发送短信
		$rs = D('TipsView')->where(['is_finish' => 0, 'stop_buy_time' => ['LT', time()-60]])->select();
		if(!empty($rs)){
			foreach($rs as $row){
				//发送的客服微信号
				$serv = C('WX_SERVICE');
				$wx = isset($serv[$row['city_id']]) ? $serv[$row['city_id']] : 'yami194';

				//获取该期活动的购买数
				$count = D('OrderTipsView')->where(['tips_times_id' => $row['times_id'], 'act_status' => 1, 'status' => 1])->count();
//				$count = M('Order')->join('__ORDER_WARE__ AS ow ow.order_id = __ORDER__.id')->where(['tips_times_id' => $row['times_id'], 'act_status' => 1, 'status' => 1])-count();
				$log[]= $count;
				if($count == 0){
					//无人购买
					//将该期设置为未成局状态
					M('TipsTimes')->where(['id' => $row['times_id']])->save(['is_finish' => 2]);
					$log[] = "设置未成局活动期数:{$row['id']}-{$row['times_id']} => " . date('Y/m/d H:i:s');
				}elseif($row['min_num'] > $count){
					//未成局
					$order_rs = D('OrderTipsView')->field(['order_id', 'member_id', 'total', 'channel', 'title', 'city_id'])->where(['tips_times_id' => $row['times_id'], 'act_status' => 1, 'status' => 1])->group('A.id')->select();
					foreach($order_rs as $_row){
						//退款订单
						M('order')->where(['id' => $_row['order_id']])->save(['act_status' => 8, 'status' => 2]);
						M('OrderRefund')->add(['order_id' => $_row['order_id'], 'money' => $_row['total'], 'type' => 3]);


						//拼团状态修改
						$piece_originator_id = M('OrderPiece')->where(['order_id'=> $_row['order_id']])->getField('piece_originator_id');
						if(!empty($piece_originator_id)){
							M()->execute("Update __MEMBER_PIECE__ set `status`=10 where `id`='{$piece_originator_id}'");
						}

						//记录订单修改快照信息
						$this->SaveSnapshotLogs($_row['order_id'],3);
						//发消息通知用户
//						if(in_array($_row['channel'], [7,8,9])){
//							$channel = 1;
//							$context = "非常抱歉通知您，您购买的“{$row['title']}”由于未达到成局人数，默认不成局，我有饭会在3个工作日内为您退款。我有饭客服微信号：{$wx}";
//						}else{
//							$channel = 0;
//							$context = "非常抱歉通知您，您购买的“{$row['title']}”由于未达到成局人数，默认不成局，吖咪会在3个工作日内为您退款。客服微信号：{$wx}";
//						}
//						$this->pushMessage($_row['member_id'], $context, 'sms', 3, $_row['order_id'], 0, $channel);

						//2016-12-27
						if(in_array($_row['channel'], [7,8,9])){
							$channel = 1;
							$params = array(
								'title' =>$row['title'],
								'platform' =>'我有饭',
								'wx_number' =>$wx,
							);
						}else{
							$channel = 0;
							$params = array(
								'title' =>$row['title'],
								'platform' =>'吖咪',
								'wx_number' =>$wx,
							);
						}
						$this->push_Message($_row['member_id'], $params,'SMS_36125026', 'sms|ios',null, 3, $_row['order_id'], 0, $channel);

						$log[] = "退款未成局订单:{$_row['order_id']} => " . date('Y/m/d H:i:s');
					}
					//将该期设置为未成局状态
					M('TipsTimes')->where(['id' => $row['times_id']])->save(['is_finish' => 2]);
					//将改分期的所有拼团设置为删除状态
					M('piece')->where(['type_times_id' => $row['times_id']])->save(['status' => 0]);


					//发消息通知host
//					if($wx != 'yami194'){
//						$channel = 1;
//						$context = "非常抱歉通知您，您发布的“{$row['title']}”由于未达到成局人数，默认不成局，我有饭会在3个工作日内退款给食客。我有饭客服微信号：{$wx}";
//					}else{
//						$channel = 0;
//						$context = "非常抱歉通知您，您发布的“{$row['title']}”由于未达到成局人数，默认不成局，吖咪会在3个工作日内退款给食客。客服微信号：{$wx}";
//					}
//					$this->pushMessage($row['member_id'], $context, 'sms', 4, $row['id'], 0, $channel);

					//2016-12-27
					if($wx != 'yami194'){
						$channel = 1;
						$params = array(
							'title' =>$row['title'],
							'wx_number' =>$wx,
							'platform' =>'我有饭',
						);
					}else{
						$channel = 0;
						$params = array(
							'title' =>$row['title'],
							'wx_number' =>$wx,
							'platform' =>'吖咪',
						);
					}
					$this->push_Message($row['member_id'], $params,'SMS_36055220', 'sms|ios',null, 4, $row['id'], 0, $channel);

					$log[] = "设置未成局活动期数:{$row['id']}-{$row['times_id']} => " . date('Y/m/d H:i:s');
				}else{
					//已成局
					$citys = D('CityView')->where(['area_id' => $row['city_id']])->find();
					$address = $citys['city_name'] . $citys['city_alt'] . $citys['area_name'] . $citys['area_alt'] . $row['address'];
					$order_rs = D('OrderTipsView')->field(['order_id', 'member_id', 'total', 'channel', 'title', 'city_id'])->where(['tips_times_id' => $row['times_id'], 'act_status' => 1, 'status' => 1])->group('A.id')->select();
					$host_phone = M('MemberInfo')->where(['member_id' => $row['member_id']])->getField('contact');
					foreach($order_rs as $_row){
						//发消息通知用户
						//2016-12-27
						if(in_array($_row['channel'], [7,8,9])){
							$channel = 1;
							$params = [
								'title' => $row['title'],
								'datetime' => date('Y-m-d H:i', $row['start_time']),
								'address' => $address,
								'platform_member_1'=>'Host',
								'telephone' => $host_phone,
								'platform_member'=>'Host',
								'wx_number' => $wx,
							];
//							$context = "您购买的『{$row['title']}』已成局，活动时间" . date($row['start_time'], 'Y-m-d H:i') . "，地址：{$address}，host的电话：{$host_phone}，如有疑问请与host联系。我有饭客服微信：{$wx}";
						}else{
							$channel = 0;
							$params = [
								'title' => $row['title'],
								'datetime' => date('Y-m-d H:i', $row['start_time']),
								'address' => $address,
								'platform_member_1'=>'主人',
								'telephone' => $host_phone,
								'platform_member'=>'主人',
								'wx_number' => $wx,
							];
//							$context = "您购买的『{$row['title']}』已成局，活动时间" . date($row['start_time'], 'Y-m-d H:i') . "，地址：{$address}，主人联系电话：{$host_phone}，如有疑问请与主人联系。客服微信：{$wx}";
						}
						$this->push_Message($_row['member_id'], $params,'SMS_36320171', 'sms|ios',null, 3, $_row['order_id'], 0, $channel);


						$log[] = "发送成局短信:{$_row['order_id']} => " . date('Y/m/d H:i:s');
					}
					//将该期设置为已成局状态
					M('TipsTimes')->where(['id' => $row['times_id']])->save(['is_finish' => 1]);

					//发消息通知host
//					if($wx != 'yami194'){
//						$channel = 1;
//						$context = "您发布的“{$row['title']}”已成局，活动时间：" . date($row['start_time'], 'Y-m-d H:i') . "，已有 {$count} 位报名，请提前准备好迎接食客哦。我有饭客服微信号：{$wx}";
//					}else{
//						$channel = 0;
//						$context = "您发布的“{$row['title']}”已成局，活动时间：" . date($row['start_time'], 'Y-m-d H:i') . "，已有 {$count} 位报名，请提前准备好迎接食客哦。客服微信号：{$wx}";
//					}
//					$this->pushMessage($row['member_id'], $context, 'sms', 4, $row['id'], 0, $channel);

					//2016-12-27
					if($wx != 'yami194'){
						$channel = 1;
						$params = array(
							'title' =>$row['title'],
							'time' =>date ('Y-m-d H:i',$row['start_time']),
							'numberofpeople' =>$count,
							'platform' =>'我有饭',
							'wx_number' =>$wx,
						);
						//$context = "您发布的“{$row['title']}”已成局，活动时间：" . date($row['start_time'], 'Y-m-d H:i') . "，已有 {$count} 位报名，请提前准备好迎接食客哦。我有饭客服微信号：{$wx}";
					}else{
						$channel = 0;
						$params = array(
							'title' =>$row['title'],
							'time' =>date ('Y-m-d H:i',$row['start_time']),
							'numberofpeople' =>$count,
							'platform' =>'吖咪',
							'wx_number' =>$wx,
						);
						//$context = "您发布的“{$row['title']}”已成局，活动时间：" . date($row['start_time'], 'Y-m-d H:i') . "，已有 {$count} 位报名，请提前准备好迎接食客哦。客服微信号：{$wx}";
					}
					$this->push_Message($row['member_id'], $params,'SMS_35955013', 'sms|ios',null, 4, $row['id'], 0, $channel);
					$log[] = "设置成局活动期数:{$row['id']}-{$row['times_id']} => " . date('Y/m/d H:i:s');
				}

				//记录活动修改快照信息
				$this->SaveSnapshotLogs($row['id'],0);
			}
		}


		//活动结束后，已支付订单自动验票

//		$tip_rs = M('TipsTimes')->where([ 'start_buy_time' => ['LT', time()],'start_time' => ['LT', time()]])->getField('id', true);
//		$log[] = '活动售卖时间进行中，人数已到成局人数，活动开始时间已到，符合这个条件的ID有：'.join(',',$tip_rs).'-->'. date('Y/m/d H:i:s');










		$ids = D('CleanOrderWareView')->where(['act_status'=>1,'end_time'=>['LT',time()],'type'=>0])->group('A.id')->getField('order_id', true);
		if(!empty($ids)){
			M('Order')->where(['id'=>['IN',join(',',$ids)]])->data(['act_status'=>2])->save();
			M('OrderWares')->where(['order_id'=>['IN',join(',',$ids)]])->data(['server_status'=>1])->save();
			//延迟推送消息
			foreach($ids as $row){
				//记录订单修改快照信息
				$this->SaveSnapshotLogs($row,3);
				$result = D('OrderTipsView')->where(['A.id'=>$row])->find();
				if(!empty($result)){

					//2016-12-27
					$params = array(
						'title' => $result['title'],
					);
					if(in_array($result['channel'], [7,8,9])){
						$channel = 1;
					}else{
						$channel = 0;
					}
					//暂时屏蔽掉，出了狂发短信的事故
		//			$this->push_Message($result['member_id'], $params,'SMS_48615069', 'sms|ios',null, 3, $result['order_id'], $result['end_time'] + 3600, $channel);
					$log[] = "自动验票订单:{$result['sn']} => " . date('Y/m/d H:i:s');
				}
			}
		}

		//众筹结束后，发送短信给用户
//		$raise_ids = D('RaiseView')->where(['status'=>1,'end_time'=>['LT',time()]])->group('B.id')->getField('times_id', true);
//		if(!empty($raise_ids)){
//			$result_1 = D('OrderRaiseView')->where(['A.id'=>$row,'tips_times_id'=>['IN',$raise_ids],'type'=>2,'act_status'=>['IN','1,2,3,4']])->group('B.id')->select();
//			foreach($result_1 as $row){
////				$context = "您支持的众筹项目《始于1880年的传奇茶楼 首次众筹》认筹金额已达成目标，项目众筹成功。谢谢您的支持，也希望您能把这个项目告诉更多人。客服微信：yami194（工作时间：9:00-20:00）有问题随时保持联络！";
////				$this->pushMessage($row['member_id'], $context,'SMS_36125296', 'sms', 3, $row['order_id'], $row['end_time'] + 300, 0);
//				//2016-12-27
//				$params=array(
//					'project_name' => '众筹',
//					'title' => $row['title'],
//					'project_name_1' => '众筹',
//					'wx' => 'yami194'
//				);
//				$this->push_Message($row['member_id'],$params,'SMS_39710001', 'sms',null, 3, $row['order_id'], $row['end_time'] + 300, 0);
//				$log[] = "众筹结束，给用户发送短信:{$result_1['member_id']}-{$result_1['order_id']} => " . date('Y/m/d H:i:s');
//			}
//
//		}


		if(!empty($log))echo join("\n", $log) . "\n";
	}

	//检查活动是否结束
	public function checkFinish(){
		$rs = M('TipsTimes')->where(['end_time' => ['LT', time()], 'is_finish' => 0])->save(['is_finish' => 1]);
		if($rs > 0){
			echo "调整 {$rs} 个活动时间段状态为已成局 => " . date('Y/m/d H:i:s') . "\n";
		}
	}

	// 检查众筹离结束还有7天，发送通知
	public function checkRaiseSevenDay() {
	    $g_time = time() + 7 * 86400;
	    $l_time = time() + 6 * 86400;
	    $where['end_time'] = ['between', $l_time . ',' . $g_time];
	    $where['is_finish'] = ['EQ', 0];
        $where['status'] = ['EQ', 1];

	    $rs = M('Raise')->where($where)->select();
	    echo '符合状态的众筹个数为：' . count($rs) . "\n";
	    echo M('Raise')->getLastSql() . "\n";

	    if (!empty($rs)) {
            foreach($rs as $k=>$val){
                $totaled = 0;
                $sum = 0;
                $PieceOrderView = new \Goods\Model\RaiseOrderWaresViewModel();
                $rs_arr = $PieceOrderView->where(['A.type' => 2, 'A.ware_id' => $val['id'], 'B.status' => 1, 'B.act_status' => ['in', '1,2,3,4'],'order_pid'=>['EXP', 'IS NULL']])->group('A.id')->select();
                foreach($rs_arr as $row_a){
                    $totaled += $row_a['raise_times_price'];
                    $sum ++;
                }
                unset($row_a);

                $members = D('OrderRaiseView')->where(['B.ware_id' => $val['id'], 'A.act_status' => 1])->field('member_id')->group('A.member_id')->select();

                $params = [
                    'project_name' => '众筹',
                    'title' => $val['title'],
                    'per' => (string)round($totaled / $val['total'] * 100, 2)
                ];
                foreach ($members as $men) {
                    $this->push_Message($men['member_id'], $params, 'SMS_86130029', 'sms|ios', null, 3, $val['id'], 0, 0);
                }
            }
        }
    }

	//检查众筹是否结束
	public function checkRaiseFinish(){
		$is_finish = 0;
		$rs = M('Raise')->where(['end_time' => ['LT', time()],'is_finish'=>$is_finish])->select();
		echo "SQL=> " . M('Raise')->getLastSql() . "\n";
		if(!empty($rs)){
			foreach($rs as $k=>$val){
				$totaled = 0;
				$sum = 0;
				$PieceOrderView = new \Goods\Model\RaiseOrderWaresViewModel();
				$rs_arr = $PieceOrderView->where(['A.type' => 2, 'A.ware_id' => $val['id'], 'B.status' => 1, 'B.act_status' => ['in', '1,2,3,4'],'order_pid'=>['EXP', 'IS NULL']])->group('A.id')->select();
				foreach($rs_arr as $row_a){
					$totaled += $row_a['raise_times_price'];
					$sum ++;
				}
				unset($row_a);

                // 特殊处理
                if ($val['id'] == 51) {
                    $totaled += 38640;
                    $sum += 280;
                }

                if($val['total'] > $totaled){//众筹不成功
					$is_finish = 2;
					$d = M('Raise')->where(['id' => $val['id']])->data(['buyer_num'=>$sum,'totaled'=>$totaled,'is_finish'=>$is_finish])->save();
				}else{//众筹成功
					$is_finish = 1;
					$d = M('Raise')->where(['id' => $val['id']])->data(['buyer_num'=>$sum,'totaled'=>$totaled,'is_finish'=>$is_finish])->save();
				}

				$members = D('OrderRaiseView')->where(['B.ware_id' => $val['id'], 'A.act_status' => 1])->field('member_id')->group('A.member_id')->select();

                $sms = $is_finish === 2 ? 'SMS_85965005' : 'SMS_85945001';
                $params = $is_finish === 1 ?
                                    [
                                        'project_name' => '众筹',
                                        'project_title' => $val['title'],
                                        'project_action' => '众筹',
                                        'per' => $val['total'] > 0 ? (string)round(($totaled / $val['total']) * 100, 2) : '0'
                                    ] :
                                    [
                                        'project_name' => '众筹',
                                        'project_title' => $val['title'],
                                        'project_action' => '认筹'
                                    ];
				foreach ($members as $men) {
                    $this->push_Message($men['member_id'], $params, $sms, 'sms|ios', null, 3, $val['id'], 0, 0);
                }
                unset($men);

				//记录活动修改快照信息
				$this->SaveSnapshotLogs($val['id'],2);
				if($d > 0){
					echo "众筹ID为{$val['id']}，众筹金额为 {$totaled} ，众筹人数为{$sum}，众筹进度状态改为{$is_finish}=> " . date('Y/m/d H:i:s') . "\n";
				}
			}
		}
	}


	//检查是否有外链图片,有则同步到本地
	public function checkLink(){
		$rs = M('pics')->field(['id', 'path'])->where(['path' => ['like', 'http://%']])->limit(20)->select();
		$log = [];
		if(!empty($rs)){
			foreach($rs as $row){
				$path = getPicAndSave($row['path']);
				if(empty($path)){
					$path = '-' . $row['path'];
					$log[] = '[' . date('Y/m/d H:i:s') . "] {$row['path']} -> 保存失败!";
				}else{
					$log[] = '[' . date('Y/m/d H:i:s') . "] {$row['path']} -> $path";
				}
				M('pics')->where(['id' => $row['id']])->save(['path' => $path]);
			}
		}
		if(!empty($log))echo join("\n", $log) . "\n";
	}

	//检查是否有未发送的信息
	public function message(){
		// 查询出所有未发送的信息
		// 发送最近3天内的未发送的短信
		$gtTime = time() - 86400 * 3;
		$ltTime = time();
		
		$rs = D('MsgView')->where(['is_sms' => 0, 'sendtime' => ['between', $gtTime . ',' . $ltTime]])->select();
		foreach($rs as $row){
			// 暂不支持非预约短信功能
			if($row['code_type'] != '' || $row['type'] !== 7){

				// 如果是预约短信，查询对应的众筹是否上线
				$raiseC = M('Raise')->where(['id' => $row['type_id'], 'status' => 0])->find();
				if (!empty($raiseC)) {
					continue;
				}

				$sms = 0;
				// $num = $row['sms_send'] - $row['is_sms'];
				switch($row['sms_send']){
					case 1:
						$status = smsSend($row['telephone'],$row['code_type'], json_decode($row['params'])) ;
						if ($status == 1) {
							$sms = 1;
						} else {
							$sms = 0;
						}
						break;
					case 2:
						$status = smsSend($row['telephone'],$row['code_type'], json_decode($row['params']),1) ;
						if($status == 1){
							$sms = 1;
						}else{
							$sms = 0;
						}
						break;
					case 3:
						$status = smsSend($row['telephone'],$row['code_type'], json_decode($row['params'])) && smsSend($row['telephone'],$row['code_type'], json_decode($row['params']),1);
						if($status == 1){
							$sms = 1;
						}else{
							$sms = 0;
						}
						break;
				}
				$log[] = "发送短信sms:{$sms}=>{$row['id']} => " . date('Y/m/d H:i:s');
				if ($sms) {
//				M('MemberMessage')->where(['id' => $row['id']])->save('is_sms='.$num);

					M('MemberMessage')->where(['id'=>$row['id']])->data(['is_sms'=> $row['sms_send']])->save();
					$log[] = "发送短信:{$row['telephone']} => " . date('Y/m/d H:i:s');
				}
			}

			// foreach($rs as $row){
			// 	if(in_array($row['ios_push'] ,[1,2,3]) && !empty($row['id'])) {
			// 		$log[] = "发送IOS信息:{$row['id']} => " . date('Y/m/d H:i:s');
			// 		$ios_num = $row['ios_push'] - $row['is_ios_push'];
			// 		switch ($ios_num) {
			// 			case 1:
			// 				$this->ios_push($row['member_id'], 0, $row['content'], $row['id']);
			// 				break;
			// 			case 2:
			// 				$this->ios_push($row['member_id'], 1, $row['content'], $row['id']);
			// 				break;
			// 			case 3:
			// 				$this->ios_push($row['member_id'], 0, $row['content'], $row['id']);
			// 				$this->ios_push($row['member_id'], 1, $row['content'], $row['id']);
			// 				break;
			// 		}
			// 	}
			// }
		}
		if(!empty($log))echo join("\n", $log) . "\n";
	}
	//检查是否有未推送的信息
	public function messageIOS(){
		//查询出所有未发送的信息
		$rs = D('MsgIOSView')->where(['isMass' => 0,'sms_send' =>0,'wx_send' =>0, 'sendtime' => ['LT', time()]])->select();
		foreach($rs as $row){
			$ios_num = $row['ios_push'] - $row['is_ios_push'];
//			$log[] = "发送IOS的渠道为:{$row['id']} => " . date('Y/m/d H:i:s');
			switch($ios_num){
				case 1:
					$this->ios_push($row['member_id'], 0, $row['content'], $row['id']);
					break;
				case 2:
					$this->ios_push($row['member_id'], 1, $row['content'], $row['id']);
					break;
				case 3:
					$this->ios_push($row['member_id'], 0, $row['content'], $row['id']);
					$this->ios_push($row['member_id'], 1, $row['content'], $row['id']);
					break;
			}
		}
		if(!empty($log))echo join("\n", $log) . "\n";
	}

	private function ios_push($member_id, $channel, $content, $msg_id){
		$devicetoken = M('MemberDevice')->where(['member_id' => $member_id, 'channel' => $channel])->order('id desc')->getField('device');
		if(!empty($devicetoken)){
			$dt = [
				'devicetoken' => $devicetoken,
				'msg' => $content,
				'channel' => $channel,
				'msg_id' => $msg_id
			];
			getRedis()->rPush(str_replace('.', '', DOMAIN) . '_app_push', json_encode($dt));
		}
	}


    /**
     * 每天8点执行，检查有没有项目结束了的抽奖（未开奖状态）
     */
	public function runLottery() {
        $cur_timestamp = time();
        $condition['lottery_time'] = ['lt', $cur_timestamp];
        $condition['status'] = ['eq', 0];
        $raiseLuckyResultModel = M('RaiseLuckyResult');
        $raiseLuckyModel = M('RaiseLucky');

        $lottery_times = $raiseLuckyResultModel->where($condition)->select();

        if (count($lottery_times) === 0) {
            echo '[' . date('Y-m-d H:i:s') . "]众筹抽奖开奖成功，没有需要开奖的众筹挡位\n";
            exit();
        }

        foreach($lottery_times as $lotteryInfo) {
            $sh = $lotteryInfo['sh'];
            $sz = $lotteryInfo['sz'];
            $times_id = $lotteryInfo['raise_times_id'];
            $participators = $raiseLuckyModel->where(['raise_times_id' => $times_id])->count(); // 全部的参与者

            if (empty($sz) || empty($sh)) {
                continue;
            }

            if ($participators == 0) {
                $raiseLuckyResultModel->where(['raise_times_id' => $times_id])->save(['status' => 1, 'lucky_num' => '没有人', 'run_time' => date('Y-m-d H:i:s')]); // 设置为已开奖状态
                continue;
            }

            $lucky_num = intval((string)$sh . (string)$sz) % $participators + 1; // 中奖号码
            $lucky_arr[] = $lucky_num; // 幸运号码数组
            $baseX = $lotteryInfo['base_x'];
            $num = $lotteryInfo['num'];
            if($baseX > 0 && $num > 1) {
                // 以x为基准，
                $last_num = $raiseLuckyModel->where(['raise_times_id' => $times_id])->order('id desc')->limit(1)->field('lucky_num')->find();

                $last_num = intval($last_num['lucky_num']);
                $last_num < $participators && ($last_num = $participators);
                echo '最后一个抽奖号码：' . $last_num . "\n";

                for ($i = 1; $i < $num; $i++) {
                    $lucky_num = $lucky_num + $baseX;

                    if ($lucky_num > $last_num) {
                        $lucky_num =$lucky_num - $participators;
                    }

                    while (in_array($lucky_num, $lucky_arr)) {
                        $lucky_num = $lucky_num + 1;
                    }

                    $lucky_arr[] = $lucky_num;

                    if (count($lucky_arr) >= $participators) {
                        break;
                    }
                }
            }
            $condition['raise_times_id'] = array('eq', $times_id);
            $condition['lucky_num'] = array('in', implode(',', $lucky_arr));
            $raiseLuckyModel->where($condition)->save(['lucky_status' => 1]); // 设置中奖

            $unLuckyWhere['raise_times_id'] = array('eq', $times_id);
            $unLuckyWhere['lucky_num'] = array('not in', implode(',', $lucky_arr));
            $raiseLuckyModel->where($unLuckyWhere)->save(['lucky_status' => -1]);

            $raiseLuckyResultModel->where(['raise_times_id' => $times_id])->save(['status' => 1, 'lucky_num' => implode(',', $lucky_arr), 'run_time' => date('Y-m-d H:i:s')]); // 设置为已开奖状态
            $lottery_times_id[] = $times_id;
        }


        // 中奖人发送通知
        $luckyCondition['lucky_status'] = ['eq', 1];
        $luckyCondition['raise_times_id'] = ['in', implode(',', $lottery_times_id)];
        $luckyMen = $raiseLuckyModel->where($luckyCondition)->field('member_id, order_id, raise_times_id')->select();

        foreach ($luckyMen as $men) {
            $title = D('RaiseView')->where(['B.id' => $men['raise_times_id']])->getField('title');
            $channel = 0;
            $params = array(
                'title' => '《'. $title . '》抽奖档位已开奖，恭喜您中奖',
                'user' => '众筹发起人',
                'action' => '兑奖',
                'platform' =>'吖咪',
            );
            $this->push_Message($men['member_id'], $params, 'SMS_83570001', 'sms|ios',null, 3, $men['order_id'], 0, $channel);
        }
        unset($men);

        // 未中奖人发送通知
        $unLuckyCondition['lucky_status'] = ['eq', 0];
        $unLuckyCondition['raise_times_id'] = ['in', implode(',', $lottery_times_id)];
        $unLuckyMen = $raiseLuckyModel->where($unLuckyCondition)->field('member_id, order_id, raise_times_id')->select();
        foreach ($unLuckyMen as $men) {
            $title = D('RaiseView')->where(['B.id' => $men['raise_times_id']])->getField('title');
            $channel = 0;
            $params = array(
                'title' => '《'. $title . '》抽奖档位已开奖，很遗憾您未中奖',
                'action' => '抽奖结果',
                'platform' =>'吖咪',
            );
            $this->push_Message($men['member_id'], $params, 'SMS_83610005', 'sms|ios',null, 3, $men['order_id'], 0, $channel);
        }
        unset($men);

        echo '抽奖人数: ' . $participators ."\n";
        echo '中奖号码: ' . implode(',', $lucky_arr) . "\n";
        echo '[' . date('Y-m-d H:i:s') . "]众筹抽奖开奖成功\n";
        exit();
    }


    //测试短信发送
    public function test() {
    	$params =  ['project_name' => '众筹','project_title' => '众筹','project_action' => '众筹','per' => '100'];
    	$this->push_Message('269188', $params, 'SMS_85945001', 'sms|ios', null, 3, 0, 0, 0);
    	//smsSend('15920324398',);
    	echo '1111111';
    }
    public function test2() {
    	$params = [
					'project'=>'众筹',
					'title'=>'金拱门',
					'price'=>'5',
					'nickname'=>'测试人员',
					'telephone'=>'15920324398',
				];
    	$this->push_Message('269188', $params, 'SMS_36355042', 'sms|ios', null, 3, 0, 0, 0);
    	//smsSend('15920324398',);
    	echo '1111111';
    }
    public function test3() {
    	$params = [
					'project'=>'众筹',
					'title'=>'金拱门',
					'price'=>'2.00',
					'nickname'=>'测试人员',
					'telephone'=>'15920324398',
				];
    	$this->push_Message('269188', $params, 'SMS_107035152', 'sms|ios', null, 3, 0, 0, 0);
    	//smsSend('15920324398',);
    	echo '1111111';
    }
    public function test4() {

    	$params['code'] = '1234';
    	//测试
    	smsSend('15920324398','SMS_107810071',$params);
    	echo 'smssss';
    }
    public function test5() {
    	//就这样别动，可以发送，可以在阿里云里显示出发送失败的数据。
  //  	$params['title'] = '1234';
    	$params = [];
    	//测试
    	smsSend('15920324398','SMS_109450138',$params);
    	echo 'tuiguang';
    }
    public function test6() {
    	    	$params = [
					'project'=>'众筹',
					'title'=>'金拱门',
					'price'=>'2.00',
					'nickname'=>'测试人员',
					'telephone'=>'15920324398',
				];
    	$this->push_Message('269188', $params, 'SMS_109450138', 'sms|ios', null, 3, 0, 0, 0);
    	//smsSend('15920324398',);
    	echo '11';
    }

    public function testSpread() {

    	$params = [];
    //	smsSend('18924308767','SMS_109450138',$params);
    //	smsSend('13560082043','SMS_109450138',$params);
    //	smsSend('15989078834','SMS_109450138',$params);
    //	smsSend('18688446839','SMS_109450138',$params);
    //	smsSend('15017507843','SMS_109450138',$params);

    //	smsSend('18520478056','SMS_109450138',$params);
    	echo '测完注释';
    }

    public function spreadMessage() {

//    	$where = ['status' => 1,'is_public'=>1];
//        $rs = D('SpreadMessageView')->where($where)->order('id desc')->page($page, $num)->group('id')->select();
    	$rs = D('SpreadMessageView')->select();
    	var_dump($rs);
    	echo '测试下先';
    }



    public function testRaise() {
//    	$rs_arr = D('TestRaiseView')->where(['A.type' => 2, 'A.ware_id' => $row['id'], 'B.status' => 1, 'B.act_status' => ['in', '1,2,3,4'],'order_pid'=>['EXP', 'IS NULL']])->group('A.id')->select();

//    	$rs_arr = D('TestRaiseView')->where(['A.type' => 2])->group('A.id')->select();
    	$rs_arr = D('TestRaiseView')->where(['A.ware_id' => 62])->group('A.id')->select();
    	var_dump($rs_arr);
    }


    public function testRaiseMessage() {
		$params = [
			'project'=>'众筹',
			'title'=> '1',
			'price'=>'1',
			'nickname'=>'1',
			'telephone'=>'1',
			'wx'=>'yami194'
		];
		$this->push_Message('269188', $params,'SMS_55720155', 'wx|sms|ios',null, 0, 0, 0, 0);
    }

        public function testRaiseMessage2() {
    					$params = [
					'name' =>'111',
					'project_name' => '众筹',
					'title' => '111',
					'money' => '111元',
                    'times' => '111'
				];

	$this->push_Message('269188', $params,'SMS_85395035', 'sms|ios',null, 3, 0,0,0);
    }

        public function test7() {
    	    	$params = [
			'project'=>'众筹',
			'title'=> '1',
			'price'=>'1',
			'nickname'=>'1',
			'telephone'=>'1',
			'wx'=>'yami194'
		];
		smsSend('15920324398','SMS_55720155',$params);
    }

    public function smsS() {
    	echo 'success';
//    	require_once '../../../aliyun-dysms-php-sdk/api_demo/SmsDemo.php';
 //   	require 'vendor/aliyun-dysms-php-sdk/api_demo/SmsDemo.php';
    	vendor('aliyun-dysms-php-sdk.api_demo.class#SmsDemo');

//    	import('aliyun-dysms-php-sdk.api_demo.class#SmsDemo');

		set_time_limit(0);
		header('Content-Type: text/plain; charset=utf-8');
		$response = SmsDemo::sendSms();
		echo "发送短信(sendSms)接口返回的结果:\n";
		print_r($response);

    }

   	public function alipayFund() {
   		echo '11';
		$aop = new \Aop\AopClient();
		$aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
		$aop->appId = '2018020902171024';
		$aop->rsaPrivateKey = 'MIIEpQIBAAKCAQEAqIByaybSfhgCFbmbQ+cuLr5CzqOLlY78xJr/59B3dBnOjEWaXDnFY2k38a69rcbuozT+UXiXdL1WhEKrGBVR6VokDTJJ6PBnhjdk8d7fNsu0Q3hfCvHJh5sEX2CJV3wvEr7WBemYlFRyTxOILq8F31rbgVOoJ/NRlRWLXeWcgl9Gq4zWB2H9d3pSrAy8Mi1KSCF6Io35IINMPQPw62VdcHJFDivNJ8sIH5Bh+h5pMLVvlELclIofis3jn4I/D58i2GKUb7a4H2nKoAkfDmNPz5riPy6cEDROF8WtEfEfCAlcRW+IJbSXujiRXH3ARb+/CwtjUW800FICuS+L1GpW8wIDAQABAoIBAQCSoHtwfK7spO2jhAj1VMWeTVgLQsujUHLqjsqjKYfZEt/mtma1XFxEvnm9KMbL4nEkumeX860wG9aebvk9ksfdnOAET797IT+kzq1bwApTP33UvHlQJ8ir5Rwv9uxsoZbA5CmPqY9pe/agkymNHiapDwI2DtskMJrMdZv6EGVaGC8Irzpy6AN7OfLXUCuTd8jRNUKFvkTj5vlfGr2QvsQxUkaGc0fKk6foZykyyqHkmqqBeWwGdxUXzQJqboSHBmgBMXbbq4d9hOzTFMe0FSkoz/J09RghqiCEV3J7Xl0FKvtgi8K46yfUPzcX/beWw1psmoB0O0I5m+iUDKXM8ZUBAoGBANEEQxP53/WDk79bT/hslp4CQPHqeaMUSp7f9zyCHABGH4KNvMJCie4hHHaLFM9RKFfKeL1O2bJ8fagLY9/4gn6KIV+dmrw8j5tZbT9jKiIdSK9gKQd+al40yddKPA0kaLlQPvtKTrV5dYgU9NHB544zBr8a5d9cewGjQxItEPlzAoGBAM5gx3Km1tbFUkvdGiPHTBejuiKH7yflaRyXFjD8fI5eRZAed8kzUcxZMk56X3L1IIJlQoyfl9+a9eaWYaElq3mz8fVRzNIYBsWVZo4UEn3frCNTjorI4EAzY83nIElcdNXh6dtsiPrAd7b4HzABrchsFPDIOx3Lb7+rHytmV8yBAoGASk3bksRny53k7kgF2+iueqmOcPHMIB9cj7JK8CXI0ogbN4wvqFeDNTZsKfAzi6fsUZlW5uWbGoqLGSxDayGrMlTknFso4PYejzlxTvFvzwTeDAqBS3qzUZ4uiuyHAJ0K5aYTXb4C0RzGnYPlrJPkP3cAVPu48Hit+d0SQOnoeOUCgYEAwzfG0oxBQy2qODrw5BE8yEvG4a0mK94VPMcqZbIgfGduc/JuKvORl7R0IsodCdgYJWB1wCGi/xBNNQ7hURcaCmiOIxl6nu29Uh+NwC36g/kVkuESP/PeNLyn1vifkOWVW1B072vOcyum0nwIvFeKNMEQWed+DCQJFAxh0qLfwIECgYEAn9JvpMdt29AK52tkBZKPgo3i18/Xw1e6KAkJ+bRpRyo92spWdCb82IrZwy3gljI2XKAbWVi7CxhTrZT2OzWdgxnbpYXg5m/6jFp+aUVClFCdkGQh6UQMdSDN1CprTqWNpe1lY2lo1w6JKmnZga2E7R4055w6sGgMy0ANIN8pVGk=';
		$aop->alipayrsaPublicKey = 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA0Se8Dsjd5KUCxUEJa2Q4gTU7cMDNIDo5659/nKLtvSqpk7z7q9lTGOlUXUsUXPosVCCzFBRU9NLv9poPjTYSVRlVqGQzIuT5dUAxJI+2vFyxlJlQv8NaFWhUlP7n9dRN8efmF8mhOFaJ5BKwpmlaRB2lCdMsZhq0kXl38/ozqcSPv+ttCa+yDdjHOIcTpGEbI1qyh1M7jO9YR630oEudTGbMImmH4ob+IGfb7SnfVUjQoP1/IGcHRaHckq678HFvsN2qpg4E+5Kar4iLaaCEXGEhW5iGMSwuwY22xry7kDuO0intTMTzvzJ2Do65Dn6RKWWVoCNVd+LicuXunrBjGwIDAQAB';
		$aop->apiVersion = '1.0';
		$aop->signType = 'RSA2';
		$aop->postCharset = 'utf-8';
		$aop->format = 'json';
		$request = new \Aop\request\AlipayFundTransToaccountTransferRequest();
		$request->setBizContent(
			"{" .
			"\"out_biz_no\" : \"1234567890127\"," .
			"\"payee_type\" : \"ALIPAY_LOGONID\"," .
			"\"payee_account\" : \"15920324398\"," .
			"\"amount\" : \"0.1\"," .
			"\"payer_show_name\" : \"上海交通卡退款\"," .
			"\"payee_real_name\" : \"劳嘉\"," .
			"\"remark\" : \"转账备注\"" .
			"}"
		);
		$result = $aop->execute($request);
		
		$responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
		echo $responseNode;
		$resultCode = $result->$responseNode->code;
		echo $resultCode;
		if (!empty($resultCode) && $resultCode == 10000) {
			echo "成功";
		} else {
			echo "失败";
		}
		
		print_r($result);
/*
		$responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
		$resultCode = $result->$responseNode->code;
		if(!empty($resultCode)&&$resultCode == 10000){
		echo "成功";
		} else {
		echo "失败";
		}
		*/
	}

	public function testsomething() {
		
		$mem = new \Memcache();
		$mem->connect("127.0.0.1", 11211);
//		$stats = $mem->getExtendedStats('items');
		$stats = $mem->get('ym_wechat_access_tokenwx2913320dd8970616');
		echo $stats;
//    	echo getAccessToken();
	}

	public function testisset() {
		/*
		$is_profit = M('ProfitList')->where(['type' => 1, 'goods_id' => 47])->find();
		if (isset($is_profit)) {
			echo '123';
		}
*/
		$invite_member_id = M('Order')->alias('a')->join('__MEMBER__ as b on a.invite_member_id=b.id')->where(['a.id' => '42687'])->getField('b.id');
		echo $invite_member_id;
	}
/*
	public function sendSorry() {
    	$rs = M('MemberMessage')->alias('a')
    							->join('__MESSAGE__ as b on a.message_id = b.id')
    							->join('__MEMBER__ as c on a.member_id = c.id')
    							->where(['b.code_type' => 'SMS_48615069'])
    							->group('a.member_id')
    							->having('count(a.member_id)>=2')
    							->field('c.telephone')
    							->select();
//		print_r($rs);
    	$params = [];

    	foreach ($rs as $row) {
    		smsSend($row['telephone'],'SMS_129742122',$params);
    	}
//		smsSend('15920324398','SMS_129742122',$params);

	}
	*/
	public function testCA() {
		echo '123';
		$NonceStr = $this->generateNonceStr();
		$sign = strtoupper(md5("mch_id=1251763301&nonce_str={$NonceStr}&key=kYdMDytXymsz3nxFVxacNd4jLCsTJDoe"));
		$xml = "<xml><mch_id>1251763301</mch_id><nonce_str>{$NonceStr}</nonce_str><sign>{$sign}</sign></xml>";
		echo $xml;
		$str = $this->send_post('https://apitest.mch.weixin.qq.com/sandboxnew/pay/getsignkey', $xml);
		print_r($str);
	}

	private function send_post($url, $post_data) {
 
	  $postdata = http_build_query($post_data);
	  $options = array(
	    'http' => array(
	      'method' => 'POST',
	      'header' => 'Content-type:application/x-www-form-urlencoded',
	      'content' => $postdata,
	      'timeout' => 15 * 60 // 超时时间（单位:s）
	    )
	  );
	  $context = stream_context_create($options);
	  $result = file_get_contents($url, false, $context);
	 
	  return $result;
	}

	public function generateNonceStr($length=16){
		// 密码字符集，可任意添加你需要的字符
		$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
		$str = "";
		for($i = 0; $i < $length; $i++)
		{
			$str .= $chars[mt_rand(0, strlen($chars) - 1)];
		}
		return $str;
	}

	private function http_post($url,$param,$post_file=false){
		$oCurl = curl_init();
		if(stripos($url,"https://")!==FALSE){
			curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($oCurl, CURLOPT_SSLVERSION, 1); //CURL_SSLVERSION_TLSv1
		}
		if (is_string($param) || $post_file) {
			$strPOST = $param;
		} else {
			$aPOST = array();
			foreach($param as $key=>$val){
				$aPOST[] = $key."=".urlencode($val);
			}
			$strPOST =  join("&", $aPOST);
		}
		$header = ['User-Agent: Opera/9.80 (Windows NT 6.2; Win64; x64) Presto/2.12.388 Version/12.15','Referer: http://someaddress.tld','Content-Type: multipart/form-data'];
		curl_setopt($oCurl, CURLOPT_URL, $url);
		curl_setopt($oCurl, CURLOPT_HTTPHEADER, $header);
		curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt($oCurl, CURLOPT_POST,true);
		curl_setopt($oCurl, CURLOPT_POSTFIELDS,$strPOST);
		$sContent = curl_exec($oCurl);
		$aStatus = curl_getinfo($oCurl);
		curl_close($oCurl);
		return $aStatus;
		/*
		if(intval($aStatus["http_code"])==200){
			return $sContent;
		}else{
			return false;
		}*/
	}

	public function kaishiba() {
		for ($j=1; $j < 62; $j++) { 
			# code...
			$json_str = file_get_contents('http://test.yummy194.cn/h5/kaishiba/canyin/'.$j.'.json');

			$rs = json_decode($json_str, true);

			for ($i=0; $i < 6; $i++) { 
				# code...
				$temp['name'] = $rs['result'][$i]['name'];
				$temp['userName'] = $rs['result'][$i]['userName'];
				$temp['area'] = $rs['result'][$i]['area'];
				$temp['markerName'] = $rs['result'][$i]['markerName'];
				$temp['supportNum'] = $rs['result'][$i]['supportNum'];
				$temp['total'] = $rs['result'][$i]['total'];
				$temp['funds'] = $rs['result'][$i]['funds'];
				$temp['startDate'] = $rs['result'][$i]['startDate'];
				$temp['endDate'] = $rs['result'][$i]['endDate'];
				$temp['likesNum'] = $rs['result'][$i]['likesNum'];
				$temp['commentsNum'] = $rs['result'][$i]['commentsNum'];
				$data[] = $temp;
			}
		}
		for ($j=1; $j < 4; $j++) { 
			# code...
			$json_str = file_get_contents('http://test.yummy194.cn/h5/kaishiba/jiudian/'.$j.'.json');

			$rs = json_decode($json_str, true);

			for ($i=0; $i < 6; $i++) { 
				# code...
				$temp['name'] = $rs['result'][$i]['name'];
				$temp['userName'] = $rs['result'][$i]['userName'];
				$temp['area'] = $rs['result'][$i]['area'];
				$temp['markerName'] = $rs['result'][$i]['markerName'];
				$temp['supportNum'] = $rs['result'][$i]['supportNum'];
				$temp['total'] = $rs['result'][$i]['total'];
				$temp['funds'] = $rs['result'][$i]['funds'];
				$temp['startDate'] = $rs['result'][$i]['startDate'];
				$temp['endDate'] = $rs['result'][$i]['endDate'];
				$temp['likesNum'] = $rs['result'][$i]['likesNum'];
				$temp['commentsNum'] = $rs['result'][$i]['commentsNum'];
				$data[] = $temp;
			}
		}
		for ($j=1; $j < 86; $j++) { 
			# code...
			$json_str = file_get_contents('http://test.yummy194.cn/h5/kaishiba/minsu/'.$j.'.json');

			$rs = json_decode($json_str, true);

			for ($i=0; $i < 6; $i++) { 
				# code...
				$temp['name'] = $rs['result'][$i]['name'];
				$temp['userName'] = $rs['result'][$i]['userName'];
				$temp['area'] = $rs['result'][$i]['area'];
				$temp['markerName'] = $rs['result'][$i]['markerName'];
				$temp['supportNum'] = $rs['result'][$i]['supportNum'];
				$temp['total'] = $rs['result'][$i]['total'];
				$temp['funds'] = $rs['result'][$i]['funds'];
				$temp['startDate'] = $rs['result'][$i]['startDate'];
				$temp['endDate'] = $rs['result'][$i]['endDate'];
				$temp['likesNum'] = $rs['result'][$i]['likesNum'];
				$temp['commentsNum'] = $rs['result'][$i]['commentsNum'];
				$data[] = $temp;
			}
		}
		for ($j=1; $j < 65; $j++) { 
			# code...
			$json_str = file_get_contents('http://test.yummy194.cn/h5/kaishiba/nongye/'.$j.'.json');

			$rs = json_decode($json_str, true);

			for ($i=0; $i < 6; $i++) { 
				# code...
				$temp['name'] = $rs['result'][$i]['name'];
				$temp['userName'] = $rs['result'][$i]['userName'];
				$temp['area'] = $rs['result'][$i]['area'];
				$temp['markerName'] = $rs['result'][$i]['markerName'];
				$temp['supportNum'] = $rs['result'][$i]['supportNum'];
				$temp['total'] = $rs['result'][$i]['total'];
				$temp['funds'] = $rs['result'][$i]['funds'];
				$temp['startDate'] = $rs['result'][$i]['startDate'];
				$temp['endDate'] = $rs['result'][$i]['endDate'];
				$temp['likesNum'] = $rs['result'][$i]['likesNum'];
				$temp['commentsNum'] = $rs['result'][$i]['commentsNum'];
				$data[] = $temp;
			}
		}
		for ($j=1; $j < 83; $j++) { 
			# code...
			$json_str = file_get_contents('http://test.yummy194.cn/h5/kaishiba/xiuxian/'.$j.'.json');

			$rs = json_decode($json_str, true);

			for ($i=0; $i < 6; $i++) { 
				# code...
				$temp['name'] = $rs['result'][$i]['name'];
				$temp['userName'] = $rs['result'][$i]['userName'];
				$temp['area'] = $rs['result'][$i]['area'];
				$temp['markerName'] = $rs['result'][$i]['markerName'];
				$temp['supportNum'] = $rs['result'][$i]['supportNum'];
				$temp['total'] = $rs['result'][$i]['total'];
				$temp['funds'] = $rs['result'][$i]['funds'];
				$temp['startDate'] = $rs['result'][$i]['startDate'];
				$temp['endDate'] = $rs['result'][$i]['endDate'];
				$temp['likesNum'] = $rs['result'][$i]['likesNum'];
				$temp['commentsNum'] = $rs['result'][$i]['commentsNum'];
				$data[] = $temp;
			}
		}

        $titleArr = ['项目名称','发起人','地点','标签','支持人数','支持金额','目标金额','项目开始时间','项目结束时间','喜欢人数','评论人数'];
        toXls($titleArr,$data,'开始吧项目（进行中）');


//		var_dump($data);
		
	}

	public function testRemind() {
		echo '123';
		/*
    	$params['project'] = '1234';
    	$params['times'] = '2222';
    	//测试
    	smsSend('15920324398','SMS_134323553',$params);
    	*/
    	$list = M('RaiseRemind')->alias('a')->join('__MEMBER__ as b on a.member_id=b.id')->where(['raise_id' => 107, 'times_id' => 623, 'statu' => 0])
                            ->field('b.telephone')
                            ->select();
        print_r($list);
    }
}
