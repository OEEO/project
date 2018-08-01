<?php
namespace Admin\Controller;
use Admin\Controller\MainController;

class MessageController extends MainController{
    Protected $pagename = '消息管理';

    public function MessageList(){
        $this->actname = '消息列表';

        $pageSize = 20;

        $datas['datas'] = D('MessageView')->where(['member_id' => ['EXP', 'IS NULL']])->page(I('get.page'), $pageSize)->order('id desc')->select();
        $message_type = array(0=>'普通消息',1=>'评论回复',2=>'达人动态',3=>'订单动态',4=>'活动推送',5=>'专题推送');
        $message_about = array(0 =>null,1=>null,2=>'bang_path',3=>'sn',4=>'tips_title',5=>'theme_title');
        foreach($datas['datas'] as $key => $row){
            if(empty($row['nickname']))$datas['datas'][$key]['nickname'] = '系统发送';
            $datas['datas'][$key]['type'] = $message_type[$row['type']];
            $datas['datas'][$key]['about'] = $row[$message_about[$row['type']]];
            if($row['type']==2)$datas['datas'][$key]['about'] = '<img src="'.pathFormat($datas['datas'][$key]['about']).'" width="100px" height="100px">';
            $datas['datas'][$key]['sms_send'] = ($row['sms_send']>0)?'<i class="am-icon-check am-btn-icon am-icon-sm am-success"></i>':'<i class="am-icon-close am-btn-icon am-icon-sm am-danger"></i>';
            $datas['datas'][$key]['wx_send'] = ($row['wx_send']>0)?'<i class="am-icon-check am-btn-icon am-icon-sm am-success"></i>':'<i class="am-icon-close am-btn-icon am-icon-sm am-danger"></i>';
            $datas['datas'][$key]['ios_push'] = ($row['ios_push']==1)?'<i class="am-icon-check am-btn-icon am-icon-sm am-success"></i>':'<i class="am-icon-close am-btn-icon am-icon-sm am-danger"></i>';
            $datas['datas'][$key]['ismass'] = ($row['ismass']==1)?'<i class="am-icon-check am-btn-icon am-icon-sm am-success"></i>':'<i class="am-icon-close am-btn-icon am-icon-sm am-danger"></i>';
            $datas['datas'][$key]['sendtime'] = date('Y-m-d H:i:s',$row['sendtime']);
            $datas['datas'][$key]['allow_send'] = (in_array($row['type'],[0,4,5]))?1:0;
        }

        $datas['operations'] = array(
            '删除' => "messageDelete(%id)",
            //'发送' => "messageSend(%id)",
//            '发送'=> array(
//                'style' => 'success',
//                'fun' => 'messageSend(%id)',
//                'condition' => "%allow_send==1"
//            )
        );
        $datas['pages'] = array(
            'sum' => D('MessageView')->count(),
            'count' => $pageSize,
        );

        $datas['lang'] = array(
            'content' => '消息内容',
            'type'=> '消息类型',
            'about' => '相关推送',
            'sms_send'=> '发送短信',
            'wx_send' =>  '发送微信',
            'ios_push' => '发送邮件',
            'ismass' => '是否群发',
            'sendtime' => '发送时间'
        );

        $this->assign($datas);
        $this->view();
    }

    public function addMessage(){
        $data['content'] = I('post.content');
        $data['isMass'] = I('post.isMass');
        $data['ios_push'] = I('post.ios_push');
        $data['member_id'] = I('post.member_id');
        $data['sendtime'] = I('post.sendtime');
        $data['sms_send'] = I('post.sms_send');
        $data['type'] = I('post.type');
        $data['type_id'] = I('post.type_id');
        $data['wx_send'] = I('post.wx_send');
//        $data['TypeName'] = I('post.TypeName');
        $data['effectivetime'] = I('post.effectivetime',0);

        if(IS_AJAX){
            //$this->success($data['content']);
            if(empty($data['content']))$this->error('消息内容不能为空');
            $data['sendtime'] = $data['sendtime']==0?0:strtotime($data['sendtime']);
            $data['effectivetime'] = $data['effectivetime']==0?0:strtotime($data['effectivetime']);
            if(empty($data['member_id']))$data['member_id']=null;

            $this->m2('Message')->data($data)->add();
            $this->success('消息添加成功');
        }else{
            $this->error('非法访问');
        }
    }

    public function deleteMessage(){
        $id = I('post.id');

        if(IS_AJAX){
            $this->m2('MemberMessage')->where(['message_id'=>$id])->delete();
            $this->m2('Message')->where(['id'=>$id])->delete();
            $this->success('消息删除成功');
        }else{
            $this->error('非法访问');
        }
    }

