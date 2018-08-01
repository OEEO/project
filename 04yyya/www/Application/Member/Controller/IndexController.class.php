<?php

namespace Member\Controller;
use Member\Common\MainController;

// @className 常规工具
class IndexController extends MainController {
	public $str = '';

	/**
	 * @apiName 获取个人中心的各种数据
	 *
	 * @apiGetParam {string} token: 通信令牌
	 *
	 * @apiSuccessResponse
	 * {
     *	"tips": "83",
     *	"shangwei": "443",
     *	"follow": "2",
     *	"fans": "0",
     *	"bang": "1",
     *	"mibi": 999,
     *	"doing": "113",
     *	"daren_info": {
     *		"introduce": "",
     *      "pic_path": null,
     *      "cover_path": null,
     *      "group_path": []
     *	},
	 * 	"raise_order":{
	 *		"order_id":"235",//二次付款的订单号
	 *		"raise_id":"2",//众筹ID
	 *		"tips_times_id":"2",//众筹类目ID
	 *		"raise_title":"测试众筹标题",
	 *		"raise_times_title":"测试众筹类目标题",
	 *		"raise_times_retainage":"1000.00",//众筹尾款
	 *		"raise_times_prepay":"10.00",//众筹预约金
	 *		"order_status":"1",//(1-该订单已完成支付，2-该订单未支付，3-不存在该订单)
	 *	}
     * }
	 */
	public function getData(){
		$member_id = session('member.id');
		$data = [];
		//获取活动数
		$data['tips'] = M('tips')->where(['member_id' => $member_id, 'status' => ['IN', [1,2]]])->count();
		//获取赏味数
		$data['shangwei'] = D('ShangweiView')->where(['member_id' => $member_id, 'act_status' => ['IN', '1,2,3,4'], 'status' => 1])->getField('count');
		//获取关注数
		$data['follow'] = M('MemberFollow')->where(['member_id' => $member_id])->count();
		//获取粉丝数
		$data['fans'] = M('MemberFollow')->where(['follow_id' => $member_id])->count();
		//获取食报数
		//$data['bang'] = M('Bang')->where(['member_id' => $member_id])->count();
		//获取送米数
		//$data['mibi'] = D('WealthView')->where(['member_id' => $member_id, 'type' => 'huoqu', 'wealth' => 2])->getField('sum');
        //$data['mibi'] = $data['mibi']==null?0:(int)$data['mibi'];
		//获取进行中的报名活动数量
		$data['doing'] = D('GetOrderWareView')->distinct(true)->field('order_id')->where(['member_id' => $member_id,'type' => 0, 'act_status' => ['IN', [0,1]] , 'status' => 1])->count();
		//获取进行中的商品数量
		$data['going'] = D('GetOrderWareView')->distinct(true)->field('order_id')->where(['member_id' => $member_id,'type' => 1, 'act_status' => ['IN', [0,1]] , 'status' => 1])->count();

		//待付款
		$data['unpaid_tips'] = D('GetOrderWareView')->distinct(true)->field('order_id')->where(['member_id' => $member_id,'type' => 0, 'act_status' => 0 , 'status' => 1])->count();
		$data['unpaid_goods'] = D('GetOrderWareView')->distinct(true)->field('order_id')->where(['member_id' => $member_id,'type' => 1, 'act_status' => 0 , 'status' => 1])->count();
		$data['unpaid_raises'] = D('GetOrderWareView')->distinct(true)->field('order_id')->where(['member_id' => $member_id,'type' => 2, 'act_status' => 0 , 'status' => 1])->count();

        //获取达人基本信息
        $data['daren_info'] = D('DarenInfoView')->where(['A.id'=>$member_id])->find();
        $data['daren_info']['introduce'] = trim(strip_tags($data['daren_info']['member_introduce']));
        //$pic_path = $data['daren_info']['pic_path'];
        $data['daren_info']['pic_path'] = thumb($data['daren_info']['pic_path'],2);
		//众筹二次付款
		$raise_order = D('RaiseOrderWaresView')->where(['B.member_id'=>$member_id ,'A.type'=>2,'B.order_pid'=>['EXP','is not null'],'act_status'=>0,'B.status'=>1])->order('create_time desc')->find();
		if(!empty($raise_order)){
			$data['raise_order']=[
				'order_id'=>$raise_order['order_id'],
				'limit_pay_time'=>$raise_order['limit_pay_time']?$raise_order['limit_pay_time']:'',
				'raise_id'=>$raise_order['raise_id'],
				'tips_times_id'=>$raise_order['tips_times_id'],
				'raise_title'=>$raise_order['raise_title'],
				'raise_times_title'=>$raise_order['raise_times_title'],
				'raise_times_retainage'=> $raise_order['raise_times_price'] -  $raise_order['raise_times_prepay'],
				'raise_times_prepay'=>$raise_order['raise_times_prepay'],
			];
		}else{
			$data['raise_order']=array();
		}
		if(!empty($raise_order['order_id'])){
			$count = M('Order')->where(['id'=>$raise_order['order_id'],'act_status'=>['NEQ',0]])->count();
			if($count>0){
				$data['raise_order']['order_status']=1;
			}else{
				$data['raise_order']['order_status']=2;
			}
		}else{
			$data['raise_order']['order_status']=3;
		}

        //达人封面图
        if(!empty($data['daren_info']['cover_pic_id'])){
            $path = M('pics')->where(['id'=>$data['daren_info']['cover_pic_id']])->getField('path');
            $data['daren_info']['cover_path'] = thumb($path);
        }else{
            $data['daren_info']['cover_path'] = $data['daren_info']['pic_path'];
        }

        //会员拿手菜图组
        if(!empty($data['daren_info']['pic_group_id'])){
            $group_path = M('pics')->where(['group_id'=>$data['daren_info']['pic_group_id']])->getField('path',true);
            foreach($group_path as $row){
                $data['daren_info']['group_path'][] = thumb($row,3);
            }
        }else{
            $data['daren_info']['group_path'] = array();
        }

        $data['nickname'] = M('Member')->where(['id' => $member_id])->getField('nickname');
//		$params = [
//			'title' => '2255',
//			'sdate' => '2017年02月06日 12时30分',
//			'edate' => '2017年02月06日 12时40分',
//			'address' => '5156156',
//			'platform_member' => '达人',
//			'platform' => '吖咪',
//			'wx' => '56156156',
//		];
//		$this->push_Message(278518, $params,'SMS_36105291', 'wx', null,3, '25813', 0, 0);

		$this->ajaxReturn($data);
	}

