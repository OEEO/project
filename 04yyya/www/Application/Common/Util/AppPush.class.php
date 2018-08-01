<?php
namespace Common\Util;

class AppPush
{

    const cert_path = COMMON_PATH . 'Util/cacert/yummy_push.pem';
    const cert_pass = 'yami@2016';

    private $cli = null;

    //发送次数
    static private $num = 0;
    //连接上下文
    static private $context = null;
    //错误输出
    static public $errmsg = '';

    static public function connect()
    {
        static $instance = null;

        if($instance === null){
            //建立连接
            self::$context = stream_context_create();
            stream_context_set_option(self::$context, 'ssl', 'local_cert', self::cert_path);
            stream_context_set_option(self::$context, 'ssl', 'passphrase', self::cert_pass);
            $cli = stream_socket_client('ssl://gateway.sandbox.push.apple.com:2195', $err, $errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, self::$context);
            if (!$cli){
                self::$errmsg = "Failed to connect: $err $errstr";
                return false;
            }
            $instance = new self;
            $instance->cli = $cli;
        }
        var_dump($instance->cli);
        return $instance;
    }

    /**
     * 重启连接
     * @return bool
     */
    public function reconnect()
    {
        fclose($this->cli);
        $this->cli = stream_socket_client('ssl://gateway.sandbox.push.apple.com:2195', $err, $errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, self::$context);
        if (!$this->cli){
            self::$errmsg = "Failed to connect: $err $errstr";
            return false;
        }
        return true;
    }

    /**
     * 推送通知
     * @param $deviceToken 要推送的目标设备ID
     * @param $message 要推送的内容
     * @return bool
     */
    public function send($deviceToken, $message)
    {
        if(self::$num > 100){
            $this->reconnect();
            self::$num = 0;
        }
        self::$num ++;
        $body['aps'] = [
            'alert' => $message,
            'sound' => 'default',
            'badge' => +1,
            'type' => 1
        ];
        $payload = json_encode($body);
        $msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;
        $result = fwrite($this->cli, $msg, strlen($msg));

        if (!$result)
            return false;
        else
            return true;
    }

    public function __destruct()
    {
        //断开连接
        fclose($this->cli);
    }
}