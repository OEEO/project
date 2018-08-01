<?php
namespace Home\Controller;
use Home\Common\MainController;

// @className 早餐打卡
Class SignController extends MainController {

    public function __construct(){
        parent::__construct();
        //验证登录
        if(!session('?wxUser') && !in_array(strtolower(ACTION_NAME), ['index', 'login', 'getenjoysign'])){
            $this->error('没有登录,无法访问接口!');
        }
    }

    /**
     * @apiName 早餐签到列表数据
     *
     * @apiGetParam {string} token: 通信令牌
     * @apiGetParam {string} page: 分页
     *
     *
     * @apiSuccessResponse
     * {
     *     "list": [
     *         {
     *             "sign_id": "65",
     *             "open_id": "294980",
     *             "title": "测试",
     *             "pic_id": "1000669686",
     *             "datetime": "1491964065",
     *             "serial_sign": "1",
     *             "sign_path": "http://img.m.yami.ren/20170412/ZGIwNDU3N2EyYjM1YzZkNmEzMmEwMD.jpg?x-oss-process=image/rotate,0"
     *         },
     *         {
     *             "sign_id": "65",
     *             "open_id": "294980",
     *             "title": "测试",
     *             "pic_id": "1000669686",
     *             "datetime": "1491964065",
     *             "serial_sign": "1",
     *             "sign_path": "http://img.m.yami.ren/20170412/ZGIwNDU3N2EyYjM1YzZkNmEzMmEwMD.jpg?x-oss-process=image/rotate,0"
     *         }
     *     ]
     * }
     *
     */
    public function GetSignList(){
        $page = I('get.page', 1);
        $open_id = session('wxUser.id');
        $Sql = 'open_id='.$open_id.' AND status = 1';
        $list = D('SignView')->where($Sql)->page($page, 5)->group('A.id')->order('A.datetime desc')->select();
//        $rs = M('openid')->where(['id' => $open_id])->find();
//        $is_sign = 0;
//        $sign_datetime = time();
//        $s_time =date('Y-m-d 00:00:00',time());
//        $e_time = date('Y-m-d 23:59:59',time());
//        $findSql = 'A.open_id = '.$rs['id'].' AND A.datetime >= "'.$s_time.'" AND A.datetime <= "'.$e_time.'" AND status = 1';
//        $sign_count = D('SignView')->where($findSql)->find();
//        if(!empty($sign_count))$is_sign = 1;
//        $Sql = 'open_id='.$open_id .' AND A.status = 1';
//        $datetime_arr = D('SignView')->where($Sql)->group('A.id')->order('A.datetime desc')->getField('datetime',true);

//        $join_count['day'] =0;
//        if(!empty($rs['first_login']))$join_count = $this->computeDay(strtotime(date('Y-m-d',$rs['first_login'])),strtotime(date('Y-m-d',strtotime('tomorrow'))));

//        $serial_count = !empty($datetime_arr)?$this->computeTime($datetime_arr):0;//连续打卡数
//        $total_count = M('sign')->where('open_id = '.$rs['id'].' AND status =1')->count();//累计打卡数
//        $join_count =$join_count['day'];//加入打卡数

        foreach($list as $k=>$v){
            $list[$k]['sign_path'] = thumb($v['sign_path']);
            $list[$k]['datetime'] = strtotime($v['datetime']);
            $Sql = 'open_id='.$v['sign_id'] .' AND A.status = 1';
            $datetime_arr = D('SignView')->where($Sql)->group('A.id')->order('A.datetime desc')->getField('datetime',true);
            $list[$k]['serial_sign'] = $this->computeTime($datetime_arr);
        }
        $data =[
//            'is_sign'=>$is_sign,
//            'sign_datetime'=>$sign_datetime,
//            'serial_count'=>$serial_count,
//            'total_count'=>$total_count,
//            'join_count'=>$join_count,
            'list'=>$list,
        ];
        $this->put($data);
    }

    /**
     * @apiName 早餐签到用户信息
     *
     * @apiGetParam {string} token: 通信令牌
     *
     *
     * @apiSuccessResponse
     * {
     *     "is_sign": "1",
     *     "sign_datetime": "1491978392",
     *     "serial_count": "1",
     *     "total_count": "1",
     *     "join_count": "1"
     * }
     *
     */
    public function GetSigner(){
        $open_id = session('wxUser.id');
//        $Sql = 'open_id='.$open_id.' AND status = 1';
//        $list = D('SignView')->where($Sql)->group('A.id')->order('A.datetime desc')->select();
        $rs = M('openid')->where(['id' => $open_id])->find();
        $is_sign = 0;
        $sign_datetime = time();
        $s_time =date('Y-m-d 00:00:00',time());
        $e_time = date('Y-m-d 23:59:59',time());
        $findSql = 'A.open_id = '.$rs['id'].' AND A.datetime >= "'.$s_time.'" AND A.datetime <= "'.$e_time.'" AND status = 1';
        $sign_count = D('SignView')->where($findSql)->find();
        if(!empty($sign_count))$is_sign = 1;
        $Sql = 'open_id='.$open_id .' AND A.status = 1';
        $datetime_arr = D('SignView')->where($Sql)->group('A.id')->order('A.datetime desc')->getField('datetime',true);
        if(!empty($datetime_arr)){
            if($is_sign == 0){
                if((strtotime(date('Y-m-d 23:59:59',time())) - strtotime($datetime_arr[0]) < 2*24 *3600) && (strtotime(date('Y-m-d 00:00:00',time())) - strtotime($datetime_arr[0]) < 24 *3600)) {
                    $new_date[0] = time();
                    $new_arr = array_splice($new_date, 0, 0, $datetime_arr);
                    $serial_count = $this->computeTime($new_arr);
                }else{
                    $serial_count=0;
                }
            }else{
                $new_arr =$datetime_arr;
                $serial_count = $this->computeTime($new_arr);
            }
        }else{
            $serial_count = 0;//连续打卡数
        }
        $join_count['day'] =0;
        if(!empty($rs['first_login']))$join_count = $this->computeDay(strtotime(date('Y-m-d',$rs['first_login'])),strtotime(date('Y-m-d',strtotime('tomorrow'))));

        $total_count = M('sign')->where('open_id = '.$rs['id'].' AND status =1')->count();//累计打卡数
        $join_count =$join_count['day'];//加入打卡数
        $data =[
            'is_sign'=>$is_sign,
            'sign_datetime'=>$sign_datetime,
            'serial_count'=>$serial_count,
            'total_count'=>$total_count,
            'join_count'=>$join_count,
        ];
        $this->put($data);
    }

    /**
     * @apiName 提交早餐签到数据
     *
     * @apiGetParam {string} token: 通信令牌
     * @apiPostParam {int} pic_id: 图片ID
     * @apiPostParam {string} title: 内容
     * @apiPostParam {int} rotate: 图片转换角度(0-不变，1-90 2-180 3-270 )
     *
     *
     * @apiSuccessResponse
     * {
     *	 "status": 状态码,
     *	 "info": "签到成功"
     * }
     *
     * @apiErrorResponse
     * {
     *	 "status": 状态码,
     *	 "info": "签到成功"
     * }
     */
    public function PostSign()
    {
        $id = session('wxUser.id');
        $rotate = I('post.rotate',0);
        $pic_id = I('post.pic_id');
        $title = I('post.title');
        if(empty($id))$this->error('非法提交');
        if(empty($title))$this->error('标题不能为空');
        if(empty($pic_id))$this->error('图片不能为空');
        $rs = M('Openid')->where(['id'=>$id])->find();

        $path = M('Pics')->where(['id'=>$pic_id])->getField('path');
        if(!empty($path)){
            if($rotate == 1 ){
                $path .= '?x-oss-process=image/rotate,90';
            }elseif($rotate == 2){
                $path .= '?x-oss-process=image/rotate,180';
            }elseif($rotate == 3){
                $path .= '?x-oss-process=image/rotate,270';
            }else{
                $path .= '?x-oss-process=image/rotate,0';
            }
        }
        $data = [
            'open_id'=> $rs['id'],
            'pic_id'=> $pic_id,
            'title'=> $title,
        ];
        $sign_id = M('Sign')->add($data);
        $_data = [
            'path'=>$path
        ];
        M('Pics')->where(['id'=>$pic_id])->data($_data)->save();
        //$count = M('Sign')->where(['open_id'=>$id])->count();
        if(empty($rs['first_login'])) {
            M('Openid')->where(['id' => $id])->save(['first_login' => time()]);
        }
        if($sign_id>0){
            $this->success('签到成功');
        }else{
            $this->error('签到不成功');
        }

    }

    /**
     * @apiName 删除某一天签到数据
     *
     * @apiGetParam {string} token: 通信令牌
     * @apiPostParam {string} sign_id: 分享早餐签到的ID
     *
     *
     * @apiSuccessResponse
     * {
     *	 "status": 状态码,
     *	 "info": "删除成功"
     * }
     *
     * @apiErrorResponse
     * {
     *	 "status": 状态码,
     *	 "info": "删除失败"
     * }
     *
     */
    public function DelSign(){
        $sign_id = I('post.sign_id');
        if(empty($sign_id)) $this->error('不存在该签到记录');
        if(session('?wxUser')){
            $open_id = session('wxUser.id');
            $num =  M('Sign')->where(['id'=>$sign_id,'open_id'=>$open_id])->data(['status'=>2])->save();
            if($num>0){
                $this->success('删除成功');
            }else{
                $this->error('删除失败');
            }
        }else{
            $this->error('没有登录,无法访问接口');

        }
    }

    /**
     * @apiName 分享早餐签到数据
     *
     * @apiGetParam {string} token: 通信令牌
     * @apiPostParam {string} sign_id: 分享早餐签到的ID
     *
     *
     * @apiSuccessResponse
     * {
     *     "sign": {
     *         "sign_id": "1",
     *         "open_id": "294920",
     *         "title": "早餐会,早上好，今天的早餐是：三文治",
     *         "pic_id": "1000668227",
     *         "datetime": "2017-02-27 15:16:29",
     *         "sign_path": "http://img.m.yami.ren/20170205/d408feff71782ea72b1114c702e47efd1413ad95.jpg"
     *     },
     *     "open_member": {
     *         "id": "294920",
     *         "member_id": "278518",
     *         "openid": "oUX2WxOoQn7HihQD3C5Mf9la93lw",
     *         "type": "1",
     *         "nickname": "紫嫣",
     *         "sex": "2",
     *         "city_id": "228",
     *         "pic_id": "",
     *         "first_login": "1488510878",
     *         "datetime": "2017-02-28 11:32:41",
     *         "path": "",
     *         "city_name": "茂名",
     *         "head_path": "",
     *         "serial_count": "2"
     *     },
     *     "is_wxUser":"1"
     * }
     *
     */
    public function GetEnjoySign(){
        $sign_id = I('post.sign_id');
        $datetime = I('post.datetime','');
        $data= [];
        $data['is_wxUser'] =0;
        if(session('?wxUser')){
            $info = $this->wechat->getUserinfo(session('wxUser.openid'));//{subscribe,openid,nickname,sex,city,province,country,language,headimgurl,subscribe_time,[unionid]}
            if(!empty($info) && $info['subscribe']==1){
                $data['is_wxUser'] =1;
            }
        }
        if(empty($sign_id)){
            $data['status'] =0;
            $data['info'] ='非法访问';
            $this->put($data);
        }
        $rs = D('SignView')->where(['A.id'=>$sign_id,'status'=>1])->find();

        if(!empty($rs)){
            $open_member = D('OpenIdView')->where(['A.id'=>$rs['open_id']])->find();
            $rs_datetime = date('Y-m-d 23:59:59',strtotime($rs['datetime']));
            $Sql = 'A.open_id = '.$rs['open_id'].' and status=1 and A.datetime <= "'.$rs_datetime.'"';
            if(!empty($datetime)){
                $e_time = date('Y-m-d 23:59:59',$datetime);
                $Sql .= ' ADN datetime LT '.$e_time;
            }
            if(!empty($rs)){
                $rs['sign_path'] = thumb($rs['sign_path']);
            }
            if(!empty($open_member)){
                $open_member['head_path'] = thumb($open_member['path'],2);
            }
            $datetime_arr = D('SignView')->where($Sql)->order('A.datetime desc')->getField('datetime',true);
            $open_member['serial_count'] = $this->computeTime($datetime_arr);
            $rs['datetime'] = strtotime($rs['datetime']);
            $open_member['datetime'] = strtotime($open_member['datetime']);
            $data['sign'] = $rs;
            $data['open_member'] = $open_member;
            $this->put($data);
        }else{
            $data['status'] =0;
            $data['info'] ='不存在该签到';
            $this->put($data);
        }

//        $open_member = D('OpenIdView')->where(['A.id'=>$rs['open_id']])->find();
//        $Sql = 'A.open_id = '.$rs['open_id'].' and status = 1';
//        if(!empty($datetime)){
//            $e_time = date('Y-m-d 23:59:59',$datetime);
//            $Sql .= ' ADN datetime LT '.$e_time;
//        }
//        if(!empty($rs)){
//            $rs['sign_path'] = thumb($rs['sign_path']);
//        }
//        if(!empty($open_member)){
//            $open_member['head_path'] = thumb($open_member['path'],2);
//        }
//        $datetime_arr = D('SignView')->where($Sql)->order('A.datetime desc')->getField('datetime',true);
//        $open_member['serial_count'] = $this->computeTime($datetime_arr);
//        $rs['datetime'] = strtotime($rs['datetime']);
//        $open_member['datetime'] = strtotime($open_member['datetime']);
//        $data = [
//            'sign' => $rs,
//            'open_member' => $open_member,
//        ];
//        $this->put($data);
    }

    /*计算签到时间连续多少天*/
    //$datetime 数组
    public function computeTime($datetime)
    {
        $is_lx = 1; //默认数组是连续的
        foreach($datetime as $k=>$v){
            if($k>0){ //从第二条开始记录判断
                if(strtotime(date('Y-m-d',strtotime($datetime[$k-1])))-86400 !== strtotime(date('Y-m-d',strtotime($v)))){ //如果前一个日期加一天不等于当前日期
                    break;
                }else{
                    $is_lx++;
                }
            }
        }
        return $is_lx;
    }

    /*计算两个时间相差多少天*/
    //$begin_time 开始时间（时间戳）
    //$end_time 结束时间（时间戳）
    public  function computeDay($begin_time,$end_time)
    {
        if($begin_time < $end_time){
            $starttime = $begin_time;
            $endtime = $end_time;
        }else{
            $starttime = $end_time;
            $endtime = $begin_time;
        }
        //计算天数
        $timediff = $endtime-$starttime;
        $days = intval($timediff/86400);
        //计算小时数
        $remain = $timediff%86400;
        $hours = intval($remain/3600);
        //计算分钟数
        $remain = $remain%3600;
        $mins = intval($remain/60);
        //计算秒数
        $secs = $remain%60;
        $res = ["day" => $days,"hour" => $hours,"min" => $mins,"sec" => $secs];
        return $res;
    }

    /**
     * @apiName 上传图片到我的图库
     *
     * @apiGetParam {string} token: 通信令牌
     *
     * @apiPostParam {int} file: 图片编码数组
     * @apiPostParam {int} pic_id: 图片ID
     * @apiPostParam {string} size: 压缩尺寸(例如:640x420)
     *
     * @apiSuccessResponse
     * {
     *     "info": {
     * 			"pic_id" : 111,
     * 			"path" : "http://img.m.yami.ren/member/2016-01-06/5MDNmODEzNzY5N2M1M2ZkNmE1YjRjY_640x420.jpg"
     * 	   },
     *     "status": 1,
     *     "url": ""
     * }
     */
    Public function upload(){
        $files = $_POST['file'];
        $pic_id = I('post.pic_id', null);
        $size = I('post.size', null);
        if(empty($files))$this->error('检测不到要上传的图片!');
        if(!empty($pic_id) && !empty($size)){
            $rs = M('pics')->where(['id' => $pic_id])->find();
            if(empty($rs))$this->error('pic_id所对应的图片不存在!');
            $size = explode('x', $size);
            $size = [(int)$size[0], (int)$size[1]];
            $_size = json_decode($rs['size'], true);
            if(in_array($size, $_size))$this->error('缩略图已存在!');
        }else{
            $size = [];
        }

        $conf = C('UPLOAD_CONFIG');

        try {
            $ossClient = new \OSS\OssClient($conf['accessKeyId'], $conf['accessKeySecret'], $conf['endpoint']);
        } catch (\OSS\OssException $e) {
            \Think\Log::write($e->getMessage());
            return false;
        }

        $return = [];
        $date = date($conf['subName']);
        $bucket = substr(DOMAIN, 0, 1) == 't' ? "yamiimg" : "yummyimg";
        if(strlen($files) > $conf['maxSize']){
            $return = [
                'status' => 0,
                'info' => '超过上传大小限制!'
            ];
        }
        //将base64解码
        $file = base64_decode($files);
        if(empty($pic_id)){
            $myDir = $date . '/';
            $filename = fileCrypt();
            $object = $myDir . $filename . $conf['ext'];
            try {
                $ossClient->putObject($bucket, $object, $file);
            } catch (\OSS\OssException $e) {
                $return[] = [
                    'status' => 0,
                    'info' => $e->getMessage()
                ];
            }
        }elseif(!empty($size)){
            $len = strrpos($rs['path'], '/') + 1;
            $myDir = substr($rs['path'], 0, $len);
            $filename = substr($rs['path'], $len, strrpos($rs['path'], '.') - $len);
            $object = $myDir . $filename . "_{$size[0]}x{$size[1]}" . $conf['ext'];
            try {
                $ossClient->putObject($bucket, $object, $file);
            } catch (\OSS\OssException $e) {
                $return = [
                    'status' => 0,
                    'info' => $e->getMessage()
                ];
            }
        }
        if(empty($pic_id)){
            $data = [
                'path' => $myDir . $filename . $conf['ext'],
                'size' => json_encode([$size])
            ];
            $id = M('pics')->add($data);
        }else{
            $_size[] = $size;
            M('pics')->where(['id' => $pic_id])->save(['size' => json_encode($_size)]);
        }
        $return = [
            'status' => 1,
            'info' => [
                'pic_id' => $pic_id?:$id,
                'path' => thumb($myDir . $filename . '.jpg', $size)
            ]
        ];
//        if(count($return) == 1){
//            $return = $return[0];
//        }
        $this->ajaxReturn($return);
    }


}