    public function MessageInfo(){
        $id = I('post.id');

        if(IS_AJAX){
            $data = $this->m2('message')->where(['id'=>$id])->find();
            $this->ajaxReturn($data);
        }else{
            $this->ajaxReturn(null);
        }
    }

    public function sendMessage(){
        $member_id = I('post.member_id');
        $message_id = I('post.message_id');

        if(empty($member_id))$this->error('未选择接收信息用户');
        if(empty($message_id))$this->error('未选择发送的信息');
        if(IS_AJAX){
            $rs = $this->m2('Message')->where(['id'=>$message_id])->field('content,sendtime,sms_send,wx_send,ios_push,isMass')->find();
            if($rs['isMass'==1])$this->error('群发消息无须单独发送');
            $re = $this->m2('MemberMessage')->where(['member_id'=>$member_id,'message_id'=>$message_id])->find();
            if(!empty($re))$this->error('该用户已经收到信息，请勿重复发送');
            $MemberMessageId = $this->m2('MemberMessage')->data(['member_id'=>$member_id,'message_id'=>$message_id])->add();

            //发短信
            if($rs['sms_send']==1){
                $telephone = $this->m2('Member')->where(['id'=>$member_id])->getField('telephone');
                $send_time = ($rs['sendtime']==0 || $rs['sendtime']<=time())?null:$rs['sendtime'];
                sms_send($telephone,$rs['content'],$send_time,false);
                $this->m2('MemberMessage')->data(['id'=>$MemberMessageId,'is_sms'=>1])->save();
            }
            //发微信
            if($rs['wx_send'] == 1){
                $openid = $this->m2('Member')->where(['id'=>$member_id])->getField('openid');
                if(!empty($openid)){
                    $wechat = wxLoad();
                    $wx = $wechat->sendCustomMessage([
                        'touser' => $openid,
                        'msgtype' => 'text',
                        'text' => [
                            'content' => $rs['content']
                        ]
                    ]);
                }
            }

            $this->success('消息已发送');
        }else{
            $this->error('非法访问');
        }
    }


    /**
     * 反馈单
     */
    Public function feedback(){
        $this->actname = "反馈单";

        $is_answer = I('get.is_answer');
        $type = I('get.type', 0);

        $condition['type'] = $type;
        if($is_answer==1)$condition['answer'] = ['EXP', 'is null'];
        if($is_answer==2)$condition['answer'] = ['EXP', 'is not null'];

        $datas['datas'] = D('FeedbackView')->where($condition)->page(I('get.page'), 30)->order('A.id desc')->select();
        foreach($datas['datas'] as $key=>$row){
            $datas['datas'][$key]['reply'] = ($row['answer']==null)?'0':'1';
        }

        $datas['operations'] = [
            '设为已处理' => [
                'style' => 'success',
                'fun' => 'setup(%id, this)',
                'condition' => '%reply == 0'
            ]
            /*'回复' => array(
                'style' => 'success',
                'fun' => 'answer(%id)',
                'condition' => '%reply == 0'
            )*/
        ];
        $datas['pages'] = [
            'sum' => D('FeedbackView')->where($condition)->count(),
            'count' => 30
        ];
        /*$datas['batch'] = array(
                '批量设为已处理' => "disposeAll()"
        );*/

        $datas['lang'] = [
            'id' => 'ID',
            'nickname' => '昵称',
            'telephone' => '电话',
            'content' => '内容',
            //'answer' => '回复',
            'datetime' => '反馈时间',
        ];

        $this->assign($datas);

        $this->view();
    }

    //将反馈设置为已处理
    public function setFeedback(){
        $id = I('post.id');
        $this->m2('feedback')->where(['id' => ['IN', $id]])->save(['answer' => '已处理']);
        $this->success('处理成功!');
    }

    //处理单条反馈信息
    /*public function answer(){
        if(IS_AJAX){
            $id = I('post.id');
            $data = array();
            $data['answer'] = I('post.answer');
            $data['id'] = $id;
            $this->m2('feedback')->data($data)->save();
            //推送消息
            $this->success('已回复');
        }
    }*/

    /*function MessageExport(){
        $data = D('MessageExportView')->where('member_id is null')->select();

        foreach($data as $key=>$row){

        }
    }*/


