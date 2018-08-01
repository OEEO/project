<?php

namespace Common\Util;

class AppPushMessage {
    //发送apns server时发送消息的键
    const APPLE_RESERVED_NAMESPACE = 'aps';

    /*
    * 连接apns地址
    * 'https://api.push.apple.com:443/3/device/', // 生产环境
    * 'https://api.development.push.apple.com:443/3/device/' // 沙盒环境
    **/
    private $_appleServiceUrl;
    //证书
    private $_sProviderCertificateFile;
    //要发送到的device token
    private $_deviceTokens = array();
    //额外要发送的内容
    private $_customProperties;
    //私钥密码
    private $_passPhrase;
    //要推送的文字消息
    private $_pushMessage;
    //要推送的语音消息
    private $_pushSoundMessage;
    //设置角标
    private $_nBadge;
    //发送的头部信息
    private $_headers = array();
    private $_errors;
    //推送的超时时间，如果超过了这个时间，就自动不推送了，单位为秒
    private $_expiration;
    //apple 唯一标识
    private $_apns_topic;
    //10：立即接收，5：屏幕关闭，在省电时才会接收到的。如果是屏幕亮着，是不会接收到消息的。而且这种消息是没有声音提示的
    private $_priority;
    //cURL允许执行的最长秒数
    private $_timeout;
    //curl单连接
    private $_hSocket;
    //curl多连接
    private $_multi_hSocket;
    /**< @type integer Status code for internal error (not Apple). */
    const STATUS_CODE_INTERNAL_ERROR = 999;

    const ERROR_WRITE_TOKEN = 1000;
    //apple server 返回的错误信息
    protected $_aErrorResponseMessages = array(
        200 => 'Sussess',
        400 => 'Bad request',
        403 => 'There was an error with the certificate',
        405 => 'The request used a bad :method value. Only POST requests are supported',
        410 => 'The device token is no longer active for the topic',
        413 => 'The notification payload was too large',
        429 => 'The server received too many requests for the same device token',
        500 => 'Internal server error',
        503 => 'The server is shutting down and unavailable',
        self::STATUS_CODE_INTERNAL_ERROR => 'Internal error',
        self::ERROR_WRITE_TOKEN => 'Writing token error',
    );

    public function __construct() {}

    /*
    * 连接apple server
    * @params certificate_file 证书
    * @params pass_phrase 私钥密码
    * @params apple_service_url 要发送的apple apns service
    * @params expiration 推送的超时时间，如果超过了这个时间，就自动不推送了，单位为秒
    * @params apns-topic apple标识
    **/
    public function connServer($params) {
        if (empty($params['certificate_file'])) {
            return false;
        }
        $this->_sProviderCertificateFile = $params['certificate_file'];
        $this->_appleServiceUrl = $params['apple_service_url'];
        $this->_passPhrase = $params['pass_phrase'];
        $this->_apns_topic = $params['apns-topic'];
        $this->_expiration = $params['expiration'];
        $this->_priority = $params['priority'];
        $this->_timeout = $params['timeout'];

        $this->_headers = array(
            'apns-topic:'. $params['apns-topic'],
            'apns-priority'. $params['priority'],
            'apns-expiration'. $params['expiration']
        );

        $this->_multi_hSocket = curl_multi_init();

        if (!$this->_multi_hSocket) {
            $this->_errors['connServer']['cert'] = $this->_sProviderCertificateFile;
            $this->_errors['connServer']['desc'] = "Unable to connect to '{$this->_appleServiceUrl}': $this->_multi_hSocket";
            $this->_errors['connServer']['nums'] = isset($this->_errors['connServer']['nums']) ? intval($this->_errors['connServer']['nums']) : 0;
            $this->_errors['connServer']['nums'] += 1;

            return false;
        }

        return $this->_multi_hSocket;
    }

    /*
    * 断连
    **/
    public function disconnect() {
        if (is_resource($this->_multi_hSocket)) {
            curl_multi_close($this->_multi_hSocket);
        }
        if (!empty($this->_hSocket)) {
            foreach ($this->_hSocket as $val) {
                if (is_resource($val)) {
                    curl_close($val);
                }
            }

            $this->_hSocket = array();
            return true;
        }

        return false;
    }

