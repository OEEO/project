<?php
namespace Admin\Controller;
use Admin\Controller\MainController;

class RaiseController extends MainController{

    Protected $pagename = '众筹管理';

    //众筹添加
    public function raiseAdd(){
        $this->actname = '众筹添加';
        if(IS_AJAX && IS_POST){
            if(!isset($_POST['submit']) && !empty($_POST['member_id'])) {
                //选择达人并创建新活动
                $data = [
                    'city_id' => I('post.city_id'),
                    'member_id' => I('post.member_id'),
                    'title' => '标题',
                    'category_id' => null,
                    'total' => 0,
                    'start_time' => time(),
                    'end_time' => time()+3600,
                    'content' => null,
                    'introduction' => '众筹简介'
                ];
                $id = $this->m2('raise')->add($data);
                $data_time = [
                    'raise_id' =>$id,
                    'title' => '类目标题',
                    'price' => 0,
                    'content' => null
                ];
                $raise_times_id = $this->m2('raise_times')->add($data_time);
                $this->ajaxReturn($id);
                exit;
            }elseif(isset($_POST['submit']) && $_POST['submit'] == 1){
                //提交审核
                $this->submit();
            }elseif(isset($_POST['submit']) && $_POST['submit'] == 0){
                //保存并预览
                $this->save();
            }
            $this->error('非法提交');
            exit;
        }

        // 城市
        $citys = C('CITY_CONFIG');
        //获取众筹分类
        $categorys = $this->m2('category')->field(['id', 'name'])->where(['type' => 5])->order(['order'])->select();
        //获取众筹标签
        $tags = $this->m2('tag')->field(['id', 'name'])->where(['type' => 5, 'official' => 0])->select();
        $this->assign([
            'categorys' => $categorys,
            'tags' => $tags,
            'citys' => $citys
        ]);
        $this->view();
    }

    //修改
    public function raiseUpdate()
    {
        $this->actname = '众筹修改';
        if (IS_AJAX && IS_POST) {
            if (isset($_POST['submit']) && $_POST['submit'] == 0) {
                //保存并预览
                $this->save();
            }
            $this->error('非法提交');
            exit;
        }
        $raise_id = I('get.raise_id', null);
        $rs = D('RaiseEditView')->where(['id' => $raise_id])->find();
        $rs['path'] = thumb($rs['path'],11);

        if (empty($rs)) {
            $this->error('要修改的活动不存在!');
        }
        $rs['raise_times'] = $this->m2('RaiseTimes')->where(['raise_id' => $raise_id])->select();
        foreach($rs['raise_times'] as $key =>$row) {
            if ($row['stock'] == -1 && $row['quota'] >= 0) {
                $rs['raise_times'][$key]['stock_type'] = 1;
            } elseif ($row['stock'] >= 0 && $row['quota'] >= 0) {
                $rs['raise_times'][$key]['stock_type'] = 2;
            } elseif ($row['stock'] == -1 && $row['quota'] == -1) {
                $rs['raise_times'][$key]['stock_type'] = 3;
            }
        }

        //获取众筹分类
        $categorys = $this->m2('category')->field(['id', 'name'])->where(['type' => 5])->order(['order'])->select();
        //获取众筹标签
        $tags = $this->m2('tag')->field(['id', 'name'])->where(['type' => 5, 'official' => 0])->select();
        $rs['content']= preg_replace('/\[img(.*?)\/\]/', '<img$1>', $rs['content']);

        $data = [
            'data' => $rs,
            'categorys' => $categorys,
            'tags' => $tags,
        ];
        $this->assign($data);
        $this->view();
    }

    //模糊查找会员
    public function getUser(){
        if(IS_AJAX){
            $search_key = I('post.search_key');

            if(isset($search_key) && $search_key != ''){
                $condition = 'nickname LIKE '."'%$search_key%'";
                $member_rs = D('MemberView')->field('id,nickname,telephone,sex,path')->where($condition)->limit(20)->select();
                $this->ajaxReturn($member_rs);
            }
        }
    }

    //模糊查找达人
    public function getRaiseHost(){
        if(IS_AJAX){
            $search_key = I('post.search_key');

            if(isset($search_key)&&$search_key!=''){
                $condition = 'nickname LIKE '."'%$search_key%'";
                $member_rs = D('DarenInfoView')->field('id,nickname,telephone,sex,path')->where($condition)->limit(20)->select();
                $this->ajaxReturn($member_rs);
            }
        }
    }

