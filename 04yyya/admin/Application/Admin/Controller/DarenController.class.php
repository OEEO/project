<?php
namespace Admin\Controller;
use Admin\Controller\MainController;

class DarenController extends MainController
{
    Protected $pagename = '达人管理';

    public function index(){
        $this->actname = '达人列表';
        $pageSize = 20;

        //条件查询
        $condition = array();

        $member_nickname = I('get.member');
        $member_telephone = I('get.telephone');
        $member_tag = I('get.tag');

        if($member_telephone!='')$condition['B.telephone'] = array('LIKE','%'.$member_telephone.'%');
        if($member_nickname!='')$condition['B.nickname'] = array('LIKE','%'.$member_nickname.'%');
        if($member_tag != ''){
            $member_id = $this->m2('Tag')->join('__MEMBER_TAG__ ON __TAG__.id=__MEMBER_TAG__.tag_id')->where(['ym_tag.id'=>$member_tag])->getField('member_id',true);
            $condition['A.member_id'] = array('IN',join(',',$member_id));
        }

        if($member_telephone!='')$this->assign('search_member',$member_nickname);
        if($member_nickname!='')$this->assign('search_telephone',$member_telephone);
        if($member_tag!='')$this->assign('search_tag',$member_tag);

        //$condition['status'] = array('NEQ','0');
        $datas['datas']= D('DarenListView')->where($condition)->page(I('get.page'), $pageSize)->order('A.id desc')->select();
        foreach($datas['datas'] as $key=>$row){
            $datas['datas'][$key]['member_register_time'] = date('Y-m-d H:i',$row['member_register_time']);
            $member_ids[] = $datas['datas'][$key]['member_id'];
        }
        if(!empty($member_ids)){
            $tag = $this->m2('Tag')->join('__MEMBER_TAG__ ON __TAG__.id=__MEMBER_TAG__.tag_id')->where(['type'=>3,'member_id'=>['IN',join(',',$member_ids)]])->field('member_id,name')->select();
        }

        //数据&图片路径处理
        foreach($datas['datas'] as $key=>$row){
            $datas['datas'][$key]['pics_path'] = "<img src='".pathFormat($row['pics_path'])."' width='50px' height='50px'/>";
            /*if(substr($row['pics_path'],0,4) == 'http'){
                $datas['datas'][$key]['pics_path'] = "<img src='{$row['pics_path']}' width='50px' height='50px'/>";
            }elseif(strpos($row['pics_path'],'upload')!==false){
                $datas['datas'][$key]['pics_path'] = "<img src='".OLD_IMG_PATH.$row['pics_path']."' width='50px' height='50px'/>";
            }else{
                $datas['datas'][$key]['pics_path'] = "<img src='".NEW_IMG_PATH.$row['pics_path']."' width='50px' height='50px' />";
            }*/
            if(!empty($tag)){
                foreach($tag as $r){
                    if($r['member_id'] == $row['member_id']){
                        $datas['datas'][$key]['tags'] .= $r['name'].',';
                    }
                }
                if(empty($datas['datas'][$key]['tags']))$datas['datas'][$key]['tags'] = '0';
            }else{
                $datas['datas'][$key]['tags'] = '0';
            }


            $datas['datas'][$key]['tags'] = rtrim($datas['datas'][$key]['tags'],',');
            //echo $datas['datas'][$key]['tags'].'<br/>';
        }
        //var_dump(strpos('主厨达人,美食达人','明星达人'));
        //print_r($datas['datas']);exit;
        //$datas['datas'] =$ss;
        $datas['operations'] = [
            //'详细信息' => "detailInfo(%member_id)",
            //'（修改中）达人身份管理' => "deleteLabel(%member_id)",

//            '设为明星' => [
//                'style' => 'success',
//                'fun' => 'set_star(%member_id,1)',
//                'condition' => "strpos('%tags' , '明星达人')===false"
//            ],
//            '取消明星' => [
//                'style' => 'danger',
//                'fun' => 'set_star(%member_id,0)',
//                'condition' => "strpos('%tags' , '明星达人')>0"
//            ],
            '详细信息' => [
                'style' => 'success',
                'fun' => 'detailInfo(%member_id)'
            ],
            '设置银行卡' => [
                'style' => 'primary',
                'fun' => 'setBank(%member_id)'
            ]
        ];

            /*'撤销美食达人' => array(
                'style' => 'danger',
                'fun' => 'not_star(%member_id)',
                'condition' => "%status == '美食达人'"
            ),

            '撤销主厨达人' => array(
                'style' => 'danger',
                'fun' => 'not_cooker(%member_id)',
                'condition' => "%status == '主厨达人'"
            ),

            '撤销美食主厨达人' => array(
                'style' => 'danger',
                'fun' => 'not_cooker(%member_id)',
                'condition' => "%status == '美食主厨达人'"
            )*/

        //);
        $datas['pages'] = [
            'sum' => D('DarenListView')->where($condition)->count(),
            'count' => $pageSize
        ];
        $datas['lang'] = [
            'member_id' => '用户ID',
            'member_nickname' => '用户名称',
            'member_telephone' => '手机号',
            'pics_path' => '头像',
            'member_openid' =>'openID',
            //'status' => '状态',
            'tags' => '达人标签',
            'member_register_time'=>'注册时间'
        ];


        //统计达人
        $daren_count = $this->m2('MemberDaren')->count();
        $this->assign('daren_count',$daren_count);
        //统计美食达人人数
        $food_daren_count = $this->m2('MemberTag')->where(['tag_id'=>C('DAREN_LABEL.0')])->count();
        $this->assign('food_daren_count',$food_daren_count);
        //统计主厨达人人数
        $cooker_daren_count = $this->m2('MemberTag')->where(['tag_id'=>C('DAREN_LABEL.1')])->count();
        $this->assign('cooker_daren_count',$cooker_daren_count);

        $tag_list = $this->m2('tag')->field('id,name')->where(['type'=>['IN','0,3'],'official'=>2])->select();
        $this->assign('tag_list',$tag_list);

        //读取银行卡列表
        $banks = $this->m2('bank')->select();
        $this->assign('banks', $banks);

        $this->assign($datas);
        $this->view();
    }