    //设置发送文字消息
    public function setMessage($message) {
        $this->_pushMessage = $message;
    }

    //设置发送语音消息
    public function setSound($sound_message = 'default') {
        $this->_pushSoundMessage = $sound_message;
    }

    //获取要发送的文字消息
    public function getMessage() {
        if (!empty($this->_pushMessage)) {
            return $this->_pushMessage;
        }
        return '';
    }

    //获取语音消息
    public function getSoundMessage() {
        if (!empty($this->_pushSoundMessage)) {
            return $this->_pushSoundMessage;
        }
        return '';
    }

    /*
    * 接收device token 可以是数组，也可以是单个字符串
    **/
    public function addDeviceToken($device_token) {
        if (is_array($device_token) && !empty($device_token)) {
            $this->_deviceTokens = $device_token;
        } else {
            $this->_deviceTokens[] = $device_token;
        }
    }

    //返回要获取的device token
    public function getDeviceToken($key = '') {
        if ($key !== '') {
            return isset($this->_deviceTokens[$key]) ? $this->_deviceTokens[$key] : array();
        }
        return $this->_deviceTokens;
    }

    //设置角标
    public function setBadge($nBadge) {
        $this->_nBadge = intval($nBadge);
    }

    //获取角标
    public function getBadge() {
        return $this->_nBadge;
    }

    /*
    * 用来设置额外的消息
    * @params custom_params array $name 不能和 self::APPLE_RESERVED_NAMESPACE（'aps'）样
    **/
    public function setCustomProperty($custom_params) {
        foreach ($custom_params as $name=>$value) {
            if (trim($name) == self::APPLE_RESERVED_NAMESPACE) {
                $this->_errors['setCustomProperty'][] = $name.'设置不成功，'.$name.'不可以设置成 aps.';
            }
            $this->_customProperties[trim($name)] = $value;
        }
    }

    /*
    * 用来获取额外设置的值
    * @params string $name
    **/
    public function getCustomProperty($name = '') {
        if ($name !== '') {
            return isset($this->_customProperties[trim($name)]) ? $this->_customProperties[trim($name)] : '';
        }

        return $this->_customProperties;
    }

    /**
     * 组织发送的消息
     */
    protected function getPayload() {
        $aPayload[self::APPLE_RESERVED_NAMESPACE] = array();

        if (isset($this->_pushMessage)) {
            $aPayload[self::APPLE_RESERVED_NAMESPACE]['alert'] = $this->_pushMessage;
        }
        if (isset($this->_pushSoundMessage)) {
            $aPayload[self::APPLE_RESERVED_NAMESPACE]['sound'] = (string)$this->_pushSoundMessage;
        }
        if (isset($this->_nBadge)) {
            $aPayload[self::APPLE_RESERVED_NAMESPACE]['badge'] = (int)$this->_nBadge;
        }

        if (is_array($this->_customProperties) && !empty($this->_customProperties)) {
            foreach($this->_customProperties as $sPropertyName => $mPropertyValue) {
                $aPayload[$sPropertyName] = $mPropertyValue;
            }
        }

        return json_encode($aPayload);
    }

