<?php
namespace Admin\Controller;
use Admin\Controller\MainController;

class MemberController extends MainController {
	
	Protected $pagename = '会员管理';
	
	/**
	 * 首页
	 */
	Public function index(){
		$this->actname = '会员列表';
        $condition = '';
        if(IS_GET && $_GET!=null){
            $member_telephone = I('get.telephone');
            $member_nickname = I('get.member');
            $member_status = I('get.status');
            $start = I('get.start_time');
            $end = I('get.stop_time');
            $start_time = strtotime(I('get.start_time'));
            $stop_time = strtotime(I('get.stop_time'));
            
            $member_nickname && $condition['nickname'] = array('LIKE','%'.$member_nickname.'%');
            $member_telephone && $condition['telephone'] = array('LIKE','%'.$member_telephone.'%');


            if($start && $end){
                $condition['register_time'] = array('BETWEEN',"$start_time,$stop_time");
            }else{
                $start && $condition['register_time'] = array('GT',$start_time);
                $end && $condition['register_time'] = array('LT',$stop_time);
            }
            /*$start_time && $condition['register_time'] = array('GT',$start_time);
            $stop_time && $condition['register_time'] = array('LT',$stop_time);*/

            $member_status!='' && $condition['status'] = array('EQ',$member_status);
            $this->assign('search_member',$member_nickname);
            $this->assign('search_telephone',$member_telephone);
            $this->assign('search_status',$member_status);
            $this->assign('search_start_time',I('get.start_time'));
            $this->assign('search_stop_time',I('get.stop_time'));
        }

        if(IS_AJAX && IS_POST){
            if($_POST['functiontype'] == 'add_user'){
                $nickname = I('post.nickname');
                $telephone = I('post.telephone');
                $citys_id = I('post.citys_id');
                $data = array(
                    'nickname'=>$nickname,
                    'telephone'=>$telephone,
                    'username'=>$telephone,
                    'citys_id'=>$citys_id,
                    'invitecode' => createCode(32, false),
                    'register_time'=>time(),
                );
               $member_id =  $this->m2('member')->add($data);
               $member_info_id = $this->m2('member_info')->add(['member_id'=>$member_id,'citys_id'=>$citys_id]);
                if($member_id && $member_info_id) {
                    $this->success('添加会员成功');
                }else{
                    $this->success('添加会员失败');
                }

            }
        }

        //读取城市筛选列表
        $citys = C('CITY_CONFIG');
        $this->assign('citys',$citys);
		$admin = D('MemberView');
		$datas['datas'] = $admin->where($condition)->page(I('get.page'), 20)->order('id desc')->select();
        //数据处理
        foreach($datas['datas'] as $key=>$row){
            if($row['status'] == 1)$datas['datas'][$key]['status'] = '已启用';
            if($row['status'] == 2)$datas['datas'][$key]['status'] = '已禁用';
            if($row['status'] == 0)$datas['datas'][$key]['status'] = '已删除';
            $datas['datas'][$key]['register_time'] = date('Y-m-d H:i',$row['register_time']);
            $datas['datas'][$key]['login_times'] = $this->m2('member_login_log')->where('member_id='.$row['id'])->count();
            $datas['datas'][$key]['order_times'] = $this->m2('order')->where('member_id='.$row['id'])->count();
            //图片路径处理
            if(substr($row['path'],0,1)=='/'){
                $path = substr($row['path'],1);
            }else{
                $path = $row['path'];
            }

            if(substr($row['path'],0,7)=='http://'){
                $datas['datas'][$key]['path'] = '<img src ="'. $path .'" width="50" />';
            }elseif(strpos($path,'uploads/') !== fALSE){
                $datas['datas'][$key]['path'] = '<img src="http://yummy194.cn/'. $path . '"width="50" />';
            }else{
                $datas['datas'][$key]['path'] = '<img src="http://img.'. WEB_DOMAIN .'/'. $path . '"width="50" />';
            }

            foreach($citys as $citykey=>$cityvalue) {
                if($row['citys_id'] == $citykey) {
                    $datas['datas'][$key]['citys_name'] = $cityvalue;
                }
            }
        }

        //添加达人标签
		$datas['operations'] = [
            '修改密码' => "resetpass(%id)",
            '标签修改' => "member_tags(%id)",
            '加入白名单' => 'addWhiteList(%id)',
            '修改手机号码' => 'UpdatePhone(%id)',
            '修改头像' => 'UpdateHeadPic(%id)',
            '修改昵称' => 'UpdateNickname(%id)',
            '禁用' => [
                'style' => 'danger',
                'fun' => 'disable(%id)',
                'condition' => "%status == '已启用'"
            ],
            '恢复' => [
                'style' => 'success',
                'fun' => 'able(%id)',
                'condition' => "%status == '已禁用'"
            ],
        ];
		$datas['pages'] = [
            'sum' => $admin->where($condition)->count(),
            'count' => 20
        ];
        $datas['batch'] = [
            '推送信息' => "PushMessage()",
        ];
		$datas['lang'] = [
            'id' => '会员ID',
            'nickname' => '会员名称',
            'telephone' => '手机号',
            //'path' => array('头像', '<img src="http://yummy194.cn/%*%" width="50" />'),
            'path' =>'头像',
            'invitecode' =>'邀请码',
            'login_times' => '登录次数',
            'order_times' => '购买次数',
            'citys_name' => '城市',
            'status' => '状态',
            'register_time' => '注册时间'
        ];

		$this->assign($datas);
		$this->view();
	}