    public function request(){
        $this->actname = '申请列表';
        $pageSize = 20;

        //条件查询
        $condition = '';
        if(IS_GET && $_GET!=null){
            $name = I('get.member');
            $name && $condition['nickname'] = array('LIKE','%'.$name.'%');
            $this->assign('search_member',$name);
        }

        $condition['A.type']=2;
        $datas['datas'] = D('MemberDetailView')->where($condition)->order('A.id desc')->page(I('get.page', 1), $pageSize)->select();

        //图片路径及其他数据处理
        foreach($datas['datas'] as $key=>$row){
            $apply_ids[] = $row['apply_id'];
            $datas['datas'][$key]['path'] = '<img src="'.pathFormat($datas['datas'][$key]['path']).'" width="50px" height="50px"/>';
        }
        $apply_ids = join(',',$apply_ids);
        if(!empty($apply_ids)){
            $apply = D('ApplyTypeView')->where(['A.id'=>['IN',$apply_ids]])->select();
        }
        //var_dump($apply);exit;
        $channel = C('CHANNEL');
        foreach($datas['datas'] as $key=>$row){
            foreach($apply as $row2){
                if($row2['apply_id'] == $row['apply_id']){
                    $datas['datas'][$key]['apply_type'] = $row2['apply_type'];
                    $datas['datas'][$key]['category_id'] = $row2['category_id'];
                }
            }

            if($row['is_pass'] == 1){
                $datas['datas'][$key]['_is_pass'] = '通过';
            }elseif($row['is_pass'] == 2){
                $datas['datas'][$key]['_is_pass'] = '拒绝';
            }elseif($row['is_pass'] == 0){
                $datas['datas'][$key]['_is_pass'] = '未操作';
            }

            $datas['datas'][$key]['channel'] = $channel[$row['channel']];
        }


        $datas['operations'] = array(
            '详细信息' => "detailInfo(%apply_id)",
            //'通过' => "pass(%apply_id,%category_id)",
            '通过' => array(
                'style' => 'success',
                'fun' => 'pass(%apply_id,%category_id)',
                'condition' => "%is_pass == 0"
                ),
            //'拒绝' => "refuse(%apply_id,%category_id)",
            '拒绝' => array(
                'style' => 'danger',
                'fun' => 'refuse(%apply_id,%category_id)',
                'condition' => "%is_pass == 0"
            ),
        );
        $datas['pages'] = array(
            'sum' => D('MemberDetailView')->where($condition)->count(),
            'count' => $pageSize
        );
        $datas['lang'] = array(
            'nickname' => '用户名称',
            'path' => '头像',
            'telephone' => '联系方式',
            'channel' => '申请渠道',
            '_is_pass' => '是否通过',
            'datetime' => '申请时间'
        );
        $this->assign($datas);
        $this->view();
    }