    /**
     * @apiName 更换达人背景图
     *
     * @apiGetParam {string} token: 通信令牌
     * @apiPostParam {int} pic_id: 图片ID
     *
     * @apiSuccessResponse
     * {
     * 	"status": 1,
     *	 "info": "更新成功"
     * }
     *
     * @apiErrorResponse
     * {
     * 	"status": 0,
     *	 "info": "失败原因"
     * }
     */
    Public function centerCover(){
        $member_id = session('member.id');
        $pic_id = I('post.pic_id');

        if(empty($pic_id))$this->error('未选择图片');
		$_id = M('MemberInfo')->where(['member_id' => $member_id])->getField('cover_pic_id');
		M('pics')->where(['id' => $_id])->save(['is_used' => 0]);
		M('pics')->where(['id' => $pic_id])->save(['is_used' => 1]);
        M('MemberInfo')->where(['member_id'=>$member_id])->data(['cover_pic_id'=>$pic_id])->save();
        $this->success('更新成功');
       /* $cover_group_id = M('MemberInfo')->where(['member_id'=>$member_id])->getField('cover_group_id');
        if(!empty($cover_group_id)){
            M('pics')->where(['id'=>$pic_id])->data(['group_id'=>$cover_group_id])->save();
            $this->success('更改成功');
        }else{
            $cover_group_id = M('PicsGroup')->data(['type'=>3])->add();
            M('pics')->where(['id'=>$pic_id])->data(['group_id'=>$cover_group_id])->save();
            $this->success('更改成功');
        }*/
    }

	/**
	 * @apiName 会员信息回调
	 * 
	 * @apiGetParam {string} token: 通信令牌
	 * @apiGetParam {int} member_id: 要获取信息的会员ID（默认为当前登录的会员）
	 * 
	 * @apiSuccessResponse
	 *  {
	 *      "id": "9979",
	 *      "username": "18664861856",
	 *      "nickname": "弦霄",
	 *      "telephone": "18664861856",
	 *      "unionid": "oMOIruJjrhC4BwhMGN1HJ0GgY9Wk",
	 *      "datetime": "2016-01-06 20:13:08",
	 *      "status": "1",
	 *      "signature": null,
	 *      "sex": null,
	 *      "path": null,
	 *      "city_name": null,
	 *      "dr_name": null,
	 *      "dr_goodat": null,
	 *      "dr_contact": null,
	 *      "dr_introduce": null,
	 *      "dr_status": null,
	 *      "tags": null
	 *  }
	 */
	public function info($myid = null){
		$mid = session('member.id');
		$mid = I('get.member_id', $mid);
		if(!empty($myid))$mid = $myid;
		$rs = D('MemberView')->where(['id' => $mid, 'status' => 1])->find();
		if(!empty($rs)) {
            $rs['path'] = thumb($rs['path'],2);
			$rs['password'] = !empty($rs['password']) ? 1 : 0;
			//$rs['dr_status'] = $rs['dr_status']?:0;
            //$rs['openid'] = empty($rs['openid'])?'':$rs['openid'];
            $rs['birth'] = empty($rs['birth'])?'':$rs['birth'];
			//读取城市
			$rs['area_id'] = '';
			$rs['area_name'] = '';
			$rs['city_id'] = '';
			$rs['city_name'] = '';
			$rs['province_id'] = '';
			$rs['province_name'] = '';
			$city_rs = M('citys')->where(['id' => $rs['citys_id']])->find();
			if(strpos($city_rs['alt'], '市') !== false){
				$rs['city_id'] = $city_rs['id'];
				$rs['city_name'] = $city_rs['name'];
				$_city_rs = M('citys')->where(['id' => $city_rs['pid']])->find();
				$rs['province_id'] = $_city_rs['id'];
				$rs['province_name'] = $_city_rs['name'];
			}elseif(strpos($city_rs['alt'], '区') !== false || strpos($city_rs['alt'], '县') !== false){
				$rs['area_id'] = $city_rs['id'];
				$rs['area_name'] = $city_rs['name'];
				$_city_rs = M('citys')->where(['id' => $city_rs['pid']])->find();
				$rs['city_id'] = $_city_rs['id'];
				$rs['city_name'] = $_city_rs['name'];
				$__city_rs = M('citys')->where(['id' => $_city_rs['pid']])->find();
				$rs['province_id'] = $__city_rs['id'];
				$rs['province_name'] = $__city_rs['name'];
			}
            $rs['dr_contact'] = empty($rs['dr_contact'])?'':$rs['dr_contact'];
            $rs['dr_introduce'] = empty($rs['dr_introduce'])?'':$rs['dr_introduce'];
            $rs['path'] = empty($rs['path'])?'':$rs['path'];
            $rs['pic_id'] = empty($rs['pic_id'])?'':$rs['pic_id'];
            $rs['signature'] = empty($rs['signature'])?'':$rs['signature'];
            $rs['unionid'] = empty($rs['unionid'])?'':$rs['unionid'];
            $dr_status = M('MemberApply')->where(['member_id'=>$mid,'type'=>2,'type_id'=>18])->field('is_pass')->find();
            $rs['dr_status'] = (empty($dr_status))?-1:$dr_status['is_pass'];
			$tag = M('MemberTag')->where(['member_id' => $mid, 'tag_id' => 18])->find();
			if(!empty($tag))$rs['dr_status'] = 1;
			$default_address = M('MemberAddress')->field('id,citys_id,address')->where(['member_id' => $mid, 'is_default' => 1,'status'=>1])->find();
			if(!empty($default_address)){
				$city_alt = D('CityView')->where(['district_id'=>$default_address['citys_id']])->find();
				$rs['default_address'] = $city_alt['province_name'].$city_alt['province_alt'].$city_alt['city_name'].$city_alt['city_alt'].$city_alt['district_name'].$city_alt['district_alt'].$default_address['address'];
				$rs['default_address_id'] =$default_address['id'];
			}

			if($myid !== null) return $rs;
			$this->ajaxReturn($rs);
		} else
			$this->error('用户不存在或已被禁用！');
	}