    //修改会员手机号码
    public function UpdatePhone(){
        if(IS_AJAX && IS_POST){
            if($_POST['typeName']=='getPhone' && !empty($_POST['member_id'])){
                $member_id = I('post.member_id');
                $rs = $this->m2('member')->field('telephone')->where(['id'=>$member_id])->select();
                $this->ajaxReturn($rs);
            }
            $member_id = I('post.member_id');
            $telephone = I('post.telephone');
            $rs = $this->m2('member')->where(['id'=>$member_id])->save(['telephone'=>$telephone]);
            if($rs){
                $this->success('修改手机号成功！');
            }else{
                $this->error('修改手机号失败！');
            }
        }

    }

    //修改会员昵称
    public function UpdateNickname(){
        if(IS_AJAX && IS_POST){
            if($_POST['typeName']=='getNickname' && !empty($_POST['member_id'])){
                $member_id = I('post.member_id');
                $rs = $this->m2('member')->where(['id'=>$member_id])->getField('nickname');
                $this->ajaxReturn($rs);
            }
            $member_id = I('post.member_id');
            $nickname = $_POST['nickname'];
            $rs = $this->m2('member')->where(['id'=>$member_id])->save(['nickname'=>$nickname]);
            if($rs){
                $this->success('修改昵称成功！');
            }else{
                $this->error('修改昵称失败！');
            }
        }

    }

    //会员标签查改
    function getMemberTags(){
        if(IS_AJAX && I('post.member_id')==''){
            $id = I('post.id');
            $member_tags = $this->m2('tag')->where('type=0 and official=0')->select();
            $official_member_tags = $this->m2('tag')->where('type=0 and official=1')->select();
            $my_tags = $this->m2('member_tag')->join('__TAG__ ON ym_member_tag.tag_id = ym_tag.id')->where('member_id='.$id)->select();
            $label = array();
            $official_label = array();
            foreach($my_tags as $row){
                if($row['official']==0){
                    $label[] = $row['tag_id'];
                }else{
                    $official_label[] = $row['tag_id'];
                }
            }
            $data['member_tags'] = $member_tags;
            $data['official_member_tags'] = $official_member_tags;
            $data['my_label'] = $label;
            $data['my_official'] = $official_label;

            $this->ajaxReturn($data);
            exit;
        }
        if(IS_AJAX){
            $id = I('post.member_id');
            $official_member_ids = I('post.official_tag_ids');
            $tag_ids = I('post.tag_ids');
            $this->m2('member_tag')->where('member_id='.$id .' and tag_id <> 18 ')->delete();
            foreach($official_member_ids as $row){
                $data = array();
                $data['member_id'] = $id;
                $data['tag_id'] = $row;
                $this->m2('member_tag')->data($data)->add();
            }
            foreach($tag_ids as $row){
                $data = array();
                $data['member_id'] = $id;
                $data['tag_id'] = $row;
                $this->m2('member_tag')->data($data)->add();
            }
            $this->success('修改成功');
        }
    }