    //设置明星达人
    public function set_star(){
        $member_id = I('post.member_id');
        $oper = I('post.oper');

        if(IS_AJAX){
            if($oper==1){
                //36--明星达人
                $this->m2('MemberTag')->data(['member_id'=>$member_id,'tag_id'=>36])->add();
            }elseif($oper==0){
                $this->m2('MemberTag')->where(['member_id'=>$member_id,'tag_id'=>36])->delete();
            }
            $this->success('操作成功');
        }else{
            $this->error('非法访问');
        }

    }

    //删除达人标签
    public function deleteLabel(){
        if(IS_AJAX){
            $member_id = I('post.member_id',null);
            $oper = I('post.oper',null);
            $label_id = I('post.label_id',null); //数组格式

            if($oper == 1){ //查找标签
                $all_label = $this->m2('Tag')->where(['type'=>3])->field('id,name')->select();
                $my_label = $this->m2('MemberTag')->join('__TAG__ on __MEMBER_TAG__.tag_id=__TAG__.id')->where(['member_id'=>$member_id,'type'=> 3])->field('tag_id,name')->select();
                $data['all'] = $all_label;
                $data['my'] = $my_label;
                $this->ajaxReturn($data);
            }
            if($oper == 2){ //删除操作
                $label_id = join(',',$label_id);
                $this->m2('MemberTag')->where(['member_id'=>$member_id,'tag_id'=>['IN',$label_id]])->delete();
                $this->success('标签删除成功！');
            }
        }
        $this->error('非法访问！');
    }
    /*//美食达人操作-tag_id=27
    public function Star(){
        if(IS_AJAX){
            $id = I('post.id');
            $oper = I('post.oper');

            if($oper == '0'){
                $data = array();
                $data['status'] = 1;
                $this->m2('member_daren')->where('member_id='.$id)->data($data)->save();
                $this->m2('member_tag')->where(['member_id'=>$id,'tag_id'=>27])->delete();
                $this->success('撤销成功');
            }
            if($oper == '1'){
                $data = array();
                $data['status'] = 2;
                $this->m2('member_daren')->where('member_id='.$id)->data($data)->save();
                $this->m2('member_tag')->data(['member_id'=>$id,'tag_id'=>27])->add();
                $this->success('添加成功');
            }
        }else{
            $this->error('非法访问');
        }
    }
    //主厨达人操作-tag_id=18
    public function cooker(){
        if(IS_AJAX){
            $id = I('post.id');
            $oper = I('post.oper');

            if($oper == '0'){
                $data = array();
                $data['status'] = 1;
                $this->m2('member_daren')->where('member_id='.$id)->data($data)->save();
                $this->m2('member_tag')->where(['member_id'=>$id,'tag_id'=>18])->delete();
                $this->success('撤销成功');
            }
            if($oper == '1'){
                $data = array();
                $data['status'] = 3;
                $this->m2('member_daren')->where('member_id='.$id)->data($data)->save();
                $this->m2('member_tag')->data(['member_id'=>$id,'tag_id'=>18])->add();
                $this->success('添加成功');
            }
        }else{
            $this->error('非法访问');
        }
    }

    //美食主厨达人操作tag_id=33
    public function starCooker(){
        if(IS_AJAX){
            $id = I('post.id');
            $oper = I('post.oper');

            if($oper == '0'){
                $data = array();
                $data['status'] = 1;
                $this->m2('member_daren')->where('member_id='.$id)->data($data)->save();
                $this->m2('member_tag')->where(['member_id'=>$id,'tag_id'=>33])->delete();
                $this->success('撤销成功');
            }
            if($oper == '1'){
                $data = array();
                $data['status'] = 4;
                $this->m2('member_daren')->where('member_id='.$id)->data($data)->save();
                $this->m2('member_tag')->data(['member_id'=>$id,'tag_id'=>33])->add();
                $this->success('添加成功');
            }
        }else{
            $this->error('非法访问');
        }
    }*/

