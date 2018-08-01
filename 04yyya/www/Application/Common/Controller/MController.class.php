<?php

namespace Common\Controller;
use Think\Controller;

class MController extends Controller {

    Protected $wechat = null;
    Protected $TokenAuth = null;
    Protected $sn = null;
    protected $refund = false;
    protected $channel = 0;
    protected $openidType = 1;

    /**
     * 所有模块通用入口
     */
    Public function __construct(){
        parent::__construct();
        ini_set('session.save_path', realpath(dirname($_SERVER['DOCUMENT_ROOT']) . '/tmp'));
        $channel = 0;

        // $domain = str_replace(['http://', 'www.', '/'], '', I('server.HTTP_ORIGIN'));
        $domain = preg_replace(array('/https?\:\/\//', '/www\./', '/\//'), '', I('server.HTTP_ORIGIN'));
        $token = I('request.token');
        $channel = I('request.channel', $channel);

        if(strpos(DOMAIN, 'youfan') !== false){
            C('WX_CONF', C('YF_WX_CONF'));
            $this->openidType = 2;
            $channel = 1;
        }

        //加载微信插件
        $this->wxLoad();

        if(strtolower(ACTION_NAME) == 'wx_notify') {
            $_token = createCode(40, false);
            //微信支付回调
            $this->channel = 5;
            $xml = file_get_contents('php://input');
            if (!empty($xml)) {
                \Think\Log::write($xml);
                \Think\Log::write('123');
                $xml = str_replace(['<![CDATA[', ']]>'], ['', ''], $xml);
                $xml = simplexml_load_string($xml);
                if ($xml->return_code == 'SUCCESS' && $xml->result_code == 'SUCCESS') {
                    if ($xml->paycode == C('payCode')) {  //支付回调
                        $this->sn = (string)$xml->out_trade_no;
                        $this->price = (int)$xml->total_fee / 100;

                        \Think\Log::write($xml->appid);
                        if ($xml->appid == C('WX_APP.appid')) {
                            $this->pay_type = 4;
                        } else {
                            $this->pay_type = 2;
                        }
                        \Think\Log::write($this->openidType);
                        $this->trade_no = (string)$xml->transaction_id;
                        \Think\Log::write($this->trade_no);
                    } elseif (!empty($xml->refund_id) && $xml->mch_id == $this->getWXCONF('mchid')) {  //退款成功回调
                        $this->sn = (string)$xml->out_trade_no;
                        $this->refund = true;
                    } else {
                        $this->error('非法访问!');
                    }
                }
            } else {
                $this->error('非法访问！');
            }
        }
        elseif(strtolower(ACTION_NAME) == 'alipay_notify') {
            $_token = createCode(40, false);
            //支付宝回调
            $this->channel = 6;
            $params = file_get_contents('php://input');
            parse_str($params, $parr);
            ob_start();
            $str = ob_get_clean();
            if ($parr['paycode'] == C('payCode')) {  //支付回调
                if ($parr['notify_type'] == 'trade_status_sync' && $parr['trade_status'] == 'TRADE_SUCCESS') {
                    $this->sn = $parr['out_trade_no'];
                    $this->trade_no = $parr['trade_no'];
                    if(isset($parr['total_fee'])){
                        //支付宝APP支付
                        $this->price = $parr['total_fee'];
                        $this->pay_type = 0;
                    }elseif(isset($parr['total_amount'])){
                        //支付宝手机网页支付
                        $this->price = $parr['total_amount'];
                        $this->pay_type = 3;
                    }else{
                        echo 'success';
                        exit;
                    }
                } else {
                    echo 'success';
                    exit;
                }
            } else {
                $this->error('非法访问！');
            }
        }
        elseif(strtolower(CONTROLLER_NAME) == 'tips' && strtolower(ACTION_NAME) == 'getinfo'){
            $_token = createCode(40, false);
        }
        elseif(strtolower(CONTROLLER_NAME) == 'apply' && strtolower(ACTION_NAME) == 'getresult'){
            $_token = createCode(40, false);
        }
        elseif(strtolower(ACTION_NAME) == 'getcallback') {
            $_token = createCode(40, false);
            //微信回调
            $this->channel = 5;
            //$xml = file_get_contents('php://input');
            //\Think\Log::write($xml);
            //}elseif(strtolower(ACTION_NAME) == "applicant"){
            $_token = createCode(40, false);
            //微信回调
            $this->channel = 5;
            //$xml = file_get_contents('php://input');
            //\Think\Log::write($xml);
            //}elseif(strtolower(ACTION_NAME) == "applicant"){
        }
        elseif(strtolower(ACTION_NAME) == 'getcoupon' && empty($token)){
            $_token = createCode(40, false);
            $this->channel = 3;
            $key = I('post.key');
            if($key != C('OTHER_KEY.k11')){
                $this->error('无权访问该接口!');
            }
        }
        elseif(empty($domain) || strpos($domain, 'weixin') !== false || strpos($domain, 'pre') || strpos($domain, '127.0.0.1') !== false){
            // App访问 或 weixin.xxx、pre.xxx域名访问
//			\Think\Log::write('DOMAIN1'.$domain);
            if(empty($token)){
                $key = I('request.key');
                $ios_version = I('request.ios_version', 0);
                $android_version = I('request.android_version', 0);
                $wx_version = I('request.wxapp_version', 0);
                $version = [];
                if(!empty($ios_version)){
                    $version = 'ios_v' . $ios_version;
                    if(!$channel)$this->channel = 1;
                    else $this->channel = 8;
                    $this->openidType += 3;
                }elseif(!empty($android_version)){
                    $version = 'android_v' . $android_version;
                    if(!$channel)$this->channel = 2;
                    else $this->channel = 9;
                    $this->openidType += 5;
                }elseif(!empty($wx_version)){
                    $version = 'wxapp_v' . $wx_version;
                    $this->channel = 10;
                    $this->openidType = 3;
                }
                $time = I('request.time');
                if(empty($key) || empty($time))$this->error('非法访问！');
                elseif ($time < time() - 30)$this->error("Key 已经超时，无法使用!");
                elseif ($key == sha1(C('key') . $time . DOMAIN)) {
                    $this->sendToken($version);
                }elseif($key == sha1(C('wxkey') . $time . DOMAIN) && !empty($wx_version)){
               // }elseif($key == sha1(C('wxkey') . $time . 'm.yami.ren') && !empty($wx_version)){
                    $this->sendToken($version);
                }elseif ($key == sha1(C('OTHER_KEY.k11') . $time . DOMAIN)){
                    $this->channel = 3;
                    $this->sendToken('other');
                }else
                    $this->error('Key 验证失败！');
            }
        }
        elseif($domain == DOMAIN){
            if(empty($channel))$this->channel = 0;
            else $this->channel = 7;
            if(empty($token)){
                $data = $this->getWxInfo();
                $this->sendToken($data);
            }
        }
        else{
            $this->error('非法访问！');
        }

        if(!isset($_token)){
            //判断sessionid是否存在
            $path = session_save_path() . '/sess_' . $token;
            if(!file_exists($path)){
                $this->error('TOKEN不存在!');
            }
            if(!empty($token))session_id($token);
        }else{
            session_id($_token);
        }
        session_start();
        if(session('?channel'))$this->channel = session('channel');
        if(session('?version')){
            $version = session('version');
            if(strpos($version, 'ios_v') !== false){
                if(!$channel)$this->channel = 1;
                else $this->channel = 8;
                $this->openidType += 3;
            }elseif(strpos($version, 'android_v') !== false){
                if(!$channel)$this->channel = 2;
                else $this->channel = 9;
                $this->openidType += 5;
            }elseif(strpos($version, 'wxapp_v') !== false){
                $this->channel = 10;
                $this->openidType = 3;
                C('WX_CONF', C('WX_APP'));
            }
        }
        //记录会员操作日志
        $this->log();
    }