	/** 管理员添加*/
	Public function add(){
		if(IS_AJAX){
			$data['username'] =$_POST['username'];
			$data['password'] = I('post.password');
			$data['type'] = I('post.type');
			
			$user = D('user');
			if(!$user->create($data)){
				$this->error($user->getError());
			}
			$user->add();
			$this->success('添加成功！');
			exit;
		}
		$this->error('非法访问！');
	}
	
	/**修改密码*/
	Public function updatepsw(){
		if(IS_AJAX){
			$data['password'] = md5(I('post.password'));
			$data['id'] = I('post.id');
            $this->m2('member')->save($data);
			$this->success('修改成功！');
			exit;
		}
		$this->error('非法访问！');
	}

    //选择会员iframe页面
    Public function getUser(){
        layout(false);
        $condition = '';
        if(IS_GET && $_GET!=null){

            $member_nickname = I('get.member');
            $member_nickname && $condition['nickname'] = array('LIKE','%'.$member_nickname.'%');
            $this->assign('search_member',$member_nickname);
        }

        $admin = D('MemberView');
        $datas['datas'] = $admin->where($condition)->page(I('get.page'), 20)->select();


        $datas['operations'] = array(
            '选择用户' => "selectOne(%id)"
        );
        $datas['pages'] = array(
            'sum' => $admin->where($condition)->count(),
            'count' => 20
        );

        $datas['lang'] = array(
            'id' => '会员ID',
            'nickname' => '会员名称',
            'telephone' => '手机号',
            'path' => array('头像', '<img src="http://yummy194.cn/%*%" width="50" />'),
            'datetime' => '注册时间'
        );

        $this->assign($datas);
        $this->view();
    }

    public function changeStatus(){
        if(IS_AJAX){
            $id = I('post.id');
            $status = I('post.status');

            $data = [];
            $data['id'] = $id;
            $data['status'] = $status;
            $this->m2('member')->data($data)->save();
            $this->success('设置成功');
        }
    }

    //会员登录白名单
    public function whitelist(){
        $this->actname = '白名单列表';
        if(IS_AJAX){
            $action = I('post.action');
            $id = I('post.id');
            if($action == 'add'){
                $this->m2('MemberInfo')->where(['member_id' => $id])->save(['is_white' => 1]);
                $this->success('加入白名单成功!');
            }else{
                $this->m2('MemberInfo')->where(['member_id' => $id])->save(['is_white' => 0]);
                $this->success('移除白名单成功!');
            }
            exit;
        }
        $rs = $this->m2('MemberInfo')->join('__MEMBER__ on id=member_id')->where(['is_white' => 1])->select();

        $datas['datas'] = [];
        foreach($rs as $row){
            $datas['datas'][] = [
                'id' => $row['id'],
                'nickname' => $row['nickname'],
                'telephone' => $row['telephone']
            ];
        }

        $datas['operations'] = [
            '移出白名单' => "moveout(%id, this)"
        ];

        $datas['lang'] = [
            'id' => '会员ID',
            'nickname' => '会员名称',
            'telephone' => '手机号'
        ];

        $this->assign($datas);
        $this->view();
    }

