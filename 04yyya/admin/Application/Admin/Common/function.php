<?php
//定义图片路径
define('NEW_IMG_PATH','http://img.test.yami.ren/');
define('OLD_IMG_PATH','http://yummy194.cn/');
/**
 * 加密
 * @param string $str 要加密的字符串
 * @return string 加密后的字符串
 */
function encrypt($str){
	$len = strlen($str);
	$n = ceil($len/2);
	$prevStr = substr($str, 0, $n);
	$nextStr = substr($str, $n);
	return md5(md5($prevStr) . md5($len) . md5($nextStr));
}

//格式化图片路径
function pathFormat($path){
    if(strpos($path, 'http://') !== false)return $path;
    if(substr($path,0,1) == '/')$path = substr($path,1);
    if(strpos($path, 'uploads/') !== false){
        if(strpos($path, 'yami') !== false)$path = str_replace('yami/', '', $path);
        $path = 'http://yummy194.cn/' . $path;
    }else{
        $path = 'http://img.'. WEB_DOMAIN .'/' . $path;
    }
    return $path;
}

function createSqlInsertAll($table, $data){
	$datas = array();
	foreach($data as $vals){
		$names = $values = array();
		foreach($vals as $k => $v){
			$names[] = "`{$k}`";
			$values[] = "'" . str_replace("'", '', $v) . "'";
		}
		$datas[] = "(". join(',', $values) .")";
	}
	
	return "Insert  into `{$table}` (". join(',', $names) .") values " . join(',', $datas);
}

/**
 * 获取指定模块下所有控制器
 * @param string $moduleName 指定模块名
 * @return array 返回控制器名称数组
 */
function getController($moduleName){
	$moduleName = ucfirst($moduleName);
	$dir = APP_PATH . $moduleName . '/Controller';
	$dir = realpath($dir);
	
	$dh  = opendir($dir);
	while (false !== ($filename = readdir($dh))) {
    		$files[] = $filename;
	}
	
	$arr = array();
	foreach($files as $val){
		if($val == 'MainController.class.php')continue;
		$n = strpos($val, 'Controller.class.php');
		if($n > 0){
			$arr[] = strtolower(substr($val, 0, $n));
		}
	}
	return $arr;
}

/**
 * 获取指定控制器下所有方法
 * @param string $moduleName 指定模块名
 * @param string $controllerName 指定模块名
 * @return array 返回方法名称数组
 */
function getMethod($moduleName, $controllerName){
	$module = ucfirst($moduleName);
	$controller = ucfirst($controllerName) . 'Controller';
	$classPath = $module .'\\Controller\\' . $controller;
	if(!class_exists($classPath)){
		$controller = strtoupper($controllerName) . 'Controller';
		$classPath = $module .'\\Controller\\' . $controller;
		if(!class_exists($classPath)){
			return array();
		}
	}
	$r = new reflectionclass($classPath);
	$methods = array();
	foreach($r->getmethods() as $key=>$methodobj){
		if($methodobj->name == '__construct' || $methodobj->name == '_initialize')continue;
		if($methodobj->class == $classPath){
			$methods[] = $methodobj->name;
		}
	}
	return $methods;
}

/**
 * 获取微信access_token
 * @return AccessToken
 */
function getAccessToken(){
    $key = 'wechat_access_token' . C('WX_CONF.appid');
    $token = cache($key);
    if (is_string($token)) {
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
            $code .= $char[rand(0, 62) - 1];
        }
        return $code;
    }
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
 * 短信发送
 * @param string[array] $tel 发送号码[数组为多条发送]
 * @param string $msg 待发送消息
 * @param int $time 定时发送时间戳，默认立即发送
 * @param int $channel 渠道 0-吖咪 1-我有饭
 */
function sms_send($tel, $msg, $time = null, $channel = 0){

    if(is_array($tel)){
        $tel = join(',', $tel);
    }

    if($channel){
        $conf = C('YF_SMS_CONFIG');
        $data = [
            'apikey' => $conf['apikey'],
            'text' => $msg,
            'mobile' => $tel
        ];
    }else {
        $conf = C('SMS_CONFIG');
        $data = [
            'userid' => $conf['userid'],
            'password' => $conf['password'],
            'msg' => $msg,
            'destnumbers' => $tel
        ];
        if(!empty($time))$data['sendtime'] = date('Y-m-d H:i:s', $time);
    }

    $post_data = [];
    foreach($data as $key => $val){
        $post_data[] = "{$key}={$val}";
    }
    $post_data = implode('&', $post_data);
    $url = $conf['url'];
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    ob_start();
    curl_exec($ch);
    $result = ob_get_contents();
    ob_end_clean();
    if(!empty($result)){
        //preg_match('/return="(.+?)" info="(.+?)"/', $result, $arr);
        return true;
    }else{
        return false;
    }
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

    \Think\Log::write('$data'.$data['ParamString']);
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
    \Think\Log::write('Telephone: '. $data['RecNum'] ."\nTemplateCode: " . $templateCode . "\nParams: " . json_encode($params) . "\nReturnInfo: " . $rs);
    if($status == 200)
        return true;
    else
        return false;
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
        $sizes = json_decode(M('pics', C('DB2.DB_PREFIX'), 'DB2')->where(['path' => $p])->getField('size'), true);
        if(json_last_error() === JSON_ERROR_NONE) {
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
        if(WEB_DOMAIN == 'yummy.com'){
            $myPath = 'http://img.m.yami.ren/' . $path;
        }else{
            $myPath = 'http://img.'. WEB_DOMAIN .'/' . $_path;
        }
    }
    return $myPath;
}

