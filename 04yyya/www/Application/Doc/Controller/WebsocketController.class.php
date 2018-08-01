<?php
namespace Doc\Controller;
use Think\Controller;

class WebsocketController extends Controller
{

    Const PUSH_IOS_MESSAGE = 1;//推送信息
    Const GET_WECHAT_HEADIMG = 2;//转换头像链接

    Public $ws;
    Public $redis = [];
    //客户数组
    Public $clients = [];
    //账户信息
    Public $infos = [];
    //吖咪微信
    Public $wechat = null;
    //有饭微信
    Public $yfwechat = null;
    //客服开关
    Public $customer_power = 0;
    //单用户依次发送
    Public $send_client_datas = [];

    Public function __construct()
    {
        parent::__construct();

        error_reporting(E_ALL);
        ini_set('default_socket_timeout', -1);

        //验证密码
        $key = I('get.key');
        if($key != C('WS.key')){
            $this->error('非法访问！');
        }
        ini_set('session.save_path', realpath('../tmp'));
        session_start();

        $opt = [
            'appsecret' => C('WX_CONF.secret'),//填写高级调用功能的密钥
            'appid' => C('WX_CONF.appid')	//填写高级调用功能的appid
        ];
        $this->wechat = new \Common\Util\Wechat($opt);
        $this->wechat->checkAuth();

        $yfopt = [
            'appsecret' => C('YF_WX_CONF.secret'),//填写高级调用功能的密钥
            'appid' => C('YF_WX_CONF.appid')	//填写高级调用功能的appid
        ];
        $this->yfwechat = new \Common\Util\Wechat($yfopt);
        $this->yfwechat->checkAuth();
    }

    public function getWXCONF($name) {
        if (strpos(DOMAIN, 'youfan') !== false) {
            return C('YF_WX_CONF.'.$name);
        } else {
            return C('WX_CONF.'.$name);
        }
    }

    /**
     * 启动websocket
     */
    public function start(){
        $conf = C('WS');
        $this->ws = new \swoole_websocket_server($conf['ip'], $conf['port']);
        $opt = [
            'worker_num' => 1,
            'daemonize' => 1,
            'task_worker_num' => 16,
            'task_ipc_mode' => 3
        ];
        if(strpos(DOMAIN, 't') === false)
            $opt['log_file'] = '/disk/shell/websocket.log';
        else
            $opt['log_file'] = '/home/shell/websocket.log';
        $this->ws->set($opt);

        $this->ws->on('workerstart', [$this, 'onWorkerStart']);

        $this->ws->on('open', [$this, 'onOpen']);

        //微信专用回调
        $this->ws->on('request', [$this, 'onRequest']);

        $this->ws->on('message', [$this, 'onMessage']);

        $this->ws->on('close', [$this, 'onClose']);

        $this->ws->on('task', [$this, 'onTask']);

        $this->ws->on('finish', [$this, 'onFinish']);

        $this->ws->start();
    }

    //绑定worker创建
    public function onWorkerStart($serv){
        if(!$serv->taskworker){
            $that = $this;
            $serv->tick(15000, function() use ($that) {
                if(!empty($that->clients)){
                    foreach($that->clients as $fd => $member_id){
                        if(preg_match('/^\d+$/', $member_id)){
                            //检查是否有新的通播消息
                            $sql = M('MemberMessage')->field(['message_id'])->where(['member_id' => $member_id])->buildSql();
                            $mass = M('message')->where("`isMass`=1 and `effectivetime`>".time()." and `id` NOT IN {$sql}")->select();
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
                            $rs = M()->query("Select count(1) as 'count' from __MEMBER_MESSAGE__ a join __MESSAGE__ b on a.message_id=b.id where a.member_id={$member_id} and is_read=0 and b.sendtime<" . time());
                            if(!empty($rs) && $rs[0]['count'] > 0)$that->send(['count' => $rs[0]['count']], $fd, 'newMsgCount');
                        }
                    }
                }
            });

            $redis = new \swoole_redis;
            $redis->connect('127.0.0.1', 6379, function (swoole_redis $redis, $result) use ($that) {

                $that->redisPop($redis, self::PUSH_IOS_MESSAGE);
                $that->redisPop($redis, self::GET_WECHAT_HEADIMG);
            });
        }
    }