    //提交编辑内容
    private function save(){
        $raise_id = I('post.raise_id');
        $member_id = I('post.member_id');
        $rs = $this->m2('Raise')->where(['id' => $raise_id, 'member_id' => $member_id])->find();
        if (empty($rs)) {
            $this->error('非法提交!');
        }
        $this->m2()->startTrans();
        $data = ['member_id' => $member_id];
        $data['city_id'] = I('post.city_id');
        $data['category_id'] = I('post.category_id');
        $data['is_address'] = I('post.is_address');
        $data['title'] = I('post.title');
        $data['total'] = I('post.price');
        $data['pic_id'] = I('post.pics_id');
        $data['content'] = I('post.content');
        $data['start_time'] = I('post.start_time');
        $data['introduction'] = $_POST['introduction'];
        $data['end_time'] = I('post.end_time');
        $data['is_public'] = I('post.is_public');
        $data['content'] = preg_replace(['/<script.*?>.*?<\/script>/', '/<iframe.*?>.*?<\/iframe>/', '/<iframe.*?\/>/', '/<textarea.*?>.*?<\/textarea>/', '/<img(.*?)>/'], ['', '', '', '','[img$1/]'], $_POST['content']);
        if(empty($data['title']))$this->error('众筹标题不能为空！');
        if(empty($data['total']))$this->error('众筹目标金额不能为空！');
        if(empty($data['introduction']))$this->error('众筹简介不能为空！');
        if(!empty($data['introduction'])){
            $introduction_length = $this->abslength($data['introduction']);
            if($introduction_length>101)$this->error('众筹简介长度不能超过100个中文！已输入'.$introduction_length.'个文字');
        }
        if (I('post.tags_id')) {
            //活动标签
            $tag_ids = explode(',', I('post.tags_id'));
            //删除旧标签
            $sql = $this->m2('tag')->field(['id'])->where(['official' => 0, 'type' => 5])->buildSql();
            $this->m2('raise_tag')->where(['raise_id' => $raise_id, 'tag_id' => ['EXP', "in {$sql}"]])->delete();
            //添加新标签
            $tags = [];
            foreach ($tag_ids as $id) {
                $tags[] = ['raise_id' => $raise_id, 'tag_id' => $id];
            }
            $this->m2('raise_tag')->addAll($tags);
        }

        //时间区间
        if (empty($data['start_time'])) $this->error('众筹开始时间不能为空!');
        $data['start_time'] = strtotime($data['start_time'] . ':00');
        if (empty($data['end_time'])) $this->error('众筹结束时间不能为空!');
        $data['end_time'] = strtotime($data['end_time'] . ':00');
        if ($data['start_time'] >= $data['end_time']) $this->error('开始时间必须小于结束时间!');

        //类目
        $raise_times = I('post.raise_times');
        if (count($raise_times) == 0) {
            $this->error('不能没有类目!');
        }
        $times_ids = [];
        foreach ($raise_times as $row) {
            $order_count = $this->m2('order_wares')->join('__ORDER__ a on a.id=order_id')->where(['type' => 2, 'ware_id' => $raise_id, 'tips_times_id' => $row['id'], 'status' => 1])->find();
            if(!empty($row['price']) || $row['is_free'] == '1') {
                // 需要填写价格，或则，是免费分享抽奖
                $row['price_type'] = $row['price'];
                $row['raise_id'] = $raise_id;
                $row['title'] = $row['title'];
                $row['price'] = $row['price'];
                $row['prepay'] = $row['prepay'];
                $row['limit_num'] = $row['limit_num'];
                $row['is_address'] = $row['is_address'];
                $row['is_buy'] = $row['is_buy'];
                $row['is_realname'] = $row['is_realname'];
                $row['screen_num'] = $row['screen_num'];
                $row['send_day'] = floor($row['send_day']);
                $row['type'] = $row['times_type'];
                $row['is_free'] = $row['is_free'];

                if($row['stock_type'] == 1 ){
                    $row['stock'] = -1;
                    $row['quota'] = $row['stock_num'];
                }elseif($row['stock_type'] == 2 ){
                    if($data['start_time']>time() && empty($order_count)){
                        $row['stock'] = $row['stock_num'];
                    }elseif($data['start_time']<=time() && empty($order_count) && empty($row['id'])){
                        $row['stock'] = $row['stock_num'];
                    }
                    $row['quota'] = $row['stock_num'];
                }elseif($row['stock_type'] == 3 ){
                    if($order_count == 0){
                        $row['stock'] = -1;
                        $row['quota'] = -1;
                    }
                }
                $row['content'] = $row['content'];
                if (empty($row['id'])) {
                    unset($row['id']);
                    $times_ids[] = $this->m2('raise_times')->add($row);
                }else{
                    $times_ids[] = $row['id'];
                    $this->m2('raise_times')->save($row);
                }
            }
        }
        $rs = $this->m2('order_wares')->join('__ORDER__ a on a.id=order_id')->where(['type' => 2, 'ware_id' => $raise_id, 'tips_times_id' => ['NOT IN', join(',', $times_ids)], 'status' => 1])->find();
        if (!empty($rs)) $this->error('已产生订单的时间段不能删除!');
        $this->m2('raise_times')->where(['raise_id' => $raise_id, 'id' => ['NOT IN', join(',', $times_ids)]])->delete();

        if (!empty( $data['pic_id'])) {
            $pics = $this->m2('pics')->where(['id' =>  $data['pic_id']])->find();
            if(!empty($pics)){
                $_data = [
                    'type' =>5,
                    'member_id' =>$member_id,
                ];
                $this->m2('pics')->save($_data);

            }
        }else {
            $this->error('封面主图不能为空!');
        }

        //统一修改
        $_data = [];
        foreach ($data as $key => $row) {
            if ($row === 0 || $row === '0' || !empty($row) ) {
                $_data[$key] = $row;
//                $_data['datetime'] = date('Y-m-d H:i:s');
            }
        }

        if(!empty($_data)) {

            $this->m2('raise')->where(['id' => $raise_id])->save($_data);
            $this->m2()->commit();
            $this->success($raise_id);
        }
    }