    //Host管理列表
    public function memberhost(){
        $this->actname = 'Host管理列表';

        if(IS_AJAX && IS_POST) {
            if(!empty($_POST['search_key'])) {
                $search_key = I('post.search_key');
                $condition = array();

                if (isset($search_key) && $search_key != '') {
                    $condition = ' nickname LIKE ' . "'%$search_key%'";
                    $member_rs = $this->m2('member')->field('id,telephone,nickname')->where($condition)->limit(20)->select();
                    $this->ajaxReturn($member_rs);
                }
            }elseif($_POST['typeName'] =='checkmember_id'){
                $member_id = I('post.member_id');
                $rs = $this->m2('member_tag')->where(['member_id'=>$member_id,'tag_id'=>18])->find();
                if(!empty($rs)){
                    $this->success('该会员已成为Host');
                }else{
                    $this->error('该会员未成为Host');

                }
                exit;
            }
        }


        $pageSize = 20;
        //条件查询
        $condition = " E.tag_id =18";
        $member_nickname = I('get.member');
        $member_telephone = I('get.telephone');
        if(isset($_GET['member_id']) && is_numeric($_GET['member_id'])) {
            $condition .= ' and A.id = ' . $_GET['member_id'];
        }
        $member_telephone && $condition .=' and A.telephone LIKE "%'.$member_telephone.'%"';
        $member_nickname && $condition .=' and A.nickname LIKE "%'.$member_nickname.'%" ';
        $this->assign('search_member',$member_nickname);
        $this->assign('search_telephone',$member_telephone);
        $this->assign('search_member_id',$_GET['member_id']);
        $citys = C('CITY_CONFIG');
        $datas['datas']= D('MemberInfoHostView')->where($condition)->page(I('get.page'), $pageSize)->order('A.id desc')->select();

        //数据&图片路径处理
        foreach($datas['datas'] as $key=>$row){
            $datas['datas'][$key]['tutor_pic'] = "<img src='".pathFormat($row['path'])."' data='pic_id' width='50px' height='50px'/>";
            foreach($citys as $citykey=>$cityvalue) {
                if($row['citys_id'] == $citykey) {
                    $datas['datas'][$key]['citys_name'] = $cityvalue;
                }
            }
        }

        $datas['operations'] = [
            '修改信息' => "detailInfo(%id)",
            '修改头像' => "UpdateHeadPic(%id)",
            '修改背景' => "updateBg(%id)",
            '查看简介' => "check_synopsis(%id)",
            '查看支付账号' => "HostPay(%id)",
            '审核' => [
                'condition' => '%is_pass == 0',
                'style' => 'danger',
                'fun' => 'Auditing(%id)'
            ]
        ];
        $datas['pages'] = [
            'sum' => D('MemberInfoHostView')->where($condition)->count(),
            'count' => $pageSize
        ];
        $datas['lang'] = [
            'id' => '用户ID',
            'nickname' => '昵称',
            'telephone' => '登录手机号',
            'contact' => '联系用户电话',
            'citys_name' => '城市',
            'tutor_pic' => '头像'
        ];
        //统计host
        $daren_count = D('MemberInfoHostView')->where($condition)->count();
        $this->assign('daren_count',$daren_count);
        //读取银行卡列表
        $banks = $this->m2('bank')->select();
        $this->assign('banks', $banks);

        $this->assign($datas);
        $this->view();
    }

    //添加Host
    public function AddHost(){
        if(IS_AJAX && IS_POST) {
            $member_id = I('post.add_member_id');
            $identity = I('post.add_identity');
            $realname =I('post.add_realname');
            $contact = I('post.add_contact');
            $sex =I('post.add_sex');
            $number = I('post.add_number');
            $bank_id = I('post.add_bank_id');
            //$health_pic_id = I('post.health_pic_id');
            $rs = $this->m2('member_tag')->where(['member_id'=>$member_id,'tag_id'=>18])->find();
//            if(!preg_match('/^(\d{15}$|^\d{18}$|^\d{17}(\d|X))$/', $identity)){
//                $this->error('身份证号码格式不正确!');
//            }
            if(empty($identity)){
                $identity ='0000000000000000000';
            }
            if(!empty($rs)){
                $this->success('该会员已成为Host');
            }else{
                $rss = $this->m2('MemberInfo')->where(['member_id' => $member_id])->find();
                $Bank = $this->m2('MemberBank')->where(['member_id' => $member_id])->find();
                if(empty($Bank)){
                    $memberbank = $this->m2('MemberBank')->add([
                        'member_id' => $member_id,
                        'bank_id' => $bank_id,
                        'name' => $realname,
                        'number' => $number,
                        'status' => 1
                    ]);

                }else{
                    $memberbank = $this->m2('MemberBank')->where(['member_id' => $member_id])->save([
                        'bank_id' => $bank_id,
                        'name' => $realname,
                        'number' => $number,
                        'status' => 1
                    ]);
                }
                $membertag = $this->m2('MemberTag')->add([
                    'member_id' => $member_id,
                    'tag_id' => 18
                ]);
//                $memberbank = $this->m2('MemberBank')->add([
//                    'member_id' => $member_id,
//                    'bank_id' => $bank_id,
//                    'name' => $realname,
//                    'number' => $number,
//                    'status' => 1
//                ]);
                if(!empty($rss)) {
                    $memberinfo = $this->m2('MemberInfo')->where(['member_id' => $member_id])->save([
                        'identity' => $identity,
                        'contact' => $contact,
                        'sex' => $sex,
                        //'health_pic_id' => $health_pic_id
                    ]);
                }else{
                    $memberinfo = $this->m2('MemberInfo')->add([
                        'member_id' =>$member_id,
                        'identity' => $identity,
                        'contact' => $contact,
                        //'health_pic_id' => $health_pic_id,
                        'sex' => $sex,
                        'is_white'=>0
                    ]);

                }
                if(!empty($membertag) && $memberbank !==false && $memberbank !==0  && $memberinfo !==false && $memberinfo !==0 ){
                    $this->success('会员添加成为host成功');

                }else {
                    $this->error('会员添加成为host失败');
                }
            }
        }

    }

