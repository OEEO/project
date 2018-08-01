<?php
namespace Admin\Controller;

class IndexController extends MainController {
	
	Protected $pagename = '运营后台';

    /**
     * 运营首页
     */
	public function index(){

		$this->actname = '首页';

//        $start_time = I('get.start_time');
//        $end_time = I('get.end_time');
        $time = strtotime(date('Y-m-d 00:00:00',time()));
        if(empty($start_time))
            $start_time = strtotime(date('Y-m-d 00:00:00',time()));
        else
            $start_time = strtotime($start_time . ':00');
        if(empty($end_time))
            $end_time = strtotime(date('Y-m-d 23:59:59',time()));
        else
            $end_time = strtotime($end_time . ':00');
//        if($start_time > $end_time)$this->error('开始时间不能大于结束时间!');

        //查询今日新增用户
        $new_member = $this->m2('member')->where(['register_time' => ['GT', $time]])->count();
        $this->assign('new_member',$new_member);
        //查询今日成交单数
        $new_deal = $this->m2('OrderPay')->where(['datetime'=>['GT', date('Y-m-d 00:00:00')]])->count();
        $this->assign('new_deal',$new_deal);
        //获取新增用户信息
        $channel = C('CHANNEL');
        $datas['datas'] = D('MemberView')->where("register_time >= '{$start_time}' and register_time <= '{$end_time}'")->order(['id' => 'desc'])->page(1, 10)->select();
        foreach($datas['datas'] as $key=>$row){
            $datas['datas'][$key]['path'] = pathFormat($row['path']);
            $datas['datas'][$key]['register_time'] = date('Y-m-d H:i:s', $row['register_time']);
            $datas['datas'][$key]['channel'] = $channel[$row['channel']];
        }
        $datas['lang'] = [
            'nickname' => '昵称',
            'path' => ['头像','<img src="%*%" height="50px" width="50px">'],
            'channel' => '渠道',
            'register_time' => '注册时间',
        ];

        $dateUnitstart =time()+( 1 *  24  *  60  *  60 );
        $dateUnitend =time()-( 29 *  24  *  60  *  60 );
        $dateday = date('Y-m-d',$dateUnitstart);
        $totalTime=date("Y-m-d",$dateUnitend);
        $daysArr=array();
        for($i=0;$i<=29;$i++){
            $datas['orderarr'][]['dateList']=$daysarr[]=date("Y-m-d",strtotime('-'.$i.'day'));
        }
        //查询30天内每天的订单数
        $sordercount = $this->m2('order')->field("FROM_UNIXTIME(`create_time`, '%Y-%m-%d') as createtime , count(distinct `id`) as ordernum" )->where("FROM_UNIXTIME(`create_time`, '%Y-%m-%d')< '{$dateday}' and FROM_UNIXTIME(`create_time`, '%Y-%m-%d')>='{$totalTime}' and act_status< 2 and act_status <> 6")->order("create_time desc")->group("FROM_UNIXTIME(`create_time`, '%Y-%m-%d')")->select();
        //查询30天内每天的新增用户数
        $smentberscont = $this->m2('member')->field("FROM_UNIXTIME(`register_time`, '%Y-%m-%d') as registertime , count(distinct `id`) as usernum" )->where("FROM_UNIXTIME(`register_time`, '%Y-%m-%d')< '{$dateday}' and FROM_UNIXTIME(`register_time`, '%Y-%m-%d')>='{$totalTime}' and status = 1")->order("register_time desc")->group("FROM_UNIXTIME(`register_time`, '%Y-%m-%d')")->select();
        //查询30天内用户的活跃度
        $smenbersactive = $this->m2('member_act_log')->field("date(`datetime`) as userdate , count(distinct `member_id`) as membernum")->where("datetime< '{$dateday}' and datetime>='{$totalTime}' and member_id is not null")->order("datetime desc")->group("date(`datetime`)")->select();

        $arr = [];
        for($i=0; $i<30; $i++){
            $date = date('Y-m-d', time() - $i*24*3600);
            $arr['datehight'][$i] = $date;
            $arr['ordernum'][$date] = 0;
            $arr['usernum'][$date] = 0;
            $arr['membernum'][$date] = 0;

        }
        foreach($sordercount as $row){
            $arr['ordernum'][$row['createtime']] = $row['ordernum'];
        }
        foreach($smentberscont as $row){
            $arr['usernum'][$row['registertime']] = $row['usernum'];
        }
        foreach($smenbersactive as $row){
            $arr['membernum'][$row['userdate']] = $row['membernum'];
        }
        krsort($arr['datehight']);
        $membernum = array_values($arr['membernum']);
        $usernum = array_values($arr['usernum']);
        $ordernum = array_values($arr['ordernum']);

        krsort($membernum);
        krsort($usernum);
        krsort($ordernum);
        $datas['datehight'] =$arr['datehight'];
        $datas['membernum'] = $membernum;
        $datas['usernum'] = $usernum;
        $datas['ordernum'] = $ordernum;
        $datas['ws'] = C('WS');
        $this->assign($datas);
        $this->view();
	}

    /**
     * 运营首页下拉加载新增会员
     */
    public function loadUser(){
//        $start_time = I('get.start_time');
//        $end_time = I('get.end_time');
        $page = I('post.page');
        $time = strtotime(date('Y-m-d 00:00:00',time()));
        if(empty($start_time))
            $start_time = strtotime(date('Y-m-d 00:00:00',time()));
        else
            $start_time = strtotime($start_time . ':00');
        if(empty($end_time))
            $end_time = strtotime(date('Y-m-d 23:59:59',time()));
        else
            $end_time = strtotime($end_time . ':00');
//        if($start_time > $end_time)$this->error('开始时间不能大于结束时间!');
        //获取新增用户信息
        $channel = C('CHANNEL');
        $datas = D('MemberView')->where("register_time >= '{$start_time}' and register_time <= '{$end_time}'")->order(['id' => 'desc'])->page($page, 10)->select();
        foreach($datas as $key=>$row){
            $datas[$key]['path'] = pathFormat($row['path']);
            $datas[$key]['register_time'] = date('Y-m-d H:i:s', $row['register_time']);
            $datas[$key]['channel'] = $channel[$row['channel']];
        }
        $this->ajaxReturn($datas);
    }

	/**
	 * 反馈单
	 */
	Public function feedback(){
		$this->actname = "会员反馈单";

        $condition = '';
        $is_answer = I('get.is_answer');
        if($is_answer==1)$condition .= 'A.answer is null ';
        if($is_answer==2)$condition .= 'A.answer is not null ';
        if($condition)$this->assign('search_answer',$is_answer);
		$feedback = D('FeedbackView');
		$datas['datas'] = $feedback->where($condition)->page(I('get.page'), 30)->select();
        foreach($datas['datas'] as $key=>$row){
            $datas['datas'][$key]['reply'] = ($row['answer']==null)?'0':'1';
        }

		$datas['operations'] = array(
            '回复' => array(
                'style' => 'success',
                'fun' => 'answer(%id)',
                'condition' => '%reply == 0'
            )
		);
		$datas['pages'] = array(
            'sum' => $feedback->count(),
            'count' => 30
		);
		$datas['lang'] = array(
            'id' => '会员ID',
            'type' => '反馈类型',
            'nickname' => '昵称',
            'telephone' => '电话',
            'content' => '内容',
            'answer' => '回复',
            'datetime' => '反馈时间',
		);

		$this->assign($datas);

		$this->view();
	}

    //处理单条反馈信息
    public function answer(){
        if(IS_AJAX){
            $id = I('post.id');
            $data = array();
            $data['answer'] = I('post.answer');
            $data['id'] = $id;
            $this->m2('feedback')->data($data)->save();
            $this->success('已回复');
        }
    }

    /*//批量处理反馈信息
    public function disposeAll(){
        if(IS_POST){
            $ids = I('post.question_ids');

            foreach($ids as $row){
                $data = array();
                $data['id'] = $row;
                $data['is_dispose'] = 1;
                $this->m2('feedback')->data($data)->save();
            }
            $this->success('处理成功');
        }
    }*/
	
	//生成验证码
	Public function captcha(){
		$config = array(
			'fontSize'	=>	30,	// 验证码字体大小
			'length'	  =>	5,	 // 验证码位数
		);
		
		$Verify = new \Think\Verify($config);
		$Verify->entry();
	}
	