    /*
    * 推送消息
    **/
    public function send() {
        if (empty($this->_multi_hSocket)) {
            return false;
        }

        if (isset($this->_errors['connServer'])) {
            unset($this->_errors['connServer']);
        }

        if (empty($this->_deviceTokens)) {
            $this->_errors['send']['not_deviceTokens']['desc'] = 'No device tokens';
            $this->_errors['send']['not_deviceTokens']['time'] = date("Y-m-d H:i:s",time());
            return false;
        }

        if (empty($sendMessage = $this->getPayload())) {
            $this->_errors['send']['not_message']['desc'] = 'No message to push';
            $this->_errors['send']['not_message']['time'] = date("Y-m-d H:i:s",time());
            return false;
        }

        var_dump($sendMessage);exit;

        $hArr = array();
        foreach ($this->_deviceTokens as $key=>$token) {
            $this->_hSocket[$key] = curl_init();
            if (!defined(CURL_HTTP_VERSION_2_0)) {
                define(CURL_HTTP_VERSION_2_0, 3);
            }
            curl_setopt($this->_hSocket[$key], CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_2_0);
            curl_setopt($this->_hSocket[$key], CURLOPT_SSLCERT, $this->_sProviderCertificateFile);
            curl_setopt($this->_hSocket[$key], CURLOPT_SSLCERTPASSWD, $this->_passPhrase);
            curl_setopt($this->_hSocket[$key], CURLOPT_SSLCERTTYPE, 'PEM');
            curl_setopt($this->_hSocket[$key], CURLOPT_TIMEOUT, $this->_timeout);
            curl_setopt($this->_hSocket[$key], CURLOPT_URL, $this->_appleServiceUrl . $token);
            curl_setopt($this->_hSocket[$key], CURLOPT_POSTFIELDS, $sendMessage);
            curl_setopt($this->_hSocket[$key], CURLOPT_HTTPHEADER, $this->_headers);
            curl_setopt($this->_hSocket[$key], CURLOPT_RETURNTRANSFER, 1);

            $rs = curl_exec($this->_hSocket[$key]);
            $info = curl_getinfo($this->_hSocket[$key]);
            $err = curl_error($this->_hSocket[$key]);
            curl_close($this->_hSocket[$key]);
            var_dump($rs);
            var_dump($info);
            var_dump($err);
            exit;

            if (!$this->_hSocket[$key]) {
                $this->_errors['send']['cert'] = $this->_sProviderCertificateFile;
                $this->_errors['send']['desc'][$key] = "Unable to connect to '{$this->_appleServiceUrl}': $this->_hSocket[$key]";
            } else {
                array_push($hArr, $this->_hSocket[$key]);
                curl_multi_add_handle($this->_multi_hSocket,$this->_hSocket[$key]);
            }
        }
        if (empty($hArr)) {
            $this->_errors['send']['hSocket'] = "all socket link faild";
            $this->_errors['send']['time'] = date("Y-m-d H:i:s", time());
            return false;
        }

        $running = null;
        do {
            $rs = curl_multi_exec($this->_multi_hSocket, $running);
        } while ($running > 0);

        foreach ($hArr as $h) {
            $info = curl_getinfo($h);
            $response_errors = curl_multi_getcontent($h);
            if ($info['http_code'] !== 200) {
                $device_token = explode('/',$info['url']);
                $this->_writeErrorMessage(json_decode($response_errors, true), $info['http_code'], array_pop($device_token));
            }
            curl_multi_remove_handle($this->_multi_hSocket, $h);
        }

        $this->_deviceTokens = [];
        return true;
    }

    //获取发送过程中的错误
    public function getError() {
        return $this->_errors;
    }

    /*
    * 读取错误信息
    *@params res_errors 发送失败的具体信息
    *@params res_code 响应头返回的错误code
    *@params token 发送失败的device token
    **/
    protected function _writeErrorMessage($res_errors, $res_code, $token) {
        $errors = [
            'reason' => $res_errors,
            'response_code' => $res_code,
            'token' => $token,
            'time' => date("Y-m-d H:i:s",time())
        ];
        if (isset($this->_aErrorResponseMessages[$res_code])) {
            $errors['msg'] = $this->_aErrorResponseMessages[$res_code];
        }
        $this->_errors['send']['response'][] = $errors;

        $this->disconnect();
        sleep(0.5);
        $this->_resConnect();
    }

    //重新连接
    protected function _resConnect() {
        $conn_res = $this->connServer(array(
            'certificate_file' => $this->_sProviderCertificateFile,
            'apple_service_url' => $this->_appleServiceUrl,
            'pass_phrase'	=> $this->_passPhrase,
            'priority'	=> $this->_priority,
            'apns-topic'	=> $this->_apns_topic,
            'expiration'	=> $this->_expiration,
            'timeout'	=> $this->_timeout
        ));

        if (!$conn_res) {
            $this->_errors['connServer']['res_conn_nums'] = isset($this->_errors['connServer']['res_conn_nums']) ? intval($this->_errors['connServer']['res_conn_nums']) : 0;
            $this->_errors['connServer']['res_conn_nums'] += 1;
            if ($this->_errors['connServer']['res_conn_nums'] >=5) {
                return false;
            }

            return $this->_resConnect();
        }
        if (isset($this->_errors['connServer'])) {
            unset($this->_errors['connServer']);
        }
        return true;
    }

}