    //获取Host信息
    public function getHostInfo(){
        if(IS_AJAX) {
            $member_id = I('post.member_id');
            $rs = D('MemberInfoHostView')->where(['A.id' => $member_id,'tag_id'=>18])->find();
            $rsbank = D('MemberHostBankView')->where(['member_id'=>$rs['id']])->find();
            $data1['id'] = $rs['id'];
            $data1['nickname'] = $rs['nickname'];
            $data1['identity'] = $rs['identity'];
            $data1['sex'] = $rs['sex'];
            $data1['telephone'] = $rs['telephone'];
            $data1['contact'] = $rs['contact'];
            $data1['realname'] = $rsbank['realname'];
            $data1['bank_number'] = $rsbank['bank_number'];
            $data1['bank_name'] = $rsbank['bank_name'];
            $data1['bank_id'] = $rsbank['bank_id'];
            $data1['health_pic'] = pathFormat($rs['path']);
            $data1['health_pic_id'] = $rs['health_pic_id'];
            $this->ajaxReturn($data1);
        }

    }

    //审核实名认证
    public function host_auditing(){
        if(IS_AJAX) {
            $id = I('post.id');
            $rs = $this->m2('member_info')->where(['member_id' => $id])->find();
           if(!empty($rs)){
               $this->m2('member_info')->where(['member_id'=>$id])->save(['is_pass'=>1]);
               $this->success('审核通过');
           }else{
               $this->success('非法操作');
           }
        }

    }

    //修改Host信息
    public function HostUpdate(){
        if(IS_AJAX && IS_POST) {
            $member_id = I('post.member_id');
            $identity = I('post.identity');
            $realname =$_POST['realname'];
            $contact = I('post.contact');
            $sex =I('post.sex');
            $number = I('post.number');
            $bank_id = I('post.bank_id');
            $rs = D('MemberInfoHostView')->where(['A.id' => $member_id,'tag_id'=>18])->find();
            if (  !empty($realname) && !empty($contact) && $sex !=null && $sex !='' && !empty($number) && !empty($bank_id)) {
//                if (!preg_match('/^(\d{15}$|^\d{18}$|^\d{17}(\d|X))$/', $identity)) {
//                    $this->error('身份证号码格式不正确!');
//                    exit;
//                }
                if(empty($identity)){
                    $identity ='0000000000000000000';
                }
                if (empty($rs)) {
                    $this->m2('MemberBank')->add([
                        'member_id' => $member_id,
                        'bank_id' => $bank_id,
                        'name' => $realname,
                        'number' => $number
                    ]);
                    $this->m2('MemberInfo')->where(['member_id' => $member_id])->save([
                        'identity' => $identity,
                        'contact' => $contact,
                        'sex' => $sex
                    ]);
                } else {
                    $member_info = array(
                        'identity' => $identity,
                        'contact' => $contact,
                        'sex' => $sex,

                    );
                    $member_bank = array(
                        'number' => $number,
                        'name' => $realname,
                        'bank_id' => $bank_id
                    );
                    $Add_member_bank = array(
                        'member_id'=>$member_id,
                        'number' => $number,
                        'name' => $realname,
                        'bank_id' => $bank_id
                    );
                    $bank_member = $this->m2('MemberBank')->where(['member_id' => $member_id])->find();
                    if(!empty($bank_member)){
                        $this->m2('MemberBank')->data($member_bank)->where(['member_id' => $member_id])->save();
                    }else{
                        $this->m2('MemberBank')->data($Add_member_bank)->add();
                    }
                    //member_info数据保存
                    $this->m2('MemberInfo')->data($member_info)->where(['member_id' => $member_id])->save();
                    //MemberBank
                }
                $this->success('修改成功');
            }
        }

    }