	//架构表收入
	Public function framework(){
		if(IS_POST){
			$name = I('post.name');
			$sign = I('post.sign');
			$type = I('post.type');
			$pid = I('post.pid', null);
			$content = I('post.content', null);
			if(empty($name) || empty($sign) || empty($type)){
				$this->error('架构名称、架构标签、架构类型必须填写！');
			}
			
			//判断该架构是否真实存在
//	 		if($type > 1 && $pid != null){
//	 			$p_sign = $this->m1('framework')->where(array('pid' => $pid))->getField('sign');
//	 			$code = file_get_contents(__DIR__ . '/' . $p_sign . 'Controller' . EXT);
//	 			if(!preg_match('/function ' . $sign . '\(/i', $code))$this->error('该方法不存在，请先创建再加入架构！');
//	 		}else{
//	 			if(!is_file(__DIR__ . '/' . $sign . 'Controller' . EXT))$this->error('该控制器不存在，请先创建再加入架构！');
//	 		}
			
			$frame = $this->m1('framework');
			$frame->name = $name;
			$frame->sign = $sign;
			$frame->type = $type;
			$frame->pid = $pid;
			$frame->content = $content;
			if($frame->add()){
				$this->success('架构录入成功！');
			}else{
				$this->error('架构录入失败！');
			}
			exit;
		}
		$this->actname = '架构录入';
		$controller = $this->m1('framework')->where(array('type' => 1))->select();
		$this->assign('controller', $controller);
		$this->view();
	}

    //厨房家列表
    Public function kitchen(){
        $this->actname = '厨房列表';
        $pageSize = 20;
        if(isset($_GET['status']) && is_numeric($_GET['status'])){
            $condition = "A.status = " . $_GET['status'];
            $status = $_GET['status'];
        }else{
            $condition = "A.status = 1 ";
            $status = 1;
        }
        $this->assign('search_status', $status);
        $datas['datas'] = D('KitchenListView')->where($condition)->page(I('get.page',1),$pageSize)->order('id desc')->select();
        foreach($datas['datas'] as $key=>$row){
            $ids[] = $row['id'];
        }
        $result = C('FACILITY');
        if(!empty($ids)){
            //$result = $this->m2('SpaceFacility')->join('__FACILITY__ ON __SPACE_FACILITY__.facility_id=__FACILITY__.id')->where(['space_id'=>['IN',join(',',$ids)]])->field('space_id,name')->select();
            $tag = $this->m2('SpaceTag')->join('__TAG__ ON __SPACE_TAG__.tag_id=__TAG__.id')->where(['space_id'=>['IN',join(',',$ids)]])->field('space_id,name')->select();
        }

        foreach($datas['datas'] as $key=>$row) {
            foreach ( explode(',',$datas['datas'][$key]['facility']) as $row5) {
                foreach ($result as $row2) {
                    if ($row2['id'] == $row5['id']) {
                        $datas['datas'][$key]['facilityname'] .= $row2['name'] . ',';
                    }
                }
            }
            foreach($tag as $row4){
                if($row4['space_id'] == $row['id']){
                    $datas['datas'][$key]['tag'] .= $row4['name'].',';
                }
            }
            $times = explode(',',$row['opening_time']);
            $date_array = [1=>'一',2=>'二',3=>'三',4=>'四',5=>'五',6=>'六',7=>'日'];
            foreach($times as $row3){
                $times_sub = explode('-',$row3);
                //本周一0点时间戳
                $re = getmonsun();
                $monday = $re['mon'];
                $datas['datas'][$key]['server_time'] .= '周'.$date_array[date('N',$monday+$times_sub[0])].' '.date('H:i',$monday+$times_sub[0]) .'~'. '周'.$date_array[date('N',$monday+$times_sub[1])].' '.date('H:i',$monday+$times_sub[1]).'</br>';
            }
        }

        $datas['operations'] = array(
            '链接' => "getline(%id)",
            '修改' => "ModifyKitchen(%id)",
            '删除' => "kitchenDelete(%id)",
        );
        $datas['pages'] = array(
            'sum' => D('KitchenListView')->where($condition)->count(),
            'count' => $pageSize,
        );

        $datas['lang'] = array(
            'id' => 'ID',
            'nickname' => '负责人昵称',
            'telephone' => '手机号码',
            'weixincode' => '微信号',
            'name'=>'场地名称',
            'address' => '场地地址',
            'volume' => '容量',
            'server_time' => '服务时间',
            'tag'=>'关键字',
            'facilityname'=>'设备',
            'context' => '其他'
        );

        $this->assign($datas);
        $this->view();
    }

    //添加厨房
    Public function kitchenAdd(){
        $this->actname = '厨房添加';

        if(IS_AJAX){
            $oper = I('post.oper');
            if($oper == 'get_area'){
                $id = I('post.id');
                $area = $this->m2('citys')->field('id,name')->where(['pid'=>$id])->select();
                $this->ajaxReturn($area);
            }
            if($oper == 'submit'){
                $data = I('post.data');

                if(empty($data['category']))$this->error('分类不能为空');
                if(empty($data['kitchenName']))$this->error('厨房名称不能为空');
                if(empty($data['nickname']))$this->error('负责人昵称不能为空');
                if(empty($data['telephone']))$this->error('负责人电话号码不能为空');
                if(empty($data['weixincode']))$this->error('负责人微信号不能为空');
                if(empty($data['introduction']))$this->error('厨房介绍不能为空');
                if(empty($data['city_id']))$this->error('未选择城市');
                if(empty($data['address']))$this->error('厨房地址不能为空');
                if(empty($data['pic_id']))$this->error('厨房主图不能为空');
                if(empty($data['lat']))$this->error('经纬度不能为空');
                if(empty($data['lng']))$this->error('经纬度不能为空');
                if(empty($data['proportion']))$this->error('厨房面积不能为空');
                if(empty($data['volume']))$this->error('厨房接纳人数不能为空');
                if(empty($data['pic_group']))$this->error('厨房组图不能为空');
                if(empty($data['open_time_start_week']) || empty($data['open_time_end_week']))$this->error('厨房开放时间不能为空');


                $_data['category_id'] = $data['category'];
                $_data['name'] = $data['kitchenName'];
                $_data['nickname'] = $data['nickname'];
                $_data['telephone'] = $data['telephone'];
                $_data['weixincode'] = $data['weixincode'];
                $_data['introduction'] = $data['introduction'];
                $_data['city_id'] = $data['city_id'];
                $_data['address'] = $data['address'];
                $_data['pic_id'] = $data['pic_id'];
                $_data['latitude'] = $data['lat'];
                $_data['longitude'] = $data['lng'];
                $_data['proportion'] = $data['proportion'];
                $_data['volume'] = $data['volume'];
                $_data['context'] = $data['context'];
                $_data['status'] = 1;//公开状态

                //图组
                $pic_group = $data['pic_group'];
                $group_ids = join(',',$pic_group);
                $pics_group_id = $this->m2('PicsGroup')->data(['type'=>0])->add();
                $this->m2('Pics')->where(['id'=>['IN',$group_ids]])->data(['group_id'=>$pics_group_id])->save();
                $_data['pic_group_id'] = $pics_group_id;

                foreach($data['open_time_start_week'] as $key=>$row){
                    $start_day = ($row-1)*86400;
                    $end_day = ($data['open_time_end_week'][$key]-1)*86400;
                    $start_hour = $data['open_time_start_hour'][$key] * 3600;
                    $end_hour = $data['open_time_end_hour'][$key] * 3600;
                    $start_min = $data['open_time_start_min'][$key] * 60;
                    $end_min = $data['open_time_end_min'][$key] * 60;

                    $start = $start_day+$start_hour+$start_min;
                    $end = $end_day+$end_hour+$end_min;

                    if($end < $start)$this->error('结束时间不能小于开始时间');
                    $server_time[$key] = $start.'-'.$end;
                }
                $_data['opening_time'] = join(',',$server_time);
                //厨房设备
                $_data['facility'] = join(',',$data['facility']);

                //厨房标签
                $space_id = $this->m2('Space')->data($_data)->add();
//                foreach($data['facility'] as $row){
//                    $this->m2('SpaceFacility')->data(['space_id'=>$space_id,'facility_id'=>$row])->add();
//                }
                foreach(explode(',',$data['tags_id']) as $row){
                    $this->m2('SpaceTag')->data(['space_id'=>$space_id,'tag_id'=>$row])->add();
                }

                $this->success('添加成功');
            }
        }
        //查找厨房分类
        $category = $this->m2('category')->where(['type'=>4])->field('id,name')->select();
        $this->assign('category',$category);
        //查找厨房标签
        $label = $this->m2('Tag')->where(['type'=>4])->select();
        $this->assign('label',$label);
        //查找城市ID
        $citys = C('CITY_CONFIG');
        $this->assign('citys',$citys);
        //查找厨房设备
        //$facility = $this->m2('facility')->select();
        $facility = C('FACILITY');
        $this->assign('facility',$facility);
        $this->view();
    }

