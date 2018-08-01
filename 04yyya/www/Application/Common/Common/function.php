<?php
/**
 * 短信发送
 * @param string[array] $tel 发送号码[数组为多条发送]
 * @param string $msg 待发送消息
 * @param bool $code 是否使用标准格式
 * @param int $channel 渠道 0-吖咪 1-我有饭
 */

function sms_send($tel, $msg, $code = false, $channel = 0){
	\Think\Log::write('推送短信的手机号为：'.$tel);
	if((session('?channel') && in_array(session('channel'), [7,8,9])) || $channel == 1)
		$conf = C('YF_SMS_CONFIG');
	else
		$conf = C('SMS_CONFIG');
	
	if(is_array($tel)){
		$tel = join(',', $tel);
	}

    if($code){
		if((session('?channel') && in_array(session('channel'), [7,8,9])) || $channel == 1){
			$data = [
				'apikey' => $conf['apikey'],
				'text' => str_replace('#短信验证码#', $msg, $conf['template']),
				'mobile' => $tel
			];
		}else{
			$data = [
				'userid' => $conf['userid'],
				'password' => $conf['password'],
				'msg' => str_replace('#短信验证码#', $msg, $conf['template']),
				'destnumbers' => $tel
			];
		}
    }else{
		if((session('?channel') && in_array(session('channel'), [7,8,9])) || $channel == 1){
			$data = [
				'apikey' => $conf['apikey'],
				'text' => $msg,
				'mobile' => $tel
			];
		}else{
			$data = [
				'userid' => $conf['userid'],
				'password' => $conf['password'],
				'msg' => $msg,
				'destnumbers' => $tel
			];
		}
    }

	$post_data = [];
	foreach($data as $key => $val){
		$post_data[] = "{$key}={$val}";
	}
	$post_data = implode('&', $post_data);
	\Think\Log::write('post_data:'.$post_data);
	$url = $conf['url'];
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
	ob_start();
	$rs = curl_exec($ch);
	$result = ob_get_clean();
	if($rs){
		if(!$channel){
			preg_match('/return="(.+?)" info="(.+?)"/', $result, $arr);
			if($arr[1] == 0){
				return true;
			}
		}else{
			$json = json_decode($result, true);
			if($json['total_count'] > 0){
				return true;
			}
		}
	}
	return false;
}

/**
 * 阿里云短信发送
 * @param string[array] $tel 发送号码[数组为多条发送]
 * @param string $templateCode 阿里云短信模板
 * @param array $params 短信参数
 * @param int $channel 渠道 0-吖咪 1-我有饭
 */
function smsSend($tel, $templateCode, $params=array(), $channel = 0){
	$url = 'https://sms.aliyuncs.com/?';
	$data = [
		"SignName" => $channel ? "我有饭":"吖咪",
		"TemplateCode" => $templateCode,
		"RecNum" => is_array($tel) ? join(',', $tel) : $tel,
		"ParamString" =>!empty($params)?json_encode($params):json_encode((object)array()),
		"RegionId" => "cn-shenzhen",
		"AccessKeyId" => C('UPLOAD_CONFIG.accessKeyId'),
		"Format" => "JSON",
		"SignatureMethod" => "HMAC-SHA1",
		"SignatureVersion" => "1.0",
		"SignatureNonce" => createCode(48, false),
		"Timestamp" => date('Y-m-d\TH:i:s\Z', time() - 8*3600),
		"Action" => "SingleSendSms",
		"Version" => "2016-09-27"
	];
	//签名
	ksort($data);
	$arr = [];
	foreach($data as $k => $v){
		$k = str_replace(['+', '%7E', '*'], ['%20', '~', '%2A'], urlencode($k));
		$v = str_replace(['+', '%7E', '*'], ['%20', '~', '%2A'], urlencode($v));
		$arr[] = "{$k}={$v}";
	}
	$source = urlencode(join('&', $arr));
	$source = 'GET&%2F&' . str_replace(['+', '%7E', '*'], ['%20', '~', '%2A'], $source);
	$code = hash_hmac('sha1', $source, C('UPLOAD_CONFIG.accessKeySecret') . '&', true);
	$code = base64_encode($code);
	$data['Signature'] = $code;

	$arr = [];
	foreach($data as $k => $v){
		$arr[] = $k . '=' . urlencode($v);
	}
	$url .= join('&', $arr);
	\Think\Log::write('url'.$url);
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_FAILONERROR, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, null);

	$rs = curl_exec($ch);
	$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	curl_close($ch);

	\Think\Log::write('Telephone: '. $data['RecNum'] ."\nTemplateCode: " . $templateCode . "\nParams: " . json_encode($params) . "\nReturnInfo: " . $rs."\nStatus: ".$status);
	if($status == 200){
		return true;
	}else{
		return $rs;
	}
}