    //发送token
    Private function sendToken($data = []){
        $token = createCode(40, false);
        session_id($token);
        session_start();
        session('channel', $this->channel);
        if(!session('?city_id')){
            session('city_id', 224);
            session('city_name', '广州');
        }

        //接口版本
        $v = M('version')->order('id desc')->limit(1)->find();
        if(empty($data)) {
            echo $token;
        }elseif(strpos((string)$data, 'wxapp_v') !== false){
            session('version', $data);
            $this->ajaxReturn([
                'token' => $token,
                'city' => [
                    'id' => session('city_id'),
                    'name' => session('city_name')
                ],
                'api_version' => "{$v['num1']}.{$v['num2']}.{$v['num3']}"
            ]);
        }elseif(strpos((string)$data, '_v') > 0){
            session('version', $data);
            echo $token;
        }elseif($data == 'other'){
            session('other', true);
            echo $token;
        }else{
            $data['token'] = $token;
            $data['city'] = [
                'id' => session('city_id'),
                'name' => session('city_name')
            ];
            $data['api_version'] = "{$v['num1']}.{$v['num2']}.{$v['num3']}";
            $this->ajaxReturn($data);
        }
        exit;
    }

    //获取微信api信息
    Private function getWxInfo(){
        $js_ticket = $this->wechat->getJsTicket();
        if (!$js_ticket) {
            $err = "获取js_ticket失败！\n";
            $err .= '错误码：' . $this->wechat->errCode . "\n";
            $err .= ' 错误原因：' . \Common\Util\ErrCode::getErrText($this->wechat->errCode);
            $this->error($err);
        }
        $url = $_GET['url'];
        if(empty($url)){
            $url = 'http://' . DOMAIN . '/';
        }
        $data = $this->wechat->getJsSign($url);
        return $data;
    }