    public function vip(){
        if(IS_AJAX){
            $apply_id = I('post.id');
            $category_id = I('post.category_id');
            $reason = I('post.reason');
            $oper = I('post.oper');

            if($oper == '0'){
                $data = [
                    'id'=>$apply_id,
                    'refusal_reason'=>$reason,
                    'is_pass'=>2
                ];
                $this->m2('member_apply')->data($data)->save();
                $this->success('已拒绝');
            }
            if($oper == '1'){
                $applay = $this->m2('member_apply')->where(['id'=>$apply_id])->find();
                $member_id = $applay['member_id'];

                //添加标签
                $label_id = C("APPLY_CONF");
                $tag_id = $label_id[$category_id];
                $result = $this->m2('MemberTag')->where(['member_id'=>$member_id,'tag_id'=>$tag_id])->find();
                if(empty($result)){
                    $this->m2('MemberTag')->data(['member_id'=>$member_id,'tag_id'=>$tag_id])->add();
                }

                //memberDaren表
                $rs = D('ApplyAnswerView')->where(['apply_id' => $apply_id])->select();
                $data = [];
                $data['member_id'] = $member_id;
                foreach($rs as $row){
                    switch($row['ask_value']){
                        case 'city_id':
                            $data['city_id'] = $row['answer_value'];
                            break;
                        case 'job':
                            $data['job'] = $row['content'];
                            break;
                        case 'age':
                            $data['age'] = $row['answer_value'];
                            break;
                        case 'pic_group_id':
                            if(!empty($row['answer_id']))$data['pic_group_id'] = $row['answer_id'];
                            break;
                        case 'wechat':
                            $data['wechat'] = $row['content'];
                            break;
                        case 'site':
                            $data['site'] = $row['content'];
                            break;
                        case 'introduce':
                            $data['introduce'] = $row['content'];
                            break;
                    }
                }
                //个人介绍->达人简介
                $this->m2('member_daren')->data($data)->add();
                $data = [
                    'id'=>$apply_id,
                    'is_pass'=>1
                ];
                //申请表
                $this->m2('member_apply')->data($data)->save();
                if(in_array($applay['channel'], [7,8,9]))
                    $this->pushMessage($member_id,'祝贺您正式成为我有饭主人，快去完善个人资料发布新活动吧，您离100分的主人又近了一步','sms', 0, 0, 0, 1);
                else
                    $this->pushMessage($member_id,'祝贺您正式成为吖咪达人，快去完善个人资料发布新活动吧，你离100分的主人又近了一步','sms');
                $this->success('已成为达人');
            }
        }else{
            $this->error('非法访问');
        }
    }