    public function index(){
        $this->actname = '众筹列表';
        if(isset($_GET['status']) && is_numeric($_GET['status'])){
            $condition = "A.status = " . $_GET['status'];
        }else{
            $condition = "A.status = 1 ";
        }
        if(IS_GET && $_GET!=null){                      //条件查询

            $raise_id = I('get.id');
            $raise_title = I('get.title');
            $member_nickname = I('get.member');
            $raise_start_time = strtotime(I('get.start_time'));
            $raise_end_time = strtotime(I('get.end_time'));
            $raise_category = I('get.category');
            $raise_tag = I('get.tag');
            $status = I('get.status', null);

            //$condition = array(); //新版 12/21
            $raise_id &&  $condition .= " AND A.id='$raise_id' ";
            $raise_title && $condition .= " AND A.title LIKE '%$raise_title%' ";
            if(!empty($raise_start_time) && !empty( $raise_end_time)){
                $condition .= " AND A.start_time > '$raise_start_time' And A.end_time < '$raise_end_time'";
            }elseif(!empty($raise_start_time) && empty($raise_end_time)){
                $condition .= " AND A.start_time > '$raise_start_time'";
            }elseif(!empty($raise_end_time) && empty($raise_start_time)){
                $condition .= " AND A.end_time < '$raise_start_time'";
            }


            $member_nickname && $condition .= " AND B.nickname LIKE '%$member_nickname%' ";
            $raise_category && $condition .= " AND A.category_id = '$raise_category' ";

            //标签筛选
            if(!empty($raise_tag)){
                $tag_raise_id = $this->m2('RaiseTag')->where(['tag_id' => $raise_tag])->getField('raise_id',true);
                $tag_raise_id = join(',',$tag_raise_id);
                if(!empty($tag_raise_id)){
                    $condition .= " AND A.id IN ($tag_raise_id) ";
                }
            }

            $status!==null && $condition .= " AND A.status='$status' ";

            $this->assign('search_id',$raise_id);
            $this->assign('search_title',$raise_title);
            $this->assign('search_member',$member_nickname);
            $this->assign('search_category',$raise_category);
            $this->assign('search_tag',$raise_tag);
            $this->assign('search_status',$status);
            $raise_start_time && $this->assign('search_start_time',date('Y-m-d H:i',$raise_start_time));
            $raise_start_time && $this->assign('search_end_time',date('Y-m-d H:i',$raise_end_time));

        }
        $datas['datas'] = D('RaiseView')->where($condition)->page(I('get.page'), 20)->order('datetime desc, A.id desc')->group('A.id')->select();
//            print_r(D('RaiseView')->getLastSql());

        if(!empty($datas['datas'])) {
            foreach($datas['datas'] as $key =>$val){
                $datas['datas'][$key]['pic_path'] = $this->m2('pics')->where(['id'=>$val['pic_id']])->getField('path');
                $datas['datas'][$key]['raise_pic_path']= '<img src='. thumb($datas['datas'][$key]['pic_path'],11).' style="width:100px;"/>';
                $datas['datas'][$key]['start_time']=date('Y-m-d H:i:s',$val['start_time']);
                $datas['datas'][$key]['end_time']=date('Y-m-d H:i:s',$val['end_time']);
                if($val['status']==0) $datas['datas'][$key]['status'] ='下架';
                if($val['status']==1) $datas['datas'][$key]['status'] ='正常';
            }
        }
        //table页面参数设置
        $datas['operations'] = [
            '查看类目' => "showCategory(%id)",
            '问答列表' => "AddUpdate(%id)",
            '设置特权' => "setPrivilege(%id)",
//                '修改标签' => "raise_tags(%id)",
            '发送达到目标金额短信'=>'raiseSendSms(%id)',
            '修改' => "location.href='raiseUpdate.html?raise_id=%id'",
//                '修改记录'=> 'showLogs(%id)',
            '下架' => [
                'style' => 'warning',
                'fun' => 'checkout(%id, 2)',
                'condition' => "%status=='正常'"
            ],
            '上架' => [
                'style' => 'success',
                'fun' => 'checkout(%id, 1)',
                'condition' => "%status=='下架'"
            ],
//            '设置为首页推荐' => 'setToHome(%id, 1)',
            '查看抽奖' => 'viewLottery(%id)'
        ];
        $datas['pages'] = [
            'sum' => D('RaiseView')->where($condition)->count(),
            'count' => 20,
        ];
        $datas['lang'] = [
            'id' => ['预览', '<input type="hidden" value="%*%" > <a><i class="am-icon-eye" onclick="preview(%*%)"></i></a>'],
            'title' => '众筹标题',
            'nickname' => '发布者',
            'telephone' => '手机号码',
            'raise_pic_path' => '封面主图',
            'total' => '众筹目标金额',
            'category_name' => '分类',
            'start_time' => '开始时间',
            'end_time' => '结束时间',
            'status' => '状态'
        ];


        //读取众筹标签列表
        $tags = $this->m2('tag')->where('type=5')->select();
        foreach($tags as $key=>$result){
            if($result['official']==0)$tags[$key]['name'] = '(普)'.$tags[$key]['name'];
            if($result['official']==1)$tags[$key]['name'] = '(官)'.$tags[$key]['name'];
        }
        $this->assign('tags',$tags);
        //读取分类列表
        $category = $this->m2('category')->where(['type'=>5])->select();;

        $this->assign('category_list',$category);
        $this->assign($datas);
        $this->view();
    }