    //修改厨房
    public function kitchenModify()
    {
        $this->actname = '修改厨房';
        if (IS_AJAX) {
            $data = I('post.data');

            if (empty($data['category'])) $this->error('分类不能为空');
            if (empty($data['kitchenName'])) $this->error('厨房名称不能为空');
            if (empty($data['nickname']))$this->error('负责人昵称不能为空');
            if (empty($data['telephone']))$this->error('负责人电话号码不能为空');
            if (empty($data['weixincode']))$this->error('负责人微信号不能为空');
            if (empty($data['introduction'])) $this->error('厨房介绍不能为空');
            if (empty($data['city_id'])) $this->error('未选择城市');
            if (empty($data['address'])) $this->error('厨房地址不能为空');
            if (empty($data['pic_id'])) $this->error('厨房主图不能为空');
            if (empty($data['lat'])) $this->error('经纬度不能为空');
            if (empty($data['lng'])) $this->error('经纬度不能为空');
            if (empty($data['proportion'])) $this->error('厨房面积不能为空');
            if (empty($data['volume'])) $this->error('厨房接纳人数不能为空');
            if (empty($data['pic_group'])) $this->error('厨房组图不能为空');
            if (empty($data['open_time_start_week']) || empty($data['open_time_end_week'])) $this->error('厨房开放时间不能为空');

            $_data['id'] = $data['id'];
            $_data['category_id'] = $data['category'];
            $_data['name'] = $data['kitchenName'];
            $_data['nickname'] = $data['nickname'];
            $_data['telephone'] = $data['telephone'];
            $_data['weixincode'] = $data['weixincode'];
            $_data['introduction'] = $data['introduction'];
            $_data['city_id'] = $data['city_id'];
            $_data['address'] = $data['address'];
            $_data['pic_id'] = $data['pic_id'];
            $_data['latitude'] = $data['lat'];
            $_data['longitude'] = $data['lng'];
            $_data['proportion'] = $data['proportion'];
            $_data['volume'] = $data['volume'];
            $_data['context'] = $data['context'];

            //图组
            $pic_group = $data['pic_group'];
            $group_ids = join(',', $pic_group);
            $pics_group_id = $this->m2('PicsGroup')->data(['type' => 0])->add();
            $this->m2('Pics')->where(['id' => ['IN', $group_ids]])->data(['group_id' => $pics_group_id])->save();
            $_data['pic_group_id'] = $pics_group_id;

            foreach ($data['open_time_start_week'] as $key => $row) {
                $start_day = ($row - 1) * 86400;
                $end_day = ($data['open_time_end_week'][$key] - 1) * 86400;
                $start_hour = $data['open_time_start_hour'][$key] * 3600;
                $end_hour = $data['open_time_end_hour'][$key] * 3600;
                $start_min = $data['open_time_start_min'][$key] * 60;
                $end_min = $data['open_time_end_min'][$key] * 60;

                $start = $start_day + $start_hour + $start_min;
                $end = $end_day + $end_hour + $end_min;

                if ($end < $start) $this->error('结束时间不能小于开始时间');
                $server_time[$key] = $start . '-' . $end;
            }
            $_data['opening_time'] = join(',', $server_time);
            $_data['facility'] = join(',', $data['facility']);
            $_data['status'] = 1;
            //厨房表
            $this->m2('Space')->data($_data)->save();

            //厨房设备表
//            $this->m2('SpaceFacility')->where(['space_id'=>$_data['id']])->delete();
//            foreach($data['facility'] as $row){
//                $this->m2('SpaceFacility')->data(['space_id'=>$_data['id'],'facility_id'=>$row])->add();
//            }
            //厨房标签表
            $this->m2('SpaceTag')->where(['space_id' => $_data['id']])->delete();
            if (!empty($data['tags_id'])){
                foreach (explode(',', $data['tags_id']) as $row) {
                    $this->m2('SpaceTag')->data(['space_id' => $_data['id'], 'tag_id' => $row])->add();
                }
            }
            $this->success('修改成功');
        }

        $id = I('get.id');
        $space_data = $this->m2('Space')->where(['id'=>$id])->find();
        //还原时间段
        $server_time = explode(',',$space_data['opening_time']);
        foreach($server_time as $key=>$row){
            $result = explode('-',$row);
            $start_week[$key] = floor($result[0]/86400)+1;
            $end_week[$key] = floor($result[1]/86400)+1;
            $start_hour[$key] = floor(($result[0]-(($start_week[$key]-1)*86400))/3600);
            $end_hour[$key] = floor(($result[1]-(($end_week[$key]-1)*86400))/3600);
            $start_min[$key] = floor(($result[0]-(($start_week[$key]-1)*86400)-($start_hour[$key]*3600))/60);
            $end_min[$key] = floor(($result[1]-(($end_week[$key]-1)*86400)-($end_hour[$key]*3600))/60);
        }
        $server_time = ['start_week'=>$start_week,'end_week'=>$end_week,'start_hour'=>$start_hour,'end_hour'=>$end_hour,'start_min'=>$start_min,'end_min'=>$end_min];

        //城市列表
        $city_pid = $this->m2('citys')->where(['id'=>$space_data['city_id']])->getField('pid');
        $city_id = $this->m2('citys')->where(['id'=>$city_pid])->getField('id');
        $area_list = $this->m2('citys')->where(['pid'=>$city_pid])->select();
        $citys = C('CITY_CONFIG');
        $this->assign('citys',$citys);
        $this->assign('city_id',$city_id);
        $this->assign('area_list',$area_list);

        //封面图与图组
        $path = $this->m2('pics')->where(['id'=>$space_data['pic_id']])->find();
        $path = ['id'=>$path['id'],'path'=>thumb($path['path'],1)];
        $group_path = $this->m2('pics')->where(['group_id'=>$space_data['pic_group_id']])->select();
        foreach($group_path as $row){
            $groupPath[] = ['id'=>$row['id'],'path'=>thumb($row['path'],1)];
        }
        $picPath = ['path'=>$path,'group_path'=>$groupPath];
        $this->assign('picPath',$picPath);

        //查找厨房设备
        $facility = C('FACILITY');
        $this->assign('facility',$facility);

        //查找厨房分类
        $category = $this->m2('category')->where(['type'=>4])->field('id,name')->select();
        $this->assign('category',$category);

        //查找厨房标签
        $label = $this->m2('Tag')->where(['type'=>4])->select();
        $this->assign('label',$label);
        $select_label = $this->m2('SpaceTag')->where(['space_id'=>$id])->getField('tag_id',true);
        $this->assign('SelectLabel',$select_label);

        $facilitySpace = explode(',',$space_data['facility']);
        $this->assign('space_data',$space_data);
        $this->assign('facility_data',$facilitySpace);
        $this->assign('server_time',$server_time);
        $this->assign('id',$id);

        $this->view();
    }

    //申请列表
    function kitchenApply(){
        $this->actname = '咨询列表';
        $page = I('get.page',1);
        $pageSize = 10;

        $condition = array();
        $datas = D('SpaceApplyView')->where($condition)->page($page,$pageSize)->order('id desc')->select();

        foreach($datas as $key=>$row){
            $datas[$key]['path'] = thumb($row['path'],10);
            if($row['time']==0)$row['time'] = '全天';
            if($row['time']==1)$row['time'] = '上午';
            if($row['time']==2)$row['time'] = '下午';
            if($row['time']==3)$row['time'] = '晚上';
            $datas[$key]['date'] = $row['month'].'月'.$row['day'].'日'.$row['time'];
        }
        $datas['datas'] = $datas;
        $datas['operations'] = array(
            '设为已处理' => array(
                'style' => 'success',
                'fun' => 'updateStatus(%id)',
                'condition' => '%status == 0'
            ),
            '备注' => "remark(%id)"
        );
        $datas['pages'] = array(
            'sum' => D('SpaceApplyView')->where($condition)->count(),
            'count' => $pageSize,
        );

        $datas['lang'] = array(
            'id' => 'ID',
            'name'=>'场地名称',
            'path' => array('场地图片', '<img src="%*%" height="100px" width="100px"/>'),
            'aim' => '活动目的',
            'num' => '人数',
            'budget' => '预算',
            'contacts' => '联系人',
            'telephone' => '申请者',
            'date' => '预约时间',
            'context' => '申请者留言',
            'remark' => '其他'
        );
        $this->assign($datas);
        $this->view();

    }

    //添加厨房场地
    public function kitchenApplyStatus(){
        if(IS_AJAX){
            $id = I('post.id');
            if(empty($id))$this->error('非法访问');
            $rs = $this->m2('SpaceApply')->where(['id'=>$id])->find();
            if(empty($rs))$this->error('该申请不存在');
            $this->m2('SpaceApply')->data(['id'=>$id,'status'=>1])->save();
            $this->success('操作成功');
        }else{
            $this->error('非法访问');
        }
    }

    public function remark(){
        if(IS_AJAX){
            $oper = I('post.oper');
            if($oper==0){
                $id = I('post.id');
                $remark = $this->m2('SpaceApply')->where(['id'=>$id])->getField('remark');
                $remark = empty($remark)?'':$remark;
                $this->ajaxReturn($remark);
            }
            if($oper==1){
                $id = I('post.id');
                $remark = I('post.remark');
                $this->m2('SpaceApply')->data(['id'=>$id,'remark'=>$remark])->save();
                $this->success('备注成功');
            }
        }else{
            $this->error('非法访问');
        }
    }