/**
 * IOS推送信息发送
 * @param string $member_id 会员ID
 * @param int $channel 渠道 0-吖咪 1-我有饭
 * @param string $content 信息内容
 * @param array $msg_id member_message表对应message表的ID
 */
function ios_push($member_id, $channel, $content, $msg_id){
		$devicetoken = M('MemberDevice')->where(['member_id' => $member_id, 'channel' => $channel])->order('id desc')->getField('device');
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

/**
 * 文件名加密
 * @param string $name 原文件名
 * @return string 加密后的文件名
 */
function fileCrypt($name = ''){
	if(empty($name))$name = time().rand(1000,9999);
	$name = substr(base64_encode(sha1($name)), rand(10,20), 30);
	return $name;
}

/**
 * 下载远程图片
 * @param string $pic_url 远程图片地址
 * @param string $save_path 图片保存目录
 * @param string $is_thumb 生成缩略图
 * @return boolean 是否成功
 */
function getPicAndSave($pic_url) {
	$header_array = get_headers($pic_url, true);
	$size = $header_array['Content-Length'];
	$conf = C('UPLOAD_CONFIG');
	if($size > $conf['maxSize'])return false;
	$picinfo = getimagesize($pic_url);
	$type = strrchr($pic_url, '.');
	switch($picinfo[2]){
		case 1 : $type = '.gif';break;
		case 2 : $type = '.jpg';break;
		case 3 : $type = '.png';break;
	}

	$newname = date($conf['subName']) . '/' . fileCrypt() . $type;

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $pic_url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	$output = curl_exec($ch);
	curl_close($ch);

	if(empty($output))return false;

	try {
		$ossClient = new \OSS\OssClient($conf['accessKeyId'], $conf['accessKeySecret'], $conf['endpoint']);
	} catch (\OSS\OssException $e) {
		\Think\Log::write($e->getMessage());
		return false;
	}
	$bucket = substr(DOMAIN, 0, 1) == 't' ? "yamiimg" : "yummyimg";
	try {
		$ossClient->putObject($bucket, $newname, $output);
	} catch (\OSS\OssException $e) {
		\Think\Log::write($e->getMessage());
		return false;
	}

	return $newname;
}

//生成记住登录信息用的skey
function createSkey($member_id, $datetime){
	$time = strtotime($datetime);
	$skey = substr(base64_encode(sha1($member_id . $time)), 5 + $member_id % 15, 30);
	return $skey;
}

/**
 * 生成随机码
 * @param int $len 随机码长度
 * @param bool|true $isNumber 是否为纯数字
 */
function createCode($len, $isNumber = true){
	$char = '1234567890qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM';
	if($isNumber){
		return rand(pow(10, $len - 1), pow(10, $len)) - 1;
	}else{
		$code = '';
		for($i=0; $i<$len; $i++){
			$code .= $char[rand(0, 61)];
		}
		return $code;
	}
}

//将图片地址转换为缩略图地址
function thumb($path, $size = null){
    $p = $path;
	if(empty($path))return '';
	if(preg_match('/.+_.+x.+\..+/', $path) || strpos($path, 'http://') !== false)return $path;
	if(substr($path,0,1) == '/')$path = substr($path,1);
	if(strpos($path, 'uploads/') !== false || strpos($path,'themes/default') !== false){
		if(strpos($path, 'yami') !== false)$path = str_replace('yami/', '', $path);
		$myPath = 'http://old.yami.ren/' . $path;
	}else{
		$arr = explode('.', $path);
		$_path = $path;
		$sizes = json_decode(M('pics')->where(['path' => $p])->getField('size'), true);
		if(!empty($sizes) && json_last_error() === JSON_ERROR_NONE) {
			$conf = C('THUMB_CONFIG');
			if (is_array($size) && !empty($size) && in_array($size, $sizes)) {
				$_path = "{$arr[0]}_{$size[0]}x{$size[1]}.{$arr[1]}";
			} elseif (is_numeric($size) && in_array($conf[$size], $sizes)) {
				$size = $conf[$size];
				$_path = "{$arr[0]}_{$size[0]}x{$size[1]}.{$arr[1]}";
			} else {
				if (!empty($sizes)) {
					$size = $sizes[0];
					if (!empty($size)) {
						$_path = "{$arr[0]}_{$size[0]}x{$size[1]}.{$arr[1]}";
					} else {
						$_path = "{$arr[0]}.{$arr[1]}";
					}
				} else {
					$_path = "{$arr[0]}.{$arr[1]}";
				}
			}
		}
		if(DOMAIN == 'yummy.com'){
			$myPath = 'http://img.m.yami.ren/' . $path;
		}else{
			if(strpos($_SERVER['HTTP_HOST'], 'api.yami.ren') !== false){
				$myPath = 'http://img.yummy194.cn/' . $_path;
			}else{
				$myPath = 'http://img.'. DOMAIN .'/' . $_path;
			}
		}
	}
	return $myPath;
}

/**
 * 获取图片base64编码
 * @param $path 图片路径
 * @return string
 */
function getBase64($path){
	if(empty($path))return '';
	$path = thumb($path);
	$filetype = getimagesize($path);
	$filetype = $filetype['mime'];
	if(in_array($filetype, ['image/png', 'image/gif', 'image/x-icon', 'image/jpeg'])){
		while(empty($code = file_get_contents($path))){
			usleep(10000);
		}
		return 'data:'. $filetype .';base64,' . str_replace(' ', '', base64_encode($code));
	}
	return '';
}

//格式化图片路径
function pathFormat($path){
	if(strpos($path, 'http://') !== false)return $path;
	if(substr($path,0,1) == '/')$path = substr($path,1);
	if(strpos($path, 'uploads/') !== false){
		if(strpos($path, 'yami') !== false)$path = str_replace('yami/', '', $path);
		$path = 'http://yummy194.cn/' . $path;
	}else{
		if(DOMIAN == 'yummy.com'){
			$path = 'http://img.m.yami.ren/' . $path;
		}else {
			$path = 'http://img.' + DOMAIN + '/' . $path;
		}
	}
	return $path;
}

/**
 * 合成带键名的数组
 * @return array 合成后的数组
 */
function array_key_merge(){
    $data = func_get_args();
    $array = [];
    foreach($data as $d){
        if(is_array($d)){
            foreach($d as $k => $v) {
                $array[$k] = $v;
            }
        }
    }
    return $array;
}

/**
 * 可以统计中文字符串长度的函数
 * @param $str 要计算长度的字符串
 */
function abslength($str)
{
	if(empty($str)){
		return 0;
	}
	if(function_exists('mb_strlen')){
		return mb_strlen($str,'utf-8');
	}else{
		preg_match_all("/./u", $str, $ar);
		return count($ar[0]);
	}
}

/**
 * utf-8编码下截取中文字符串,参数可以参照substr函数
 * @param $str 要进行截取的字符串
 * @param $start 要进行截取的开始位置，负数为反向截取
 * @param $end 要进行截取的长度
*/
function utf8_substr($str,$start=0) {
	if(empty($str)){
		return false;
	}
	if (function_exists('mb_substr')){
		if(func_num_args() >= 3) {
			$end = func_get_arg(2);
			return mb_substr($str,$start,$end,'utf-8');
		}
		else {
			mb_internal_encoding("UTF-8");
			return mb_substr($str,$start);
		}

	}
	else {
		$null = "";
		preg_match_all("/./u", $str, $ar);
		if(func_num_args() >= 3) {
			$end = func_get_arg(2);
			return join($null, array_slice($ar[0],$start,$end));
		}
		else {
			return join($null, array_slice($ar[0],$start));
		}
	}
}

/**
 * 获取微信access_token
 * @return AccessToken
 */
function getAccessToken(){
	$key = 'wechat_access_token' . C('WX_CONF.appid');
	$token = cache($key);
	if (is_string($token)) {
//		echo $key;
		return $token;
	} else {
		$result = file_get_contents('https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=' . C('WX_CONF.appid') . '&secret=' . C('WX_CONF.secret'));
		if ($result) {
			$json = json_decode($result, true);
			if (!$json || isset($json['errcode'])) {
				die($json['errmsg']);
			}
			$access_token = $json['access_token'];
			$expire = $json['expires_in'] ? intval($json['expires_in']) - 1000 : 3600;
			cache($key, $access_token, $expire);
			return $access_token;
		}else{
			return false;
		}
	}
}


//模拟文件上传
function curl_post($url, $data, $header = array(),$weiXinRefund = false){
	if(function_exists('curl_init')) {
		$ch = curl_init();
		curl_setopt($ch,CURLOPT_TIMEOUT,30);
		curl_setopt($ch, CURLOPT_URL, $url);
		if(is_array($header) && !empty($header)){
			$set_head = array();
			foreach ($header as $k=>$v){
				$set_head[] = "$k:$v";
			}
			curl_setopt($ch, CURLOPT_HTTPHEADER, $set_head);
		}
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
			$error = curl_errno($ch);
			echo "Call faild, errorCode:$error\n";
			curl_close($ch);
			return false;
		}
	} else {
		throw new \Exception('Do not support CURL function.');
	}
}