    public function getWXCONF($name) {
        if (strpos(DOMAIN, 'youfan') !== false) {
            return C('YF_WX_CONF.'.$name);
        } else {
            return C('WX_CONF.'.$name);
        }
    }

    //加载微信插件
    Private function wxLoad(){

        $opt = [
            'appsecret' => $this->getWXCONF('secret'),
            'appid' => $this->getWXCONF('appid')
        ];

        $this->wechat = new \Common\Util\Wechat($opt);
        $this->TokenAuth = $this->wechat->checkAuth();
    }

    //文件上传
//	Protected function upload($type = 0, $is_thumb = null ,$sec_thumb = null){
//		if(!session('?member'))$this->error('尚未登录，无法使用上传！');
//		$types = C('UPLOAD_TYPES');
//		$upload = new \Think\Upload(C('UPLOAD_CONFIG'));
//		$upload->savePath  =     $types[$type] . '/'; // 设置附件上传目录
//		$info   =   $upload->upload();
//		if(!$info) {
//			// 上传错误提示错误信息
//			$this->error($upload->getError());
//		}
//
//		$datas = array();
//		$datas['member_id'] = session('member.id');
//		$datas['type'] = $type;
//		$datas['path'] = $info['file']['savepath'].$info['file']['savename'];
//		//生成缩略图
//		$thumb_conf = C('THUMB_CONFIG');
//		$image = new \Think\Image();
//		$image->open(C('UPLOAD_CONFIG.rootPath').$info['file']['savepath'].$info['file']['savename']);
//		$name_arr = explode('.', $info['file']['savename']);
//		if(!empty($is_thumb)){
//			$conf = is_array($is_thumb) ? $is_thumb : $thumb_conf[$is_thumb];
//			$thumbname = "{$name_arr[0]}_{$conf[0]}x{$conf[1]}.{$name_arr[1]}";
//			$image->thumb($conf[0], $conf[1], \Think\Image::IMAGE_THUMB_CENTER)->save(C('UPLOAD_CONFIG.rootPath').$info['file']['savepath'].$thumbname);
//			//商品副缩略图
//			if(!empty($sec_thumb)){
//				$_conf = is_array($sec_thumb) ? $is_thumb : $thumb_conf[$sec_thumb];
//				$thumbname = "{$name_arr[0]}_{$_conf[0]}x{$_conf[1]}.{$name_arr[1]}";
//				$image->open(C('UPLOAD_CONFIG.rootPath').$info['file']['savepath'].$info['file']['savename']);
//				$image->thumb($_conf[0], $_conf[1], \Think\Image::IMAGE_THUMB_CENTER)->save(C('UPLOAD_CONFIG.rootPath').$info['file']['savepath'].$thumbname);
//			}
//		}else{
//			$size = $image->size();
//			$conf = [640, round(640 * $size[1] / $size[0])];
//			$thumbname = "{$name_arr[0]}_{$conf[0]}x{$conf[1]}.{$name_arr[1]}";
//			$image->thumb($conf[0], $conf[1], \Think\Image::IMAGE_THUMB_CENTER)->save(C('UPLOAD_CONFIG.rootPath').$info['file']['savepath'].$thumbname);
//		}
//		//记录缩略图尺寸
//		$conf = [(string)$conf[0], (string)$conf[1]];
//		$datas['size'][] = $conf;
//		if(!empty($_conf))$datas['size'][] = $_conf;
//		$datas['size'] = json_encode($datas['size']);
//		$pic_id = M('pics')->add($datas);
//		$data = [
//			'pic_id' => $pic_id,
//			'size' => $conf,
//			'filename' => $info['file']['savename'],
//			'filepath' => $info['file']['savepath']
//		];
//		return $data;
//	}