    Public function applyDetail(){
        $apply_id = I('post.apply_id');

        if(empty($apply_id))$this->error('非法访问');

        $rs = D('ApplyAnswerView')->where(['apply_id' => $apply_id])->order('sort desc')->select();
        $data = [];
        foreach($rs as $row){
            if($row['type'] == 0)
                $answer = $row['content'];
            elseif($row['type'] == 1)
                $answer = $row['answer_content'];
            elseif($row['type'] == 2){
                $answer = '';
                if(!empty($row['answer_id'])){
                    $pic = $this->m2('pics')->where(['group_id' => $row['answer_id']])->getField('path');
                    $answer = '<img width="100" src="'. thumb($pic) .'" onclick="imgEnlarge(this)">';
                }
            }
            $data[] = [
                'question' => $row['ask_content'],
                'answer' => $answer
            ];
        }

        $this->ajaxReturn($data);
    }

    //获取达人信息
    public function getDarenInfo(){
        $member_id = I('post.member_id');

        if(empty($member_id))$this->error('未选择达人');
        if(IS_AJAX){
            $data = D('DarenInfoView')->where(['id'=>$member_id])->find();
            $data['birth'] = date('Y-m-d', $data['birth']);
            $data['path'] = thumb($data['path'] ,2);
            $data['cover_path'] = thumb($data['cover_path'], 7);
            $data['signature'] = strip_tags($data['signature']);
            $this->success($data);
        }else{
            $this->error('非法访问');
        }
    }

    //达人添加
    public function addDaren(){
        $data = $_POST['data'];

        if(empty($data['telephone']))$this->error('手机号不能为空!');
        //if(strlen($data['nickname'])<=0)$this->error('昵称不能为空');
        //if(empty($data['pic_id']))$this->error('头像不能为空');
        //if(empty($data['job']))$this->error('职业不能为空');
        //if(empty($data['age']))$this->error('年龄段不能为空');

        $member_data = [
            'username' => $data['telephone'],
            'nickname' => $data['nickname'],
            'pic_id' => $data['pic_id'],
            'telephone' => $data['telephone']
        ];
        $member_info_data = [
            'surname'=>$data['surname'],
            'sex'=>$data['sex']?:0,
            'birth'=>strtotime($data['birth'].' 00:00:00'),
            'signature'=>$data['signature'],
            'cover_pic_id'=>$data['cover_pic_id'],
            'citys_id'=>224
        ];
        //判断是否是现有达人
        $rs = $this->m2('member')->where(['telephone' => $data['telephone']])->order('id desc')->find();
        if(empty($rs)){
            $member_data['register_time'] = time();
            $member_id = $this->m2('Member')->add($member_data);
            $member_info_data['member_id'] = $member_id;
            $this->m2('MemberInfo')->add($member_info_data);
        }else{
            $member_id = $rs['id'];
            $tag_id = $this->m2('MemberTag')->where(['member_id' => $member_id])->getField('tag_id', true);
            if(in_array('18', $tag_id)){
                $this->error('该手机号所对应的会员已经是达人了,无需再添加!');
            }
            $this->m2('Member')->where(['id'=>$member_id])->save($member_data);
            $this->m2('MemberInfo')->where(['member_id'=>$member_id])->save($member_info_data);
        }

        //member_info数据保存
        $member_daren_data = [
            'member_id'=>$member_id,
            'age'=>$data['age'],
            'job'=>$data['job'],
            'city_id'=>$data['city_id']
        ];
        //member_daren
        $this->m2('MemberDaren')->where(['member_id'=>$member_id])->add($member_daren_data);
        $this->m2('MemberTag')->add(['member_id'=>$member_id,'tag_id'=>18]);

        $this->success('添加成功');
    }

