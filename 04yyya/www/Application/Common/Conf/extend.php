<?php
return [
	'TMPL_PARSE_STRING' => [
		'__RS__' => '/Resource/',
		'__AMUI__' => '/Resource/amaze',
	],

	//上传配置
	"UPLOAD_CONFIG" => [
		"accessKeyId" => "LTAIInYzMHLKPUyP",
		"maxSize" => 3 * 1024 * 1024, //上传大小限制 3M
		"accessKeySecret" => "DnS95Q9NTpHgQs0cQkjk1GF2KyvTkX",
		"endpoint" => "oss-cn-shenzhen-internal.aliyuncs.com",
		"subName" => 'Ymd',
		"ext" => '.jpg'
	],

	//阿里云配置
	"ALIYUN" => [
		"accessKeyId" => "LTAIInYzMHLKPUyP",
		"accessKeySecret" => "DnS95Q9NTpHgQs0cQkjk1GF2KyvTkX",
		"endpoint" => "oss-cn-shenzhen-internal.aliyuncs.com",
		"appkey" => "23515157"
	],

    //上传类型目录  
    "UPLOAD_TYPES" => [
		99 => 'public', //我的图库中的公共图片,使用后会将缩略图分到指定类型的目录中
        0 => 'tips',
        1 => 'goods',
        2 => 'member',
        3 => 'official',
		5 => 'comment',
        6 => 'apply',    //达人申请资料相关图片
		7 => 'article'
	],
    //缩略图尺寸
    "THUMB_CONFIG" => [
        1 => [640,420], //商品缩略图
        2 => [320,320], //头像缩略图
        3 => [640,260], //BANNER图
        4 => [208,208], //商品副缩略图
		5 => [200,200], //评论缩略图
		6 => [640,420], //文章封面图
        7 => [640,488], //个人中心封面图
		8 => [640,140], //精选专题缩略图
		9 => [640,420], //普通专题缩略图
        10 => [640,420], //厨房
	],
	
	'SESSION_AUTO_START' =>false, //取消自动session_start
	'key' => 'Q3NDk1NmNhZDYyMTYzNj', //APP通信密钥
	'wxkey' => '2CBEku0Mn6zgP8fz0PchMqEaWpcQdw', //WXAPP通信密钥
	'pwdCode' => "WI4NTFjOGNmODYxMDI1Y", //密码干扰字串
	'CliKey' => "NDk1jOGMTYDYxMzNj", //Cli模式下的验证密钥
	'payCode' => "VcH3ly6qSghgjs7392RjvJe2DnAbn0mx", //支付验证加密串
	'tel' => '020-11223344', //客服电话
	'DefaultInviteMember' => 11353, //特权邀请会员ID
	'URL_HTML_SUFFIX' => 'html|do',
	'TX_MAP_KEY' => 'B6JBZ-JLVK4-QFCUC-DFNRG-PBIP7-OTFAJ', //腾讯地图key
	'OTHER_KEY' => [
		'k11' => 'jOGNYxMcH3',
		'sport' => '2RVjKvJeJL4',
	],
	
	//城市ID
	'CITYS' => [
		35 => '北京',
		37 => '上海',
		224 => '广州',
//		234 => '深圳',
//		112 => '杭州',
//		139 => '厦门'
	],

    //场地负责人
    'SPACE_CHARGE' => [
		35 => 18672366543,
		37 => 18672366543,
        224 => 18672366543, //广州佛山--A喵
//        236 => 18672366543,
//        234 => 13928950715, //深圳--SK
//		112 => 18672366543
    ],

	//微信客服
	'WX_SERVICE' => [
		35 => 'woyoufan-beijing',
		37 => 'woyoufan-beijing',
		224 => 'yami194',
		236 => 'yami194',
//		234 => 'yami194',
//		112 => 'yami194'
	],
	
	//要记录接口发布的模块
	'API_MODULE' => [
		'Home' => '吖咪精选',
		'Daren' => '达人平台',
		'Website' => '吖咪官网',
		'Member' => '会员中心',
		'Goods' => '商品中心',
		'Order' => '订单中心'
	],
	
	//短信基本配置
	'SMS_CONFIG'   => [
		'spacing' => 60, //两次短信发送间隔时间
		'url' => 'http://183.61.109.140:9801/CASServer/SmsAPI/SendMessage.jsp',
		'userid' => '92608',
		'password' => 'cellzfr',
		'template' => '欢迎贵客莅临吖咪！您的验证码：#短信验证码#。工作人员不会向您索要，请勿向任何人透露。[吖咪]'
	],
	//我有饭短信配置
	'YF_SMS_CONFIG' => [
		'spacing' => 60, //两次短信发送间隔时间
		'url' => 'https://sms.yunpian.com/v2/sms/batch_send.json',
		'apikey' => '80024aa6d3124f601d2c2426f67c17b4',
		'template' => '您的验证码是 #短信验证码#。如非本人操作，请忽略本短信'
	],
	
	//会员自动登录设置
	'autologin' => [
		'timeout' => 7 * 24 * 60 * 60, //登录信息保存时间
		'isContinue' => true, //每次自动登录是否更新超时时间
	],

	//微信公众号配置
	'YF_WX_CONF' => [
		'id' => 'gh_7d993478b2a7',
		//'appid' => 'wx3c5361d4483273d1',
		//'secret' => 'fba4e24039aa6bf623ede3987dd0fbbc',
		'appid' => '',
		'secret' => '8bcb454212eb0c2a3d92c78f17f6d32d', // 新的
		'mchid' => '1242851902',
		'key' => 'HkAMEnvvEEQN7zxzVi3nhrBHMnrucfha',
		'apiclient_cert' => COMMON_PATH . 'Util/cacert/apiclient_cert.pem',
		'apiclient_key' => COMMON_PATH . 'Util/cacert/apiclient_key.pem'
	],

	//微信应用配置
	'WXPAY' => [
		'appid' => 'wx094603534de831bd',
		'secret' => '5b052b0d6242e5e0a8f752493b04c7d3',
		'mchid' => '1332407801',
        'apiclient_cert' => COMMON_PATH . 'Util/wxRefund/apiclient_cert.pem',
        'apiclient_key' => COMMON_PATH . 'Util/wxRefund/apiclient_key.pem'
	],

	//支付宝配置
	'ALIPAY' => [
		'appid' => '2016040901280163',
		'key' => 'anleppw025hdrv4tlua1x9d83ofvv0me',
		// 合作身份者ID，签约账号，以2088开头由16位纯数字组成的字符串，查看地址：https://b.alipay.com/order/pidAndKey.htm
		'partner' => '2088021838504065',
		// 卖家支付宝账号，以2088开头由16位纯数字组成的字符串，一般情况下收款账号就是签约账号
		'seller_user_id' => '2088021838504065',
		// 商户的私钥文件路径,原始格式，RSA公私钥生成：https://doc.open.alipay.com/doc2/detail.htm?spm=a219a.7629140.0.0.nBDxfy&treeId=58&articleId=103242&docType=1
		'private_key_path' => COMMON_PATH . 'Util/alipayRefund/key/rsa_private_key.pem',
		// 支付宝公钥（后缀是.pen）文件相对路径，查看地址：https://b.alipay.com/order/pidAndKey.htm
		'ali_public_key_path' => COMMON_PATH . 'Util/alipayRefund/key/rsa_public_key.pem',
		// 服务器异步通知页面路径，需http://格式的完整路径，不能加?id=123这类自定义参数,必须外网可以正常访问
		'notify_url' => "http://api.". DOMAIN ."/order/pay/alipay_notify.html",
		// 签名方式
		'sign_type' => 'MD5',
		// 退款日期 时间格式 yyyy-MM-dd HH:mm:ss
		//date_default_timezone_set('PRC');//设置当前系统服务器时间为北京时间，PHP5.1以上可使用。
		'refund_date' => date("Y-m-d H:i:s"),
		// 调用的接口名，无需修改
		'service' => 'refund_fastpay_by_platform_pwd',
		//字符编码格式 目前支持 gbk 或 utf-8
		'input_charset' => 'utf-8',
		//ca证书路径地址，用于curl中ssl校验
		//请保证cacert.pem文件在当前文件夹目录中
		'cacert' => COMMON_PATH . 'Util/alipayRefund/cacert.pem',
		//访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http
		'transport' => 'http'
	],

	//钱包数值
	'WEALTH' => [
		'0' => [
			'name' => '积分',
			'init' => 0 //初始数值
		],
		'1' => [
			'name' => '余额',
			'init' => 0
		],
		'2' => [
			'name' => '米币',
			'init' => 50
		]
	],
	//身份证实名认证
	'IDENTITY' =>[
		'url' => 'http://apis.haoservice.com/idcard/VerifyIdcard',
		'key' =>'b5b0b4472b7d4174828577dcb4f66249',
	],

	//菜单模型
	'MENUS' => [
		'中餐菜单' => [
			'前菜', '汤品', '热菜','主食','点心', '甜品', '其他', '酒水饮品', 'Tips'
		],
		'西餐菜单' => [
			'头盘','沙拉','汤', '主菜', '主食', '甜品', '其他', '酒水饮品', 'Tips'
		],
		'其他菜单' => [
			'活动流程', '菜单',  '饮品', '伴手礼', 'Tips'
		]
	]
];