    /**
     * 取消订单公共方法
     * @param $order_id 订单id
     */
    Protected function cancelOrder($order_id){
        $order = M('order')->where(['id'=>$order_id])->find();
        $orderWares = M('OrderWares')->where(['order_id' => $order_id])->select();
        $num = count($orderWares);

        foreach($orderWares as $row){
            //若有消耗折扣,将折扣恢复
            if(is_numeric($row['marketing_id'])){
                M('marketing')->where(['id' => $row['marketing_id']])->setInc('num', $num);
            }
        }
        //若有使用优惠券,将优惠券还原
        if(is_numeric($order['member_coupon_id'])){
            M('MemberCoupon')->where(['id' => $order['member_coupon_id']])->save(['used_time' => 0]);
        }

        //将订单设置为关闭
        M('order')->where(['id' => $order_id])->save(['status' => 2]);

        //恢复库存
        if($orderWares['type'] == 0){
            $tips = M('tips')->where(['id' => $orderWares[0]['ware_id']])->find();
            if($tips['limit_time'] > 0){
                if($order['is_book'])
                    M('TipsTimes')->where(['id' => $orderWares[0]['tips_times_id']])->save(['stock', ['EXP', 'max_num']]);
                else
                    M('TipsTimes')->where(['id' => $orderWares[0]['tips_times_id']])->setInc('stock', $num);
            }
        }elseif($orderWares['type'] == 1){
            $limit_time = M('goods')->where(['id' => $orderWares[0]['ware_id']])->getField('limit_time');
            if($limit_time > 0){
                M('goods')->where(['id' => $orderWares[0]['ware_id']])->setInc('stocks', $num);
            }
        }elseif($orderWares['type'] == 2){
            //若是优先众筹，则恢复优先众筹的库存
            $is_privilege = session('?privilege.privilege_id');
            $raise = M('raise')->where(['id' => $orderWares[0]['ware_id']])->find();
            if($is_privilege&&$raise['start_time']>time()){
                $privilege_id = session('privilege.privilege_id');
                $privilege_info = M('Privilege')->where(['id'=>$privilege_id])->find();
                if($privilege_info['number']>=0){
                    M('Privilege')->where(['id'=>$privilege_id])->setInc('number', $num);
                }
            }//else{
            if($raise['limit_time'] > 0){
                M('raise_time')->where(['id' => $orderWares[0]['tips_times_id']])->setInc('stocks', $num);
            }
            //}
        }

        return true;
    }

    /**
     * 订单退款公共方法
     * @param $order_id 订单id
     */
    Protected function refundOrder($order_id, $type){
        //退款接口start
        $rs = M('order')->where(['id' => $order_id])->find();
        M('order')->where(['id' => $order_id])->save(['act_status' => 8, 'status' => 2]);
        $_rs = M('OrderRefund')->where(['order_id' => $order_id, 'is_allow' => 0])->find();
        if(empty($_rs)){
            M('OrderRefund')->add(['order_id' => $order_id, 'money' => $rs['price'], 'type' => $type]);
        }
        return true;
    }