    //众筹上下架操作
    public function online(){
        if(IS_AJAX && isset($_POST['id']) && isset($_POST['status'])){
            $id = I('post.id');
            $status = I('post.status');
            if($status == 1){
                $this->m2('raise')->where(['id' => $id])->save(['status' => 1,'datetime'=>date('Y-m-d H:i:s')]);
                $this->success('上架成功!');
            }else{
                $this->m2('raise')->where(['id' => $id])->save(['status' => 0,'datetime'=>date('Y-m-d H:i:s')]);
                $this->success('下架成功!');
            }
        }
        $this->error('非法操作!');
    }

    //众筹评论列表
    public function raise_comment(){
        $this->actname = '众筹列表';
        $datas['datas'] = D('FeedbackView')->where(['A.type'=>3])->page(I('get.page'), 20)->order('A.id desc')->select();

        foreach($datas['datas'] as $key=>$val){
           $datas['datas'][$key]['title'] = $this->m2('raise')->where(['id'=>$val['type_id']])->getField('title');
            if(!empty($val['answer'])){
                $datas['datas'][$key]['answer_status']='不为空';
                if($val['answer_member_nickname'] == ''){
                    $datas['datas'][$key]['content'] .= '<br/>吖咪酱 回复 '.$val['nickname'].'：【'.$val['answer'].'】';
                }else{
                    $datas['datas'][$key]['content'] .= '<br/>'.$val['answer_member_nickname'].' 回复 '.$val['nickname'].'：【'.$val['answer'].'】';
                }

            }else{
                $datas['datas'][$key]['answer_status']='为空';
            }


        }
        //table页面参数设置
        $datas['operations'] = [
            '回复内容' => [
                'style' => 'success',
                'fun' => 'reply_answer(%id)',
                'condition' => "%answer_status=='为空'"
            ],
            '修改回复内容' => [
                'style' => 'default',
                'fun' => 'reply_answer(%id)',
                'condition' => "%answer_status=='不为空'"
            ],
            '删掉评论' => [
            'style' => 'warning',
            'fun' => 'delete_comment(%id)',
        ]
        ];
        $datas['pages'] = [
            'sum' => D('FeedbackView')->where(['A.type'=>3])->count(),
            'count' => 20,
        ];
        $datas['lang'] = [
            'id' => 'ID',
            'nickname' => '会员昵称',
            'telephone' => '手机号码',
            'title' => '众筹标题',
            'content' => '反馈内容',
            'datetime' => '反馈时间',
        ];
        $this->assign($datas);
        $this->view();
    }