	/**
	 * @apiName 自动登录接口
	 * 
	 * @apiGetParam {string} token: 通信令牌
	 * @apiPostParam {int} id: 用于自动登录的会员ID
	 * @apiPostParam {int} skey: 用于自动登录的skey
	 * 
	 * @apiSuccessResponse
	 * {
	 *     "info": {
	 *         "info": {
	 *             "id": "9979",
	 *             "username": "18664861856",
	 *             "nickname": "弦霄",
	 *             "telephone": "18664861856",
	 *             "unionid": "oMOIruJjrhC4BwhMGN1HJ0GgY9Wk",
	 *             "datetime": "2016-01-06 20:13:08",
	 *             "status": "1",
	 *             "signature": null,
	 *             "sex": null,
	 *             "path": null,
	 *             "city_name": null,
	 *             "dr_name": null,
	 *             "dr_goodat": null,
	 *             "dr_contact": null,
	 *             "dr_introduce": null,
	 *             "dr_status": null,
	 *             "tags": null
	 *         },
	 *         "skey": "TYwMzhlYWQyMmQ0OTVmY2JiM2QyMmV"
	 *     },
	 *     "status": 1,
	 *     "url": ""
	 * }
	 * @apiErrorResponse
	 * {
	 *	 "status": 状态码,
	 *	 "info": "失败原因"
	 * }
	 */
	Public function autologin(){
		$id = I('post.id');
		$skey = I('post.skey');
		if(empty($skey) || empty($id))$this->error('SKEY和会员ID必须的！');
	
		$rs = M('member')->where(['id' => $id, 'status' => 1])->find();
		if(empty($rs))$this->error('要自动登录的会员不存在或已被禁用！');
		if(createSkey($id, $rs['register_time']) == $skey){
			if(empty($rs['telephone'])){
				$this->error('no_telephone');
			}
			$tags = M('MemberTag')->join('join `__TAG__` on __TAG__.id=tag_id')->where(['member_id' => $rs['id']])->getField('tag_id', true);
			$info = $this->info($rs['id']);
			foreach($tags as $k =>$v){
				if($v == 18){
					$t = $tags[0];
					$tags[0] = $v;
					$tags[$k] = $t;
				}
			}
			$info['tags'] = empty($tags)? [] :$tags;
			session('member', $info);

			//获取最后一次优惠券领取时间
			$coupon = D('MemberCouponView')->where(['member_id' => $rs['id'], 'type' => 0])->order('A.datetime desc')->find();
			if(!empty($coupon)){
				$couponGetTime = strtotime($coupon['datetime']);
				//获取上次登录的时间
				$lastLoginTime = M('MemberLoginLog')->where(['member_id' => $rs['id']])->order('id desc')->getField('datetime');
				$lastLoginTime = strtotime($lastLoginTime) ?: 0;
				//判断是否有新的优惠券
				if($couponGetTime > $lastLoginTime){
					$info['coupon'] = [
						'name' => $coupon['name'],
						'value' => $coupon['value']
					];
				}
			}
			//记录本次登录时间
			M('MemberLoginLog')->add([
				'member_id' => $id,
				'channel' => $this->channel,
				'ip' => get_client_ip(1),
				'version' => session('version')?:''
			]);
			$this->success([
				'info' => $info,
				'skey' => createSkey($rs['id'], $rs['register_time'])
			]);
		}
		$this->error('SKEY不正确或已过期！');
	}

	/**
	 * @apiName 记录设备的ID和token 用于后续APP消息推送
	 *
	 * @apiGetParam {string} token: 通信令牌
	 *
	 * @apiPostParam {string} device: APP设备推送token
	 *
	 * @apiSuccessResponse
	 * {
	 *	 "status": 1,
	 *	 "info": "记录设备成功!"
	 * }
	 * @apiErrorResponse
	 * {
	 *	 "status": 0,
	 *	 "info": "记录设备失败!"
	 * }
	 */
	public function setDevice(){
		$device = I('post.device', null);
		if(!empty($device)){
			//清空角标
			getRedis()->set($device . '_badge', 0);
			session('device', $device);
			$rs = M('MemberDevice')->where(['device' => $device])->find();
			if(empty($rs)){
				$data = [
					'type' => strpos(session('version'), 'ios_v') === false ? 1 : 0,
					'channel' => in_array($this->channel, [7,8,9]) ? 1 : 0,
					'device' => $device
				];
				if(session('?member'))$data['member_id'] = session('member.id');
				M('MemberDevice')->add($data);
			}
			$this->success('记录设备成功!');
		}
		$this->error('记录设备失败!');
	}

	/**
	 * @apiName 短信发送接口
	 *
	 * @apiGetParam {string} token: 通信令牌
	 * @apiPostParam {string} telephone: 接收短信的目标手机号
	 *
	 * @apiSuccessResponse
	 * {
	 *	 "status": 1,
	 *	 "info": "成功"
	 * }
	 *
	 * @apiErrorResponse
	 * {
	 *	 "status": 状态码,
	 *	 "info": "失败原因"
	 * }
	 */
	Public function sendSMS(){
		$telephone = I('post.telephone');

		$from = I('post.from');

		$code = rand(1000, 9999);
		if(session('?smsverify')){
			if(session('smsverify.time') + C('SMS_CONFIG.spacing') > time())
				$this->error('请在 '. (session('smsverify.time') + C('SMS_CONFIG.spacing') - time()) .' 秒后再次发送！');
		}
		if(preg_match('/^1\d{10}$/', $telephone)){
			session('smsverify', [
				'code' => $code,
				'telephone' => $telephone,
				'time' => time()
			]);
			$params['code'] = (string)$code;
/*
			if (I('imgcode')) {
				$token = I('get.token');
				$key = 'code_'.$token;
				$redis = getRedis();
				$code = $redis->get($key);
				if (I('imgcode') != $code) {
					$this->error('图片验证码不正确!');
				}
			}
			*/
			//新的验证码短信暂不输出来源
			if(in_array($this->channel,[7,8,9])){
				$channel = 1;
			//	$params['platform'] = '我有饭';
				$token = I('get.token');
				$key = 'code_'.$token;
				$redis = getRedis();
				$code = $redis->get($key);
				if (strtolower(I('imgcode')) != strtolower($code)) {
					$this->error('图片验证码不正确!');
				}
				$message = smsSend($telephone,'SMS_107810071', $params, $channel);
//				smsSend($telephone,'SMS_107810071', $params, $channel);
			}else{
				$channel = 0;
			//	$params['platform'] = '吖咪';
				$message = smsSend($telephone,'SMS_107810071', $params, $channel);
			}
			$this->saveIp($telephone, $from);

//			$data['status'] = sms_send($telephone, $code, true) ? 1 : 0;

//			$message = smsSend($telephone,'SMS_35915140', $params, $channel);
//			$message = smsSend($telephone,'SMS_107810071', $params, $channel);

			if( $message == 1){
				$data['status'] =  1 ;
				$data['spacing_time'] = C('SMS_CONFIG.spacing');
				$this->ajaxReturn($data);
			}else{

				$rs = json_decode($message);
				if(stripos($rs->Message, 'limit reaches.')){
					$this->error('已达到今天限制的发送次数了');
				}
				$this->error($rs->Message);
			}
		}
		$this->error('手机号填写不正确！');
	}
	