    //删除厨房
    function kitchenDelete(){
        if(IS_AJAX){
            $id = I('post.id');
            $this->m2('SpaceTag')->where(['space_id'=>$id])->delete();
            $this->m2('SpaceFacility')->where(['space_id'=>$id])->delete();
            $this->m2('Space')->where(['id'=>$id])->delete();
            $this->success('删除成功');
        }else{
            $this->error('非法访问！');
        }
    }

    //导出厨房场地
    public function kitchenExport(){
        if(isset($_GET['status']) && is_numeric($_GET['status'])){
            $condition = "A.status = " . $_GET['status'];
            $status = $_GET['status'];
        }else{
            $condition = "A.status = 1 ";
            $status = 1;
        }
        $data = D('KitchenListView')->where($condition)->order('id desc')->select();
        foreach($data as $key=>$row){
            $ids[] = $row['id'];
        }
        if(!empty($ids)){
            $result = $this->m2('SpaceFacility')->join('__FACILITY__ ON __SPACE_FACILITY__.facility_id=__FACILITY__.id')->where(['space_id'=>['IN',join(',',$ids)]])->field('space_id,name')->select();
            $tag = $this->m2('SpaceTag')->join('__TAG__ ON __SPACE_TAG__.tag_id=__TAG__.id')->where(['space_id'=>['IN',join(',',$ids)]])->field('space_id,name')->select();
        }

        foreach($data as $key=>$row){
            foreach($result as $row2){
                if($row2['space_id'] == $row['id']){
                    $data[$key]['facility'] .= $row2['name'].',';
                }
            }
            foreach($tag as $row4){
                if($row4['space_id'] == $row['id']){
                    $data[$key]['tag'] .= $row4['name'].',';
                }
            }
            $times = explode(',',$row['opening_time']);
            $date_array = [1=>'一',2=>'二',3=>'三',4=>'四',5=>'五',6=>'六',7=>'日'];
            foreach($times as $row3){
                $times_sub = explode('-',$row3);
                //本周一0点时间戳
                $re = getmonsun();
                $monday = $re['mon'];
                $data[$key]['server_time'] .= '周'.$date_array[date('N',$monday+$times_sub[0])].' '.date('H:i',$monday+$times_sub[0]) .'~'. '周'.$date_array[date('N',$monday+$times_sub[1])].' '.date('H:i',$monday+$times_sub[1]).'</br>';
            }
        }

        $title = [
            'id' => 'ID',
            'nickname' => '负责人昵称',
            'telephone' => '手机号码',
            'name'=>'场地名称',
            'address' => '场地地址',
            'volume' => '容量',
            'server_time' => '服务时间',
            'tag'=>'关键字',
            'facility'=>'设备',
            'context' => '其他'
        ];

        $datas = [];
        foreach($data as $d){
            $datas[] = [
                $d['id'],$d['nickname'],$d['telephone'],$d['name'],$d['address'],$d['volume'],$d['server_time'],$d['tag'],$d['facility'],$d['context']
            ];
        }

        toXls($title,$datas,'厨房场地导出');
    }

    //banner管理
    public function banner()
    {
        $this->actname = 'banner设置';

        $is_show = I('get.is_show', 1);

        if (IS_POST ) {
            if($_POST['oper'] == 'history'){
                $data = $this->upload(3);  //3:640x260缩略图
                $returnData = [];
                if(!empty($data)){
                    $returnData['status'] = 1;
                    $returnData['info'] = $data;
                }
                $this->ajaxReturn($returnData);
                exit;
            }elseif(IS_AJAX) {
                //上移
                if ($_POST['opers'] == 'shift_up') {
                    $id = I('post.id');
                    $sort_num = $this->m2('Banners')->where(['id' => $id])->getField('sort');
                    $sort_num = $sort_num + 1;
                    $this->m2('Banners')->where(['id' => $id])->save(['sort' => $sort_num]);
                    $this->success('上移成功');
                    //下移
                } elseif ($_POST['opers'] == 'shift_down') {
                    $id = I('post.id');
                    $sort_num = $this->m2('Banners')->where(['id' => $id])->getField('sort');
                    $sort_num = $sort_num - 1;
                    $this->m2('Banners')->where(['id' => $id])->save(['sort' => $sort_num]);
                    $this->success('下移成功');

                }
            }
        }

        $datas['datas'] = D('BannerView')->where(['is_show' => $is_show])->order('A.sort desc,A.id desc')->select();
        //数据处理
        foreach($datas['datas'] as $key=>$row){
            if($row['type']==2)$datas['datas'][$key]['type'] = '美食福利社';
            if($row['type']==3)$datas['datas'][$key]['type'] = '众筹';
            if($row['type']==4)$datas['datas'][$key]['type'] = '早餐打卡';
            if($row['type']==5)$datas['datas'][$key]['type'] = '食物学园';
            if($row['type']==1)$datas['datas'][$key]['type'] = '我有饭';
            if($row['type']==0)$datas['datas'][$key]['type'] = '吖咪';
            $datas['datas'][$key]['path'] = thumb($row['path'], 3);
            if(empty($row['citys_id']))$datas['datas'][$key]['city_name'] = '全站通用';
        }
        $datas['operations'] = [
            '更改' => [
                'style' => 'success',
                'fun' => 'update(%id,1)',
                'condition' => '%is_show == 1'
            ],
            '下架' => [
                'style' => 'danger',
                'fun' => 'checkout(%id,3)',
                'condition' => '%is_show == 1'
            ],
            '上架' => [
                'style' => 'success',
                'fun' => 'checkout(%id,2)',
                'condition' => '%is_show == 0'
            ],
            '上移' => [
                'style' => 'secondary',
                'fun' => "shift(%id,'shift_up')",
            ],
            '下移' => [
                'style' => 'primary',
                'fun' => "shift(%id,'shift_down')",
                'condition' => '%sort > 0'
            ],
        ];
        $datas['lang'] = [
            'id' => 'ID',
            'city_name' => '城市',
            'title' => '标题',
            'url'=> '跳转地址',
            'path' => ['预览', '<img  src="%*%" width="400" height="300" />'],
            'type' => '类型'
        ];
        $this->assign($datas);

        $this->view();
    }

    public function setbanner(){
        $id = I('post.id');
        $oper = I('post.oper');
        $title = I('post.title');
        $pic_id = I('post.pic_id');
        $url = $_POST['url'];//I('post.url');
        $citys_id = I('post.citys_id', '');
        $type = I('post.type');
        $url = (strpos($url,'http://')===false||strpos($url,'http://')!=0)?'http://'.$url:$url;
        if($oper == 3) {
            $data['is_show'] = 0;
            $data['id'] = $id;
            $this->m2('banners')->save($data);
            $this->success('下架成功');
            exit;
        }elseif($oper == 2){
            $data['is_show'] = 1;
            $data['id'] = $id;
            $this->m2('banners')->save($data);
            $this->success('上架成功');
            exit;
        }elseif($oper == 1){
            $data['title'] = $title;
            $data['pic_id'] = $pic_id;
            $data['url'] = $url;
            $data['is_show'] = 1;
            $data['type'] = $type;
            $data['citys_id'] = $citys_id?:['exp', 'null'];
            $this->m2('banners')->where(['id' => $id])->save($data);

            $this->success('更新成功');
        }elseif($oper == 4){
            $data['title'] = $title;
            $data['pic_id'] = $pic_id;
            $data['url'] = $url;
            $data['is_show'] = 1;
            $data['type'] = $type;
            $data['citys_id'] = $citys_id?:['exp', 'null'];
            $this->m2('banners')->add($data);

            $this->success('新增成功');
        }else{
            //获取当前记录信息
            $banner_re = $this->m2('banners')->where(['id' => $id])->find();
            $pic_re = $this->m2('pics')->where(['id' => $banner_re['pic_id']])->find();
            $result['title'] = $banner_re['title'];
            $result['citys_id'] = $banner_re['citys_id'];
            $result['url'] = $banner_re['url'];
            $result['path'] = thumb($pic_re['path']);
            $result['pic_id'] = $banner_re['pic_id'];
            $result['type'] = $banner_re['type'];

            $this->ajaxReturn($result);
        }
    }