/**
 * 推送消息
 * @param $member_id 推送的目标会员ID
 * @param $context 推送消息内容
 * @param int|string|null $origin_id 推送来源会员ID(number-来源会员ID, 'wx'-发送到微信公众号, 'sms'-发送到会员短信上, 'email'-发送到会员邮箱)
 * @param int $type 消息关联资源(0-普通消息 1-评论回复通知 2-达人动态通知 3-订单动态通知 4-活动推送消息 5-专题推送消息)
 * @param int $type_id 关联的活动ID、专题ID、订单ID、食报ID
 * @param int $sendtime 发送时间
 */
/*function pushMessage($member_id, $context, $origin_id = null, $type = 0, $type_id = 0, $sendtime = 0){
    if(strlen($context) > 250 || empty($context)){
        \Think\Log::write($context . ' Length:' . strlen($context));
        $this->error('消息不能为空或太长,推送失败!');
    }

    $data = [
        'type' => $type,
        'type_id' => $type_id,
        'content' => $context,
        'sendtime' => $sendtime
    ];

    if($origin_id === null)$origin_id = null;
    elseif(is_string($origin_id)){
        $send_way = explode('|', $origin_id);
        $rs = $this->m2('member')->where(['id' => $member_id])->find();
        if(in_array('wx', $send_way)){
            $data['wx_send'] = 1;
            if(!empty($rs['openid'])){
                $wechat = wxLoad();
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
            $sms = sms_send($rs['telephone'], $context, $sendtime, false);
        }
    }

    if(is_numeric($origin_id))$data['member_id'] = $origin_id;
    $message_id = $this->m2('message')->add($data);

    $this->m2('MemberMessage')->add([
        'member_id' => $member_id,
        'message_id' => $message_id,
        'is_sms' => !empty($sms) ? 1 : 0,
        'is_wx' => !empty($wx) ? 1: 0
    ]);

    return true;
}*/


//加载微信
/*function wxLoad(){
    $opt = array(
        'secret' => C('WX_CONF.secret'),//填写高级调用功能的密钥
        'appid' => C('WX_CONF.appid')	//填写高级调用功能的appid
    );
    $wechat = new \Common\Util\Wechat($opt);
    return $wechat;
}*/
function addFileToZip($path,$zip){
    $handler=opendir($path); //打开当前文件夹由$path指定。
    while(($filename=readdir($handler))!==false){
        if($filename != "." && $filename != ".."){//文件夹文件名字为'.'和‘..’，不要对他们进行操作
            if(is_dir($path."/".$filename)){// 如果读取的某个对象是文件夹，则递归
                addFileToZip($path."/".$filename, $zip);
            }else{ //将文件加入zip对象
                $zip->addFile($path."/".$filename);
            }
        }
    }
    @closedir($path);
}


function toXls($title, $data, $filename){

    header("Content-type:application/vnd.ms-excel");
    header("Content-Disposition:attachment;filename=".$filename."-".date('Y-m-d',time()).".xls");

    echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html><head><meta http-equiv="Content-type" content="text/html;charset=UTF-8" /><style id="Classeur1_16681_Styles"></style></head>
<body><div id="Classeur1_16681" align=center x:publishsource="Excel"><table x:str border=0 cellpadding=0 cellspacing=0 width=100% style="border-collapse: collapse">';

    echo '<tr style="line-height: 30px; height: 30px;">';
    foreach($title as $val){
        if(is_array($val)&&!empty($val[1]) && !empty($val[2])){
            echo '<td class=xl2216681 nowrap style="font-size: 16px; height: '.$val[2].';line-height: '.$val[2].'; font-weight: bold;width:'.$val[1].';">'. $val[0] .'</td>';
        }elseif(is_array($val)&&empty($val[1]) && !empty($val[2])){
            echo '<td class=xl2216681 nowrap style="font-size: 16px; height: '.$val[2].';line-height: '.$val[2].'; font-weight: bold;">'. $val[0] .'</td>';
        }elseif(is_array($val)&&!empty($val[1]) && empty($val[2])){
            echo '<td class=xl2216681 nowrap style="font-size: 16px; height: 20px;line-height: 22px; font-weight: bold;width:'.$val[1].';">'. $val[0] .'</td>';
        }else{
            echo '<td class=xl2216681 nowrap style="font-size: 16px; height: 20px;line-height: 22px; font-weight: bold;" >'. $val.'</td>';
        }
    }
    echo '</tr>';

    foreach($data as $row){
        echo '<tr style="line-height: 26px; height: 26px;">';
        foreach($row as $r){
            if(is_array($r)&&!empty($r[1]) && !empty($r[2])){
                echo '<td class=xl2216681 nowrap style="font-size: 14px;width:'.$r[1].'; height: '.$r[2].'; line-height: '.$r[2].';">'. $r[0] .'</td>';
            }elseif(is_array($r)&&empty($r[1]) && !empty($r[2])){
                echo '<td class=xl2216681 nowrap style="font-size: 14px; height: '.$r[2].'; line-height: '.$r[2].';">'. $r[0] .'</td>';
            }elseif(is_array($r)&&!empty($r[1]) && empty($r[2])){
                echo '<td class=xl2216681 nowrap style="font-size: 14px; width:'.$r[1].'height: 18px; line-height: 20px;">'. $r[0] .'</td>';
            }else{
                echo '<td class=xl2216681 nowrap style="font-size: 14px; height: 18px; line-height: 20px;">'. $r .'</td>';
            }
        }
        echo '</tr>';
    }
    echo '</table></div></body></html>';

    exit;
}