    //绑定接受http请求
    public function onRequest($request, $response){
        $signature = $request->get["signature"];
        $timestamp = $request->get["timestamp"];
        $nonce = $request->get["nonce"];
        $token = C('WX_CONF.key');
        $tmpArr = [$token, $timestamp, $nonce];
        sort($tmpArr, SORT_STRING);
        //$response->end($echostr);
        //return;
        $tmpStr = implode($tmpArr);
        $tmpStr = sha1($tmpStr);
        if($tmpStr == $signature){
            $xml = $request->rawContent();
            if(!empty($xml)){
                echo $xml;
                $xml = str_replace(['<![CDATA[', ']]>'], ['',''], $xml);
                $xml = simplexml_load_string($xml);
                $callBack = json_decode(json_encode($xml), true);
                //判断吖咪和我有饭
                $callBack['channel'] = 0;
                if(strpos($request->header['host'], 'youfanapp.com')){
                    $callBack['channel'] = 1;
                }
                ob_start();
                //路由判断处理事件推送
                switch($callBack['Event']){
                    //获取微信卡券
                    case 'user_get_card':
                        $this->userGetCard($callBack);
                        break;
                    //删除微信卡券
                    case 'user_del_card':
                        $this->userDelCard($callBack);
                        break;
                    //扫描二维码
                    case 'SCAN':
                    case 'subscribe':
                    $this->scanQrcode($callBack);
                        break;
                    //获取地理位置
                    case 'LOCATION':
                        echo 'success';
                        break;
                    default:
                        echo 'success'; // 1、直接回复success（推荐方式）, 保证5秒内处理并回复
                        break;
                }
                if(!isset($callBack['Event']) && isset($callBack['MsgType'])){
                    //处理客服消息
                    $this->getMessage($callBack);
                }
                $content = ob_get_clean();
                if(empty($content))$content = 'success';
                $response->end($content);
                return;
            }
        }
        $response->end('unauthorized access!!!!!!!');
    }

    //绑定websocket开启事件
    public function onOpen($server, $request){
        //获取通道
        $clientid = $request->fd;
        //获取GET参数
        $get = isset($request->get) ? $request->get : [];
        $path = session_save_path() . '/sess_' . $get['token'];
        if(isset($get['token']) && is_file($path)){
            session_decode(file_get_contents($path));
            //判断是否登录
            if(session('?member')){
                $this->clients[$clientid] = session('member.id');
                echo "{$this->clients[$clientid]} 已连接 - 当前在线:". count($this->clients) ."\n";
            }else{
                $this->clients[$clientid] = $get['token'];
                echo "{$get['token']} 已连接 - 当前在线:". count($this->clients) ."\n";
            }
            $this->infos[$this->clients[$clientid]] = $_SESSION;
            $this->infos[$this->clients[$clientid]]['fd'] = $clientid;
        }elseif(isset($get['skey']) && $get['skey'] == C('WS.key')){
            $this->clients[$clientid] = 'admin';
            echo "admin 已连接 - 当前在线:". count($this->clients) ."\n";
            $this->infos['admin'] = ['fd' => $clientid];
        }else{
            echo "非法访问!\n";
            $server->close($clientid);
            return;
        }
        //发送即时通信的在线列表
        $count = 0;
        foreach($this->clients as $row){
            if($row != 'admin')$count ++;
        }
        foreach($this->clients as $fd => $row){
            if($row == 'admin')$this->send($count, $fd, 'imCount');
        }

        //发送在线列表
        foreach($this->infos as $key => $row){
            if(isset($row['talking']) && in_array($this->clients[$clientid], $row['talking'])){
                $this->imOnline(['from_id' => $row['talking']], $row['fd']);
            }
        }

        //发送客服系统状态
        $this->send($this->customer_power, $clientid, 'power');
    }

    //绑定接受消息事件
    public function onMessage ($server, $frame) {
        if($frame->data === '0')return;
        $data = json_decode($frame->data, true);
        if(json_last_error() == JSON_ERROR_NONE){
            $act = $data['act'];
            $data = $data['data'];
            $method = 'im' . ucfirst($act);
            if(method_exists($this, $method)){
                $this->$method($data, $frame->fd);
            }
        }
    }