	//发送短信是记录ip手机号等信息
	public function saveIp($tel,$from='') {
		$Ip = M('LoginIp');
		$data = array(
			'ip' => get_client_ip(),
			'tel' => $tel,
			'from' => $from,
		);
		if ($Ip->where($data)->find()) {
			$Ip->where($data)->setInc('count', 1);
		} else {
			$Ip->add($data);
		}
	}

	//图片验证码
	public function img() {
		if (I('imgcode')) {
//			$data['code1'] = base64_encode(session('ImgVerify'));
//			$data['code2'] = base64_decode(I('code'));
			$token = I('get.token');
			$key = 'code_'.$token;
			$redis = getRedis();
			$code = $redis->get($key);
			$data['imgcode'] = I('imgcode');
			$data['code'] = $code;
			if (strtolower(I('imgcode')) == strtolower($code)) {
				$data['statu'] = 1;
			} else {
				$data['statu'] = 0;
			}
			$this->ajaxReturn($data);
		} else {
//			$code = rand(1000, 9999);
			$charset = 'abcdefghkmnprstuvwxyzABCDEFGHKMNPRSTUVWXYZ23456789';
			$_len = strlen($charset)-1;
        	for ($i=0;$i<4;$i++) {
            	$code .= $charset[mt_rand(0,$_len)];
        	}
//			session('ImgVerify', $code);
			$token = I('get.token');
			$key = 'code_'.$token;
			$redis = getRedis();
    		$redis->set($key,$code);
			$redis->expire($key, 1200);
			$data['code'] = base64_encode($code);
			$data['token'] = $token;
			$data['c'] = $code;
			$data['key'] = $key;
			$this->ajaxReturn($data);
		}
	}


    public function getredis() {
    	$redis = getRedis();
    	echo $redis->get("test");

    }

	/**
	 * @apiName 修改个人信息
	 *
	 * @apiGetParam {string} token: 通信令牌
     * @apiPostParam {int} pic_id: 头像图片ID
	 * @apiPostParam {string} nickname: 用户昵称
	 * @apiPostParam {string} city_id: 城市ID
	 * @apiPostParam {string} sex: 用户性别（0-保密 1-男 2-女）
	 * @apiPostParam {string} birth: 出生年月日
	 * @apiPostParam {string} signature: 个性签名
	 *
	 * @apiSuccessResponse
	 * {
	 *	 "status": 1,
	 *	 "info": "成功"
	 * }
	 *
	 * @apiErrorResponse
	 * {
	 *	 "status": 0,
	 *	 "info": "失败原因"
	 * }
	 */
	Public function modifyInfo(){
		//$data = ['id' => session('member.id')];
        $data['pic_id'] = I('post.pic_id');
		$data['nickname'] = $_POST['nickname'];
		$data['citys_id'] = I('post.city_id');
		$data['sex'] = I('post.sex', 0);
		$data['birth'] = I('post.birth', '');
		$data['birth'] = !empty($data['birth']) ? $data['birth'] : '';
		$data['signature'] = I('post.signature', '');

//		$rs = M('pics')->where(['id' => $data['pic_id']])->find();
//		if(!empty($rs) && $rs['member_id'] != session('member.id')){
//			$this->error('该图片不属于你,无法使用!');
//		}

        //允许单独修改其中一项
        foreach($data as $key=>$row){
            if(empty($data[$key]))unset($data[$key]);
        }

        //M('member')->where(['id' => session('member.id')])->save(['nickname' => $data['nickname'] , 'pic_id' => $data['pic_id']]);
        M('member')->where(['id' => session('member.id')])->save($data);
		M('member_info')->where(['member_id' => session('member.id')])->save($data);
		
		$tags = M('MemberTag')->join('join `__TAG__` on __TAG__.id=tag_id')->where(array('member_id' => session('member.id')))->getField('name', true);
		$info = $this->info(session('member.id'));
		$info['tags'] = $tags;

		session('member', $info);

		$this->success($info);
	}

    /**
     * @apiName 绑定/改绑手机
     *
     * @apiGetParam {string} token: 通信令牌
     * @apiPostParam {string} telephone: 新手机号
     * @apiPostParam {int} verifycode: 验证码
     *
     * @apiSuccessResponse
     * {
     *	 "status": 1,
     *	 "info": "成功"
     * }
     *
     * @apiErrorResponse
     * {
     *	 "status": 状态码,
     *	 "info": "失败原因"
     * }
     */
    Public function resetPhone(){
        $verifycode = I('post.verifycode');
        $telephone = I('post.telephone');
        if(empty($verifycode) || empty($telephone))$this->error('验证码和电话号码必填！');
        if(session('smsverify.time') + C('SMS_CONFIG.spacing')<time())$this->error('验证码已过期');
        if(session('smsverify.telephone')!=$telephone)$this->error('非法号码');
        if(session('smsverify.code')!=$verifycode)$this->error('验证码不正确');
        $data = array();
        $data['id'] = session('member.id');
        $data['telephone'] = $telephone;
        $rs = M('member')->data($data)->save();
        if($rs!==false){
            $this->success('修改成功！');
        }else{
            $this->error('修改失败，请稍后再试！');
        }
    }