//2016-10-20
function toXls_old($titleArr, $data,$filename){
    $comma_data = [];
    foreach($data as $row){
        $d = [];
        foreach($row as $r){
            if(is_numeric($r) && $r>999999999)$r = '\'' . $r;
            $r = preg_replace('/(&nbsp;|\r|\n|\t)+/', '', trim($r));
            $d[] = iconv('utf-8','gbk',$r);
            //$d[] = $r;
        }
        $comma_data[] = join("\t", $d);
    }

    $title = join("\t",$titleArr);
    $title = iconv('utf-8','gbk',$title);
    $comma_data = $title . "\n" . join("\n", $comma_data);

    header("Content-type:application/vnd.ms-excel; charset=UTF-8");
    header("Content-Disposition:attachment;filename=".$filename.date("Y-m-d",time()).".xls");
    header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
    header('Expires:0');
    header('Pragma:public');
    echo $comma_data;
    exit;
}

function toXls_new($data,$titleArr,$count,$fileName){
    set_time_limit(0);
    $i = 1;
    $dir =  date("Ymd").mt_rand(100,999);
    $comma_data = '';
    foreach ($data as $key => $row) {
        $data_row = [];
        foreach($row as $k=>$r){
            if(is_numeric($r) && $r>999999999)$r = '\'' . $r;
            $r = preg_replace('/(&nbsp;|\r|\n|\t)+/', '', trim($r));
            $data_row[] .= iconv('utf-8','gb2312',$r);
        }
        $comma_data .= join("\t",$data_row)."\n";

        if($i % $count == 0 || $i == count($data)){

            /*//最后一条数据
            if($i == count($data) - 1){
                $data_row = array();
                foreach($row as $k=>$r){
                    if(is_numeric($r) && $r>999999999)$r = '\'' . $r;
                    $r = preg_replace('/(&nbsp;|\r|\n|\t)+/', '', trim($r));
                    $data_row[] .= iconv('utf-8','gb2312',$r);
                }
                $comma_data .= join("\t",$data_row)."\n";
            }*/
            $comma_data = iconv('utf-8', 'gb2312', join("\t",$titleArr))."\n" .$comma_data;
            if(isset($comma_data) && !empty($comma_data)) {
                ob_start();
                echo $comma_data;
                $context = ob_get_clean();
                if(!is_dir($dir))mkdir($dir);
                file_put_contents($dir.'/' . date("YmdHis"). $fileName . '('.ceil($i / $count).')' . ".xls", $context);
                $comma_data = null;
            }

            //$comma_data = iconv('utf-8', 'gb2312', $comma_data);
        }

        $i ++;
    }
    $open_dir = 'export/'.date("YmdHis").'-'.mt_rand(100,999).'.zip';
    if(!is_dir('export'))mkdir('export');
    $zip=new \ZipArchive();
    if(/*$zip->open($open_dir, $zip::OVERWRITE)===*/ TRUE) {
        $zip->open($open_dir, \ZipArchive::CREATE);
        addFileToZip($dir, $zip); //调用方法，对要打包的根目录进行操作，并将ZipArchive的对象传递给方法
        $zip->close(); //关闭处理的zip文件

        $dh = opendir($dir);
        while ($file = readdir($dh)) {
            if ($file != "." && $file != "..") {
                $fullpath = $dir . "/" . $file;
                if (!is_dir($fullpath)) {
                    unlink($fullpath);
                } else {
                    //deldir($fullpath);
                }
            }
        }
        closedir($dh);
        //删除当前文件夹：
        rmdir($dir);
    }
    header('location: http://'. $_SERVER['HTTP_HOST'] .'/'.$open_dir);
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

