<?php
namespace Home\Controller;
use Home\Common\MainController;

// @className 厨房场地
Class SpaceController extends MainController {

    /**
     * @apiName 获取厨房列表
     *
     * @apiGetParam {string} token: 通信令牌
     * @apiGetParam {int} page: 页数
     *
     * @apiPostParam {int} member_id: 筛选会员ID(当会员ID等于登录会员ID,则显示非公开部分)
     *
     * @apiSuccessResponse
     * [
     *     {
     *         "id": "19",
     *         "member_id": "",
     *         "name": "123", //厨房名称(简写地址)
     *         "introduction": "123123",
     *         "address": "艺苑路",
     *         "pic_group_id": "23971",
     *         "latitude": "23.1003",
     *         "longitude": "113.327",
     *         "facility": ["1","2","3","4","5","6","7","8"], //厨房设施 1.wifi 2.酒具 3.电视音响 4.餐具 5.空调 6.明火 7.开放式厨房 8.吸烟
     *         "proportion": "1000",
     *         "volume": "123",
     *         "opening_time": "0-0",
     *         "context": "111",
     *         "status": "1",
     *         "path": "http://img.m.yami.ren/20160607/7663987b2517958e0493b8d348b623b843d00e04.jpg",
     *         "area_id": "2094",
     *         "area_name": "海珠",
     *         "city_id": "224",
     *         "city_name": "广州",
     *         "province_id": "19",
     *         "province_name": "广东",
     *         "category_name": "中餐",
     *         "tag": ["中餐","西餐","日料"]
     *     },
     * ]
     */
    public function getList(){
        $member_id = I('post.member_id');

        $page = I('get.page',1);
        $pageSize = 5;

        $condition = ['status' => 1];

        if(!empty($member_id)){
            $condition['member_id'] = $member_id;
            if(session('?member') && $member_id == session('member.id'))
                $condition['status'] = ['in', '1,2'];
        }else{
            //城市筛选
            $area_id = session('city_id');
            if(!empty($area_id)){
                $citys = M('citys')->field(['id'])->where(['id|pid' => $area_id])->buildSql();
                $condition['area_id'] = ['exp', "in ({$citys})"];
            }
        }
        $rs = D('SpaceView')->where($condition)->page($page, $pageSize)->order('id desc')->select();
        foreach($rs as $row){
            $ids[] = $row['id'];
        }
        $data = [];
        if(!empty($ids)){
            $space_tag = M('SpaceTag')->field(['space_id', 'name'])->join('__TAG__ a on tag_id=a.id')->where(['space_id'=>['IN',join(',',$ids)]])->select();
            foreach($rs as $key => $row){
                $row['path'] = thumb($row['path'], 10);
                $row['facility'] = explode(',', $row['facility'])?:[];
                $row['tag'] = [];
                foreach($space_tag as $re){
                    if($row['id'] == $re['space_id']){
                        $row['tag'][] = $re['name'];
                    }
                }
                $data[] = $row;
            }
        }
        $this->put($data);
    }

    /**
     * @apiName 获取厨房详情
     *
     * @apiGetParam {string} token: 通信令牌
     * @apiPostParam {int} space_id: 厨房ID
     *
     * @apiSuccessResponse
     * {
     *     "id": "16",
     *     "member_id": "",
     *     "name": "吕苑", //厨房名称(简写地址)
     *     "introduction": "一座建于1933年的东山口老別墅，\n红色老砖墙，铺着具有时代感的花地砖，\n挑高的楼层，通透的对流沉淀了近百年的历史感。", //简介
     *     "address": "新河浦路新河浦路24号",
     *     "pic_group_id": "159",
     *     "latitude": "23.1181",
     *     "longitude": "113.296",
     *     "facility": ["4","5","6","7","8"], //厨房设施 1.wifi 2.酒具 3.电视音响 4.餐具 5.空调 6.明火 7.开放式厨房 8.吸烟
     *     "proportion": "23", //场地面积
     *     "volume": "12", //可容纳人数
     *     "opening_time": "68400-75600", //开放时间（距离周一00:00:00的总秒数，用-隔开开始和结束时间，多个时间段用逗号隔开）
     *     "context": "lalla", //备注
     *     "status": "1",
     *     "path": "http://img.m.yami.ren/20160531/2c98fcee88fcd2e12c091c758ec8940796aab55c.jpg",
     *     "area_id": "2093",
     *     "area_name": "越秀",
     *     "city_id": "224",
     *     "city_name": "广州",
     *     "province_id": "19",
     *     "province_name": "广东",
     *     "category_name": "中餐",
     *     "tags": ["日料","饭局","培训","茶室"],
     *     "server_time": [
     *         "周一 19:00~周一 21:00" //开放时间
     *     ],
     *     "group_path": [
     *         {
     *         "pic_id": "14526",
     *             "path": "http://img.m.yami.ren/20160531/a318cdff4c8ff9baf8ed67a2095903a55ce7f29b.jpg"
     *         },
     *         {
     *         "pic_id": "14527",
     *             "path": "http://img.m.yami.ren/20160531/d851e0b2ca4bc19e077fe7217ad87a8155f1241d.jpg"
     *         },
     *         {
     *         "pic_id": "14528",
     *             "path": "http://img.m.yami.ren/20160531/76f6d707b41f9bf52abae7b34fdd8d2f7a52c994.jpg"
     *         }
     *     ]
     * }
     */
    public function getDetail(){
        $space_id = I('post.space_id');

        if(empty($space_id))$this->ajaxReturn('非法访问');
        $data = D('SpaceView')->where(['id' => $space_id])->find();
        if(empty($data) || $data['status'] == 0){
            $this->error('场地不存在!');
        }elseif($data['status'] == 2 && (!session('?member') || session('member.id') != $data['member_id'])){
            $this->error('场地非公开,无法访问!');
        }

        //厨房标签
        $tags = $space_tag = M('SpaceTag')->join('__TAG__ a on tag_id=a.id')->where(['space_id'=>$space_id])->getField('name', true);
        $data['tags'] = $tags ?: [];

        //数据格式处理
        $data['path'] = thumb($data['path'], 10);

        //计算开放时间
        if(!empty($data['opening_time'])){
            $times = explode(',', $data['opening_time']);
            $date_array = [1=>'一',2=>'二',3=>'三',4=>'四',5=>'五',6=>'六',7=>'日'];
            foreach($times as $row3){
                $times_sub = explode('-',$row3);
                //本周一0点时间戳
                $re = getmonsun();
                $monday = $re['mon'];
                $data['server_time'][] = '周'.$date_array[date('N',$monday+$times_sub[0])].' '.date('H:i',$monday+$times_sub[0]) .'~'. '周'.$date_array[date('N',$monday+$times_sub[1])].' '.date('H:i',$monday+$times_sub[1]);
            }
        }

        //厨房图组
        if(!empty($data['pic_group_id'])){
            $group_path = M('pics')->field(['id', 'path'])->where(['group_id' => $data['pic_group_id']])->select();
            $data['group_path'] = [];
            foreach($group_path as $key => $row){
                $data['group_path'][] = [
                    'pic_id' => $row['id'],
                    'path' => thumb($row['path'],10)
                ];
            }
        }

        //设备
        $data['facility'] = explode(',', $data['facility']);

        $this->put($data);
    }

    /**
     * @apiName 场地询价
     *
     * @apiGetParam {string} token: 通信令牌
     * @apiPostParam {int} space_id: 场地ID
     * @apiPostParam {int} telephone: 联系电话
     * @apiPostParam {string} aim: 活动目的
     * @apiPostParam {int} month: 月份
     * @apiPostParam {int} day: 日期
     * @apiPostParam {int} time: 时间段(0-全天 1-上午 2-下午 3-晚上)
     * @apiPostParam {int} num: 预估参与人数
     * @apiPostParam {int} budget: 预算
     * @apiPostParam {string} contacts: 联系人
     * @apiPostParam {string} context: 留言
     *
     * @apiSuccessResponse
     * [
     *  status:1,
     *  info:'申请已提交，请耐心等候回复'
     * ]
     */
    public function query(){
        if(!session('?member'))$this->error('没有登录!');
        $data = I('post.');
        if(empty($data))$this->error('资料填写不完整');
        if(!is_numeric($data['telephone']) || strlen($data['telephone'])>15)$this->error('非法电话号码');
        if(empty($data['aim']))$this->error('活动目的未填写');
        if(empty($data['month']) || empty($data['day']) || !isset($data['time']))$this->error('预约时间未填写');
        if(empty($data['num']))$this->error('参加人数未填写');
        if(empty($data['contacts']))$this->error('联系人未填写');

        $data['budget'] = empty($data['budget'])?0:$data['budget'];
        $re = M('SpaceApply')->where(['space_id'=>$data['space_id'],'telephone'=>$data['telephone'],'month'=>$data['month'],'day'=>$data['day'],'time'=>$data['time']])->find();
        if(!empty($re))$this->error('请勿重复申请');
        M('SpaceApply')->data($data)->add();

        if(session('member.id')){
            //发送信息
            $space_re = M('Space')->where(['id'=>$data['space_id']])->find();
            $city_id = M('Citys')->where(['id'=>$space_re['city_id']])->getField('pid');
            $charge = C('SPACE_CHARGE');
            if(in_array($city_id,array_keys($charge))){
                $telephone = $charge[$city_id];
//                $message = session('member.nickname').' 提交了 “厨房+” 【'.$space_re['name'].'】的询价';
//                sms_send($telephone, $message);

                //2016-12-27
                $params=array(
                    'nickname'=>session('member.nickname'),
                    'space_name'=>$space_re['name'],
                );
                smsSend($telephone,'SMS_35990158', $params);//厨房+的询价短信模板
            }
            //发送站内消息
            $message = '您的询价已反馈成功，我们会尽快联系您，若您有什么问题，可以直接联系吖咪厨房+负责人电话--广州：18672366543 ';
            $this->push_Message(session('member.id'),array(),null,null, $message);
        }
        $this->success('申请已提交，请耐心等候回复');
    }

    /**
     * @apiName 保存活动场地
     *
     * @apiGetParam {string} token: 通信令牌
     *
     * @apiPostParam {int} space_id: 要编辑的场地ID(不填则为新增)
     * @apiPostParam {int} area_id: 区ID
     * @apiPostParam {int} longitude: 经度坐标
     * @apiPostParam {int} latitude: 纬度坐标
     * @apiPostParam {string} address: 详细地址
     * @apiPostParam {string} name: 简要地址
     * @apiPostParam {string} pics: 图片ID(多个ID用逗号隔开)
     *
     * @apiSuccessResponse
     * {
     *     "info": "保存成功！",
     *     "status": 1,
     *     "url": ""
     * }
     * @apiErrorResponse
     * {
     *     "info": "失败原因",
     *     "status": 0,
     *     "url": ""
     * }
     */
    Public function save(){
        \Think\Log::write('微信授权234：'.json_encode(session('member')));
        if(!session('?member') || !in_array(18, session('member.tags')))$this->error('没有登录或不是HOST,无法操作!');

        $member_id = session('member.id');
        $space_id = I('post.space_id');
        $address = I('post.address');
        $name = I('post.name');
        $longitude = I('post.longitude');
        $latitude = I('post.latitude');
        $area_id = I('post.area_id');
        $pics = I('post.pics');

        $member = M('Member')->join('__MEMBER_INFO__ as B ON B.member_id = __MEMBER__.id')->where(['id'=>$member_id])->find();
        //判断是否有space_id,没有则添加,有则修改
        if(empty($space_id)){
            $data = [
                'name' => $name,
                'nickname' => $member['nickname'],
                'telephone' => $member['telephone'],
                'weixincode' => $member['weixincode'],
                'address' => $address,
                'member_id' => $member_id,
                'longitude' => $longitude,
                'latitude' => $latitude,
                'city_id' => $area_id,
                'pic_id' => null,
                'pic_group_id' => null,
                'status' => 2
            ];
            if(!empty($pics)){
                $data['pic_id'] = explode(',', $pics)[0];
                $data['pic_group_id'] = M('pics_group')->add(['type' => 0]);
                M('pics')->where(['id' => ['IN', $pics], 'member_id' => $member_id])->save(['group_id' => $data['pic_group_id']]);
            }
            foreach($data as $key => $val){
                if(empty($val) && $key != 'weixincode'){
                    $this->error($key . ' no value!');
                }
            }
            M('space')->add($data);
        }else{
            $rs = M('space')->where(['id' => $space_id, 'member_id' => $member_id, 'status' => ['in', '1,2']])->find();
            if(empty($rs))$this->error('找不到指定的场地!');
            $data = [
                'name' => $name,
                'address' => $address,
                'member_id' => $member_id,
                'nickname' => $member['nickname'],
                'telephone' => $member['telephone'],
                'weixincode' => $member['weixincode'],
                'longitude' => $longitude,
                'latitude' => $latitude,
                'city_id' => $area_id,
                'pic_id' => null,
                'pic_group_id' => null
            ];
            if(!empty($pics)){
                $data['pic_id'] = explode(',', $pics)[0];
                //判断是否有原先的图组
                if(!empty($rs['pic_group_id'])){
                    $data['pic_group_id'] = $rs['pic_group_id'];
                    M('pics')->where(['group_id' => $rs['pic_group_id']])->save(['group_id' => ['exp','null'], 'is_used' => 0]);
                }else{
                    //添加新的图组
                    $data['pic_group_id'] = M('PicsGroup')->add([
                        'type' => 0
                    ]);
                }
                M('pics')->where(['id' => ['IN', $pics], 'member_id' => $member_id])->save(['group_id' => $data['pic_group_id'], 'is_used' => 1]);
            }
            foreach($data as $key => $val){
                if(empty($val) && $key != 'weixincode'){
                    $this->error($key . ' no value!');
                }
            }
            M('space')->where(['id' => $space_id])->save($data);
        }

        $this->success('保存成功!');
    }

    /**
     * @apiName 删除活动场地
     *
     * @apiGetParam {string} token: 通信令牌
     *
     * @apiPostParam {int} space_id: 要删除的场地ID
     *
     * @apiSuccessResponse
     * {
     *     "info": "场地删除成功！",
     *     "status": 1,
     *     "url": ""
     * }
     * @apiErrorResponse
     * {
     *     "info": "失败原因",
     *     "status": 0,
     *     "url": ""
     * }
     */
    Public function remove(){
        if(!session('?member') || !in_array(18, session('member.tags')))$this->error('没有登录或不是HOST,无法操作!');
        $member_id = session('member.id');
        $space_id = I('post.space_id');
        $rs = M('space')->where(['id' => $space_id, 'member_id' => $member_id, 'status' => ['in', '1,2']])->find();
        if(empty($rs))$this->error('找不到指定的场地!');
        M('space')->where(['id' => $space_id])->save(['status' => 0]);
        $this->success('场地删除成功!');
    }

}