//获取真实IP
function getClientIP($type = 0) {
	$type = $type ? 1 : 0;
	static $ip = NULL;
	if ($ip !== NULL)return $ip[$type];
	if(isset($_SERVER['HTTP_X_REAL_IP'])){//nginx 代理模式下，获取客户端真实IP
		$ip = $_SERVER['HTTP_X_REAL_IP'];
	}elseif(isset($_SERVER['HTTP_CLIENT_IP'])) {//客户端的ip
		$ip = $_SERVER['HTTP_CLIENT_IP'];
	}elseif(isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {//浏览当前页面的用户计算机的网关
		$arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
		$pos = array_search('unknown',$arr);
		if(false !== $pos) unset($arr[$pos]);
		$ip = trim($arr[0]);
	}elseif(isset($_SERVER['REMOTE_ADDR'])) {
		$ip = $_SERVER['REMOTE_ADDR'];//浏览当前页面的用户计算机的ip地址
	}else{
		$ip = $_SERVER['REMOTE_ADDR'];
	}
	// IP地址合法验证
	$long = sprintf("%u",ip2long($ip));
	$ip   = $long ? [$ip, $long] : ['0.0.0.0', 0];
	return $ip[$type];
}

//获取本周一和周日的时间戳
function getmonsun(){
    $curtime=time();

    $curweekday = date('w');

    //为0是 就是 星期七
    $curweekday = $curweekday?$curweekday:7;


    $curmon = $curtime - ($curweekday-1)*86400;
    $cursun = $curtime + (7 - $curweekday)*86400;

    //零点时刻
    $curmon = strtotime(date('Y-m-d',$curmon));
    $cursun = strtotime(date('Y-m-d',$cursun));

    $cur['mon'] = $curmon;
    $cur['sun'] = $cursun;

    return $cur;
}

/**
 * 缓存控制函数
 * @param string $key
 * @param string $val
 * @return boolean|multitype:
 */
function cache($key, $val = '[NULL]', $exp = 0){
	if($val === '[NULL]')return \Common\Util\Cache::getInstance()->get($key);
	elseif ($val === null) return \Common\Util\Cache::getInstance()->rm($key);
	else return \Common\Util\Cache::getInstance()->set($key, $val, $exp);
}

/**
 * 将数组中的每个值转为字符串类型
 * @param array $array 要转的数组
 */
function array_change_value($array){
	foreach($array as $key => $val){
		if(is_array($val))
			$array[$key] = array_change_value($val);
		elseif($val === null)
			$array[$key] = '';
		else
			$array[$key] = (string)$val;
	}
	return $array;
}

function toXls($title, $data, $filename){

	header("Content-type:application/vnd.ms-excel");
	header("Content-Disposition:attachment;filename={$filename}.xls");

	echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html><head><meta http-equiv="Content-type" content="text/html;charset=UTF-8" /><style id="Classeur1_16681_Styles"></style></head>
<body><div id="Classeur1_16681" align=center x:publishsource="Excel"><table x:str border=0 cellpadding=0 cellspacing=0 width=100% style="border-collapse: collapse">';

	echo '<tr>';
	foreach($title as $val){
		echo '<td class=xl2216681 nowrap>'. $val .'</td>';
	}
	echo '</tr>';

	foreach($data as $row){
		echo '<tr>';
		foreach($row as $r){
			echo '<td class=xl2216681 nowrap>'. $r .'</td>';
		}
		echo '</tr>';
	}
	echo '</table></div></body></html>';

	exit;
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
 * 实例化并获取redis连接
 * @return null|Redis
 */
function getRedis(){
	static $instance = null;
	if($instance === null){
		$instance = new \Redis();
		$instance->connect('127.0.0.1', 6379);
	}
	return $instance;
}

/*

 * 功能：生成二维码
 * @param string $qr_data   手机扫描后要跳转的网址
 * @param string $qr_level  默认纠错比例 分为L、M、Q、H四个等级，H代表最高纠错能力
 * @param string $qr_size   二维码图大小，1－10可选，数字越大图片尺寸越大
 * @param string $save_path 图片存储路径
 * @param string $save_prefix 图片名称前缀
function createQRcode($save_path,$qr_data='PHP QR Code :)',$qr_level='L',$qr_size=4,$save_prefix='qrcode'){
    if(!isset($save_path)) return '';
    //设置生成png图片的路径
    $PNG_TEMP_DIR = & $save_path;
    //导入二维码核心程序
    vendor('PHPQRcode.class#phpqrcode');  //注意这里的大小写哦，不然会出现找不到类，PHPQRcode是文件夹名字，class#phpqrcode就代表class.phpqrcode.php文件名
    //检测并创建生成文件夹
    if (!file_exists($PNG_TEMP_DIR)){
        mkdir($PNG_TEMP_DIR);
    }
    $filename = $PNG_TEMP_DIR.'test.png';
    $errorCorrectionLevel = 'L';
    if (isset($qr_level) && in_array($qr_level, array('L','M','Q','H'))){
        $errorCorrectionLevel = & $qr_level;
    }
    $matrixPointSize = 4;
    if (isset($qr_size)){
        $matrixPointSize = & min(max((int)$qr_size, 1), 10);
    }
    if (isset($qr_data)) {
        if (trim($qr_data) == ''){
            die('data cannot be empty!');
        }
        //生成文件名 文件路径+图片名字前缀+md5(名称)+.png
        $filename = $PNG_TEMP_DIR.$save_prefix.md5($qr_data.'|'.$errorCorrectionLevel.'|'.$matrixPointSize).'.png';
        //开始生成
        QRcode::png($qr_data, $filename, $errorCorrectionLevel, $matrixPointSize, 2);
    } else {
        //默认生成
        QRcode::png('PHP QR Code :)', $filename, $errorCorrectionLevel, $matrixPointSize, 2);
    }
    if(file_exists($PNG_TEMP_DIR.basename($filename)))
        return basename($filename);
    else
        return FALSE;
}

*/