    /**
     * 重载error方法，默认为AJAX
     * @see \Think\Controller::error()
     */
    Protected function error($msg = '', $url = '', $ajax = false){
        $this->ajaxReturn([
            'status' => 0,
            'info' => $msg
        ]);
    }

    /**
     * 重载success方法，默认为ajax
     * @see \Think\Controller::success()
     */
    Protected function success($msg = '', $url = '', $ajax = false){
        $this->ajaxReturn([
            'status' => 1,
            'info' => $msg
        ]);
    }

    /**
     * 接口输出json格式数据
     * @param $data 要输出的数据
     */
    Protected function put($data){
        if(!is_array($data))$data = [$data];
        //将数组中的每个值都变为字符串类型
        $data = array_change_value($data);
        //返回JSON数据格式到客户端 包含状态信息
        header('Content-Type:application/json; charset=utf-8');
        exit(json_encode($data));
    }

    /**
     * 记录会员操作日志
     */
    Private function log(){
        $data = ['module' => strtolower(MODULE_NAME), 'controller' => strtolower(CONTROLLER_NAME), 'action' => strtolower(ACTION_NAME)];
        $id = M('Framework')->where($data)->getField('id');
        if(empty($id))$id = M('Framework')->add($data);
        $data = [
            'framework_id' => $id,
            'channel' => $this->channel?:0,
            'ip' => ip2long(getClientIP()),
            'get' => substr(json_encode($_GET), 0, 100),
            'post' => substr(json_encode($_POST), 0, 100)
        ];
        if(session('?member'))$data['member_id'] = session('member.id');
        if(session('?signUser'))$data['open_id'] = session('signUser.id');
        if(session('?invite'))$data['invitecode'] = session('invite.code');
        M('MemberActLog')->add($data);
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
        $o = 'openid';
        if($channel){
            $o = 'yf_openid';
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
            if(in_array('wx', $send_way)){
                $data['wx_send'] = 1 + $channel;
                $wx = 0;
                if($sendtime < time() && !empty($rs[$o])){
                    $wx = $this->wechat->sendCustomMessage([
                        'touser' => $rs[$o],
                        'msgtype' => 'text',
                        'text' => [
                            'content' => $context
                        ]
                    ]);
                    if(!empty($this->wechat->errCode))\Think\Log::write($this->wechat->errMsg);
                }
            }
            if(in_array('sms', $send_way)){
                $data['sms_send'] = 1 + $channel;
                $sms = 0;
                if($sendtime < time()){
                    $sms = sms_send($rs['telephone'], $context, false, $channel);
                    \Think\Log::write($sms.'------'.$rs['telephone'].'----'. abslength($context));
                }

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

        \Think\Log::write('发送信息渠道：'.$origin_id.'=>'.$origin_id.'----'.date('Y-m-d H:i:s'));
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
        }elseif(!empty($code_key) && empty($param)){
            $content = $code_config['content'];
        }elseif(empty($code_key) && empty($param) && !empty($site_message)){//发送站内信息
            $content =$site_message;
        }
        \Think\Log::write('content=>'.$content );
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
                \Think\Log::write('微信发送信息=>member_id:'.$member_id.'=>'.date('Y-m-d H:i:s'));
                \Think\Log::write('微信发送信息内容=>'.$content);
                $data['wx_send'] = 1 + $channel;
                $wx = 0;
                $openidInfo = M('Openid')->where(['member_id' => $member_id, 'type' => 1 + $channel])->find();
                if($sendtime < time() && !empty($openidInfo['openid'])){
                    if(!empty($code_config['wxtemplate'])){
//						foreach($code_config['wxtemplate'] as $key => $val ){
//							if(in_array($val,$code_config['params'])){
//								$WXcode_params[$key] = [
//									'value' => $param[$val],
//									'color' => '#173177'
//								];
//							}
//							if($key !== 'template_id' && in_array($val,$code_config['params']) === false){
//								$WXcode_params[$key] = [
//									'value' => $val,
//									'color' => '#173177'
//								];
//							}
//						}
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

                    }else{
                        \Think\Log::write('openid type=>'.$channel);
//                        \Think\Log::write('accessToken=>'.$this->wechat->access_token);
                        \Think\Log::write('微信openid=>'.$openidInfo['openid']);
                        $wx = $this->wechat->sendCustomMessage([
                            'touser' => $openidInfo['openid'],
                            'msgtype' => 'text',
                            'text' => [
                                'content' => $content,
                            ]
                        ]);
//                        $accessToken = getAccessToken();
//                        $jsonData = [
//                            'touser' => $openidInfo['openid'],
//                            'msgtype' => 'text',
//                            'text' => [
//                                'content' => $content,
//                            ]
//                        ];
//                        $wx = curl_post('https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token='. $accessToken, json_encode($jsonData));

                        \Think\Log::write('wx=>'.json_encode($wx));
//                        \Think\Log::write('accessToken=>'.$accessToken);
                        if(!empty($this->wechat->errCode))\Think\Log::write($this->wechat->errMsg);

                    }
                }
            }
            if(in_array('sms', $send_way)){
                \Think\Log::write('短信发送信息内容=>'.$content);
                $data['sms_send'] = 1 + $channel;
                $sms = 0;
                if($sendtime < time()){
                    $smsParam = [];
                    foreach($param as $key => $val) {
                        if (in_array($key, $code_config['params'])) {
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
            'is_wx' => !empty($wx) ? 1 + $channel: 0
        ]);

        if(!empty($dt)){
            $dt['msg_id'] = $msg_id;
            getRedis()->rPush(str_replace('.', '', DOMAIN) . '_app_push', json_encode($dt));
        }
        return true;
    }


    /**
     * 实名认证
     * @param $name 姓名
     * @param $cardNo 身份证号
     */
    Protected function identity($name,$cardNo){
        //定义传递的参数数组；
        $data_01['realName']=$name;
        $data_01['cardNo']=$cardNo;
        $data_01['key']=C('IDENTITY.key');
        //定义返回值接收变量；
        $data =  httpUrl(C('IDENTITY.url'), $data_01, 'POST');
        return $data;
    }

    /**
     * 获取框架ID
     *
     */
    Protected function framework_id(){
        $data = ['module' => strtolower(MODULE_NAME), 'controller' => strtolower(CONTROLLER_NAME), 'action' => strtolower(ACTION_NAME)];
        $id = M('Framework')->where($data)->getField('id');
        return $id;
    }

    /**
     * 截取字符串
     * @param $name 字符串
     * @param $cardNo 截取的长度
     */
    function SubCN4($str,$len){

        if($str=='' || strlen($str)<=$len) return $str;

        if(ord(substr($str,$len-1,1))>0xa0) $len++;

        return substr($str,0,$len);

    }

    /**
     * 记录活动，商品，众筹，订单更改
     * @param $type_id 活动，商品，众筹，订单ID
     * @param $type  类型 （0-活动，1-商品 2-众筹 3-订单）
     * @param $framework_id  框架ID
     */
    Protected function SaveSnapshotLogs($type_id,$type,$framework_id=''){
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
                $context['fight_groups'] = M('GoodsPiece')->where(['goods_id' => $type_id])->select();
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
                    $context['OrderPay'] = M('OrderPay')->where(['order_id' => $type_id,'trade_no'=>['EXP','IS NOT NULL'],'success_pay_time'=>['EXP','IS NOT NULL']])->find();
                    $context['OrderRefund'] = M('OrderRefund')->where(['order_id' => $type_id])->find();
                    $context['OrderPiece'] = M('OrderPiece')->where(['order_id' => $type_id])->find();
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
                            $context['goods']['Piece'] = M('Piece')->where(['type_times_id'=>$context['id'],'type'=>1])->select();
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


    /**
     * 验证码检测
     */
    Protected function checkVerify($code, $id = ''){
        $verify = new \Think\Verify();
        return $verify->check($code, $id);
    }

}