    //修改Host头像
    public function UpdateHostTutor(){
        if(IS_AJAX && IS_POST) {
            if($_POST['typeName']=='get_tutorImg' && !empty($_POST['member_id'])){
                $member_id = $_POST['member_id'];
                $member_pic = D('MemberView')->field('pic_id,path')->where(['id'=>$member_id])->find();
                $member_pic['path'] =thumb($member_pic['path']);
                $this->ajaxReturn($member_pic);
            }elseif($_POST['typeName']=='updatetutor' && !empty($_POST['member_id'])){
                $member_id = $_POST['member_id'];
                $pic_id = $_POST['pic_id'];
                $member_pic = $this->m2('Member')->where(['id'=>$member_id])->save(['pic_id'=>$pic_id]);
                if($member_pic>0 || $member_pic !==0 ){
                    $this->success('修改头像成功');
                }else{
                    $this->error('修改头像失败');

                }
            }
        }
    }

    //修改头像
    public function UpdateHeadPic(){
        if(IS_AJAX && IS_POST) {
            if($_POST['typeName']=='getHeadPic' && !empty($_POST['member_id'])){
                $member_id = $_POST['member_id'];
                $member_pic = D('MemberView')->field('pic_id,path')->where(['id'=>$member_id])->find();
                $member_pic['path'] =thumb($member_pic['path']);
                $this->ajaxReturn($member_pic);
            }elseif($_POST['typeName']=='PostHeadPic' && !empty($_POST['member_id'])){
                $member_id = $_POST['member_id'];
                $pic_id = $_POST['pic_id'];
                $member_pic = $this->m2('Member')->where(['id'=>$member_id])->save(['pic_id'=>$pic_id]);
                if($member_pic>0 || $member_pic !==0 ){
                    $this->success('修改头像成功');
                }else{
                    $this->error('修改头像失败');

                }
            }
        }
    }

    //修改Host背景图
    public function UpdateHostBg(){
        if(IS_AJAX && IS_POST) {
            if($_POST['typeName']=='get_Bg' && !empty($_POST['member_id'])){
                $member_id = $_POST['member_id'];
                $member_pic = D('MemberView')->field('cover_pic_id')->where(['id'=>$member_id])->find();
                $member_cover_path = $this->m2('pics')->field('id,path')->where(['id'=>$member_pic['cover_pic_id']])->find();
                $member_cover_path['path'] =thumb($member_cover_path['path']);
                $this->ajaxReturn($member_cover_path);
            }elseif($_POST['typeName']=='updateBg' && !empty($_POST['member_id'])){
                $member_id = $_POST['member_id'];
                $cover_pic_id = $_POST['cover_pic_id'];
                $member_pic = $this->m2('MemberInfo')->where(['member_id'=>$member_id])->save(['cover_pic_id'=>$cover_pic_id]);
                if($member_pic>0 || $member_pic !==0 ){
                    $this->success('修改背景图成功');
                }else{
                    $this->error('修改背景图失败');

                }
            }
        }
    }

    //查看修改简介
    public function check_synopsis(){
        if(IS_AJAX && IS_POST){
            if($_POST['typeName']=='check_synopsis' &&  !empty($_POST['member_id'])){
                $member_id = I('post.member_id');
                $synopsis = $this->m2('member_info')->where(['member_id'=>$member_id])->getField('signature');
                $this->ajaxReturn($synopsis);
            }elseif($_POST['typeName']=='Update_synopsis' &&  !empty($_POST['member_id'])){
                $member_id = I('post.member_id');
                $introduce = $_POST['signature'];
                $rs = $this->m2('member_info')->where(['member_id'=>$member_id])->select();
                if(!empty($rs)){
                    $synopsis = $this->m2('member_info')->where(['member_id'=>$member_id])->save([
                        'signature'=>$introduce
                    ]);
                }
                if($synopsis !=0 && $synopsis != ''){
                    $this->success('修改成功！');
                }else{
                    $this->error('修改失败！');
                }

            }

        }

    }

    public function device_list(){

    }

