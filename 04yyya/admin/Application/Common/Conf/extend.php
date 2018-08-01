<?php
return [
    //上传配置
    "UPLOAD_CONFIG" => [
        "accessKeyId" => "LTAIInYzMHLKPUyP", //  本机测试用 D:/yummy/uploads/  上传SVN一定要改回来 /home/uploads/
        "maxSize" => 3 * 1024 * 1024, //上传大小限制 3M
        "accessKeySecret" => "DnS95Q9NTpHgQs0cQkjk1GF2KyvTkX",
		"endpoint" => "oss-cn-shenzhen-internal.aliyuncs.com",
        "subName" => 'Ymd',
        "ext" => '.jpg'
	],
    //上传类型目录  
    "UPLOAD_TYPES" => [
        0 => 'tips',
        1 => 'goods',
        2 => 'member',
        3 => 'official'
	],
    //缩略图尺寸
    "THUMB_CONFIG" => [
        1 => [640,420], //商品缩略图
        2 => [320,320], //头像缩略图
        3 => [640,260], //BANNER图
        4 => [208,208], //商品副缩略图
        5 => [200,200], //评论缩略图
        6 => [640,420], //文章封面图
        7 => [640,488],//个人中心封面图
        8 => [640,140],//精选专题
        9 => [640,420],//普通专题
        10 => [640,622],//厨房
		11 => [640,420],//众筹
    ],
    //申请分类对应的标签ID
    "APPLY_CONF" => [
        18 => 18,
        19 => 27
    ],
    //达人身份标签ID
    "DAREN_LABEL" =>[
        0 => 27,//美食达人
        1 => 18,//主厨达人
    ],
    //渠道
    "CHANNEL" => [
        0 => 'webapp',
        1 => 'ios',
        2 => 'android',
        3 => 'k11',
        4 => 'sport',
        5 => 'wechat',
        6 => 'alipay',
        7 => 'youfan_webapp',
        8 => 'youfan_ios',
        9 => 'youfan_android'
    ],
	//厨房+的设备
	"FACILITY"=>[
		0=>[
			'id'=>1,
			'name'=>'wifi',
		],
		1=>[
			'id'=>2,
			'name'=>'酒具',
		],
		2=>[
			'id'=>3,
			'name'=>'电视音响',
		],
		3=>[
			'id'=>4,
			'name'=>'餐具',
		],
		4=>[
			'id'=>5,
			'name'=>'空调',
		],
		5=>[
			'id'=>6,
			'name'=>'明火',
		],
		6=>[
			'id'=>7,
			'name'=>'开放式厨房',
		],
		7=>[
			'id'=>8,
			'name'=>'吸烟',
		]
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
	],
    //城市筛选
    "CITY_CONFIG"=>[
        35 => '北京',
        37 => '上海',
        224 => '广州',
        234 => '深圳',
        112 => '杭州',
		139 => '厦门'
    ],

	//微信公众号配置
	'YF_WX_CONF' => [
		'id' => 'gh_7d993478b2a7',
		'appid' => 'wx3c5361d4483273d1',
		'secret' => 'fba4e24039aa6bf623ede3987dd0fbbc',
		'mchid' => '1242851902',
		'key' => 'HkAMEnvvEEQN7zxzVi3nhrBHMnrucfha',
		'apiclient_cert' => COMMON_PATH . 'Util/yfcacert/apiclient_cert.pem',
		'apiclient_key' => COMMON_PATH . 'Util/yfcacert/apiclient_key.pem'
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
        'notify_url' => "http://api.". WEB_DOMAIN ."/order/pay/alipay_notify.html",
        // 签名方式
        'sign_type' => 'MD5',
        // 退款日期 时间格式 yyyy-MM-dd HH:mm:ss
        'refund_date' => date("Y-m-d H:i:s"),
        // 调用的接口名，无需修改
        'service' => 'refund_fastpay_by_platform_nopwd',
        //字符编码格式 目前支持 gbk 或 utf-8
        'input_charset' => 'utf-8',
        //ca证书路径地址，用于curl中ssl校验
        //请保证cacert.pem文件在当前文件夹目录中
        'cacert' => COMMON_PATH . 'Util/alipayRefund/cacert.pem',
        //访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http
        'transport' => 'http'
    ],
	//邮件配置
	'Mail' => [
		'state' => 1,
		'server' => 'smtp.abc.com',
		'port' => 25,
		'auth' => 1,
		'username' => 'admin@abc.com',
		'password' => '123456',
		'charset' => 'gbk',
		'mailfrom' => 'admin@abc.com'

	]
];