    //查看和回复评论
    function raise_reply(){
        if(IS_AJAX && IS_POST){
            $id = I('post.id');
            if($_POST['typename']=='check_reply') {
                $answer = $this->m2('feedback')->where(['id' => $id])->getField('answer');
                $this->success($answer);
            }elseif($_POST['typename']=='answer_reply'){
                $answer = $_POST['answer'];
                $reply_arr = D('RaiseFeedbackView')->where(['id' => $id])->find();
                if(empty($reply_arr['answer'])){
                    $message_id = $this->m2('Message')->data(['type'=>1,'content'=>'您好！你评论的【'.$reply_arr['title'].'】众筹，客服已处理，回复信息为【'.$answer.'】'])->add();
                    $this->m2('MemberMessage')->data(['member_id'=>$reply_arr['member_id'],'message_id'=>$message_id])->add();
                }

                if(isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'm.yami.ren') === false) {
                    $answer_member_id = 55;
                }else{
                    $answer_member_id = 34593;
                }
                $this->m2('feedback')->where(['id'=>$id])->save(['answer'=>$answer,'answer_member_id'=>$answer_member_id]);
                $this->success('回复成功!');
            }
        }
        $this->error('非法操作!');
    }

    //删除评论
    function raise_comment_del(){
        if(IS_AJAX && IS_POST){
            $id = I('post.id');
            $this->m2('feedback')->where(['id'=>$id])->delete();
            $this->success('删除成功!');
        }
        $this->error('非法操作!');
    }

    //达到目标金额发送短信给用户
    public function  raiseSendSms(){
        if(IS_AJAX && IS_POST){
            $raise_id = I('post.id');
            $result_1 = D('OrderRaiseView')->where(['ware_id'=>$raise_id,'act_status'=>['IN','1,2,3,4'],'B.type'=>2,'status'=>1])->group('B.id')->select();
            $i = 0;
            foreach($result_1 as $row){
//                $context = "您支持的众筹项目《始于1880年的传奇茶楼 首次众筹》认筹金额已达成目标，项目众筹成功。谢谢您的支持，也希望您能把这个项目告诉更多人。客服微信：yami194（工作时间：9:00-20:00）有问题随时保持联络！";
//                $this->pushMessage($row['member_id'],$context,'sms', 0, 0, 0, 0);

                //2016-12-29
                $params = array(
                    'project_name' => '众筹',
                    'title' => '始于1880年的传奇茶楼 首次众筹',
                    'project_name_1' => '众筹',
                    'wx' => 'yami194',
                );
                $this->push_Message($row['member_id'],$params,'SMS_36305199','sms',null, 0, 0, 0, 0);
                $i++;
            }
            $this->success($i.'条发送成功!');
        }

    }

    //查看，修改问答列表
    function Question(){
        if(IS_AJAX && IS_POST) {
            if ($_POST['postType'] == 'checkQuestion') {
                $question_title = $this->m2('raise')->where(['id'=>I('post.raise_id')])->getField('question_title');
                if(!empty($question_title)){
                    $question_arr['answer'] = json_decode($question_title);
                    $this->ajaxReturn($question_arr);
                }else{
                    $this->error('没有问答列表！');
                }
            }elseif ($_POST['postType'] == 'UpdateQuestion') {
                $raise_id = I('post.raise_id');
                $answer_json = empty(I('post.answer')) ? null : json_encode(I('post.answer'));
                $d = $this->m2('raise')->where(['id' => $raise_id])->save(['question_title' => $answer_json]);
                if ($d) {
                    $this->success('修改成功！');
                }else{
                    $this->error('没有问答列表！');
                }
            }
        }
    }

    //众筹二次支付订单生成
    function raise_nextpay(){
        if(IS_AJAX && IS_POST) {
            if ($_POST['search_type'] == 'search_raise_times' && !empty($_POST['search_key'])) {
                $search_key = $_POST['search_key'];
                if (isset($search_key) && $search_key != '') {
                    $condition = 'C.title LIKE ' . "'%$search_key%'";
                    $raise_rs = D('RaiseView')->where($condition)->limit(30)->group('C.id')->select();
                    $this->ajaxReturn($raise_rs);
                }
            }elseif ($_POST['typeName'] == 'raisePay') {
                $raise_id = I('post.raise_id');
                $raise_times_id = I('post.raise_times_id');
                $member_arr = trim($_POST['member_arr']);
                $member_id = array_filter(explode("\n", $member_arr));
                $order_arr = D('OrderRaisePayView')->where(['ware_id'=>$raise_id,'tips_times_id'=>$raise_times_id,'member_id'=>['IN',$member_id],'B.type'=>2])->group('B.id')->select();
//                    print_r(D('OrderRaisePayView')->getLastSql());
//                    exit;
                if(!empty($order_arr)){
                    $i = 0;
                    foreach($order_arr as $order_val){
                        $i++;
                        $j = 0;
                        $k = 0;
                        if(in_array($order_val['member'],$member_id) ){
                            $data=[
                                'sn' => createCode(18),
                                'member_id' => $order_val['member'],
                                'member_address_id' => $order_val['member_address_id'],
                                'price' =>  (float)$order_val['raise_times_price']-(float)$order_val['raise_times_prepay'],
                                'act_status' => 0,
                                'create_time' => time(),
                                'context' => $order_val['context'],
                                'channel' => $order_val['channel'],
                                'status' => 1,
                                'order_pid' => $order_val['id']
                            ];
                            $order_id = $this->m2('order')->add($data);
                            //快照数据
                            $snapshot = [
                                'raise_id'=>$order_val['raise_id'],
                                'raise_title'=>$order_val['raise_title'],
                                'raise_content'=>$order_val['raise_content'],
                                'raise_total'=>$order_val['total'],
                                'raise_price'=>$order_val['raise_times_price'],
                                'raise_prepay'=>$order_val['raise_times_prepay'],
                                'datetime'=>time(),
                            ];

                            $code = createCode(8);

                            $result = $this->m2('order_wares')->add(array(
                                'order_id' => $order_id,
                                'type' => 2,
                                'ware_id' => $order_val['raise_id'],
                                'price' => (float)$order_val['raise_times_price']-(float)$order_val['raise_times_prepay'],
                                'check_code' => $code,
                                'tips_times_id' => $order_val['raise_times_id'],
                                'snapshot' => json_encode($snapshot)
                            ));
                            if(!empty($result) && !empty($order_id)){
                                $j += +1;
                            }else{
                                $k += +1;
                            }
                        }
                    }
                    $this->success('发送成功');
                }else{
                    $this->error('这些用户没下单或者不存在该众筹的订单');
                }
            }
        }
    }

    //查看类目
    public function showCategory(){
        $data = [];
        $raise_id = I('post.raise_id');
        $data['raise_times'] = $this->m2('raise_times')->where(['raise_id' => $raise_id])->order('id')->select();
        foreach($data['raise_times'] as $key=>$row){
            $data['raise_times'][$key]['title'] = $row['title'];
            $data['raise_times'][$key]['content'] = $row['content'];
            $data['raise_times'][$key]['price'] =$row['price'] ;
            $data['raise_times'][$key]['prepay'] =$row['prepay'] ;
            if($row['stock']>=0 && $row['quota']>0){
                $data['raise_times'][$key]['stockType'] = '限制库存';
            }else{
                $data['raise_times'][$key]['stockType'] = '不限制库存';
            }
            if($row['prepay']>0){
                $data['raise_times'][$key]['priceType'] = '预付类型';
            }else{
                $data['raise_times'][$key]['priceType'] = '全额类型';
            }
            $data['raise_times'][$key]['stock'] =$row['stock'];
            $data['raise_times'][$key]['quota'] =$row['quota'];
            $data['raise_times'][$key]['limit_num'] =$row['limit_num'];
        }
        $this->ajaxReturn($data);
    }

    //众筹标签查改
    public function getTaiseTags(){
        //查询众筹标签
        if(IS_AJAX && I('post.raise_id')==''){
            $id = I('post.id');
            $raise_tags = $this->m2('tag')->where('type=1 and official=0')->select();    //普通活动标签
            $official_raise_tags = $this->m2('tag')->where('type=1 and official=1')->select();       //官方活动标签
            $my_tags = $this->m2('raise_tag')->join('__TAG__ ON ym_raise_tag.tag_id = ym_tag.id')->where('raise_id='.$id)->select();
            $label = array();
            $official_label = array();
            foreach($my_tags as $row){
                if($row['official']==0){
                    $label[] = $row['tag_id'];
                }else{
                    $official_label[] = $row['tag_id'];
                }
            }
            $data['raise_tags'] = $raise_tags;
            $data['official_raise_tags'] = $official_raise_tags;
            $data['my_label'] = $label;
            $data['my_official'] = $official_label;

            $this->ajaxReturn($data);
            exit;
        }
        //修改众筹标签
        if(IS_AJAX){
            $id = I('post.raise_id');
            $official_tag_ids = I('post.official_tag_ids');
            $tag_ids = I('post.tag_ids');

            $this->m2('raise_tag')->where('raise_id='.$id)->delete();

            foreach($official_tag_ids as $row){
                $data = [];
                $data['raise_id'] = $id;
                $data['tag_id'] = $row;
                $this->m2('raise_tag')->data($data)->add();
            }

            foreach($tag_ids as $row){
                $data = [];
                $data['raise_id'] = $id;
                $data['tag_id'] = $row;
                $this->m2('raise_tag')->data($data)->add();
            }

            $this->success('修改成功');
        }
    }

    //查看修改众筹日志
    public function showLogs(){
        if(IS_AJAX && IS_POST){
            $id = I('post.id');
            $starttime = I('post.starttime', null);
            $endtime = I('post.endtime', null);

            $map = ['framework_id' => 223, 'gt' => ['LIKE', '%\"'. $id .'\"%'], 'pt' => ['LIKE', '%\"title\"%']];
            if(!empty($starttime))$map['datetime'] = ['EGT', $starttime];
            if(!empty($endtime))$map['datetime'] = ['ELT', $endtime];

            $rs = D('ActMemberView')->where($map)->limit(1000)->order('datetime desc')->select();
            $this->success($rs);
        }
    }

    //图片上传
    public function upload(){
        $rs = parent::upload();
        if($rs['status'] == 1){
            echo $rs['info']['path'];
        }else{
            echo $rs['info'];
        }
    }

    //设置特权
    public function setPrivilege(){
        if(IS_AJAX && IS_POST){
            if($_POST['typeName']=='distributeList'&& !empty($_POST['raise_id'])){//分发人列表
                $data['privilege'] = D('MemberPrivilegeView')->where(['type'=>2,'type_id'=>I('post.raise_id')])->select();
                foreach($data['privilege'] as $key=>$val){
                    $rs = $this->m2('member_privilege')->field( 'member_id as receive_member_id ,order_id')->join('__MEMBER__ AS A ON A.id = __MEMBER_PRIVILEGE__.member_id')->where(['privilege_id'=>$val['id']])->select();
                    $data['privilege'][$key]['distributeNum'] = count($rs);
                    $data['privilege'][$key]['BuyerNum'] = 0;
                    $data['privilege'][$key]['BuyTotal'] = 0;
                    foreach($rs as $k=>$v){
                        if(!empty($v['order_id'])){
                            $data['privilege'][$key]['BuyerNum'] = ++$data['privilege'][$key]['BuyerNum'];
                            $raise_times_id = $this->m2('order')->join('__ORDER_WARES__ AS A ON A.order_id = __ORDER__.id')->where(['order_id'=>$v['order_id'],'act_status'=>['IN','1,2,3,4']])->getField('tips_times_id');

                            if($raise_times_id>0){
                                $price = $this->m2('raise_times')->where(['id'=>$raise_times_id])->getField('price');
                            }
                            $data['privilege'][$key]['BuyTotal']  += (float)$price;
                        }
                        if($val['number'] == -1){
                            $val['number'] == '不限制人数';
                        }
                    }
                }
                $data['tips_times'] = $this->m2('raise_times')->field('id,title')->where(['raise_id'=>I('post.raise_id')])->select();

                $this->ajaxReturn($data);
            }elseif($_POST['typeName'] == 'selectNickName' && !empty($_POST['nickname']) ){//选择分发人
                $nickname = $_POST['nickname'];
                $re = '';
                $member = $this->m2('member')->field('id,nickname,telephone')->where('status = 1 and nickname LIKE "%'.$nickname.'%"')->group('id')->limit(30)->select();
                foreach ($member as $key =>$value){
//                    $re .= '<li onClick="fill(this,'.$value['id'].');"><span>'.$value['nickname'].'</span>&nbsp;&nbsp;&nbsp;&nbsp;'.$value['telephone'].'</li>';
                    $re .= ' <li onClick="fill(this,'.$value['id'].');" class="" data-index="'.$key.'" data-group="0" data-value="'.$value['id'].'"><strong>'.$value['nickname'].'</strong>&nbsp;&nbsp;&nbsp;&nbsp;'.$value['telephone'].'</li>';
                }
                echo $re;
            }elseif($_POST['typeName'] == 'AddPrivilege' && !empty($_POST['member_id']) ){//添加分发人
                $member_id = I('post.member_id');
                $num = I('post.num');
                $tips_times_id = I('post.tips_times_id');
                $raise_id = I('post.raise_id');
                $rs = $this->m2('member')->where(['member_id'=>$member_id])->find();
                $raise_times = $this->m2('raise_times')->where(['id'=>$tips_times_id])->getField('title');
                if(!empty($rs)){
                    $count = $this->m2('privilege')->where(['member_id'=>$member_id,'tips_times_id'=>$tips_times_id,'type_id'=>$raise_id,'type'=>2])->count();
                    if($count<=0){
                        $data = [
                            'member_id'=>$member_id,
                            'number'=>$num,
                            'tips_times_id'=>$tips_times_id,
                            'type'=>2,
                            'type_id'=>$raise_id,
                        ];
                        $id =  $member = $this->m2('privilege')->add($data);
                        $this->success(['id'=>$id]);
                    }else{
                        $this->error('该发起人在‘'.$raise_times.'’档位已存在');
                    }
                }else{
                    $this->error('不存在该用户');
                }
            }elseif($_POST['typeName'] == 'DelPrivilege' && !empty($_POST['privilege_id']) ){
                $privilege_id = I('post.privilege_id');
                $count = $this->m2('member_privilege')->where(['privilege_id'=>$privilege_id])->count();
                if($count>0){
                    $this->error('不能删除该发起人，该发起人已有用户领取特权！');
                }else{
                    $this->m2('member_privilege')->where(['privilege_id'=>$privilege_id])->delete();
                    $this->m2('privilege')->where(['id'=>$privilege_id])->delete();
                    $this->success('删除成功！');

                }
            }
        }
    }

    public function lottery() {
        $this->actname = '众筹抽奖管理';

        $raiseId = I('get.raise_id');

        if(IS_AJAX && IS_POST) {
            // 保存众筹抽奖结果
            $times_id = I('post.times_id');
            $action = I('post.action');
//            $desc = I('post.desc');
            $sh = I('post.sh');
            $sz = I('post.sz');
            $trade_date = I('post.trade_date');
            $baseX = I('post.baseX', 0);
            $num = I('post.num', 1);

            switch ($action) {
                case 'save':
                    try {
                        $d = $this->saveLottreyResult($times_id, $sh, $sz, $trade_date, $baseX, $num);
                        $d ? $this->success('保存成功') : $this->error('保存失败');
                    } catch (Exception $exception) {
                        $this->error($exception->getMessage());
                    }
                    break;
                case 'run':
                    $data = $this->runLottery($times_id, $baseX, $num, 'run');
                    $this->ajaxReturn($data, 'JSON');
                    break;
                case 'poke':
                    $data = $this->runLottery($times_id, $baseX, $num, 'poke');
                    $this->ajaxReturn($data, 'JSON');
                    break;
                default:
                    $this->success('你做什么？？');
            }

            exit;
        } else if (IS_AJAX && IS_GET) {

        }

        // 显示众筹抽奖修改页面
        $raise = $this->m2('raise')->where(['id' => $raiseId])->limit(1)->find();
        $times = D('RaiseLuckyResultEditView')->where(['A.raise_id' => $raiseId, 'A.type' => 1])->select();

        foreach($times as $row) {
            $participator = $this->m2('raise_lucky')->where(['raise_times_id' => $row['id']])->count();
            $count = $this->m2('order_wares')->join('__ORDER__ a on order_id=a.id')->where(['type' => 2, 'ware_id' => $raiseId, 'tips_times_id' => $row['id'],'status' => 1, 'act_status' => ['in', '1,2,3,4'],'order_pid'=>['EXP', 'IS NULL']])->count(); //参与此挡位的人数
            $_data['id'] = $row['id'];
            $_data['title'] = $row['title'];
            $_data['lucky_num'] = $row['lucky_num'];
            $_data['content'] = $row['content'];
            $_data['base_x'] = $row['base_x'];
            $_data['num'] = $row['num'];
            $_data['participator'] = $participator;
            $_data['count'] = $count;
            $data[] = $_data;
        }

        $this->assign([
            'raise' => $raise,
            'raise_times' => $data,
            'trade_date' => count($times) > 0 ? $times[0]['trade_date'] : '',
            'sh' => count($times) > 0 ? $times[0]['sh'] : '',
            'sz' => count($times) > 0 ? $times[0]['sz'] : '',
        ]);

        $this->view();
    }

    /**
     * @param $times_id 抽奖挡位id
     * @param $sh 上证指数
     * @param $sz 深证指数
     * @param $trade_date 交易日
     * @param $baseX 基准
     * @return mixed
     */
    private function saveLottreyResult($times_id, $sh, $sz, $trade_date, $baseX, $num) {
        $old = $this->m2('raise_lucky_result')->where(['raise_times_id' => $times_id])->find();
        $end_time = D('RaiseView')->where(['C.id' => $times_id])->getField('end_time'); // 众筹结束时间


        if (!empty($old)) {
            $d = $this->m2('raise_lucky_result')->where(['raise_times_id' => $times_id])->save(['sh' => $sh, 'sz' => $sz, 'trade_date' => $trade_date, 'base_x' => $baseX, 'lottery_time' => $end_time, 'num' => $num]);
        } else {
            $data['raise_times_id'] = $times_id;
            $data['sh'] = $sh;
            $data['sz'] = $sz;
            $data['trade_date'] = $trade_date;
            $data['base_x'] = $baseX;
            $data['lottery_time'] = $end_time;
            $data['num'] = $num;
//                            $data['info'] = $info;
            $d = $this->m2('raise_lucky_result')->add($data);
        }

        return $d;
    }

    /**
     * @param int $times_id 众筹挡位id
     * @param int $baseX 基数，没有，则默认抽取一名
     * @param int $num 开奖个数
     * @param string $action 'run' -- 开奖， ’poke‘ -- 模拟抽奖
     * @return string
     */
    public function runLottery($times_id, $baseX, $num, $action = 'run') {
//        $times_id = 230;
//        $baseX = 0;
//        $num = 1;
//        $action = 'poke';

        $lotteryInfo = $this->m2('raise_lucky_result')->where(['raise_times_id' => $times_id])->find();

        if ($lotteryInfo['status'] == 1 && $action === 'run') {
            $data['status'] = '0';
            $data['info'] = '已经开过奖了';

            return $data;
        }

        if (empty($lotteryInfo)) {
            $data['status'] = '0';
            $data['info'] = '还没有保存数据';
            return $data;
        }

        $sh = $lotteryInfo['sh'];
        $sz = $lotteryInfo['sz'];
        $participators = $this->m2('raise_lucky')->where(['raise_times_id' => $times_id])->count(); // 全部的参与者

        if ($participators == 0) {
            $data['status'] = '0';
            $data['info'] = '没有人参与抽奖';
            return $data;
        }

        $lucky_num = intval((string)$sh . (string)$sz) % $participators + 1; // 中奖号码
        $lucky_arr[] = $lucky_num; // 幸运号码数组

//        echo 'sh: ' . $sh . 'sz: ' . $sz . '相加：' . intval((string)$sh . (string)$sz);
        if($baseX > 0 && $num > 1) {
            // 以x为基准，
            $last_num = $this->m2('raise_lucky')->where(['raise_times_id' => $times_id])->order('id desc')->limit(1)->field('lucky_num')->find();

            $last_num = intval($last_num['lucky_num']);
            $last_num < $participators && ($last_num = $participators);
//            echo '最后一个抽奖号码：' . $last_num . "\n";


            for ($i = 1; $i < $num; $i++) {
                $lucky_num = $lucky_num + $baseX;

                if ($lucky_num > $last_num) {
                    $lucky_num =$lucky_num - $participators;
                }

                while (in_array($lucky_num, $lucky_arr)) {
                    $lucky_num = $lucky_num + 1;
                }

                $lucky_arr[] = $lucky_num;

                if (count($lucky_arr) >= $participators) {
                    break;
                }
            }
        }

//        echo '抽奖人数: ' . $participators ."\n";
//        echo '中奖号码: ' . implode(',', $lucky_arr) . "\n";

        if ($action === 'poke') {
            $data['status'] = '1';
            $data['info'] = implode(',', $lucky_arr);
            return $data;
        }

        $condition['raise_times_id'] = array('eq', $times_id);
        $condition['lucky_num'] = array('in', implode(',', $lucky_arr));
        $this->m2('raise_lucky')->where($condition)->save(['lucky_status' => 1]); // 设置中奖

        $unLuckyWhere['raise_times_id'] = array('eq', $times_id);
        $unLuckyWhere['lucky_num'] = array('not in', implode(',', $lucky_arr));
        $this->m2('raise_lucky')->where($unLuckyWhere)->save(['lucky_status' => -1]);
        $this->m2('raise_lucky_result')->where(['raise_times_id' => $times_id])->save(['status' => 1, 'lucky_num' => implode(',', $lucky_arr), 'run_time' => date('Y-m-d H:i:s')]); // 设置为已开奖状态

        $data['status'] = '1';
        $data['info'] = '开奖成功';
        $data['msg'] = $lucky_arr;

        return $data;
    }
}