    //绑定关闭websocket事件
    public function onClose ($server, $fd){
        if(!isset($this->clients[$fd]))return;
        echo "通道 {$this->clients[$fd]} 已断开 - 当前在线:". (count($this->clients)-1) ."\n";
        $val = $this->clients[$fd];
        unset($this->infos[$this->clients[$fd]]);
        unset($this->clients[$fd]);

        //发送在线列表
        foreach($this->infos as $key => $row){
            if(isset($row['talking']) && in_array($val, $row['talking'])){
                $this->imOnline(['from_id' => $row['talking']], $row['fd']);
            }
        }
    }

    //绑定接收任务数据包事件
    public function onTask($serv, $task_id, $from_id, $data) {
//        file_put_contents(WEB_ROOT . 'Application/Runtime/Logs/Websocket/apppush_' . date('Ymd') . '.log', '测试：'.json_encode($data) . PHP_EOL, FILE_APPEND);

        $data = json_decode($data, true);
        $result = [];
        if(empty($data['msg']) || empty($data['devicetoken'])){
            $result = [
                'status' => 0,
                'info' => '发送消息或发送设备不能为空!',
                'devicetoken' => $data['devicetoken']
            ];
//            $this->mq_Log(0,'',0,json_encode($result));
        }else{
            defined(CURL_HTTP_VERSION_2_0) or define(CURL_HTTP_VERSION_2_0, 3);

            if($data['channel'] == 1){
                $pem_file = WEB_ROOT . 'Application/Common/Util/cacert/youfan_apns_cert.pem';
                $pem_secret = ' ';
                $apns_topic = 'com.justfoods.youfan';
            }else{
                $pem_file = WEB_ROOT . 'Application/Common/Util/cacert/yummy_apns_cert.pem';
                $pem_secret = ' ';
                $apns_topic = 'com.yummy.dis';
            }

            $msg = [
                'aps' => [
                    'alert' => $data['msg'],
                    'sound' => $data['sound'] ?: 'default',
                    'badge' => getRedis()->incr($data['devicetoken'] . '_badge')
                ]
            ];
            if(!empty($data['params'])){
                foreach($data['params'] as $name => $val){
                    $msg[trim($name)] = $val;
                }
            }

            if(strpos(DOMAIN, 't') === false)
                $url = 'https://api.push.apple.com/3/device/';
            else
                $url = 'https://api.development.push.apple.com/3/device/';
            $url .= $data['devicetoken'];

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($msg));
            curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_2_0);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ["apns-topic: $apns_topic"]);
            curl_setopt($ch, CURLOPT_SSLCERT, $pem_file);
            curl_setopt($ch, CURLOPT_SSLCERTPASSWD, $pem_secret);
            $rs = [];
            ob_start();
            curl_exec($ch);
            $rs['result'] = ob_get_clean();
            $rs['code'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $rs['error'] = curl_error($ch);
            curl_close($ch);
            if($rs['code'] == 200){
                if(isset($data['msg_id']))$rs['msg_id'] = $data['msg_id'];
                $result = [
                    'status' => 1,
                    'info' => $rs,
                    'devicetoken' => $data['devicetoken']
                ];

                $this->mq_Log(0,$data['msg_id'],json_encode($msg),1,json_encode($result));
            }else{
                $result = [
                    'status' => 0,
                    'info' => $rs,
                    'devicetoken' => $data['devicetoken']
                ];
                $this->mq_Log(0,$data['msg_id'],json_encode($msg),0,json_encode($result));
            }
        }

        //推送成功,修改数据库
        M('MemberMessage')->where(['id' => $data['msg_id']])->save(['is_ios_push' => 1+$data['channel']]);

    }

    //绑定完成任务数据包事件
    public function onFinish($serv, $task_id, $data) {}

    //创建消息
    public function imCreate($data, $fd){
        $from_id = $this->clients[$fd];
        $to_id = $data['to_id']?:'admin';
        $where = "(from_id='{$from_id}' and to_id='{$to_id}') or (to_id='{$from_id}' and from_id='{$to_id}')";
        if(!empty($data['lasttime']))$where = "({$where}) and datetime > '". date('Y-m-d H:i:s', $data['lasttime']) ."'";
        $rs = M('im')->where($where)->select();
        $data = [];
        foreach($rs as $row){
            $row['datetime'] = strtotime($row['datetime']);
            $data[] = $row;
        }
        $this->send($data, $fd, 'create', $to_id);
    }

    //接收并保存/发送消息
    public function imAdd($data, $fd){
        $to_id = $data['to_id'];
        $type = $data['type'];
        $msg = trim($data['msg']);

        $from_id = $this->clients[$fd];

        //处理商品发送
        if($type == 2){
            $arr = explode('-', $msg, 2);
            switch($arr[0]){
                case '0':
                    $rs = M()->query("Select A.id,A.title,B.path from __TIPS__ A join __PICS__ B on A.pic_id=B.id where A.id={$arr[1]}");
                    if(empty($rs)){
                        $this->send([
                            'from_id' => $from_id,
                            'to_id' => $to_id,
                            'type' => 0,
                            'content' => '[发布的活动不存在!]'
                        ], $fd, 'add', $to_id);
                        return;
                    } else {
                        $msg = $rs[0];
                    }
                    break;
                case '1':
                    $rs = M()->query("Select A.id,A.title,B.path from __GOODS__ A join __PICS__ B on A.pic_id=B.id where A.id={$arr[1]}");
                    if(empty($rs)){
                        $this->send([
                            'from_id' => $from_id,
                            'to_id' => $to_id,
                            'type' => 0,
                            'content' => '[发布的商品不存在!]'
                        ], $fd, 'add', $to_id);
                        return;
                    } else {
                        $msg = $rs[0];
                    }
                    break;
                case '2':
                    $rs = M()->query("Select A.id,A.title,B.path from __RAISE__ A join __PICS__ B on A.pic_id=B.id where A.id={$arr[1]}");
                    if(empty($rs)){
                        $this->send([
                            'from_id' => $from_id,
                            'to_id' => $to_id,
                            'type' => 0,
                            'content' => '[发布的众筹不存在!]'
                        ], $fd, 'add', $to_id);
                        return;
                    } else {
                        $msg = $rs[0];
                    }
                    break;
            }
            $msg['path'] = 'http://img.'. DOMAIN .'/'. $msg['path'];
            $msg['type'] = $arr[0];
        }

        $data = [
            'from_id' => $from_id,
            'to_id' => $to_id,
            'type' => $type,
            'content' => $msg
        ];
        if(strpos($to_id, 'openid-') === false){
            //发送消息
            foreach($this->clients as $_fd => $val){
                if($val == $to_id){
                    $this->send($data, $_fd, 'add', $from_id);
                }
            }
        }else{
            //发送微信
            $r = $this->send($msg, str_replace(['openid-', 'yf_openid-'], '', $to_id), strpos($to_id, 'yf_')===false?0:1, $type);
            if($r === false){
                $data['type'] = 0;
                $data['content'] = '[消息发送失败!]';
                $this->send($data, $fd, 'add', $to_id);
                return;
            }
            $data['is_wx'] = 1;
        }

        //发送消息
        $this->send($data, $fd, 'add', $to_id);
        if(is_array($data['content']))$data['content'] = json_encode($data['content']);
        M('im')->add($data);
    }

    //获取客户是否在线
    public function imOnline($data, $fd){
        $from_ids = $data['from_id'];
        $this->infos[$this->clients[$fd]]['talking'] = [];
        $data = [];
        foreach($from_ids as $from_id){
            $from_id = str_replace('token-', '', $from_id);
            $this->infos[$this->clients[$fd]]['talking'][] = $from_id;
            if(in_array($from_id, $this->clients)){
                $data[] = $from_id;
            }
        }
        $this->send($data, $fd, 'online');
    }

    //开关客服系统
    public function imPower($data, $fd){
        echo $fd."\n";
        if($this->clients[$fd] == 'admin'){
            if($data == 0){
                $this->customer_power = 0;
            }else{
                $this->customer_power = 1;
            }
            foreach($this->clients as $_fd => $val){
                $this->send($data, $_fd, 'power');
            }
        }
    }

    /**
     * websocket消息发送
     * @param $msg 要发送的数据
     * @param $fd 消息通道 int-websocket通道ID string-微信 openid wx-微信客户消息
     */
    public function send($msg, $fd = null, $act = null, $from_id = null){
        if(is_numeric($fd)) {
            if(!empty($act)){
                $msg = [
                    'act' => $act,
                    'data' => $msg
                ];
                if(!empty($from_id))$msg['from_id'] = $from_id;
            }
            $this->ws->push($fd, json_encode($msg));
        }elseif($fd == 'wx'){
            if($act == 1){
                $this->yfwechat->sendCustomMessage($msg);
            }else{
                $this->wechat->sendCustomMessage($msg);
            }
        }elseif(is_string($fd)){
            if($act == 1){
                $wx = $this->yfwechat;
            }else{
                $wx = $this->wechat;
            }

            if($from_id == 1){
                $file = realpath('../upload/' . $msg);
                $cfile = curl_file_create($file, 'image/jpeg');
                $imgdata = ['media' => $cfile];
                $media = $wx->uploadForeverMedia($imgdata, 'image');
                unlink($file);
                if(!$media){
                    var_dump($wx->errMsg);
                    return false;
                }
                $_data = [
                    "touser" => $fd,
                    "msgtype" => "image",
                    "image" => [
                        "media_id" => $media['media_id']
                    ]
                ];
            }else{
                $_data = [
                    "touser" => $fd,
                    "msgtype" => "text",
                    "text" => [
                        "content" => $msg
                    ]
                ];
            }

            $a = $wx->sendCustomMessage($_data);
            if(!$a){
                var_dump($wx->errMsg);
                return false;
            }
            return true;
        }
    }

    public function error($msg){
        $data = [
            'status' => 0,
            'info' => $msg
        ];
        $this->ws->send(json_decode($data));
    }

    public function success($msg){
        $data = [
            'status' => 1,
            'info' => $msg
        ];
        $this->ws->send(json_decode($data));
    }

    public function redisPop($redis, $type = 1){
        $that = $this;
        if($type === 1){
            $redis->blPop(str_replace('.', '', DOMAIN) . '_app_push', 0, function($redis, $data) use ($that) {
                $that->ws->task($data[1]);
                echo json_encode($data).'---->'.date('Y-m-d H:i:s',time());
                $that->redisPop($redis);
            });
        }elseif($type === 2){
            $redis->blPop(str_replace('.', '', DOMAIN) . '_img_down', 0, function($redis, $data) use ($that) {
                $data = json_decode($data[1], true);
                if(!empty($data['pic_id']) && !empty($data['path'])){
                    if($newname = getPicAndSave($data['path'])){
                        M('pics')->where(['id' => $data['pic_id']])->save(['path' => $newname]);
                    }
                }
                $that->redisPop($redis, 2);
            });
        }
    }

    //微信卡券回调 - 获取卡券
    Public function userGetCart($callBack){
        if($callBack['IsGiveByFriend'] == 1){
            $openid = $callBack['FriendUserName'];
        }else{
            $openid = $callBack['FromUserName'];
        }
        $code = $callBack['UserCardCode'];
        $wx_sn = $callBack['CardId'];
        $coupon_id = M('coupon')->where(['wx_sn' => $wx_sn])->getField('id');
        $data = [
            'sn' => $code,
            'coupon_id' => $coupon_id
        ];
        $_data = [
            'openid' => $openid,
        ];
        $member_id = M('member')->where(['openid|yf_openid' => $openid])->getField('id');
        if(!empty($member_id)){
            $_data['member_id'] = $member_id;
        }
        $rs = M('MemberCoupon')->where($data)->save($_data);
        if($rs == 0){
            $data = array_key_merge($data, $_data);
            M('MemberCoupon')->add($data);
        }
    }

    //微信卡券回调 - 删除卡券
    Public function userDelCart($callBack){
        $wx_sn = $callBack['CardId'];
        $cardCode = $callBack['UserCardCode'];
        $coupon_id = M('coupon')->where(['wx_sn' => $wx_sn])->getField('id');
        $data = [
            'sn' => $cardCode,
            'coupon_id' => $coupon_id
        ];
        M('MemberCoupon')->where($data)->delete();
    }

    //微信定制二维码回调
    Public function scanQrcode($callBack){
        $openid = $callBack['FromUserName'];
        $data = ['openid' => $openid];
        //获取qrcode_id
        if($callBack['Event'] == 'SCAN'){
            $data['qrcode_id'] = $callBack['EventKey'];
            $data['event'] = 1;
        }else{
            if(!empty($callBack['EventKey'])){
                $data['qrcode_id'] = str_replace('qrscene_', '', $callBack['EventKey']);
            }else{
                $rs = M('WechatReply')->where(['channel' => $callBack['channel'], 'name' => 'autoreply', 'status' => 1])->find();
                if(empty($rs))return;
                $json = json_decode($rs['contents'], true);
                $json = $json[0];
                if($json['status']){
                    if(isset($json['media_id'])){
                        $data = [
                            'touser' => $openid,
                            'msgtype' => 'mpnews',
                            'mpnews' => [
                                'media_id' => $json['media_id']
                            ]
                        ];
                        $this->send($data, 'wx', $callBack['channel']);
                    }else{
                        $dataStr = "<xml>";
                        $dataStr .= "<ToUserName><![CDATA[{$callBack['FromUserName']}]]></ToUserName>";
                        $dataStr .= "<FromUserName><![CDATA[{$callBack['ToUserName']}]]></FromUserName>";
                        $dataStr .= "<CreateTime><![CDATA[". time() ."]]></CreateTime>";
                        $dataStr .= "<MsgType><![CDATA[text]]></MsgType>";
                        $dataStr .= "<Content><![CDATA[{$json['text']}]]></Content>";
                        $dataStr .= '</xml>';
                        echo $dataStr;
                    }
                }
                return;
            }
            $data['event'] = 0;
        }
        //获取member_id
        $rs = M('member')->where(['openid|yf_openid' => $openid])->getField('id');
        if(!empty($rs))$data['member_id'] = $rs;
        //插入数据
        M('QrcodeUsers')->add($data);

        //查找推送并推送消息
        $rs = M('qrcode')->field(['media_id', 'context'])->where(['id' => $data['qrcode_id'], 'status' => 1])->find();
        if(!empty($rs) && !empty($rs['media_id'])){
            $data = [
                'touser' => $openid,
                'msgtype' => 'mpnews',
                'mpnews' => [
                    'media_id' => $rs['media_id']
                ]
            ];
            $this->send($data, 'wx', $callBack['channel']);
        }elseif(!empty($rs) && !empty($rs['context'])){
            $data = [
                'touser' => $openid,
                'msgtype' => 'text',
                'text' => [
                    'content' => $rs['context']
                ]
            ];
            $this->send($data, 'wx', $callBack['channel']);
        }
    }

    //接收微信回调的客户消息
    Public function getMessage($callBack){
        $openid = $callBack['FromUserName'];
        $ch = $callBack['channel'] ? 'yf_openid' : 'openid';
        //        echo 'success';
        switch($callBack['MsgType']){
            case 'text':
                $from_id = $ch . '-' . $openid;
                $data = [
                    'from_id' => $from_id,
                    'to_id' => 'admin',
                    'type' => 0,
                    'content' => $callBack['Content'],
                    'is_wx' => 1
                ];

                //接收文本消息
//                foreach($this->clients as $fd => $admin){
//                    if($admin == 'admin')
//                        $this->send($data, $fd, 'add', $from_id);
//                }
                //消息入库
                M('im')->add($data);

                //自动回复
                $rs = M('WechatReply')->where(['channel' => $callBack['channel'], 'status' => 1, 'keys' => ['NEQ', '[]']])->order('id desc')->select();
                foreach($rs as $row){
                    if (!isset($rs) || empty($rs)) {
                        continue;
                    }
                    $keys = json_decode($row['keys'], true);
                    foreach($keys as $key){
                        if(($key['match'] == 1 && $key['keyword'] == $callBack['Content']) || ($key['match'] == 0 && strpos($callBack['Content'], $key['keyword']) !== false)){
                            //匹配成功
                            $contents = json_decode($row['contents'], true);
                            $data = [];
                            foreach($contents as $json){
                                if($json['status']){
                                    if(isset($json['media_id'])){
                                        $data[] = [
                                            'touser' => $openid,
                                            'msgtype' => 'mpnews',
                                            'mpnews' => [
                                                'media_id' => $json['media_id']
                                            ]
                                        ];
                                    }else{
                                        $data[] = [
                                            'touser' => $openid,
                                            'msgtype' => 'text',
                                            'text' => [
                                                'content' => $json['text']
                                            ]
                                        ];
                                    }
                                }
                            }
                            if(!empty($data)){
                                switch((int)$row['send_type']){
                                    case 0:
                                        foreach($data as $d){
                                            $this->send($d, 'wx', $callBack['channel']);
                                        }
                                        break;
                                    case 1:
                                        $this->send($data[rand(0, count($data) - 1)], 'wx', $callBack['channel']);
                                        break;
                                    case 2:
                                        $skey = md5($openid . $row['id']);
                                        if(!isset($this->send_client_datas[$skey]))$this->send_client_datas[$skey] = 0;
                                        $this->send($data[$this->send_client_datas[$skey]], 'wx', $callBack['channel']);
                                        $this->send_client_datas[$skey] ++;
                                        if($this->send_client_datas[$skey] >= count($data))$this->send_client_datas[$skey] = 0;
                                        break;
                                }
                            }
                            return;
                        }
                    }
                }
                $rs = M('WechatReply')->where(['channel' => $callBack['channel'], 'status' => 1, 'name' => 'default'])->find();
                if(empty($rs))return;
                $json = json_decode($rs['contents'], true);
                $json = $json[0];
                if($json['status']){
                    if(isset($json['media_id'])){
                        $data = [
                            'touser' => $openid,
                            'msgtype' => 'mpnews',
                            'mpnews' => [
                                'media_id' => $json['media_id']
                            ]
                        ];
                        $this->send($data, 'wx', $callBack['channel']);
                    }else{
                        $dataStr = "<xml>";
                        $dataStr .= "<ToUserName><![CDATA[{$callBack['FromUserName']}]]></ToUserName>";
                        $dataStr .= "<FromUserName><![CDATA[{$callBack['ToUserName']}]]></FromUserName>";
                        $dataStr .= "<CreateTime><![CDATA[". time() ."]]></CreateTime>";
                        $dataStr .= "<MsgType><![CDATA[text]]></MsgType>";
                        $dataStr .= "<Content><![CDATA[{$json['text']}]]></Content>";
                        $dataStr .= '</xml>';
                        echo $dataStr;
                    }
                }
                break;
            case 'image':
                //接收图片消息
                $media = $this->wechat->getMedia($callBack['MediaId']);
                if($media){
                    $conf = C('UPLOAD_CONFIG');
                    $filetype = $this->wechat->info['content_type'];
                    $ext = '.jpg';
                    switch($filetype){
                        case 'image/png':
                            $ext = '.png';
                            break;
                        case 'image/gif':
                            $ext = '.gif';
                            break;
                    }
                    $myDir = date('Ymd') . '/';
                    $filename = fileCrypt();
                    $object = $myDir . $filename . $ext;
                    $bucket = substr(DOMAIN, 0, 1) == 't' ? "yamiimg" : "yummyimg";
                    $ossClient = new \OSS\OssClient($conf['accessKeyId'], $conf['accessKeySecret'], $conf['endpoint']);
                    $ossClient->putObject($bucket, $object, $media);

                    $from_id = $ch . '-' . $openid;
                    $data = [
                        'from_id' => $from_id,
                        'to_id' => 'admin',
                        'type' => 1,
                        'content' => $object,
                        'is_wx' => 1
                    ];
//                    foreach($this->clients as $fd => $admin){
//                        if($admin == 'admin')
//                            $this->send($data, $fd, 'add', $from_id);
//                    }
                    //消息入库
                    M('im')->add($data);
                }
                break;
            default:
                echo "<xml><ToUserName><![CDATA[{$openid}]]></ToUserName><FromUserName><![CDATA[{$callBack['ToUserName']}]]></FromUserName><CreateTime>".time()."</CreateTime><MsgType><![CDATA[text]]></MsgType><Content><![CDATA[暂不支持除文本和图片外的其他消息!]]></Content></xml>";
                return;
        }
      //  echo 'success';
    }

    /**
     * 记录消息队列日志
     */
    private function mq_Log($type,$type_id,$data='',$status,$result=''){
        if(!empty($type_id)){
            M('MqLog')->add([
                'type'=>$type,
                'type_id'=>$type_id,
                'data'=>$data,
                'status'=>$status,
                'result'=>$result,
            ]);
        }
    }

}