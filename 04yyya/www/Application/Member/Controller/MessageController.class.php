<?php
namespace Member\Controller;
use Member\Common\MainController;

// @className 会员消息
Class MessageController extends MainController {
	
	/**
	 * @apiName 获取未读消息条数
	 *
	 * @apiGetParam {string} token: 通信令牌
	 * @apiPostParam {int} is_web: 是否是web
	 *
	 * @apiSuccessResponse
	 * {"count":"4"}
	 */
	Public function UnreadCount(){
		$member_id = session('member.id');
		$is_web = I('post.is_web', 0);

		//检查是否有新的通播消息
		$sql = M('MemberMessage')->field(['message_id'])->where(['member_id' => $member_id])->buildSql();
		$mass = M('message')->where("`isMass`=1 and `id` NOT IN {$sql}")->select();
		if(!empty($mass)){
			$data = [];
			foreach($mass as $row){
				$data[] = [
					'member_id' => $member_id,
					'message_id' => $row['id']
				];
			}
			M('MemberMessage')->addAll($data);
		}

		$count = D('MsgView')->where(['member_id' => $member_id, 'is_read' => 0, 'sendtime' => ['LT', time()]])->count();
		$this->ajaxReturn(['count' => $count]);
	}
	
	/**
	 * @apiName 获取消息列表
	 *
	 * @apiGetParam {string} token: 通信令牌
	 * @apiPostParam {int} is_all: 是否获取全部消息(0-否[默认] 1-是)
	 *
	 * @apiSuccessResponse
	 * {
	 * 	"system": {
	 * 		"message": {
	 * 			"content": "欢迎来到吖咪！欢迎来到吖咪！欢迎来.....到吖咪！欢迎来到吖咪！",
	 * 			"count": "2"
	 * 		},
	 * 		"comment": {
	 * 			"count": "7",
	 * 			"nickname": "Yp",
	 * 			"title": "广州 吖咪&边度 小花的6寸裸蛋糕教学"
	 * 		}
	 * 	},
	 * 	"more": [
	 * 		{
	 * 			"id": "7000",
	 * 			"content": "延迟发送测试一下延迟发送测试一下延迟发送测试一下延迟发送测试",
	 * 			"count": "1",
	 * 			"nickname": "Stephen",
	 * 			"headpic": "http://wx.qlogo.cn/mmopen/hRkOoB5ZTmLIra60eAye25MEKTRaZbvxIyT78DOnc6cO9m0ybFuelSFIDkKtyhA5jVMjuEsF1NQgicOZaDsicDRHF92wgqNOjq/0",
	 * 			"datetime": "2016-03-22 10:52:15"
	 * 		},
	 * 		{
	 * 			"id": "6000",
	 * 			"content": "延时发送测试延时发送测试延时发送测试延时发送测试延时发送测试",
	 * 			"count": "1",
	 * 			"nickname": "EchoSong",
	 * 			"headpic": "http://wx.qlogo.cn/mmopen/1Qw8iaBGVXgN5T5j13zFx7gGBoVZFvnvrsFgmXSpqLeOkGoEqzRbA2pCvrxia5PbkHLehdOjk4kUWF2Vz4YZzV3PKLfDw8dAvE/0",
	 * 			"datetime": "2016-03-22 10:52:13"
	 * 		},
	 * 		{
	 * 			"id": "8000",
	 * 			"content": "会员之间发送的测试会员之间发送的测试会员之间发送的测试会员之",
	 * 			"count": "1",
	 * 			"nickname": "只如初见",
	 * 			"headpic": "http://wx.qlogo.cn/mmopen/jZUIEF2vTww9jc6eZXw3TTxy1Jr436QGC4fQxQPwyvvJQp2phv6pqJB2mSfvqVC4TwOYuSJIIdESEfUgzgMynjSjwmdQxU4v/0",
	 * 			"datetime": "2016-03-22 10:52:10"
	 * 		}
	 * 	]
	 * }
	 */
	Public function getList(){
		$member_id = session('member.id');
		$is_all = I('post.is_all', 0);
		$page = I('get.page', 1);

		$data = [
			'system' => [],
			'more' => []
		];
		//先查询是否有新的通知
		$where = ['member_id' => $member_id, 'origin_id' => ['EXP', 'IS NULL'], 'sendtime' => ['LT', time()]];
		if(!$is_all){
            $where['is_read'] = 0;
        }
		$rs = D('MsgSystemView')->where($where)->order('B.id desc')->find();

		$count = D('MsgSystemView')->where(['member_id' => $member_id, 'origin_id' => ['EXP', 'IS NULL'], 'sendtime' => ['LT', time()], 'is_read' => 0])->count();

		$data['system']['message'] = [
			'content' => $rs['content'],
			'count' => $count?$count:0,
            'message_id' => $rs['message_id'],
            'origin_id' => $rs['origin_id']
		];

		$data['system']['comment'] = '';
        $data['system']['bang'] = '';
		//查询新的评论
//		$framework_id = M('framework')->where(['module' => strtolower(MODULE_NAME), 'controer' => strtolower(CONTROLLER_NAME), 'action' => 'getdetail'])->getField('id');
//		$datetime = M('MemberActLog')->where(['member_id' => $member_id, 'framework_id' => $framewo_id, 'post' => ['LIKE', '%type%1%']])->order('id desc')->getField('datetime');/		$where = ['C.member_id|D.member_id|E.member_id|F.member_id'  $member_id];
//		if(!empty($datetime))$where['datetime'] = ['GT', $datetime];
//		$data['system']['comment'] = D('CmtView')->field(['came', 'titl, 'count', 'at_id'])where($where)->order('id desc')->find();
//
//		//查询新的动态
//        //找出关注了哪些达人
//        $follow_id_list = MMemberFollow')->where(['member_id'=>$member_id])-etField('follow_id',rue);
//        $follow_id = join(',', $follow_id_st);
//        //获取这些达人的食报
//      $datetime = empty($datetime)?0:$datetime;
//        if(!empty($follow_id)){
//            $bang_data = D('BangView')->field('A.id as id,nickname,content,send_time')->where('A.member_id in ('.$flow_id.') and send_time >= '.strtotime($datetime) )->der('id desc')->find();
//            $bang_data['unt'] = count($ng_data);
//            $data['system']['bang'] $bang_data;
//        }else{
//            $data['system']['bang'] = array();
//        }
		//查询普通消息通知
		$where = ['member_id' => $member_id, 'origin_id' => ['EXP','IS NOT NULL']];
		if(!$is_all)$where['is_read'] = 0;
		$sql = D('MsgMoreView')->field('max(A.id)')->where($where)->group('B.member_id')->buildSql();
		$where['id'] = ['EXP', 'in ' . $sql];
		$rs = D('MsgMoreView')->field(['origin_id', 'message_type', 'content', 'id', 'nickname', 'path', 'datetime'])->where($where)->order('datetime desc')->page($page, 5)->select();
		//$_rs = D('MsgMoreView')->field(['id', 'count'])->where($where)->group('B.member_id')->select();
        //$_rs = D('MsgMoreView')->field(['id','count'])->where(['member_id' => $member_id, 'origin_id' => ['EXP','IS NOT NULL'],'is_read'=>0])->group('B.member_id')->select();
        $where['is_read'] = 0;
        $_rs = D('MsgMoreView')->field(['id','count'])->where($where)->group('B.member_id')->select();
		$r = [];
		foreach($_rs as $row){
		    $r[$row['id']] = $row['count'];
		}
        foreach($rs as $rr){
            $memberMessageIds[] = $rr['id'];
        }
		foreach($rs as $key => $row){
            $data['more'][$key] = [
                'id' => $row['origin_id'],
                'message_type'=>$row['message_type'],
                'content' => utf8_substr($row['content'], 0, 30),
                'count' => $r[$row['id']]?$r[$row['id']]:0,
                'nickname' => $row['nickname'],
                'headpic' => thumb($row['path'],2),
                'datetime' => $row['datetime']
            ];
		}

		$this->ajaxReturn($data);
	}

	/**
	 * @apiName 获取消息详情
	 *
	 * @apiGetParam {string} token: 通信令牌
	 * @apiGetParam {int} page: 分页页码(每页10条)
	 *
	 * @apiPostParam {int} type: 来源类型(0-吖咪小助手 1-评论与回复 2-达人动态)[达人消息则忽略该参数]
	 * @apiPostParam {int} origin_id: 来源ID(0-系统消息 会员ID-达人消息)[系统消息可以忽略该参数]
	 *
	 * @apiSuccessResponse
	 * [
	 * 	{
	 * 		"id": "53",
	 * 		"is_read": "1", //是否已读
	 * 		"datetime": "2016-05-03 11:26:00",
	 * 		"content": "您的活动『新产品试吃』审核已通过，请留意下单情况，做好准备哦~",
	 * 		"type": "0", //消息类型 0-普通消息 1-评论回复通知 2-达人动态通知 3-订单动态通知 4-活动推送消息 5-专题推送消息
	 * 		"type_id": "0", //关联的活动ID、专题ID、订单ID、食报ID
	 * 		"relation": []
	 * 	},
	 * 	{
	 * 		"id": "49",
	 * 		"is_read": "1",
	 * 		"datetime": "2016-05-03 11:26:00",
	 * 		"content": "【吖咪活动通知】感谢小主购买了【小花の新春亲子【草莓大福+草莓伯爵】】，小主的入席时间是2016年05月08日 (周日) 18:00 - 20:00，地址：广东省珠海市香洲区多玩珠海农庄。为了让小主有更好的体验，特为您配置贴心吖咪酱一枚，请添加微信号：yami194，随时保持联系! ^_^",
	 * 		"type": "3", //订单动态通知
	 * 		"type_id": "8485", //订单ID
	 * 		"relation": { //关联内容
	 * 			"count": "1",
	 *          "title": "小花の新春亲子【草莓大福+草莓伯爵】",
	 * 			"price": "0.10",
	 * 			"id": "8485",
	 * 			"path": "http://img.m.yami.ren/20160426/bea7322e33b80659c49af68ed6fe087f4e922280_640x420.jpg"
	 * 		}
	 * 	},
	 * 	{
	 * 		"id": "48",
	 * 		"is_read": "1",
	 * 		"datetime": "2016-05-03 11:26:00",
	 * 		"content": "您的活动『吖咪生活美学 | 用Brunch唤醒周末慢活时光』审核已通过，请留意下单情况，做好准备哦~",
	 * 		"type": "4", //活动推送消息
	 * 		"type_id": "12485", //活动ID
	 * 		"relation": {
	 * 			"title": "吖咪生活美学 | 用Brunch唤醒周末慢活时光",
	 * 			"intro": "吖咪生活美学 | 用Brunch唤醒周末慢活时光",
	 * 			"id": "12485",
	 *          "is_pass": "1", //是否已通过审核 1已通过 0未通过
	 * 			"path": "http://img.m.yami.ren/20160426/bea7322e33b80659c49af68ed6fe087f4e922280_640x420.jpg"
	 * 		}
	 * 	},
	 * 	{
	 * 		"id": "47",
	 * 		"is_read": "1",
	 * 		"datetime": "2016-05-03 11:26:00",
	 * 		"content": "您的活动『吖咪生活美学 | 用Brunch唤醒周末慢活时光』审核已通过，请留意下单情况，做好准备哦~",
	 * 		"type": "5", //主题推送通知
	 * 		"type_id": "12485", //主题ID
	 * 		"relation": {
	 * 			"title": "吖咪生活美学 | 用Brunch唤醒周末慢活时光",
	 * 			"content": "吖咪生活美学 | 用Brunch唤醒周末慢活时光",
	 * 			"id": "12485",
	 * 			"path": "http://img.m.yami.ren/20160426/bea7322e33b80659c49af68ed6fe087f4e922280_640x420.jpg",
	 *          "url": "http://xxxxxxx......"
	 * 		}
	 * 	}
	 * ]
	 */
	Public function getDetail(){
		$member_id = session('member.id');
		$type = (int)I('post.type', -1);
		$origin_id = (int)I('post.origin_id', 0);
		$page = I('get.page', 1);

		//判断并处理系统消息
		switch($type){
			case 0:
				$data = D('MsgView')->field(['id', 'is_read', 'content', 'datetime', 'type', 'type_id', 'code_type'])->where(['member_id' => $member_id, 'origin_id' => ['EXP', 'IS NULL']])->order('A.datetime desc')->page($page, 10)->select();

				foreach($data as $key => $row){
					$_data = [];
					switch($row['type']){
						case '3':
							if ($row['code_type'] == 'SMS_85965005' || $row['code_type'] == 'SMS_85945001' || $row['code_type'] == 'SMS_86130029') {
								$_data = D('OrderWareView')->field(['path', 'title', 'price', 'count', 'id', 'goods_title', 'goods_path', 'type', 'ware_id'])->where(['ware_id' => $row['type_id'], 'type' => '2'])->group('id')->find();
	//							$data['type_id'] = $_data['ware_id'];		
							} else {
								$_data = D('OrderWareView')->field(['path', 'title', 'price', 'count', 'id', 'goods_title', 'goods_path', 'type', 'ware_id'])->where(['id' => $row['type_id']])->group('id')->find();					
							}
							if($_data['type'] == 1){
								$_data['title'] = $_data['goods_title'];
								$_data['path'] = $_data['goods_path'];
							}elseif($_data['type'] == 2){
								$raise_rs = D('RaiseView')->where(['id' => $_data['ware_id']])->find();
								$_data['title'] = $raise_rs['title'];
								$_data['path'] = $raise_rs['path'];
							}
							unset($_data['goods_title']);
							unset($_data['goods_path']);
							break;
						case '4':
							$_data = D('TipsView')->field(['path', 'title', 'intro', 'id', 'is_pass'])->where(['id' => $row['type_id'], 'is_pass' => 1, 'status' => 1])->find();
							break;
						case '5':
							$m = new \Home\Model\ThemeViewModel;
							$_data = $m->field(['id', 'title', 'url', 'path', 'content'])->where(['id' => $row['type_id']])->find();
                            if(is_numeric($_data['url']))$_data['url'] == '';
							break;
						case '6':
							$_data = D('GoodsView')->field(['path', 'title', 'id', 'is_pass'])->where(['id' => $row['type_id'], 'is_pass' => 1, 'status' => 1])->find();
							break;
						case '7':
							$_data = D('RaiseView')->field(['path', 'title', 'id'])->where(['id' => $row['type_id'],'status' => 1])->find();
							break;
					}
					$data[$key]['relation'] = $_data?:[];
                    $data[$key]['datetime'] = strtotime($row['datetime']);
				}
				break;
			case 1:
				//$data = D('CommentView')->where(['C.member_id|D.member_id|E.member_id|F.member_id' => $member_id])->select();
				break;
			case 2:
				$data = [];
				break;
			default:
				//$data = D('MsgView')->where(['member_id' => ['IN', [$member_id, $origin_id]], 'origin_id' => ['IN', [$member_id, $origin_id]]])->order('datetime desc')->page($page, 10)->select();
                $data = D('MsgView')->where(['member_id' => $member_id, 'origin_id' => $origin_id])->order('datetime desc')->page($page, 10)->select();

                foreach($data as $key => $row){
                    $_data = [];
                    switch($row['type']){
                        case '3':
                            //$_data = D('OrderWareView')->field(['path', 'title', 'price', 'count', 'id', 'goods_title', 'goods_path', 'type'])->where(['id' => $row['type_id']])->group('id')->find();
							if ($row['code_type'] == 'SMS_85965005' || $row['code_type'] == 'SMS_85945001' || $row['code_type'] == 'SMS_86130029') {
								$_data = D('OrderWareView')->field(['path', 'title', 'price', 'count', 'id', 'goods_title', 'goods_path', 'type', 'ware_id'])->where(['wid' => $row['type_id'], 'type' => '2'])->group('id')->find();				
							} else {
								$_data = D('OrderWareView')->field(['path', 'title', 'price', 'count', 'id', 'goods_title', 'goods_path', 'type', 'ware_id'])->where(['id' => $row['type_id']])->group('id')->find();								
							}
							if($_data['type'] == 1){
								$_data['title'] = $_data['goods_title'];
								$_data['path'] = $_data['goods_path'];
							}
							unset($_data['goods_title']);
							unset($_data['goods_path']);
							break;
                        case '4':
                            $_data = D('TipsView')->field(['path', 'title', 'intro', 'id', 'is_pass'])->where(['id' => $row['type_id']])->find();
                            break;
                        case '5':
                            $m = new \Home\Model\ThemeViewModel;
                            $_data = $m->field(['id', 'title', 'url', 'path', 'content'])->where(['id' => $row['type_id']])->find();
                            if(is_numeric($_data['url']))$_data['url'] == '';
                            break;
                    }
                    $data[$key]['relation'] = $_data;
                }
		}

		//将消息都转为已读
		$ids = [];
		foreach($data as $key => $row){
			if(!empty($row['relation']['path']))$data[$key]['relation']['path'] = thumb($row['relation']['path'], 1);
			if($row['is_read'] == 0){
				$ids[] = $row['id'];
			}
            if(!empty($row['member_path']))$data[$key]['member_path'] = thumb($row['member_path'],2);
            if(!empty($row['path']))$data[$key]['path'] = thumb($row['path'],2);
		}
		M('MemberMessage')->where(['id' => ['IN', join(',', $ids)]])->save(['is_read' => 1]);

		$this->ajaxReturn($data);
	}
	
}