    public function CommentList(){
        $this->actname = "评论列表";

        $page = I('get.page',1);
        $pageSize = 20;

        $condition = ['pid' => ['EXP', 'IS NULL']];
        $title = I('get.title');
        $nickname = I('get.nickname');
        $content = I('get.content');
        $status = I('get.status');

        $nickname && $condition['B.nickname'] = ['like','%'.$nickname.'%'];
        $title && $condition['C.title|E.title'] = ['like','%'.$title.'%'];
        $content && $condition['A.content'] = ['LIKE','%'.$content.'%'];
        if($status != '') $condition['A.status'] = $status;

        if(!empty($_GET['report'])){
            $sql = $this->m2('feedback')->field(['type_id'])->where(['type' => 3])->buildSql();
            $condition['id'] = ['exp', "in({$sql})"];
        }

        $this->assign('search_nickname',$nickname);
        $this->assign('search_title',$title);
        $this->assign('search_content',$content);
        $this->assign('search_status',$status);

        $datas['datas'] = D('CommentListView')->where($condition)->page($page,$pageSize)->order('A.id desc')->select();
        foreach($datas['datas'] as $key=>$row){
            $datas['datas'][$key]['pics'] = '';
            if(!empty($row['pics_group_id'])){
                $pics = $this->m2('pics')->where(['group_id'=>$row['pics_group_id']])->getField('path',true);
                foreach($pics as $r){
                    $datas['datas'][$key]['pics'] .= '<img src="'.pathFormat($r).'" height="100px" width="100px">';
                }
            }
            //if(!empty($row['reply_nickname']))$datas['datas'][$key]['content'] = '达人回复：'.$datas['datas'][$key]['content'];
            if($row['status']==0)$datas['datas'][$key]['_status'] = '隐藏';
            if($row['status']==1)$datas['datas'][$key]['_status'] = '显示';
            if($row['type'] == 0)
                $datas['datas'][$key]['title'] = $row['tips_title'];
            elseif($row['type'] == 1)
                $datas['datas'][$key]['title'] = $row['goods_title'];
            else
                $datas['datas'][$key]['title'] = '';

            //举报信息
            $datas['datas'][$key]['fid'] = '';
            $datas['datas'][$key]['is_report'] = 1;
            $reports = $this->m2('feedback')->where(['type' => 3, 'type_id' => $row['id']])->select();
            if(!empty($reports)){
                $report_ids = [];
                $report_content = [];
                foreach($reports as $r){
                    $report_ids[] = $r['id'];
                    $report_content[] = $r['content'];
                    if(empty($r['answer'])){
                        $datas['datas'][$key]['is_report'] = 0;
                    }
                }
                $datas['datas'][$key]['fid'] = join(',', $report_ids);
                $datas['datas'][$key]['content'] .= '<br><small>举报内容:['. join(']<br>[', $report_content) .']</small>';
            }
        }

        $datas['operations'] = [
            '删除评论' => "deleteComment(%id)",
            '隐藏评论' => [
                'style' => 'danger',
                'fun' => 'hideComment(%id,0)',
                'condition' => '%status == 1'
            ],
            '显示评论' => [
                'style' => 'success',
                'fun' => 'hideComment(%id,1)',
                'condition' => '%status == 0'
            ],
            '设为已处理' => [
                'style' => 'primary',
                'fun' => 'setup(%fid, this)',
                'condition' => '%is_report == 0'
            ]
        ];
        $datas['pages'] = [
            'sum' => D('CommentListView')->where($condition)->count(),
            'count' => $pageSize
        ];
        $datas['lang'] = [
            'id' => 'ID',
            'member_nickname' => '评论者',
            'stars' => '星级',
            'content' => '内容',
            'pics' => '图片',
            'title' => '标题',
            '_status' => '显示状态'
        ];
        $this->assign($datas);
        $this->view();
    }

    //删除消息
    public function deleteComment(){
        if(IS_AJAX){
            $id = I('post.id');
            //$this->success($id);
            $this->m2('MemberComment')->where(['id'=>$id])->delete();
            $this->success('删除成功');
        }else{
            $this->error('非法访问');
        }
    }

    //隐藏消息
    public function hideComment(){
        if(IS_AJAX){
            $id = I('post.id');
            $status = I('post.status');

            $this->m2('MemberComment')->data(['id'=>$id,'status'=>$status])->save();
            $this->success('操作成功');
        }else{
            $this->error('非法访问');
        }
    }

    //客服中心
    public function customer(){
        $this->actname = '客服中心';

        //获取未读消息客户及数量
        $data = $this->getImMsg(0);
        //获取websocket连接地址和端口
        $data['ws'] = C('WS');
        $this->assign($data);
        $this->view();
    }