    /**
     * @apiName 更换密码
     *
     * @apiGetParam {string} token: 通信令牌
     * @apiPostParam {string} oldpassword: 旧密码
     * @apiPostParam {string} newpassword: 新密码
     *
     * @apiSuccessResponse
     * {
     *	 "status": 1,
     *	 "info": "成功"
     * }
     *
     * @apiErrorResponse
     * {
     *	 "status": 状态码,
     *	 "info": "失败原因"
     * }
     */
    Public function resetPassword(){
        $oldpassword = I('post.oldpassword');
        $newpassword = I('post.newpassword');
        $hasPassword = I('post.hasPassword');

        if($hasPassword == 1){
            if($oldpassword == '' || $newpassword == '')$this->error('密码不能为空');
            //if($oldpassword == $newpassword)$this->error('旧密码和新密码相同');
            $rs = M('member')->where(array('id'=>session('member.id'),'password'=>md5(md5($oldpassword) . C('pwdCode'))))->find();
            if(empty($rs))$this->error('旧密码不正确');
        }
        
        $data = array();
        $data['id'] = session('member.id');
        $data['password'] = md5(md5($newpassword) . C('pwdCode'));
        $rs = M('member')->data($data)->save();
        if($rs > 0){
            $this->success('修改成功');
        }else{
            $this->error('修改失败，请稍后重试！');
        }
    }