    //标签设置
    public function label_old(){
        $this->actname = '标签设置';

        $condition = ['type' => ['LT', 2]];
        if($_GET !=null){
            $label_type = I('get.search_type');
            $label_official = I('get.search_official');

            if($label_type || $label_type!=null)$condition['type'] = ['EQ', $label_type];
            if($label_official || $label_official!=null)$condition['official'] = ['EQ', $label_official];

            $this->assign('search_type',$label_type);
            $this->assign('search_official',$label_official);
        }

        $datas['datas'] = $this->m2('tag')->where($condition)->page(I('get.page'), 20)->order('type')->select();

        foreach($datas['datas'] as $key=>$row){

            switch($row['type']){
                case 0:
                    $datas['datas'][$key]['type'] = '会员标签';
                    break;
                case 1:
                    $datas['datas'][$key]['type'] = '活动标签';
                    break;
                case 2:
                    $datas['datas'][$key]['type'] = '商品标签';
                    break;
                case 3:
                    $datas['datas'][$key]['type'] = '达人标签';
                    break;
                case 4:
                    $datas['datas'][$key]['type'] = '场地标签';
                    break;
                default:
                    $datas['datas'][$key]['type'] = '未定义';
                    break;
            }

            if($row['official']==1){
                $datas['datas'][$key]['official'] = '官方';
            }else{
                $datas['datas'][$key]['official'] = '非官方';
            }

        }

        $datas['operations'] = array(
            '修改标签' => "updateLabel(%id,this)",
            '删除标签' => "deleteLabel(%id)"
        );
        $datas['pages'] = array(
            'sum' => $this->m2('tag')->where($condition)->count(),
            'count' => 20
        );
        $datas['lang'] = array(
            'type' => '标签分类',
            'official' => '标签类型',
            'name' => '标签名',
            'datetime' => '创建时间'
        );
        $this->assign($datas);
        $this->view();
    }

    //标签设置(2017-3-15)
    public function label(){
        $this->actname = '标签设置';

        $datas['datas'] = $this->m2('tag')->order('type')->select();
        $official = $noofficial =[];
        foreach($datas['datas'] as $key=>$row){

            if($row['official']==1){
                $official[] =$row;
            }elseif($row['official']==0){
                $noofficial[] = $row;
            }

        }
//        print_r($official);
        $this->assign('official',$official);
        $this->assign('noofficial',$noofficial);
        $this->view();
    }

    //添加标签
    public function addlabel(){
        //$id = I('post.id');
        $labelname = I('post.labelname');
        $labeltype = I('post.labeltype');
        $official = I('post.official');

       // $data['id'] = $id;
        $data['type'] = $labeltype;
        $data['name'] = $labelname;
        $data['official'] = $official;
        $this->m2('tag')->data($data)->add();
        $this->success('添加成功');
        exit;
    }

    //修改标签
    public function updatelabel(){
        $id = I('post.id');
        $labelname = I('post.labelname');
        $labeltype = I('post.labeltype');

       /* if(IS_POST && $labelname && $labeltype){
            $data['id'] = $id;
            $data['type'] = $labeltype;
            $data['name'] = $labelname;
            $this->m2('tag')->data($data)->save();
            $this->success('修改成功');
            exit;
        }*/

        if(IS_AJAX){
            //$id = I(',id');
            //echo $id;exit;
            if($labelname || $labelname===0 && $labeltype){
                $data['id'] = $id;
                $data['type'] = $labeltype;
                $data['name'] = $labelname;
                $this->m2('tag')->data($data)->save();
                $this->success('修改成功');
                exit;
            }
            $result = $this->m2('tag')->where('id='.$id)->find();
            $this->ajaxReturn($result);
            exit;
        }
    }

    //删除标签
    public function deletelabel(){
        $id = I('post.id');

//        $tag_rs = $this->m2('tag')->where('id='.$id)->find();
        $this->m2('member_tag')->where('tag_id='.$id)->delete();
        $this->m2('tips_tag')->where('tag_id='.$id)->delete();
        $this->m2('goods_tag')->where('tag_id='.$id)->delete();


        $this->m2('tag')->where('id='.$id)->delete();
        $this->success('删除成功');
    }

	//退出登录
	public function logout(){
		session('admin', null);
		$this->success('退出登录成功！', __MODULE__);
	}

    //专题列表
    public function theme()
    {
        $this->actname = "运营专题";
        $pageSize=10;

        if (IS_POST) {
            $type = I('post.type');
            if($type==1 || $type==3)$thum_type = 8;//精选
            if($type==0 || $type==2)$thum_type = 9;//普通
            $data = $this->upload($thum_type);
            $returnData = [];
            if(!empty($data)){
                $returnData['status'] = 1;
                $returnData['info'] = $data;
            }
            $this->ajaxReturn($returnData);
            //$this->ajaxPut(1, $data);
            exit;
        }

        $condition = '';

        $title = I('get.title');
        $type = I('get.type');
        $is_show = I('get.is_show', 1);

        if(!empty($title))$condition['title'] = ['LIKE', '%'.$title.'%'];
        if($type !== '')$condition['A.type'] = ['EQ', $type];
        if($is_show == 1)$condition['sort'] = ['EGT', 0];
        else $condition['sort'] = ['LT', 0];

        $this->assign('search_title', $title);
        $this->assign('search_type', $type);
        $this->assign('search_is_show', $is_show);

        $datas['datas'] = D('ThemeView')->page(I('get.page'), $pageSize)->where($condition)->order('`sort` asc datetime desc')->select();

        $citys = C('CITY_CONFIG');
        foreach($datas['datas'] as $key=>$row){
            $datas['datas'][$key]['_type'] = ['吖咪普通', '吖咪精选', '我有饭普通', '我有饭精选'][$row['type']];
            $datas['datas'][$key]['path'] = thumb($row['path']);
            $datas['datas'][$key]['cityname'] = !empty($row['citys_id']) ? $citys[$row['citys_id']] : '通用';
        }

        $datas['operations'] = [
            '上移' => [
                'style' => 'success',
                'fun' => 'setTheme(%id, 2)',
                'condition' => "%sort>0"
            ],
            '下移' => [
                'style' => 'warning',
                'fun' => 'setTheme(%id, 3)',
                'condition' => "%sort>=0"
            ],
            '下架' => [
                //'style' => 'warning',
                'fun' => 'setTheme(%id, 0)',
                'condition' => "%sort>=0"
            ],
            '上架' => [
                //'style' => 'success',
                'fun' => 'setTheme(%id, 1)',
                'condition' => "%sort<0"
            ],
            '修改' => "resettheme(%id)",
            '删除' => 'deletetheme(%id)'
        ];
        $datas['pages'] = [
            'sum' => D('ThemeView')->where($condition)->count(),
            'count' => $pageSize
        ];

        $datas['lang'] = [
            'id' => '专题ID',
            '_type' => '分类',
            'cityname' => '所属城市',
            'title' => '专题名',
            'path' =>  ['banner图', '<img src="%*%" width="50" />'],
            'datetime' => '添加时间'
        ];

        $category = $this->m2('Category')->where('type=0 and pid is null')->field('id,name')->select();
        $this->assign('category', $category);
        $this->assign('citys', C('CITY_CONFIG'));
        $this->assign($datas);
        $this->view();
    }

    //添加/修改专题
    Public function editTheme(){
        if(IS_POST){
            $id = I('post.id');
            if(!empty($id)){
                $rs = $this->m2('theme')->where(['id' => $id])->find();
                if(empty($rs)){
                    $this->error('专题不存在！');
                }
                $data['id'] = $id;
            }

            $data['title'] =I('post.title');
            if(!empty($_POST['citys_id']))$data['citys_id'] = I('post.citys_id');
            $url = I('post.url');
            if(!empty($url)){
                $url = trim($url);
                $data['url']=strpos($url,'http://')===false && !is_numeric($url)?'http://'.$url:$url;
            }else{
                $data['url'] = '';
            }

            $data['pic_id']=I('post.pic_id');
            $data['type'] = I('post.type');
            $data['content'] = $_POST['content']?:'';
            $group = I('post.group');

            if(!empty($group)){
                //创建新图组
                if(!empty($rs['pic_group_id'])){
                    $group_id = $rs['pic_group_id'];
                }else{
                    $group_id = $this->m2('PicsGroup')->data(['type'=>4])->add();
                }
                $group_pic_id = join(',',$group);
                $this->m2('Pics')->where(['group_id'=>$group_id, 'id'=>['NOT IN',$group_pic_id]])->delete();
                $this->m2('Pics')->where(['id'=>['IN',$group_pic_id]])->save(['group_id'=>$group_id]);
                $data['pic_group_id'] = $group_id;
            }else{
                $data['pic_group_id'] = '';
            }

            if(!empty($id)){
                $this->m2('theme')->save($data);
                $this->success('专题修改成功！');
            }else{

                $this->m2('theme')->add($data);
                $this->success('专题添加成功！');
            }
            exit;
        }

        $themeid=I('get.id');
        $theme = D('ThemeView')->where(['id' => $themeid])->find();
        $group_path = $this->m2('Pics')->where(['group_id'=>$theme['pic_group_id']])->Field('path,id')->select();
        foreach($group_path as $key=>$row){
            $group_path[$key]['path'] = thumb($row['path']);
        }
        $theme['path'] = thumb($theme['path']);
        $theme['group_path'] = $group_path;
        $theme['category'] = $this->m2('Category')->where('type=0 and pid is null')->field('id,name')->select();

        $this->ajaxReturn($theme);
    }