    public function modifyDarenInfo(){
        $data = $_POST['data'];
        $member_id = $data['memberId'];

        if(strlen($data['nickname'])<=0)$this->error('昵称不能为空');
        if(empty($data['pic_id']))$this->error('头像不能为空');

        $member_data = array(
            'nickname' => $data['nickname'],
            'pic_id' => $data['pic_id']
        );
        $birth = strtotime($data['birth'].' 00:00:00');
        if($birth < 0 || $birth > time()){
        	$birth = 0;
        }
        $member_info_data = array(
            'surname'=>$data['surname'],
            'sex'=>$data['sex'],
            'birth'=>$birth,
            'signature'=>$data['signature'],
            'cover_pic_id'=>$data['cover_pic_id']
        );
        $member_daren_data = array(
            'age'=>$data['age'],
            'job'=>$data['job'],
        );

        //member
        $this->m2('Member')->data($member_data)->where(['id'=>$member_id])->save();
        //member_info数据保存
        $this->m2('MemberInfo')->data($member_info_data)->where(['member_id'=>$member_id])->save();
        //member_daren
        $this->m2('MemberDaren')->data($member_daren_data)->where(['member_id'=>$member_id])->save();

        $this->success('修改成功');
    }


    public function DarenExport(){

        $data = D('DarenExportView')->select();
        //数据处理
        foreach($data as $key=>$row){
            $data[$key]['birth'] = $row['birth']?date('Y-m-d H:i',$row['birth']):'未设置';
            $data[$key]['signature'] = preg_replace('/(&nbsp;|\r|\n|\t)+/', '', trim(strip_tags($row['signature'])));
            if($data[$key]['sex'] == 0)$data[$key]['sex'] = '保密';
            if($data[$key]['sex'] == 1)$data[$key]['sex'] = '男';
            if($data[$key]['sex'] == 2)$data[$key]['sex'] = '女';
            $data[$key]['register_time'] = date('Y-m-d H:i'.$row['register_time']);
        }

        $comma_data = $title = [];
        foreach($data as $row){
            $d = $title = array();
            foreach($row as $k => $r){
                $title[] = $k;
                $r = str_replace(',','，',$r);
                $d[] = iconv('utf-8','gb2312',$r);
            }
            $comma_data[] = join("\t", $d);
        }
        $excel_title = iconv('utf-8','gb2312','id'."\t".'昵称'."\t".'账号'."\t".'openid'."\t".'unionid'."\t".'注册时间'."\t".'真实姓名'."\t".'性别'."\t".'生日'."\t".'简介'."\t".'联系电话'."\t".'年龄'."\t".'职业'."\t".'城市');
        //$excel_title = iconv('utf-8','gb2312',join("\t",$title));
        $comma_data = $excel_title . "\n" . join("\n", $comma_data);
        //$comma_data = join(',', $title) . "\n" . join("\n", $comma_data);

        //header("Content-type:text/csv");
        header("Content-type:application/vnd.ms-excel");
        header("Content-Disposition:attachment;filename="."达人数据".date("Y-m-d",time()).".xls");
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
        header('Expires:0');
        header('Pragma:public');
        echo $comma_data;
        exit;
    }

    //绑定银行卡
    public function setBank(){
        $member_id = I('post.member_id');
        $bank_id = I('post.bank_id');
        $name = I('post.name');
        $number = I('post.number');
        $rs = $this->m2('MemberBank')->where(['member_id' => $member_id])->find();
        if(!empty($bank_id) && !empty($name) && !empty($number)){
            if(empty($rs)){
                $this->m2('MemberBank')->add([
                    'member_id' => $member_id,
                    'bank_id' => $bank_id,
                    'name' => $name,
                    'number' => $number
                ]);
            }else{
                $this->m2('MemberBank')->where(['id' => $rs['id']])->save([
                    'bank_id' => $bank_id,
                    'name' => $name,
                    'number' => $number
                ]);
            }
            $this->success('设置成功!');
        }
        $this->ajaxReturn($rs?:[]);
    }

}