<?php
namespace Home\Controller;
use Home\Common\MainController;
use Think\Log;
use Think\Think;

// @className 微信操作接口
Class WxController extends MainController {
	
	/**
	 * @apiName 获取用户授权跳转的url
	 *
	 * @apiGetParam {string} token: 通信令牌
     * @apiGetParam {int} isReturn: 是否返回网址 (默认1：返回)
     *
     * @apiPostParam {int} type: 授权类型 0-主动授权 1-静默授权
     * @apiPostParam {string} url: 回调地址
     *
	 * @apiSuccessResponse
	 * //用于跳转的url字符串
	 * "https:\/\/open.weixin.qq.com\/connect\/oauth2\/authorize?appid=wxa9f30cec0b573147&redirect_uri=http%3A%2F%2Fyummy.com%2F%23ucenter&response_type=code&scope=snsapi_userinfo&state=#wechat_redirect"
	 */
	Public function getOauthUrl($isReturn = 1){
		$isReturn = I('get.isReturn', $isReturn);
		$type = I('post.type', 2);
        $state = I('request.token');
        $url = I('post.url', 'http://' . DOMAIN);
        if($type == 1){
            //$apiType = 'snsapi_base';
            $apiType = 'snsapi_userinfo';
            $state .= '|base';
        }else{
            $apiType = 'snsapi_userinfo';
            $state .= '|userinfo';
        }
        $url = $this->wechat->getOauthRedirect($url, $state, $apiType);
		if (!$url) {
			$err = '错误码：'.$this->wechat->errCode . "\n";
			$err .= ' 错误原因：' . \Common\Util\ErrCode::getErrText($this->wechat->errCode);
			$this->error($err);
		}
        if($isReturn == 1)
		    $this->ajaxReturn($url);
        else
            header('location: ' . $url);
	}
	
	/**
	 * @apiName 获取用户授权的access_token
	 *
	 * @apiGetParam {string} token: 通信令牌
	 * @apiGetParam {string} code: 通过上一步获得的code
     * @apiGetParam {int} isapp: 1-微信小程序 2-iosapp/androidapp
	 *
	 * @apiSuccessResponse
	 * //用于跳转的url字符串
	 * "https:\/\/open.weixin.qq.com\/connect\/oauth2\/authorize?appid=wxa9f30cec0b573147&redirect_uri=http%3A%2F%2Fyummy.com%2F%23ucenter&response_type=code&scope=snsapi_userinfo&state=#wechat_redirect"
	 */
	Public function getOauthLogin(){
        $code = isset($_GET['code'])?$_GET['code']:'';
        if(empty($_GET['isapp']))
		    $json = $this->wechat->getOauthAccessToken($code);
        elseif($_GET['isapp'] == 1) { //获取微信小程序的用户授权session
            $this->wechat = \Common\Util\Wxapp::instance();
            $json = $this->wechat->getSession($code);
        }elseif($_GET['isapp'] == 2){
            //ios 微信登录
            $json = [
                'openid' => I('post.openid', ''),
                'unionid' => I('post.unionid', ''),
                'nickname' => I('post.nickname', ''),
                'sex' => I('post.sex', ''),
                'headimgurl' => I('post.headimgurl', ''),
            ];
        }
		if (!$json) {
            $err = '错误码：' . $this->wechat->errCode . "\n";
            $err .= '错误原因：' . \Common\Util\ErrCode::getErrText($this->wechat->errCode) . "\n";
            $err .= '错误信息：' . $this->wechat->errMsg;
            $this->error($err);
        }elseif(empty($json['openid'])){
            $this->error('openid不能为空!');
		} else {
			$rs = M('MemberView')->where("`openid`='{$json['openid']}' and `telephone` REGEXP '^1[358][0-9]{9}$'")->find();
            $open_rs = M('Openid')->where(['openid' => $json['openid']])->find();
            if(!empty($rs)){
                if($rs['status'] == 2)$this->error("该用户已被禁用，请联系客服！");
                $_data = [];
                if(empty($rs['unionid']) && !empty($json['unionid'])){
                    $_data['unionid'] = $json['unionid'];
                    $rs['unionid'] = $json['unionid'];
                }
                if($_GET['isapp'] == 2){
                    if(!empty($json['headimgurl'])){
                        //将微信头像抓取到本地保存并生成缩略图
                        $pic_id = M('pics')->add([
                            'type' => 2,
                            'path' => $json['headimgurl'],
                            'original_path' => $json['headimgurl'],
                            'is_used' => 1
                        ]);
                        $dt = [
                            'pic_id' => $pic_id,
                            'path' => $json['headimgurl'],
                        ];
                        getRedis()->rPush(str_replace('.', '', DOMAIN) . '_img_down', json_encode($dt));

                    }
                    $_data = [
                        'nickname' => !empty($json['nickname'])?$json['nickname']:$rs['nickname'],
                        'sex' => !empty($json['sex'])?$json['sex']:$rs['sex'],
                        'pic_id' =>!empty($pic_id)?$pic_id:$rs['pic_id'],
                    ];
                }
                \Think\Log::write('$_data信息：'.json_encode($_data));
                if(!empty($_data)){
                    $_data['type'] = $this->openidType;
                    M('openid')->where(['openid' => $json['openid']])->save($_data);
                }
                \Think\Log::write('getLastSql信息：'. M('openid')->getLastSql());
                $info = $this->getinfo($rs['id']);
                $this->success([
                    'info' => $info,
                    'skey' => createSkey($rs['id'], $info['register_time']),
                    'isRegister' => 1,
                    'logined' => 0
                ]);
			}else{
                if(session('?member')){
                    $data = ['openid' => $json['openid']];
                    if(isset($json['unionid'])){
                        $data['unionid'] = $json['unionid'];
                    }
                    $data['member_id'] = session('member.id');
                    $data['nickname'] = session('member.nickname');
                    $data['sex'] = session('member.sex');
                    $data['city_id'] = session('member.city_id');
                    $data['pic_id'] = session('member.pic_id');
                    $data['type'] = $this->openidType;
                    M('openid')->add($data);

                    $info = $this->getinfo(session('member.id'));
                    $this->success([
                        'info' => $info,
                        'skey' => createSkey(session('member.id'), $info['register_time']),
                        'isRegister' => 1,
                        'logined' => 1
                    ]);
                }else{
                    $use = I('post.use');
                    if(!empty($use) && $use == 'getCoupon'){
                        $this->success([
                            'open_id' => $json['openid']
                        ]);
                    }else{
                        //判断是否有unionid
                        if(empty($json['unionid']) && !empty($_GET['isapp']) && $_GET['isapp'] == 1){
                            //获取unionid
                            $encryptedData = I('post.encryptedData');
                            $iv = I('post.iv');
                            $info = $this->wechat->decryptData($encryptedData, $iv, $json['session_key']);
                            $json['unionid'] = $info->unionId;
                        }


                        //unionid 登录
                        $rs = M('MemberView')->where("`unionid`='{$json['unionid']}' and `telephone` REGEXP '^1[358][0-9]{9}$'")->order('id desc')->find();
                        if(!empty($rs)){
                            $_da = [
                                'member_id' => $rs['id'],
                                'type' => $this->openidType,
                                'openid' => $json['openid'],
                                'sex' => $rs['sex'],
                                'pic_id' =>$rs['pic_id'],
                                'nickname' => $rs['nickname'],
                                'unionid' => $json['unionid']
                            ];
                            if($_GET['isapp'] == 2){
                                if(!empty($json['pic_id'])){
                                    //将微信头像抓取到本地保存并生成缩略图
                                    $pic_id = M('pics')->add([
                                        'type' => 2,
                                        'path' => $json['headimgurl'],
                                        'original_path' => $json['headimgurl'],
                                        'is_used' => 1
                                    ]);
                                    $dt = [
                                        'pic_id' => $pic_id,
                                        'path' => $json['headimgurl'],
                                    ];
                                    getRedis()->rPush(str_replace('.', '', DOMAIN) . '_img_down', json_encode($dt));
                                }

                                    $_da['nickname'] = !empty($json['nickname'])?$json['nickname']:$rs['nickname'];
                                    $_da['sex'] = !empty($json['sex'])?$json['sex']:$rs['sex'];
                                    $_da['pic_id'] =!empty($pic_id)?$pic_id:$rs['pic_id'];
                            }
                            if(empty($open_rs)){
                                M('openid')->add($_da);
                                $info = $this->getinfo($rs['id']);
                                $this->success([
                                    'info' => $info,
                                    'skey' => createSkey($rs['id'], $info['register_time']),
                                    'isRegister' => 1,
                                    'logined' => 0
                                ]);
                            }else {
                                M('openid')->where(['openid' => $json['openid']])->save($_da);
                                $info = $this->getinfo($rs['id']);
                                $this->success([
                                    'info' => $info,
                                    'skey' => createSkey($rs['id'], $info['register_time']),
                                    'isRegister' => 1,
                                    'logined' => 0
                                ]);
                            }
                        }

                        if(isset($json['session_key'])){
                            session('session_key', $json['session_key']);
                            session('session_timeout', $json['expires_in'] + time());
                        }elseif(isset($json['access_token'])){
                            session('access_token', $json['access_token']);
                            session('access_token_timeout', $json['expires_in'] + time());
                            session('refresh_token', $json['refresh_token']);
                        }
                        session('openid', $json['openid']);
                        if(isset($json['unionid']))session('unionid', $json['unionid']);
                        $this->success("获取授权成功！请验证手机号完成注册！");
                    }
                }
			}
		}
	}

    //获取基本用户信息
    private function getinfo($member_id){
        //读取会员标签数组
        $tags = M('MemberTag')->join('join `__TAG__` on __TAG__.id=tag_id')->field('tag_id,name')->where(['member_id' => $member_id])->select();

        $member = new \Member\Model\MemberViewModel();
        $rs = $member->where(['id' => $member_id, 'status' => 1])->find();

        $openid = M('Openid')->field('type,openid')->where(['member_id'=>$member_id])->select();
        foreach($openid as $row){
            if($row['type'] == 1){
                $rs['openid'] = $row['openid'];
            }elseif($row['type'] == 2){
                $rs['yf_openid'] = $row['openid'];
            }elseif($row['type'] == 3){
                $rs['app_openid'] = $row['openid'];
            }elseif($row['type'] == 4){
                $rs['ios_openid'] = $row['openid'];
            }elseif($row['type'] == 5){
                $rs['openid'] = $row['openid'];
            }elseif($row['type'] == 6){
                $rs['openid'] = $row['openid'];
            }elseif($row['type'] == 7){
                $rs['openid'] = $row['openid'];
            }
        }
        $rs['path'] = thumb($rs['path'],2);
        $rs['password'] = !empty($rs['password']) ? 1 : 0;
//        $rs['dr_status'] = $rs['dr_status']?:0;
        $rs['birth'] = empty($rs['birth'])?'':$rs['birth'];
        //读取城市
        $rs['area_id'] = '';
        $rs['area_name'] = '';
        $rs['city_id'] = '';
        $rs['city_name'] = '';
        $rs['province_id'] = '';
        $rs['province_name'] = '';
        $city_rs = M('citys')->where(['id' => $rs['citys_id']])->find();
        if(strpos($city_rs['alt'], '市') !== false){
            $rs['city_id'] = $city_rs['id'];
            $rs['city_name'] = $city_rs['name'];
            $_city_rs = M('citys')->where(['id' => $city_rs['pid']])->find();
            $rs['province_id'] = $_city_rs['id'];
            $rs['province_name'] = $_city_rs['name'];
        }elseif(strpos($city_rs['alt'], '区') !== false || strpos($city_rs['alt'], '县') !== false){
            $rs['area_id'] = $city_rs['id'];
            $rs['area_name'] = $city_rs['name'];
            $_city_rs = M('citys')->where(['id' => $city_rs['pid']])->find();
            $rs['city_id'] = $_city_rs['id'];
            $rs['city_name'] = $_city_rs['name'];
            $__city_rs = M('citys')->where(['id' => $_city_rs['pid']])->find();
            $rs['province_id'] = $__city_rs['id'];
            $rs['province_name'] = $__city_rs['name'];
        }
        $rs['dr_contact'] = empty($rs['dr_contact'])?'':$rs['dr_contact'];
        $rs['dr_introduce'] = empty($rs['dr_introduce'])?'':$rs['dr_introduce'];
        $rs['path'] = empty($rs['path'])?'':thumb($rs['path'],2);
        $rs['pic_id'] = empty($rs['pic_id'])?'':$rs['pic_id'];
        $rs['signature'] = empty($rs['signature'])?'':$rs['signature'];
        $dr_status = M('MemberApply')->where(['member_id'=>$rs['id'],'type'=>2,'type_id'=>18])->field('is_pass')->find();
        $rs['dr_status'] = (empty($dr_status))?-1:$dr_status['is_pass'];

        $info = $rs;

        $info['tags'] = $tags;
        session('member', $info);
        //记录本次登录时间
        M('MemberLoginLog')->add([
            'member_id' => $rs['id'],
            'ip' => get_client_ip(1)
        ]);
        return $info;
    }


    /**
     * @apiName 获取注册用户的access_token(保存用户信息入库)
     *
     * @apiGetParam {string} token: 通信令牌
     * @apiGetParam {string} code: 通过上一步获得的code
     *
     * @apiSuccessResponse
     * //用于跳转的url字符串
     * skey:'ksjandsafdsfkodnkgfoik'
     */
    public function index(){
        $code = isset($_GET['code'])?$_GET['code']:'';
        if(empty($code)) $this->error('code不能为空！');
        $json = $this->wechat->getOauthAccessToken($code);

        if (!$json) {
            $err = '错误码：' . $this->wechat->errCode . "\n";
            $err .= '错误原因：' . \Common\Util\ErrCode::getErrText($this->wechat->errCode) . "\n";
            $err .= '错误信息：' . $this->wechat->errMsg;
            $this->error($err);
        }else{
            $userInfo = $this->wechat->getOauthUserinfo($json['access_token'],$json['openid']);//{subscribe,openid,nickname,sex,city,province,country,language,headimgurl[unionid]}
            \Think\Log::write('openid信息：'.json_encode($userInfo));
            $Info = $this->wechat->getUserinfo($json['openid']);//{subscribe,openid,nickname,sex,city,province,country,language,headimgurl,subscribe_time,[unionid]}
            $userInfo['subscribe_time'] = 0;
            if($Info && $Info['subscribe'] == 1){
                $userInfo['subscribe_time'] = $Info['subscribe_time'];
            }
            if(!$userInfo){
                $err = '错误码：' . $userInfo['errcode'] . "\n";
                $err .= '错误信息：' .$userInfo['errmsg'];
                $this->error($err);

            }else{
                $rs = M('openid')->where("`openid`='{$userInfo['openid']}'")->find();
                //获取城市ID
                $city_id = D('CityView')->where(['city_pinyin' => $userInfo['city'], 'province_pinyin' => $userInfo['province']])->getField('city_id');
                //将微信头像抓取到本地保存并生成缩略图
                $pic_id = M('pics')->add([
                    'type' => 2,
                    'path' => $userInfo['headimgurl'],
                    'is_used' => 1
                ]);
                $dt = [
                    'pic_id' => $pic_id,
                    'path' => $userInfo['headimgurl'],
                ];
                getRedis()->rPush(str_replace('.', '', DOMAIN) . '_img_down', json_encode($dt));

                if(!empty($rs)){
                    //如果有会员member_id(不修改member表里的数据)
                    if(!empty($rs['member_id'])){
                        $member = M('member')->join('__MEMBER_INFO__ AS B ON B.member_id = __MEMBER__.id')->where(['id'=>$rs['member_id']])->find();
                        $mem_info = [];
                        $mem_data = [];
                        if(preg_match('/^手机号_\d/',$member['nickname'])){
                            $mem_data['nickname'] = $userInfo['nickname'];
                        }
                        if($member['sex'] == 0 && $member['sex'] == ''){
                            $mem_info['sex'] = $userInfo['sex'];
                        }
                        if($member['pic_id'] == 0 && $member['pic_id'] == ''){
                            $mem_data['pic_id'] = $pic_id;
                        }
                        if($member['citys_id'] == 0 && $member['citys_id'] == ''){
                            $mem_info['citys_id'] = $city_id;
                        }
                        M('Member')->where(['id'=>$rs['member_id']])->save($mem_data);
                        M('MemberInfo')->where(['member_id'=>$rs['member_id']])->save($mem_info);
                    }
                    M('openid')->where(['openid' => $json['openid']])->save([
                        'sex' => $userInfo['sex'],
                        'nickname' => $userInfo['nickname'],
                        'type' => $this->openidType,
                        'city_id' => $city_id,
                        'unionid' => $userInfo['unionid'],
                        'subscribe_time' => $userInfo['subscribe_time'],
                    ]);
                    if(isset($json['access_token'])){
                        session('access_token', $json['access_token']);
                    }
                    $this->success([
                        'skey' => createSkey($json['openid'], strtotime($rs['datetime'])),
                        'id' => $rs['id'],
                    ]);

                }else{
                    $id = M('openid')->add([
                        'type' => $this->openidType,
                        'openid' => $json['openid'],
                        'unionid' => $json['unionid'],
                        'pic_id' =>$pic_id,
                        'nickname' =>$userInfo['nickname'],
                        'sex' =>$userInfo['sex'],
                        'city_id' =>$city_id,
                        'subscribe_time' =>$userInfo['subscribe_time'],
                    ]);

                    $openid_rs = M('openid')->where("`openid`='{$json['openid']}'")->find();
                    if(isset($json['access_token'])){
                        session('access_token', $json['access_token']);
                    }
                    $this->success([
                        'skey' => createSkey($json['openid'], strtotime($openid_rs['datetime'])),
                        'id' =>$id
                    ]);
                }

            }
        }
    }

    /**
     * @apiName 登录
     * @apiGetParam {string} token: 通信令牌
     * @apiPostParam {string} skey: 保存登录的密钥
     * @apiPostParam {string} id: 微信用户ID
     *
     *
     * @apiSuccessResponse
     * {
     *     "id": "294920",
     *     "member_id": "278518",
     *     "openid": "oUX2WxOoQn7HihQD3C5Mf9la93lw",
     *     "type": "1",
     *     "nickname": "紫嫣",
     *     "sex": "2",
     *     "city_id": "228",
     *     "pic_id": "",
     *     "first_login": "1488510878",
     *     "datetime": "2017-02-28 11:32:41",
     *     "path": "",
     *     "city_name": "茂名",
     *     "head_path": "",
     * }
     *
     * @apiErrorResponse
     * {
     *	 "status": 状态码,
     *	 "info": "签到成功"
     * }
     */
    public function Login()
    {
        $skey = I('post.skey');
        $id = I('post.id');
        if(empty($skey) || empty($id))$this->error('SKEY和会员ID不能为空！');
        $sess = $rs = D('OpenIdView')->where(['id'=>$id])->find();
        \Think\Log::write('Login$rs信息：'.json_encode($rs));
        if(!empty($rs)) {
            if(!empty($rs['openid'])){
                $info = $this->wechat->getUserinfo($rs['openid']);
                \Think\Log::write('Login信息：'.json_encode($info));
                if(!empty($info) && $info['subscribe'] == 1){
                    if(!empty($info['sex']))$data['sex'] = $rs['sex'] = $info['sex'];
                    if(!empty($info['nickname']))$data['nickname'] = $rs['nickname'] = $info['nickname'];
                    if(!empty($info['subscribe_time']))$data['subscribe_time'] = $rs['subscribe_time'] = $info['subscribe_time'];
                    //获取城市ID
                    if(!empty($info['city']) && !empty($info['province'])){
                        $city_id = D('CityView')->where(['city_name' => $info['city'], 'province_name' => $info['province']])->getField('city_id');
                        if(!empty($city_id)) $data['city_id'] = $rs['city_id'] = $city_id;
                    }
                    if( !empty($info['headimgurl'])&&$rs['original_path'] != $info['headimgurl']){
                        //将微信头像抓取到本地保存并生成缩略图
                        $pic_id = M('pics')->add([
                            'type' => 2,
                            'path' => $info['headimgurl'],
                            'original_path' => $info['headimgurl'],
                            'is_used' => 1
                        ]);
                        $dt = [
                            'pic_id' => $pic_id,
                            'path' => $info['headimgurl'],
                        ];
                        getRedis()->rPush(str_replace('.', '', DOMAIN) . '_img_down', json_encode($dt));
                        if(!empty($pic_id))$data['pic_id'] = $rs['pic_id'] = $pic_id;
                    }
                    if(!empty($data)){
                        M('Openid')->where(['openid'=>$rs['openid']])->save($data);
                    }
                }

            }
            if (createSkey($rs['openid'], strtotime($rs['datetime'])) == $skey) {
                $sess['sex'] = $rs['sex'];
                $sess['nickname'] = $rs['nickname'];
                $sess['city_id'] = $rs['city_id'];
                $sess['subscribe_time'] = $rs['subscribe_time'];
                $sess['path'] = $rs['path'];

                $sess['head_path'] = $rs['head_path'] = thumb($rs['path'], 2);
                $sess['datetime'] = $rs['datetime'] = strtotime($rs['datetime']);
                session('wxUser', $sess);
//                $info = $sess;
                if(!empty($rs['member_id'])){
                    $member = $this->getinfo($rs['member_id']);
                    $this->success([
                        'info' => $sess,
                        'member' => $member,
                        'skey' => createSkey($rs['openid'], strtotime($rs['datetime'])),
                    ]);
                }else{
                    $this->success([
                        'info' => $sess,
                        'skey' => createSkey($rs['openid'], strtotime($rs['datetime'])),
                    ]);

                }
            }
        }
        $this->error('SKEY不正确或已过期！');
    }
    //厨房
    public function Applicant(){
        $name = $_POST['name'];
        $tel = $_POST['tel'];
        $question = $_POST['question'];
        $kid = (int)$_POST['kid'];

        $rs = M('Kitchenapplicant')->where(['telephone'=>$tel,'kid'=>$kid])->find();
        if(!empty($rs)){
            echo 'done';
        }else{
            $id = M('Kitchenapplicant')->data(['name'=>$name,'telephone'=>$tel,'question'=>$question,'kid'=>$kid])->add();
            if(!empty($id)){
                $resultMsg = '';
                switch($kid){
                    case 0:
                        $telephone = 15202022092;
                        break;
                    case 1:
                        $telephone = 15986609882;
                        break;
                    case 2:
                        $telephone = 13902295678;
                        break;
                    case 3:
                        $telephone = 18688486558;
                        break;
                    case 4:
                        $telephone = 15913182405;
                        break;
                    case 5:
                        $telephone = 18826269655;
                        break;
                    case 6:
                        $telephone = 13825012509;
                        break;
                    case 7:
                        $telephone = 13450382383;
                        break;
                    case 8:
                        $telephone = 13602412139;
                        break;
                    case 9:
                        $telephone = 13826117765;
                        break;
                    case 10:
                        $telephone = 13763368419;
                        break;
                    case 11:
                        $telephone = 13560125400;
                        break;
                    case 12:
                        $telephone = 13560125400;
                        break;
                    case 13:
                        $telephone = 13682289839;
                        break;
                    case 14:
                        $telephone = 18664831215;
                        break;
                    case 15:
                        $telephone = 13632255642;
                        break;
                    case 16:
                        $telephone = 18565096080;
                        break;
                    case 17:
                        $telephone = 13416228388;
                        break;
                    case 18:
                        $telephone = 18026237335;
                        break;
                    case 19:
                        $telephone = 13660308340;
                        break;
                    case 20:
                        $telephone = 17002023200;
                        break;
                    case 21:
                        $telephone = 13726887202;
                        break;
                    case 22:
                        $telephone = 13711175721;
                        break;
                    case 23:
                        $telephone = 13632101360;
                        break;
                    case 24:
                        $telephone = 15521283248;
                        break;
                    case 25:
                        $telephone = 15899646333;
                        break;
                    case 26:
                        $telephone = 13798152646;
                        break;
                    case 27:
                        $telephone = 13119510945;
                        break;
                    case 28:
                        $telephone = 13692068009;
                        break;
                    case 29:
                        $telephone = 18520582588;
                        break;
                    case 30:
                        $telephone = 13535332748;
                        break;
                    case 31:
                        $telephone = 13322802400;
                        break;
                    case 32:
                        $telephone = 13824459015;
                        break;
                }
//                $sendContent='尊敬的吖咪厨房主人，您的精美厨房有料理人想要预定啦！预定人：'.$name.'；电话：'.$tel.'；';
//                if(!empty($question))$sendContent = 'TA还说：'.$question.'。';
//                $sendContent .= '请尽快联系TA，让厨房热闹起来吧！ 吖咪Yummy 更多精彩美食活动尽在yummy194.cn';
//                sms_send($telephone,$sendContent,false);

                if(!empty($question))$sendContent = 'TA还说：'.$question.'。';
                $params = array(
                    'name' => $name,
                    'tel' => $tel,
                    'question' => $sendContent,
                );
                smsSend($telephone,'SMS_36035181', $params);

                echo 'y';
            }else{
                echo 'n';
            }
        }
    }



    /*function cleanOrder(){
        $data = D('CleanOrderWareView')->where(['act_status'=>1,'end_time'=>['LT',time()],'B.type'=>0])->field('order_id,end_time')->group('A.id')->select();
        foreach($data as $row){
            $ids[] = $row['order_id'];
        }
        if(!empty($ids)){
            M('Order')->where(['id'=>['IN',join(',',$ids)]])->data(['act_status'=>4])->save();
            M('OrderWares')->where(['order_id'=>['IN',join(',',$ids)]])->data(['server_status'=>1])->save();
        }

    }*/
	
}