    //专题排序
    Public function setTheme(){
        $theme_id = I('post.theme_id');
        $sort = I('post.sort', 0); //0为下架 2下移 1上移
        if($sort == 0){
            $rs = $this->m2('Theme')->where(['id' => $theme_id])->save(['sort' => -1]);
        }elseif($sort == 1){
            $rs = $this->m2('Theme')->where(['id' => $theme_id])->save(['sort' => 0]);
        }elseif($sort == 2){
            $rs = $this->m2('Theme')->where(['id' => $theme_id])->setDec('sort');
        }elseif($sort == 3){
            $rs = $this->m2('Theme')->where(['id' => $theme_id])->setInc('sort');
        }
        if($rs > 0)$this->success('设置成功!');
        $this->error('设置失败!');
    }

    //删除专题
    Public function deletetheme(){
        if(IS_AJAX){
            $id = I('post.id');
            $this->m2('theme_element')->where(['theme_id'=>$id])->delete();
            $this->m2('Theme')->where(['id'=>$id])->delete();
            $this->success('删除成功');
        }else{
            $this->error('非法访问');
        }
    }

    //分类管理
    public function category(){
        $this->actname = '分类管理';

        if(IS_AJAX){
            $oper = I('post.oper');
            $id = I('post.id');
            $type = I('post.type');
            if($oper == 1){ //查子分类
                $result = $this->m2('category')->where(['pid'=>$id])->field('id,type,name')->select();
                $this->ajaxReturn($result);
            }elseif($oper == 2){ //查顶级分类
                if($type === '')$this->error('参数错误');
                $where = 'pid is null and type ='.$type;
                $result = $this->m2('category')->where($where)->field('id,name')->select();
                $this->ajaxReturn($result);
            }
        }

        $data = $this->m2('category')->where('pid is null')->field('id,type,name')->select();
        foreach($data as $row){
            if($row['type']==0)$tips_category[] = $row;
            if($row['type']==1)$goods_category[] = $row;
            if($row['type']==2)$article_category[] = $row;
            if($row['type']==3)$apply_category[] = $row;
            if($row['type']==4)$kitchen_category[] = $row;
        }

        $this->assign('tips_category',$tips_category);
        $this->assign('goods_category',$goods_category);
        $this->assign('article_category',$article_category);
        $this->assign('apply_category',$apply_category);
        $this->assign('kitchen_category',$kitchen_category);
        $this->view();
    }

    //修改分类
    public function modifyCategory(){
        if(IS_AJAX){
            $id = I('post.id');
            $oper = I('post.oper');
            $data = I('post.data');

            if($oper == 0){ //删除
                if(in_array($id,[1,2,3,18]))$this->error('该分类禁止删除');
                $this->m2('category')->where(['id'=>$id])->delete();
                $this->success('删除成功');
            }elseif($oper == 1){ //更新
                $this->m2('category')->data($data)->save();
                $this->success('更新成功');
            }elseif($oper == 2){ //新增
                $data['pid'] = null;
                $this->m2('category')->data($data)->add();
                $this->success('添加成功');
            }
        }
    }

    //主题列表
    public function researchList(){
        $this->actname = '主题列表';

        if(IS_AJAX && IS_POST){
            if($_POST['postType'] == 'addSearch'){
                $name = I('post.name');
                $count = $this->m2('category')->where('type=6 and pid is null')->count();
                $num = $count + 1;
                $data=[
                    'type' => 6,
                    'name' => $name,
                    'sign' => 'research_'.$num,

                ];

                $category_id = $this->m2('category')->add($data);
                if(!empty($category_id)){
                    $this->success('添加成功');
                }else{
                    $this->error('添加失败');
                }
            }elseif($_POST['postType'] == 'UpdateSearch' && !empty($_POST['id'])){
                $title = I('post.title');
                $id = I('post.id');
                $data=[
                    'name' => $title,
                ];

                $saveData = $this->m2('category')->where(['id'=>$id])->save($data);
                if(!empty($saveData)){
                    $this->success('修改成功');
                }else{
                    $this->error('修改失败');
                }
            }
        }

        $datas['datas'] = $this->m2('Category')->where('type=6 and pid is null')->field('id,type,name')->page(I('get.page'), 20)->order('id desc')->select();
        foreach($datas['datas'] as $key =>$val){
            $datas['datas'][$key]['name'] = '<span style=" width:100%; display: block;" onclick="updateSearch(this,'.$val['id'].')"><font>'.$val['name'].'</font></span>';
        }
        //table页面参数设置
        $datas['operations'] = [
            '修改' => "location.href='research_detail.html?category_id=%id'",
            '提交问卷列表' => "location.href='answerlist.html?category_id=%id'",
        ];
        $datas['pages'] = [
            'sum' => $this->m2('Category')->where('type=3 and pid is null')->count(),
            'count' => 20,
        ];
        $datas['lang'] = [
            'id' => 'ID',
            'name' => '标题',
        ];

        $this->assign($datas);
        $this->view();
    }

    //调查问答修改
    public function research_detail(){
        if(IS_AJAX && IS_POST){
            if($_POST['TypeName'] == 'addsubject'){
                $category_id = I('post.category_id');
                $subject = I('post.subject');
                $option_type = I('post.option_type');
                $count = $this->m2('apply')->where('pid is null AND category_id='.$category_id)->count();
                $num = $count + 1;
                $data = array(
                    'category_id' => $category_id,
                    'type' => $option_type,
                    'content' => $subject,
                    'value' => 'item_'.$num,
                    'pid' => null,
                    'sort ' => $num,

                );
                $subject_id = $this->m2('apply')->add($data);
                if(!empty($subject_id)){
                    $this->success('添加题目成功');
                }else{
                    $this->error('添加题目失败');
                }
            }elseif($_POST['TypeName'] == 'SaveData' && !empty($_POST['subject'])){
                $subject_title = $_POST['subject'];
                $subject_ids = [];
                foreach($subject_title as $key => $val){
                    $data['category_id'] = $val['category_id'];
                    $data['content'] = $val['content'];
                    $data['value'] = 'item_'.$val['sortNum'];
                    $data['sort'] = $val['sortNum'];
                    $data['type'] = $val['type'];
                    if(empty($val['id'])){
                        $subject_ids[] = $this->m2('apply')->add($data);
                    }else{
                        $subject_ids[] = $val['id'];
                        $this->m2('apply')->where(['id'=>$val['id']])->save($data);
                        $arr = $this->m2('apply')->where(['pid'=>$val['id'],'is_show'=>1])->select();
                        foreach($arr as $k=>$v){
                            $_data = [
                                'value'=>$data['value'].'_'.($k+1),
                                'sort'=>($k+1),
                            ];
                            $this->m2('apply')->where(['id'=>$v['id']])->save($_data);
                        }
                        $this->m2('apply')->where(['id'=>$val['id']])->save($data);
                    }
                }
                $this->m2('apply')->where(['id' => ['NOT IN', join(',', $subject_ids)],'pid'=>['EXP','IS NULL']])->save(['is_show'=>0]);
                $this->m2('apply')->where(['pid' => ['NOT IN', join(',', $subject_ids)]])->save(['is_show'=>0]);
                $this->success('保存题目列表成功');
            }elseif($_POST['TypeName'] == 'add_option' && !empty($_POST['id'])){
                $id = I('post.id');
                $category_id = I('post.category_id');
                $data = $this->m2('apply')->where(['pid'=>$id,'category_id'=>$category_id,'is_show'=>1])->select();
                if(!empty($data)) {
                    foreach ($data as $k => $v) {
                        $data[$k]['option_c'] = '<span style=" width:100%; display: block;" onclick="update(this)"><font>'.$v['content'].'</font></span>';
                    }
                }
                $this->ajaxReturn($data);
            }elseif($_POST['TypeName']=='SaveDataOption'){
                $pid = I('post.pid');
                $category_id = I('post.category_id');
                $option_title = I('post.option');
                $option_ids = [];
                $rs = $this->m2('Apply')->where(['id'=>$pid,'is_show'=>1])->find();
                foreach($option_title as $key => $val){
                    $data['category_id'] = $val['category_id'];
                    $data['content'] = $val['content'];
                    $data['value'] = $rs['value'].'_'.$val['sortNum'];
                    $data['type'] = $val['is_true'];
                    $data['sort'] = $val['sortNum'];
                    $data['pid'] = $pid;
                    $data['is_show'] = 1;
                    $data['category_id'] = $category_id;
                    if(empty($val['id'])){
                        $option_ids[] = $this->m2('apply')->add($data);
                    }else{
                        $option_ids[] = $val['id'];
                        $this->m2('apply')->where(['id'=>$val['id']])->save($data);
                    }
                }
                $this->m2('apply')->where(['category_id' => $category_id,'pid' => $pid,'id' => ['NOT IN', join(',', $option_ids)]])->save(['is_show'=>0]);
                $this->success('保存题目列表成功');

            }
        }
        $category_id = I('get.category_id', null);
        $datas['datas'] = $this->m2('apply')->where(['pid'=>['exp','is null'],'category_id'=>$category_id,'is_show'=>1])->order('sort asc ,id asc')->select();
        $code ='<table id="subject" class="am-table am-table-striped am-table-hover"><thead><tr><th>选项序号</th><th>选项内容</th><th>操作</th></tr></thead><tbody>';
        foreach($datas['datas'] as $key =>$val){
            $code .='<tr>';
            $code .='<td><input type="text" name="sortNum" value="'.($key+1).'" style="border:none; background:none; width:50px;"><input name="id" type="hidden" value="'.$val['id'].'"><input name="type" type="hidden" value="'.$val['type'].'"><input name="category_id" type="hidden" value="'.$val['category_id'].'"></td>';
            $code .='<td><span style=" width:100%; display: block;" onclick="update(this)"><font>'.$val['content'].'</font></span></td>';
            $code .='<td><button class="am-btn am-btn-primary am-btn-xs" type="button" onclick="shift_up(this)">上移</button><button class="am-btn am-btn-primary am-btn-xs" type="button" onclick="shift_down(this)">下移</button><button class="am-btn am-btn-warning am-btn-xs" type="button" onclick="$(this).parent().parent().remove()">删除</button>';
            if($val['type']==1){
                $code .='<button class="am-btn am-btn-success am-btn-xs" type="button" onclick="add_option('.$val['category_id'].','.$val['id'].')">添加选项</button>';
            }
            $code .='</td></tr>';
        }
        $code .='</tbody></table>';

        $this->assign('category_id',$category_id);
        $this->assign('code',$code);
        $this->view();
    }