	/**
	 * @apiName 短信注册/登录接口
	 *
	 * @apiGetParam {string} token: 通信令牌
	 * @apiPostParam {string} telephone: 手机号码
	 * @apiPostParam {string} smsverify: 验证的短信
	 *
	 * @apiSuccessResponse
	 * {
	 *     "info": {
	 *         "info": {
	 *             "id": "9979",
	 *             "username": "18664861856",
	 *             "nickname": "弦霄",
	 *             "telephone": "18664861856",
	 *             "unionid": "oMOIruJjrhC4BwhMGN1HJ0GgY9Wk",
	 *             "datetime": "2016-01-06 20:13:08",
	 *             "status": "1",
	 *             "signature": null,
	 *             "sex": null,
	 *             "path": null,
	 *             "city_name": null,
	 *             "dr_name": null,
	 *             "dr_goodat": null,
	 *             "dr_contact": null,
	 *             "dr_introduce": null,
	 *             "dr_status": null,
	 *             "tags": null
	 *         },
	 *         "isRegister": 1,
	 *         "skey": "TYwMzhlYWQyMmQ0OTVmY2JiM2QyMmV"
	 *     },
	 *     "status": 1,
	 *     "url": ""
	 * }
	 * @apiErrorResponse
	 * {
	 *	 "status": 状态码,
	 *	 "info": "失败原因"
	 * }
	 */
	Public function register(){
		$telephone = (string)I('post.telephone');
		$datetime = date('Y-m-d H:i:s');

		if(!preg_match('/^1\d{10}$/', $telephone)){
			$this->error('手机号格式不正确!');
		}

		if ($telephone == '11111111111' || $telephone == '12222222222' || $telephone == '15920324398') {

		} else {
			if(!(in_array($telephone, $this->tels) && I('post.smsverify') == '2016') && !session('?other')){
				if(!session('?smsverify'))$this->error('请先进行短信验证。');
				if(session('smsverify.telephone') != $telephone) {
	                $this->error('请输入发送验证码的手机号！');
	            } elseif (session('smsverify.code') != I('post.smsverify') && $telephone != 17588000099) {
	                if(!session('?smsverify_nums'))session('smsverify_nums', 4);
	                else session('smsverify_nums', session('smsverify_nums') - 1);
	                if((int)session('smsverify_nums') <= 0){
	                    session('smsverify', null);
	                    $this->error('验证码错误5次，请重新获取！');
	                }
	                $this->error('验证码不正确，请重新提交！');
	            }
			}			
		}

		$member = M('MemberView');
		$openid_rs = $openid_data = [];
		$rs = $member->where(['telephone' => $telephone, 'status' => 1])->order('id desc')->find();
		if(session('?openid') || session('?wxUser')){
			$openid = session('?wxUser')?session('wxUser.openid'):session('openid');
			$openid_rs = $member->where(['openid' => $openid, 'telephone' => ['exp', "REGEXP '^1[358][0-9]{9}$'"]])->find();
			$openid_dt = M('openid')->where(['openid' => $openid])->find();
			\Think\Log::write('$openid_dt信息：'.json_encode($openid_dt));
		}
		//判断是否有数据
		if(empty($rs)){
			//没有数据则先注册
			$nickname = '手机号_' . preg_replace('/^(\d{3})(\d{4})(\d{4})$/', '${1}****$3', $telephone);
			$data = [
				'username' => $telephone,
				'telephone' => $telephone,
				'nickname' => $nickname,
				'register_time' => time(),
				'datetime' => $datetime,
				'channel' => $this->channel,
				'invitecode' => createCode(32, false)
			];
			$_data = [];
			if(empty($openid_data))$openid_data = [];
			$openid_data['type'] = $this->openidType;
			if(session('?openid') || session('?wxUser')){
				$openid_data['openid']  = session('?wxUser')?session('wxUser.openid'):session('openid');
			}
			//判断是否是微信小程序的授权信息
			if(session('?session_timeout') && session('session_timeout') < time() && ($encryptedData = I('post.encryptedData', false)) && ($vi = I('post.vi', false))){
				$info = \Common\Util\Wxapp::instance()->decryptData($encryptedData, $vi, session('session_key'));
				if(!empty($info['nickname']))$openid_data['nickname'] = $data['nickname'] = $info['nickname'];
				if($info['unionid'])$openid_data['unionid'] = $info['unionid'];
				$_data['sex'] =$openid_data['sex'] = !empty($info['gender'])?$info['gender']:0;
				//获取城市ID
				$city_id = D('CityView')->where(['city_name' => $info['city'], 'province_name' => $info['province']])->getField('city_id');
				$_data['citys_id'] = $openid_data['city_id'] = $city_id;
				$_data['portrait'] = $info['avatarUrl'];
			}

			if(!empty($openid_dt['member_id'])) {
				$id = $openid_dt['member_id'];
				$sa_data['telephone'] = $telephone;
				M('member')->where(['id' => $id])->save($sa_data);
			}else{
				//会员主表数据插入
				$id = M('member')->add($data);
			}
			//判断是否有领取过微信卡券
			if(session('?openid')){
				$member_coupon = M('member_coupon');
				$result = $member_coupon->where(['open_id'=>session('openid')])->find();
				if(!empty($result)){
					$member_coupon->where(['open_id'=>session('openid')])->save(['member_id'=>$id]);
				}
			}

			//添加会员钱包
			foreach(C('WEALTH') as $key => $arr){
				M('MemberWealth')->add([
					'member_id' => $id,
					'wealth' => $key,
					'quantity' => $arr['init']
				]);
			}

			//判断是否有注册优惠券,并发券
			$coupon = M('coupon')->where(['category' => 2])->find();
			if(!empty($coupon)){
				M('MemberCoupon')->add([
					'member_id' => $id,
					'coupon_id' => $coupon['id'],
					'sn' => createCode(8)
				]);
			}

			$data = ['member_id' => $id];
			$openid_data['member_id'] = $id;
			//会员副表整理后插入数据
			if(!empty($_data)){
				if(!empty($_data['portrait'])){
					//将微信头像抓取到本地保存并生成缩略图
					$pic_id = M('pics')->add([
						'member_id' => $id,
						'type' => 2,
						'path' => $_data['portrait'],
						'original_path' => $_data['headimgurl'],
						'is_used' => 1
					]);
					//将图片更新到数据库中
					M('member')->where(['id' => $id])->save(['pic_id' => $pic_id]);
					$dt = [
						'pic_id' => $pic_id,
						'path' => $_data['portrait'],
					];
					getRedis()->rPush(str_replace('.', '', DOMAIN) . '_img_down', json_encode($dt));
				}

				$data['sex'] =$_data['sex'];
				$data['citys_id'] =$_data['citys_id'];
				$openid_data['pic_id'] = $pic_id;
			}

			if(!empty($openid_rs)){
				M('MemberInfo')->where(['member_id' => $id])->save($data);
				M('Openid')->where(['member_id' => $id,'type'=>$openid_data['type'],'openid'=>session('openid')])->save($openid_data);
			}else{
				$info = M('MemberInfo')->where(['member_id' => $id])->find();
				if(!empty($info)){
					M('MemberInfo')->where(['member_id' => $id])->save($data);
				}else{
					M('MemberInfo')->add($data);
				}
				if(isset($openid_dt['id'])) {
					M('Openid')->where(['id' => $openid_dt['id']])->save($openid_data);
//				}else{
//					M('Openid')->add($openid_data);
				}

			}

			//若为扫描二维码进来的用户,则做标注
			if(session('?openid'))
				M('QrcodeUsers')->where(['openid' => session('openid')])->save(['member_id' => $id]);

			//再登录
			$info = $this->info($id);
			$info['tags'] = [];
			session('member', $info);
			//记录本次登录时间
			M('MemberLoginLog')->add([
				'member_id' => $id,
				'channel' => $this->channel,
				'ip' => get_client_ip(1),
				'version' => session('version')?:''
			]);
			session('openid', null);
			if(session('?other'))$this->success('注册成功!');
			if(session('?device')){
				M('MemberDevice')->where(['device' => session('device')])->save(['member_id' => $id]);
			}
			//输出保存密码的skey
			$this->success([
				'info' => $info,
				'isRegister' => 1,
				'skey' => createSkey($id, $info['register_time'])
			]);
		}else{
			$_data = [];
			$data = ['id' => $rs['id']];
			$openid_data = ['member_id' => $rs['id']];
			if(session('?openid') || session('?wxUser')){
				if(!empty($openid_rs) && session('?openid')){
					session('telephone', $telephone);
					$this->error('Double_account');
				}
				$openid_data['openid'] = session('?wxUser')?session('wxUser.openid'):session('openid');
				if(session('?unionid'))$openid_data['unionid'] = session('unionid');

				if(session('?session_timeout') && session('session_timeout') < time() && ($encryptedData = I('post.encryptedData', false)) && ($vi = I('post.vi', false))){
					$info = \Common\Util\Wxapp::instance()->decryptData($encryptedData, $vi, session('session_key'));
					\Think\Log::write('$info信息：'.json_encode($info));
					if(!empty($info['nickname']))$openid_data['nickname'] = $data['nickname'] = $info['nickname'];
					if(isset($info['unionid']))$openid_data['unionid'] = $info['unionid'];
					if(empty($rs['pic_id'])) {
						$pic_id = M('pics')->add([
							'member_id' => $rs['id'],
							'type' => 2,
							'path' => $info['avatarUrl'],
							'original_path' => $info['avatarUrl'],
							'is_used' => 1
						]);
						if (!empty($pic_id)){
							$openid_data['pic_id'] = $data['pic_id'] = $pic_id;
							$dt = [
								'pic_id' => $pic_id,
								'path' => $info['portrait'],
							];
							getRedis()->rPush(str_replace('.', '', DOMAIN) . '_img_down', json_encode($dt));
						}
					}
					if(empty($rs['sex']) && !empty($info['gender']))$openid_data['sex'] = $_data['sex'] = $info['gender']?:0;
					if(!empty($info['city']))$_data['city'] = $info['city'];
					if(!empty($info['province']))$_data['province'] = $info['province'];
				}

				//会员主表数据插入
				M('Member')->where(['id'=>$rs['id']])->save($data);

				//判断是否有领取过微信卡券
				$member_coupon = M('member_coupon');
				$result = $member_coupon->where(['open_id'=>session('openid')])->find();
				if(!empty($result)){
					$member_coupon->where(['open_id'=>session('openid')])->save(['member_id'=>$rs['id']]);
				}

				//获取城市ID
				$city_id = D('CityView')->where(['city_name' => $_data['city'], 'province_name' => $_data['province']])->getField('city_id');

				$data = [];
				if(isset($_data['sex']))$data['sex'] = $_data['sex'];
				if(!empty($city_id))$openid_data['city_id'] = $data['citys_id'] = $city_id;
				M('MemberInfo')->where(['member_id' => $rs['id']])->save($data);
				//查找更多该账号对应的Openid
				$openid_ids = M('Openid')->where(['member_id'=>$rs['id'],'type'=>$this->openidType,'openid'=>['NEQ',session('openid')]])->getField('id',true);

				if(!empty($openid_ids)){
					M('Openid')->where(['id' => join(',',$openid_ids)])->save(['member_id'=>['EXP','NULL']]);
				}
				if(!empty($openid_dt)){
					M('Openid')->where(['id' => $openid_dt['id']])->save($openid_data);
//				}else{
//					M('Openid')->add($openid_data);
				}
			}

			//有了数据则登录
            $info = $this->info($rs['id']);
			$tags = M('MemberTag')->join('join `__TAG__` on __TAG__.id=tag_id')->where(['member_id' => $rs['id']])->getField('tag_id', true);
			$info['tags'] = empty($tags)? [] :$tags;
			session('member', $info);
			//记录本次登录时间
			M('MemberLoginLog')->add([
				'member_id' => $rs['id'],
				'channel' => $this->channel,
				'ip' => get_client_ip(1),
				'version' => session('version')?:''
			]);

			if(session('?other'))$this->success('登录成功!');
			if(session('?device')){
				M('MemberDevice')->where(['device' => session('device')])->save(['member_id' => $rs['id']]);
			}

			$code = I('post.code');
			if ($code) {
				$this->wechat = \Common\Util\Wxapp::instance();
	            $json = $this->wechat->getSession($code);
	            $info['wxapp'] = $json;
	            $wxapp_rs = M('Openid')->where(['member_id' => $rs['id'], 'type' => 3])->find();
	            if (!$wxapp_rs) {
	            	$wxapp_data['type'] = 3;
	            	$wxapp_data['member_id'] = $rs['id'];
	            	$wxapp_data['unionid'] = $json['unionid'];
	            	$wxapp_data['openid'] = $json['openid'];
	            	$wxapp_data['nickname'] = $info['nickname'];
	            	M('Openid')->add($wxapp_data);
	            }
			}


			$this->success([
				'info' => $info,
				"isRegister" => 0,
				'skey' => createSkey($rs['id'], $info['register_time'])
			]);
		}
	}

