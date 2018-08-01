<?php
namespace Common\Util;

class Wxapp
{
    private $appid;
    private $secret;
    public $errCode;
    public $errMsg;

    /**
     * 构造函数
     * @param $sessionKey string 用户在小程序登录后获取的会话密钥
     * @param $appid string 小程序的appid
     */
    private function __construct()
    {
        $conf = C('WX_APP');
        $this->secret = $conf['appSecret'];
        $this->appid = $conf['appid'];
    }

    Static public function instance()
    {
        static $instance = null;
        if($instance === null){
            $instance = new self;
        }
        return $instance;
    }

    public function getSession($code)
    {
        $result = file_get_contents("https://api.weixin.qq.com/sns/jscode2session?appid={$this->appid}&secret={$this->secret}&js_code={$code}&grant_type=authorization_code");
        $json = json_decode($result, true);
        if (!$json || !empty($json['errcode'])) {
            $this->errCode = $json['errcode'];
            $this->errMsg = $json['errmsg'];
            return false;
        }
        return $json;
    }

    /**
     * 检验数据的真实性，并且获取解密后的明文.
     * @param $encryptedData string 加密的用户数据
     * @param $iv string 与用户数据一同返回的初始向量
     * @param $sessionKey string 用于解密的密钥
     *
     * @return int 成功0，失败返回对应的错误码
     */
    public function decryptData($encryptedData, $iv, $sessionKey)
    {
        if(strlen($sessionKey) != 24){
            $this->errCode = '密钥错误';
            return false;
        }
        $aesKey = base64_decode($sessionKey);

        if(strlen($iv) != 24){
            $this->errCode = '向量错误';
            return false;
        }
        $aesIV = base64_decode($iv);

        $aesCipher = base64_decode($encryptedData);

        $result = $this->_decrypt($aesKey, $aesCipher, $aesIV);

        if($result[0] != 0){
            $this->errCode = $result[0];
            return false;
        }

        $dataObj = json_decode($result[1]);
        if($dataObj == NULL){
            return false;
        }
        if($dataObj->watermark->appid != $this->appid){
            return false;
        }
        $this->errCode = 'ok';

        return $dataObj;
    }

    private function _decrypt($aesKey, $aesCipher, $aesIV)
    {
        try {
            $module = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, '');
            mcrypt_generic_init($module, $aesKey, $aesIV);
            //解密
            $decrypted = mdecrypt_generic($module, $aesCipher);
            mcrypt_generic_deinit($module);
            mcrypt_module_close($module);
        } catch (Exception $e) {
            return ['解密出错', null];
        }

        try {
            $pad = ord(substr($decrypted, -1));
            if ($pad < 1 || $pad > 32) {
                $pad = 0;
            }
            $result = substr($decrypted, 0, (strlen($decrypted) - $pad));
        } catch (Exception $e) {
            return ['解密出错', null];
        }
        return [0, $result];
    }

}