    //提交问卷列表
    public function answerlist(){
        $this->actname = "提交问卷列表";
        $category_id  = I('get.category_id');
        if(empty($category_id)) $this->error('非法访问！');
        $datas['datas'] = D('ResearchAnswerView')->where('type=6 and type_id = '.$category_id)->group('A.id')->order('A.id desc')->select();
        $item_arr = $this->m2('apply')->where(['category_id'=>$category_id,'is_show'=>1])->getField('id,value,content,pid',true);
        $lang =[];
        foreach($datas['datas']  as $key => $val){
            $answer_arr = $this->m2('apply_answer')->where(['member_apply_id'=>$val['id']])->select();
            foreach($item_arr as $row){
                if(empty($row['pid'])){
                    $datas['datas'][$key][$row['value']] = '';
                    $lang[$row['value']] = $row['content'];
                }
            }
            foreach($answer_arr as $an_row){
                foreach($item_arr as $row){
                    if($an_row['ask_id'] == $row['id'] && empty($row['pid']) && empty($row['answer_id'])){
                        $datas['datas'][$key][$row['value']] = $an_row['content'];
                    }
                    if($an_row['answer_id'] == $row['id'] && $an_row['ask_id'] == $row['pid']){
                        $value = $this->m2('apply')->where(['id'=>$row['pid']])->getField('value');
                        $datas['datas'][$key][$value] = $row['content'];
                        $datas['datas'][$key][$row['pid']] = $row['content'];
                    }
                }

            }
        }
        $datas['pages'] = [
            'sum' => D('ResearchAnswerView')->where('type=6 and type_id = '.$category_id)->group('A.id')->order('A.id desc')->count(),
            'count' => 20,
        ];
        $datas['lang'] = array_merge(['id' => 'ID','nickname' => '昵称','datetime'=>'提交时间'],$lang);
        $this->assign($datas);
        $this->assign('category_id',$category_id);


        $this->view();
    }

    //提交问卷反馈列表导出
    public function ResearchExport(){

        $category_id  = I('get.category_id');
        if(empty($category_id)) $this->error('非法访问！');
        $datas = D('ResearchAnswerView')->where('type=6 and type_id = '.$category_id)->group('A.id')->order('A.id desc')->select();
        $item_arr = $this->m2('apply')->where(['category_id'=>$category_id,'is_show'=>1])->field('value,id,content,pid')->select();
        $lang =[];
        foreach($datas as $key => $val){
            $answer_arr = $this->m2('apply_answer')->where(['member_apply_id'=>$val['id']])->select();
            foreach($item_arr as $ke =>$row){

                if(empty($row['pid'])){
                    $datas[$key][$row['value']] = '';
                    $lang[$ke] = $row['content'];
                }
            }
            foreach($answer_arr as $an_row){
                foreach($item_arr as $row){
                    if($an_row['ask_id'] == $row['id'] && empty($row['pid']) && empty($row['answer_id'])){
                        $datas[$key][$row['value']] = $an_row['content'];
                    }
                    if($an_row['answer_id'] == $row['id'] && $an_row['ask_id'] == $row['pid']){
                        $value = $this->m2('apply')->where(['id'=>$row['pid']])->getField('value');
                        $datas[$key][$value] = $row['content'];
                    }
                }

            }
            unset($datas[$key]['member_id']);
        }
        $data = [];
        foreach($datas as $k=> $rr){
            $data[$k]=$rr;
        }
        $title = array_merge(['ID','提交时间','昵称'],$lang);
        toXls($title,$data,'提交问卷反馈列表');
    }

    //早餐打卡记录
    public function breakfastDiary(){
        $this->actname = "早餐打卡记录";
        if(isset($_GET['status']) && is_numeric($_GET['status'])){
            $condition = "A.status = " . $_GET['status'];
        }else{
            $condition = "A.status = 1 ";
        }
        if(IS_GET && $_GET!=null) {                      //条件查询
            $nickname = I('get.nickname');
            $status = I('get.status');
            $start_time = I('get.start_time');
            $stop_time = I('get.stop_time');
            if(!empty($nickname))$condition .=' AND C.nickname LIKE "%'.$nickname.'%"';
            if($start_time && $stop_time){
                $condition .=' AND A.datetime >="'.$start_time.'" AND A.datetime <="'.$stop_time.'"';
            }else{
                if(!empty($start_time))$condition .=' AND A.datetime >="'.$start_time.'"';
                if(!empty($stop_time))$condition .=' AND A.datetime <="'.$stop_time.'"';
            }
            $this->assign('search_nickname',$nickname);
            $this->assign('search_start_time',$start_time);
            $this->assign('search_stop_time',$stop_time);
            $this->assign('search_status',$status);
        }
        $researchList = D('SignView')->where($condition)->page(I('get.page'), 20)->select();
//        print_r(D('SignView')->getLastSql());
//        exit;
        foreach($researchList as $k =>$v){

            $datas['datas'][$k]['id'] = $v['sign_id'];
            $datas['datas'][$k]['nickname'] = $v['nickname'];
            $datas['datas'][$k]['head_path'] = '<img src='. thumb($v['head_path'],2).' style="width:50px;"/>';
            if($v['sex'] == 0)$datas['datas'][$k]['sex'] = '未设置';
            if($v['sex'] == 1)$datas['datas'][$k]['sex'] = '男';
            if($v['sex'] == 2)$datas['datas'][$k]['sex'] = '女';
            $datas['datas'][$k]['citys_name'] =  !empty($v['city_id'])?$this->m2('citys')->where(['id'=>$v['city_id']])->getField('name'):'未设置';
            $datas['datas'][$k]['title'] = $v['title'];
            $datas['datas'][$k]['sign_path'] = '<img src='. thumb($v['sign_path'],2).' style="width:80px;"/>';
            $datas['datas'][$k]['datetime'] = $v['datetime'];
            $datas['datas'][$k]['status'] =  $v['status'] == 1 ? '正常':'删除';
        }
        //table页面参数设置
        $datas['operations'] = [
            '删除' => [
                'condition' => "%status=='正常'",
                'style' => 'danger',
                'fun' => "DelSign(%id)"
            ],
            '正常' => [
                'condition' => "%status=='删除'",
                'style' => 'success',
                'fun' => "JoinSign(%id)"
            ]
        ];
        $datas['pages'] = [
            'sum' => D('SignView')->where($condition)->count('DISTINCT A.id'),
            'count' => 20,
        ];
        $datas['lang'] = [
            'id' => 'ID',
            'nickname' =>'昵称',
            'head_path' =>'头像',
            'sex' =>'性别',
            'citys_name' => '城市',
            'title' => ['签到内容','', '10%'],
            'sign_path' => '签到图片',
            'datetime' => '签到时间',
            'status' => '状态'
        ];
        $this->assign($datas);

        $this->view();
    }