	/**
	 * @apiName 合并并登录接口
	 *
	 * @apiGetParam {string} token: 通信令牌
	 *
	 * @apiSuccessResponse
	 * {
	 *     "info": {
	 *         "info": {
	 *             "id": "9979",
	 *             "username": "18664861856",
	 *             "nickname": "弦霄",
	 *             "telephone": "18664861856",
	 *             "openid": "o6FWTtzBPfyuMY1HIdy2Ws5iulB8",
	 *             "unionid": "oMOIruJjrhC4BwhMGN1HJ0GgY9Wk",
	 *             "datetime": "2016-01-06 20:13:08",
	 *             "status": "1",
	 *             "signature": null,
	 *             "sex": null,
	 *             "path": null,
	 *             "city_name": null,
	 *             "dr_name": null,
	 *             "dr_goodat": null,
	 *             "dr_contact": null,
	 *             "dr_introduce": null,
	 *             "dr_status": null,
	 *             "tags": null
	 *         },
	 *         "isRegister": 1,
	 *         "skey": "TYwMzhlYWQyMmQ0OTVmY2JiM2QyMmV"
	 *     },
	 *     "status": 1,
	 *     "url": ""
	 * }
	 * @apiErrorResponse
	 * {
	 *	 "status": 状态码,
	 *	 "info": "失败原因"
	 * }
	 */
	Public function MergeAccount(){
		if(session('?telephone') && session('?openid')){
			$telephone = session('telephone');
			$openid = session('openid');

			$rs = M('MemberView')->where(['telephone' => $telephone])->find();
			if(empty($rs))$this->error('手机号对应的账号不存在!');
			if(empty($_rs))$this->error('微信对应的账号不存在!');
			//将原始资料所属手机号改为9开头
			M('member')->where(['telephone' => $telephone])->save(['telephone' => '9999' . createCode(7)]);
			//更新资料
			M('openid')->where(['openid' => $openid,'type'=>$this->openidType])->save([
				'telephone' => $telephone,
				'username' => $telephone
			]);

			$info = $this->info($_rs['id']);
			$tags = M('MemberTag')->join('join `__TAG__` on __TAG__.id=tag_id')->where(['member_id' => $_rs['id']])->getField('tag_id', true);
			$info['tags'] = empty($tags)?array():$tags;
			session('member', $info);
			//记录本次登录时间
			M('MemberLoginLog')->add([
				'member_id' => $_rs['id'],
				'channel' => $this->channel,
				'ip' => get_client_ip(1),
				'version' => session('version')?:''
			]);
			$this->success([
				'info' => $info,
				"isRegister" => 0,
				'skey' => createSkey($rs['id'], $info['register_time'])
			]);
		}
		$this->error('合并失败!');
	}

	/**
	 * @apiName 记录邀请码
	 *
	 * @apiGetParam {string} token: 通信令牌
	 * @apiPostParam {string} invitecode: 邀请码
	 *
	 * @apiSuccessResponse
	 * {
	 *	 "status": 1,
	 *	 "info": "邀请成功!"
	 * }
	 */
	Public function invitecode(){
		$invitecode = I('post.invitecode');
		$rs = M('member')->where(['invitecode' => $invitecode])->find();
		if(!empty($rs)){
			session('invite', [
				'member_id' => $rs['id'],
				'code' => $invitecode
			]);
			$this->success('邀请成功!');
		}
		$this->error('邀请码不存在!');
	}

    /**
     * @apiName 会员提交反馈
     *
     * @apiGetParam {string} token: 通信令牌
     * @apiPostParam {string} content: 评论内容
     *
     * @apiSuccessResponse
     * {
     *	 "status": 1,
     *	 "info": "提交成功!"
     * }
     * @apiErrorResponse
     * {
     *	 "status": 0,
     *	 "info": "失败原因"
     * }
     */
    public function feedBack(){
        $content = trim(strip_tags(I('post.content')));
        $content = str_replace(' ', '', $content);
        if(empty($content))$this->error('请输入反馈内容！');
        if(preg_match('/^\s+$/',$content) > 0 || $content=='请输入您的意见或建议')$this->error('评论不能为空');
        $data = array();
        $data['member_id'] = session('member.id');
        $data['content'] = $content;
        $rs = M('feedback')->data($data)->add();
        if($rs>0){
            $this->success('提交成功！');
        }else{
            $this->error('提交失败，请稍后重试！');
        }
    }