    //HOST导出
    public function HostExport(){
        $condition = array();
        $member_telephone = I('get.telephone');
        $member_nickname = I('get.member');

        if(isset($_GET['member_id']) && is_numeric($_GET['member_id'])) {
            $condition .= ' and A.id = ' . $_GET['member_id'];
        }
        $member_nickname && $condition['nickname'] = array('LIKE','%'.$member_nickname.'%');
        $member_telephone && $condition['telephone'] = array('LIKE','%'.$member_telephone.'%');
        $condition['tag_id'] = ['EQ',18];
        $data = D('MemberInfoHostView')->where($condition)->group('A.id')->select();
        //数据处理
        foreach($data as $key=>$row){
            $rs = D('MemberHostBankView')->where(['member_id'=>$row['id']])->find();
            $data1[$key]['id'] = $row['id'];
            $data1[$key]['nickname'] = $row['nickname'];
            if($row['sex'] == 0)$data1[$key]['sex'] = '未设置';
            if($row['sex'] == 1)$data1[$key]['sex'] = '男';
            if($row['sex'] == 2)$data1[$key]['sex'] = '女';
            $data1[$key]['telephone'] = $row['telephone'];
            $data1[$key]['contact'] = $row['contact'];
            $data1[$key]['realname'] = $rs['realname'];
            $data1[$key]['bank_number'] = $rs['bank_number'];
            $data1[$key]['bank_name'] = $rs['bank_name'];

        }


        $titleArr = ['用户id','昵称','性别','手机号','联系电话','真实姓名','身份证','所属银行'];
        toXls($titleArr,$data1,'Host数据');
    }

    //会员导出
    public function MemberExport(){
        $condition = array();
        $member_telephone = I('get.telephone');
        $member_nickname = I('get.member');
        $member_status = I('get.status');
        $start = I('get.start_time');
        $end = I('get.stop_time');
        $start_time = strtotime($start);
        $stop_time = strtotime($end);

        $member_nickname && $condition['nickname'] = array('LIKE','%'.$member_nickname.'%');
        $member_telephone && $condition['telephone'] = array('LIKE','%'.$member_telephone.'%');
        if($start && $end){
            $condition['register_time'] = array('BETWEEN',"$start_time,$stop_time");
        }else{
            $start && $condition['register_time'] = array('GT',$start_time);
            $end && $condition['register_time'] = array('LT',$stop_time);
        }
        /*$start_time && $condition['register_time'] = array('EGT',$start_time);
        $stop_time && $condition['register_time'] =array('ELT',$stop_time);*/

        $member_status!='' && $condition['status'] = array('EQ',$member_status);
        $data = D('MemberExportView')->where($condition)->select();
        //数据处理
        foreach($data as $key=>$row){
            if($row['status'] == 1)$data[$key]['status'] = '已启用';
            if($row['status'] == 2)$data[$key]['status'] = '已禁用';
            if($row['status'] == 0)$data[$key]['status'] = '已删除';
            $data[$key]['register_time'] = date('Y-m-d H:i',$row['register_time']);
            $data[$key]['login_times'] = $this->m2('member_login_log')->where('member_id='.$row['id'])->count();
            $data[$key]['order_times'] = $this->m2('order')->where('member_id='.$row['id'])->count();
            unset($data[$key]['member_info_citys_id']);
            if($row['member_info_sex'] == 0)$data[$key]['member_info_sex'] = '未设置';
            if($row['member_info_sex'] == 1)$data[$key]['member_info_sex'] = '男';
            if($row['member_info_sex'] == 2)$data[$key]['member_info_sex'] = '女';
            //$data[$key]['member_age'] = date('Y',time()-$data[$key]['member_info_birth'])-1970;
            unset($data[$key]['member_info_birth']);
            $data[$key]['is_Host'] = $row['tag_id']==18?'是':'否';
            unset($data[$key]['tag_id']);

        }
        $titleArr = ['id','昵称','账号','openid','注册时间','是否启用','性别','城市','登录次数','消费次数','是否达人'];
        toXls($titleArr,$data,'会员数据');
    }