    //早餐打卡记录导出
    public function SignExport(){
        if(isset($_GET['status']) && is_numeric($_GET['status'])){
            $condition = "A.status = " . $_GET['status'];
        }else{
            $condition = "A.status = 1 ";
        }
        if(IS_GET && $_GET!=null) {                      //条件查询
            $nickname = I('get.nickname');
            $status = I('get.status');
            $start_time = I('get.start_time');
            $stop_time = I('get.stop_time');
            if(!empty($nickname))$condition .=' AND C.nickname LIKE "%'.$nickname.'%"';
            if($start_time && $stop_time){
                $condition .=' AND A.datetime >="'.$start_time.'" AND A.datetime <="'.$stop_time.'"';
            }else{
                if(!empty($start_time))$condition .=' AND A.datetime >="'.$start_time.'"';
                if(!empty($stop_time))$condition .=' AND A.datetime <="'.$stop_time.'"';
            }
        }
        $data = D('SignExportView')->where($condition)->order('A.datetime desc')->select();
        //数据处理
        $_data =[];
        foreach($data as $key=>$row){
            $_data[$key]['nickname'] = $row['nickname'];
            if($row['sex'] == 0)$_data[$key]['member_info_sex'] = '未设置';
            if($row['sex'] == 1)$_data[$key]['member_info_sex'] = '男';
            if($row['sex'] == 2)$_data[$key]['member_info_sex'] = '女';
            $_data[$key]['title'] = $row['title'];
            $_data[$key]['sign_path'] = ['<img src="'.thumb($row['sign_path']).'" height="20%"/>','100px','200px'];
            $_data[$key]['sign_path_link'] = thumb($row['sign_path']);
            $_data[$key]['datetime'] = $row['datetime'];


        }
        $titleArr = ['微信名','性别','文字',['图片','200px'],['图片链接','200px'],'上传时间'];
        toXls($titleArr,$_data,'早餐打卡记录数据');
    }

    //早餐打卡操作
    function operate(){
        $id = I('post.id');
        $oper = I('post.oper');
        $rs = $this->m2('sign')->where('id='.$id)->find();
        if(!empty($rs)){
            //删除签到
            if($oper == 1){
                if($rs['status'] == 1){
                    $this->m2('sign')->where('id='.$id)->save(['status'=>2]);
                    $this->success('该记录删除成功');
                }else{
                    $this->error('该记录已删除了');
                }
            }elseif($oper == 2){//恢复签到
                $start_time = date('Y-m-d 00:00:00:',$rs['datetime']);
                $end_time = date('Y-m-d 23:59:59:',$rs['datetime']);
                $count = $this->m2('sign')->where('datetime >= "'.$start_time.'" AND datetime <= "'.$end_time.'" AND status = 1')->count();
                if($count>0){
                    $this->error('该签到所记录的时间已经存在一条或者多条了，不能再恢复了');
                }else{
                    $this->m2('sign')->where('id='.$id)->save(['status'=>1]);
                    $this->success('恢复成功！');
                }
            }
        }else{
            $this->error('非法访问');
        }
    }

    //领取列表
    public function receive_list(){
        $this->actname = '领取特权列表';
        $condition ='1=1';
        if(IS_GET && $_GET!=null){
            $type = I('get.type');
            $status = I('get.status');
            if($type != '') $condition .= ' AND B.type = '.$type;
            if($status != '') $condition .= ' AND A.status = '.$status;
            $this->assign('search_type',$type);
            $this->assign('search_status',$status);
        }
        if(IS_AJAX && IS_POST){
            if(!empty(I('post.id')) && $_POST['TypeName'] == 'dataDelete'){
                $id = I('post.id');
                $rs = $this->m2('member_privilege')->where(['id'=>$id])->find();
                if(!empty($rs)){
                    $this->m2('member_privilege')->where(['id'=>$id])->save(['status'=>0]);
                    $this->success('删除成功！');
                }else{
                    $this->success('删除失败！');
                }
            }elseif(!empty(I('post.id')) && $_POST['TypeName'] == 'recovery'){
                $id = I('post.id');
                $rs = $this->m2('member_privilege')->where(['id'=>$id])->find();
                if(!empty($rs)){
                    $this->m2('member_privilege')->where(['id'=>$id])->save(['status'=>1]);
                    $this->success('恢复成功！');
                }else{
                    $this->success('恢复失败！');
                }

            }
        }

        $datas['datas'] = D('ReceivePrivilegeView')->where($condition)->page(I('get.page'), 20)->group('A.id')->order('A.id desc')->select();
        foreach($datas['datas'] as $key=>$row){
            $datas['datas'][$key]['start_time'] = $row['start_time']?date('Y-m-d H:i:s',$row['start_time']): '00-00-00 00:00:00';
            $datas['datas'][$key]['end_time'] = $row['end_time']?date('Y-m-d H:i:s',$row['end_time']):'00-00-00 00:00:00';
            $datas['datas'][$key]['order_status'] = $row['order_id']?'已使用':'未使用';
        }
        //table页面参数设置
        $datas['operations'] = [
            '删除' => [
                'condition' => "%privilege_status!=0 && %order_status == '未使用'",
                'style' => 'danger',
                'fun' => "dataDelete(%id)"
            ],
            '恢复' => [
                'condition' => "%privilege_status ==0",
                'style' => 'success',
                'fun' => "recovery(%id)"
            ]
        ];
        $datas['pages'] = [
            'sum' => D('ReceivePrivilegeView')->where($condition)->count('DISTINCT A.id'),
            'count' => 20,
        ];
        $datas['lang'] = [
            'id' => 'ID',
            'receiver' => '领取人',
            'originator' => '分发人',
            'privileg_title' => '标题',
            'privileg_times_title' => '档位标题',
            'start_time' => '开始时间',
            'end_time' => '截止时间',
            'order_id' => '使用的订单ID',
        ];
        $this->assign($datas);
        $this->view();

    }

    public function getHomeList() {
        $this->actname = '首页管理';
        $where = [];
		$where[] = 'A.status=1';
		$where[] = 'A.is_pass=1';
		$where[] = 'is_public=1';
		
		// 区域选择
		$city = I('get.city', 224);
		if (empty($city)) {
			$citys[] = 224;
		} else {
			$citys[] = $city;
		}
//		$where[] = "F.citys_id in (". join(',', $citys) .")";

		// $tags = M('TipsTag')->field('tips_id')->where(['tag_id' => ['not in', [76]]])->buildSql();
		// $where[] = "A.id in " . $tags;

		$t = $this->m2('Home')->where(['type' => 0])->getField('t_id', true);
		if (!empty($t)) {
			$where[] = "A.id in (" . join(',', $t) .")";
			$where = join(' and ', $where);

			$data = D('TipsView')->where($where)->order('L.weight desc')->select();
		} else {
			$data = array();
		}

		$_data = [];
		foreach($data as $row){

			$_data[] = [
				'id' => $row['id'],
				'nickname' => $row['nickname'],     
				'member_id' => $row['member_id'],
				'mainpic' => $row['path'],
				'catname' => $row['catname'],
				'title' => $row['title'],
                'weight' => $row['weight'],
                'path' => thumb($row['path'], 1),
				'type' => 0,
                'type_name' => '活动',
                'city_name' => C('CITY_CONFIG')[$row['tips_sub_citys_id']]
			];
		}

		// // 众筹
		$r = $this->m2('Home')->where(['type' => 1])->getField('r_id', true);
		$data2 = [];
		
		if(!empty($r)){
			$where2[] = "A.status = 1";
			$where2[] = "A.id in (" . join(',', $r) .")";
			$where2 = join(' and ', $where2);

			$rs2 = D('RaiseHomeView')->where($where2)->order('F.weight desc')->group('id')->select();
			foreach($rs2 as $row){
				$row['type'] = 1;
                $row['type_name'] = '众筹';
				$row['path'] = thumb($row['path'], 1);
				$data2[] = $row;
			}
		}
		

		$result = array_merge($_data);

        $datas['operations'] = [
            '删除' => [
                'style' => 'danger',
                'fun' => 'deleteFromHome(%id, %type, \'%title\')'
            ],
            '编辑' => 'edit(%id, %type)',
            '上移' => [
                'style' => 'secondary',
                'fun' => "shift(%id,%type,'shift_up')",
            ],
            '下移' => [
                'style' => 'primary',
                'fun' => "shift(%id,%type,'shift_down')",
                'condition' => '%weight > 0'
            ],
        ];

        $datas['lang'] = [
            'id' => ['预览', '<input type="hidden" value="%*%" > <a><i class="am-icon-eye" onclick="preview(%*%)"></i></a>'],
            'title' => '标题',
            'city_name' => '城市',
            'type_name' => '类型',
            'weight' => '权重'
        ];

        $datas['datas'] = $result;
        $this->assign($datas);

        $this->view();
    }

}