    //获取客服消息
    public function getImMsg($isHistory = 1){
        $where = ['to_id' => 'admin', 'is_exist' => 1];
        $lastLoginDate = $this->m1('LoginLog')->where(['user_id' => session('admin.id')])->order('id desc')->getField('datetime');
        if(!$isHistory){
            //$where['datetime'] = ['EGT', $lastLoginDate];
            $rs = $this->m2('im')->field(['from_id', 'is_wx', 'max(datetime)' => 'datetime'])->where($where)->group('from_id')->order('id desc')->select();
            if(empty($rs))return [];
        }else{
            $page = I('get.page', 1);
            $is_wx = I('get.is_wx', 0);
            $where['datetime'] = ['LT', $lastLoginDate];
            $where['is_wx'] = $is_wx;
            $rs = $this->m2('im')->field(['from_id', 'is_wx', 'max(datetime)' => 'datetime'])->where($where)->group('from_id')->order('id desc')->page($page, 10)->select();
            if(empty($rs))$this->ajaxReturn([]);
        }

        $data = $froms = $openids = $yf_openids = $member_ids = $tokens = [];
        foreach($rs as $row){
            $froms[] = $row['from_id'];
            if(preg_match('/^\d+$/', $row['from_id'])){
                $member_ids[] = $row['from_id'];
            }elseif(preg_match('/^openid-/', $row['from_id'])){
                $openids[] = substr($row['from_id'], 7);
            }elseif(preg_match('/^yf_openid-/', $row['from_id'])){
                $yf_openids[] = substr($row['from_id'], 10);
            }elseif(preg_match('/^token-/', $row['from_id'])){
                $tokens[] = $row['from_id'];
            }
        }

        //获取用户昵称和头像
        $where = [];
        if(!empty($member_ids))$where[] = "ym_member.id in ('". join("','", $member_ids) ."')";
        if(!empty($openids))$where[] = "openid in ('". join("','", $openids) ."')";
        if(!empty($yf_openids))$where[] = "yf_openid in ('". join("','", $yf_openids) ."')";
        $members = $this->m2('member')->field(['ym_member.id' => 'id', 'nickname', 'path', 'openid', 'yf_openid'])->join('left join __PICS__ a on a.id=pic_id')->where(join(' or ', $where))->select();

        $data['wx'] = $data['im'] = [];
        foreach($rs as $row){
            if(strpos(DOMAIN, '.cn') > 0){
                $pre = 'http://img.yummy194.cn/';
            }else{
                $pre = 'http://img.m.yami.ren/';
            }
            $dt = [
                'from_id' => $row['from_id'],
                'member_id' => 0,
                'nickname' => '匿名用户',
                'path' => 'http://img.yummy194.cn/20160608/99a2e57830afb6d3071939576771a023eae18b59.jpg',
                'datetime' => strtotime($row['datetime'])
            ];
            foreach($members as $m){
                if($m['id'] == $row['from_id'] || 'openid-'.$m['openid'] == $row['from_id'] || 'yf_openid-'.$m['yf_openid'] == $row['from_id']){
                    $dt['member_id'] = $m['id'];
                    $dt['nickname'] = $m['nickname'];
                    if(!empty($m['path']))$dt['path'] = $pre . $m['path'];
                }
            }
            //判断是否有新消息
            $rep = $this->m2('im')->where(['from_id' => 'admin', 'to_id' => $row['from_id'], 'datetime' => ['GT', $row['datetime']]])->count();
            $dt['hasnew'] = $rep > 0 ? 0 : 1;

            if($row['is_wx']){
                $data['wx'][] = $dt;
            }else{
                $data['im'][] = $dt;
            }
        }
        if(isset($is_wx)){
            if($is_wx)
                $this->ajaxReturn($data['wx']);
            else
                $this->ajaxReturn($data['im']);
        }else{
            return $data;
        }
    }

    //获取客户昵称/头像/聊天记录
    public function getCustomInfo(){
        $from_id = I('post.from_id');
        $openid = $yf_openid = $member_id = null;

        if(preg_match('/^\d+$/', $from_id)){
            $member_id = $from_id;
        }elseif(preg_match('/^openid-/', $from_id)){
            $openid = substr($from_id, 7);
        }elseif(preg_match('/^yf_openid-/', $from_id)){
            $yf_openid = substr($from_id, 10);
        }

        $data = [
            'form_id' => $from_id,
            'member_id' => 0,
            'nickname' => '匿名用户',
            'path' => 'http://img.yummy194.cn/20160608/99a2e57830afb6d3071939576771a023eae18b59.jpg',
        ];

        //获取用户昵称和头像
        $where = [];
        if(!empty($member_id))$where['ym_member.id'] = $member_id;
        if(!empty($openid))$where['openid'] = $openid;
        if(!empty($yf_openid))$where['yf_openid'] = $yf_openid;
        $rs = $this->m2('member')->field(['ym_member.id' => 'id', 'nickname', 'path', 'openid', 'yf_openid'])->join('left join __PICS__ a on a.id=pic_id')->where($where)->find();

        if(!empty($rs)){
            if(strpos(DOMAIN, '.cn') > 0){
                $pre = 'http://img.yummy194.cn/';
            }else{
                $pre = 'http://img.m.yami.ren/';
            }
            $data['member_id'] = $rs['id'];
            $data['nickname'] = $rs['nickname'];
            if(!empty($rs['path']))$data['path'] = $pre . $rs['path'];
        }

        $this->ajaxReturn($data);
    }
}