    //模糊搜索标题
    public function GetTitle()
    {
        if(IS_AJAX && IS_POST){
            $search_title = I('post.search_key');
            $type = I('post.type');
            if($type == 0){
                $sql = 'select A.id AS title_id, A.title AS title, B.nickname AS nickname from `ym_tips` AS A LEFT JOIN `ym_member` AS B ON B.id = A.member_id where A.title LIKE "%'.$search_title.'%" AND A.status=1  group by `title_id` order by `title_id` desc limit 20';
                $order_Arr = $this->m2()->query($sql);
                $this->ajaxReturn($order_Arr);
            }elseif($type == 1){
                $sql = 'select A.id AS title_id, A.title AS title, B.nickname AS nickname from `ym_goods` AS A LEFT JOIN `ym_member` AS B ON B.id = A.member_id where A.title LIKE "%'.$search_title.'%" AND A.status=1 group by `title_id` order by `title_id` desc limit 20';
                $order_Arr = $this->m2()->query($sql);
                $this->ajaxReturn($order_Arr);
            }elseif($type == 2){
                $sql = 'select A.id AS title_id, A.title AS title, B.nickname AS nickname from `ym_raise` AS A LEFT JOIN `ym_member` AS B ON B.id = A.member_id where A.title LIKE "%'.$search_title.'%" AND A.status=1 group by `title_id` order by `title_id` desc limit 20';
                $order_Arr = $this->m2()->query($sql);
                $this->ajaxReturn($order_Arr);
            }

        }

    }

    //推送IOS信息
    public function PushIOSMessage()
    {
        if(IS_AJAX && IS_POST){
            if($_POST['pushType'] == 'MemberIdType'){//批量会员ID推送信息
                $member_ids = I('post.member_ids');
                $message = I('post.message');
                $type = I('post.type');
                $tittle_id = I('post.tittle_id');
//                $memberid_arr = explode(',',$member_ids);
                if($type == 0 && $type != ''){
                    $message_type = 4;
                }elseif($type == 1){
                    $message_type = 6;
                }elseif($type == 2){
                    $message_type = 7;
                }
                $rs = $this->m2('member')->field('id,nickname,channel')->where(['id'=>['IN',$member_ids]])->select();
                $i = 0;
                $id_str = '';
                foreach($rs as $val){
                    $data=[
                        'type'=>0,
                        'content'=>$message,
                        'type'=>$message_type?$message_type:0,
                        'type_id'=>$tittle_id,
                        'is_show'=>0,
                    ];
                    if(in_array($val['channel'],[0,1,2])){
                        $data['ios_push']=1;
                    }elseif(in_array($val['channel'],[0,1,2])){
                        $data['ios_push']=2;
                    }else{
                        $data['ios_push']=1;
                    }
                    $message_id = $this->m2('message')->add($data);
                    $_data = [
                      'member_id'=>$val['id'],
                      'message_id'=>$message_id,
                      'is_ios_push'=>0,
                    ];
                    $member_message_id = $this->m2('member_message')->add($_data);
                    if($member_message_id>0){
                        $i++;
                        $id_str .= $val['nickname'].'【'.$member_message_id.'】,';
                    }else{
                        continue;
                    }
                }
                $this->success('生成订单的ID有：'.$id_str.',一共有'.$i.'条');

            }
        }
    }

    //host支付方式
    public  function host_pay(){
        $member_id = I('post.member_id');
        $type = I('post.type',0);
        if(empty($member_id)) $this->error('非法访问');
        if($_POST['typeName'] == 'checkhostpay'){
            $rs = $this->m2('MemberPayway')->where(['member_id'=>$member_id ,'status'=>1,'type'=>$type])->find();
            $data['member_id'] = $member_id;
            $data['name'] = $rs['name'];
            $data['code'] = $rs['code'];
            $data['type'] = $rs['type'];
            $this->ajaxReturn($data);
        }elseif($_POST['typeName'] == 'updatehostpay'){
            $name = I('post.name');
            $code = I('post.code');
            $rs = $this->m2('MemberPayway')->where(['member_id'=>$member_id ,'status'=>1,'type'=>$type])->find();
            if(!empty($rs)){
                $this->m2('MemberPayway')->data(['name'=>$name,'code'=>$code])->where(['member_id'=>$member_id ,'status'=>1,'type'=>$type])->save();
                $this->success('修改成功');
            }else{
                $id = $this->m2('MemberPayway')->add(['name'=>$name,'member_id'=>$member_id,'code'=>$code,'type'=>$type]);
                if($id>0){
                    $this->success('添加成功');
                }
            }
        }
    }
}