	/**
	 * @apiName 验证该用户是否已关注
	 *
	 * @apiGetParam {string} token: 通信令牌
	 *
	 * @apiPostParam {string} open_id: 要验证的用户open_id
	 *
	 * @apiSuccessResponse
	 * {
	 *     "info": 0 or 1
	 *     "status": 1,
	 * }
	 * @apiErrorResponse
	 * {
	 *	 "status": 0,
	 *	 "info": "失败原因"
	 * }
	 */
	public function auth(){
//		if(session('member'))
//			$openid = session('member.openid');
//		else
//			$openid = I('post.open_id');
//		if(empty($openid)){
//			$this->error('没有open_id');
//		}
		$member_id = session('member.id');
		$openid = M('memberView')->where(['id' => $member_id,'type'=>$this->openidType])->getField('openid');
		if(!empty($openid))
			$info = $this->wechat->getUserInfo($openid);
		else
			$info = [];
		//$members = \Common\Util\Cache::getInstance()->get('get_coupon_member');
		//if(empty($members))$members = [];
		if(empty($openid) || (isset($info['subscribe']) && $info['subscribe'] == 1)){
//			$member = M('member')->where([$this->openidStr => $openid])->find();
			//如果没有注册,则注册
//			if(empty($member)){
//				$data = [
//					'username' => $openid,
//					$this->openidStr => $openid,
//					'register_time' => time(),
//					'invitecode' => createCode(32, false)
//				];
//				if(!empty($info['nickname']))$data['nickname'] = $info['nickname'];
//				if($info['unionid'])$data['unionid'] = $info['unionid'];
//				$member_id = M('member')->add($data);
//
//				$data = ['member_id' => $member_id];
//				//会员副表整理后插入数据
//				if(!empty($info['portrait'])){
//					//将微信头像抓取到本地保存并生成缩略图
//					$pic_id = null;
//					if($path = getPicAndSave($info['portrait'], 'member', [2])){
//						//图片录入数据库
//						$pic_id = M('pics')->add([
//							'member_id' => $member_id,
//							'type' => 2,
//							'path' => $path,
//							'is_thumb' => 1,
//							'is_used' => 1
//						]);
//					}
//					//将图片更新到数据库中
//					M('member')->where(['id' => $member_id])->save(['pic_id' => $pic_id]);
//				}
//
//				//获取城市ID
//				$city_id = D('CityView')->where(['city_pinyin' => $info['city'], 'province_pinyin' => $info['province']])->getField('city_id');
//
//				$data['sex'] = $info['sex']?:0;
//				$data['privilege'] = $info['privilege'];
//				$data['citys_id'] = $city_id;
//
//				M('MemberInfo')->add($data);
//			}else{
//				$member_id = $member['id'];
//			}

			//判断是否领过优惠券
			$rs = M('MemberCoupon')->where(['member_id' => $member_id, 'coupon_id' => 145])->find();
			if(empty($rs)){
				//领取优惠券
				$coupon_id = M('MemberCoupon')->where(['coupon_id' => 145, 'channel' => 0, 'member_id' => ['EXP', 'IS NULL']])->getField('id');
				M('MemberCoupon')->save(['id' => $coupon_id, 'member_id' => $member_id]);
			}
			$rs = M('MemberCoupon')->where(['member_id' => $member_id, 'coupon_id' => 146])->find();
			if(empty($rs)){
				//领取优惠券
				$coupon_id = M('MemberCoupon')->where(['coupon_id' => 146, 'channel' => 0, 'member_id' => ['EXP', 'IS NULL']])->getField('id');
				M('MemberCoupon')->save(['id' => $coupon_id, 'member_id' => $member_id]);
			}
			$rs = M('MemberCoupon')->where(['member_id' => $member_id, 'coupon_id' => 147])->find();
			if(empty($rs)){
				//领取优惠券
				$coupon_id = M('MemberCoupon')->where(['coupon_id' => 147, 'channel' => 0, 'member_id' => ['EXP', 'IS NULL']])->getField('id');
				M('MemberCoupon')->save(['id' => $coupon_id, 'member_id' => $member_id]);
			}
			$this->success(1);
		}else{
			//$members[] = $openid;
			//\Common\Util\Cache::getInstance()->set('get_coupon_member', $members);
			$this->success(0);
		}
	}

	/**
	 * @apiName 判断该用户是否需要额外跳转
	 *
	 * @apiGetParam {string} token: 通信令牌
	 *
	 * @apiSuccessResponse
	 * {
	 *     "info": 0 or 1
	 *     "status": 1,
	 * }
	 * @apiErrorResponse
	 * {
	 *	 "status": 0,
	 *	 "info": "失败原因"
	 * }
	 */
	public function isAuth(){
//		$members = \Common\Util\Cache::getInstance()->get('get_coupon_member');
//		if(empty($members))$members = [];
//		if(in_array(session('member.id'), $members)){
//			$arr = [];
//			foreach($members as $m){
//				if($m != session('member.id'))$arr[] = $m;
//			}
//			\Common\Util\Cache::getInstance()->set('get_coupon_member', $arr);
//			$this->success('');
//		}else{
//			$this->error('');
//		}
		//查询是否领取了优惠券
		$count = M('MemberCoupon')->where(['member_id' => session('member.id'), 'coupon_id' => ['IN', '145,146,147']])->count();
		if($count < 3){
			$this->success("jump('getCoupon')");
		}else{
			$this->error('');
		}
	}

	/**
	 * @apiName 退出登录
	 *
	 * @apiGetParam {string} token: 通信令牌
	 *
	 * @apiSuccessResponse
	 * {
	 *	 "status": 1,
	 *	 "info": "退出成功"
	 * }
	 */
	Public function logout(){
		$member_id = session('member.id');
//		M('Openid')->where(['member_id'=>$member_id])->save("`member_id`=NULL");
		M()->execute("Update __OPENID__ set `member_id`=null where `member_id`='{$member_id}' AND type='{$this->openidType}'");
		session('member', null);
		$this->success('退出成功！');
	}

	/**
	 * @apiName 优先认筹权
	 *
	 * @apiGetParam {string} token: 通信令牌
	 *
	 * @apiSuccessResponse
	 *[
	 *	{
	 *	"id": "1",
	 *	"receive_id": "278518",
	 *	"start_time": "0",
	 *	"end_time": "1490709000",
	 *	"order_id": "25861",
	 *	"privilege_id": "1",
	 *	"originate_id": "278518",
	 *	"type": "2",
	 *	"receive_nickname": "紫嫣",
	 *	"originate_nickname": "紫嫣",
	 *	"title": "标题"
	 *	},
	 *	{
	 *	"id": "7",
	 *	"receive_id": "278518",
	 *	"start_time": "0",
	 *	"end_time": "1490709000",
	 *	"order_id": "",
	 *	"privilege_id": "6",
	 *	"originate_id": "57501",
	 *	"type": "2",
	 *	"receive_nickname": "紫嫣",
	 *	"originate_nickname": "小不点",
	 *	"title": "标题"
	 *	}
	 *]
	 */
	Public function privilege(){
		$member_id = session('member.id');
		$data = D('RaisePrivilegeView')->where(['A.member_id'=>$member_id,'type'=>2])->order('order_id asc id desc')->select();
		$this->put($data);
	}

}
