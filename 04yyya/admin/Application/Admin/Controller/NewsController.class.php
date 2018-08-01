<?php
/**
 * Created by PhpStorm.
 * User: Cherry
 * Date: 2017/5/8 0008
 * Time: 11:45
 */

namespace Admin\Controller;
use Admin\Controller\MainController;

class NewsController extends MainController {

    //新闻列表
    public function index(){
        $this->actname = '新闻列表';

        $condition = "A.status <> 3";
        if(IS_GET && $_GET!=null){                      //条件查询

            $news_id = I('get.id');
            $news_title = I('get.title');
            $member_nickname = I('get.member');
            $news_status = I('get.status');
            $news_category = I('get.category');

            $news_id &&  $condition .= " AND A.id='$news_id' ";
            $news_title && $condition .= " AND A.title LIKE '%$news_title%' ";
            $member_nickname && $condition .= " AND D.nickname LIKE '%$member_nickname%' ";
            $news_category && $condition .= " AND A.category_id = '$news_category' ";
            $news_status && $condition .= " AND A.status = '$news_status' ";

            $this->assign('search_id',$news_id);
            $this->assign('search_title',$news_title);
            $this->assign('search_member',$member_nickname);
            $this->assign('search_category',$news_category);
            $this->assign('search_status',$news_status);

        }
        $datas['datas'] = D('NewsView')->where($condition)->page(I('get.page'), 20)->order('A.id desc')->group('A.id')->select();
//        print_r($datas);
//        print_r(D('NewsView')->getLastSql());
//        exit;
        if(!empty($datas['datas'])){
            foreach ($datas['datas'] as $key=>$row) {
                $datas['datas'][$key]['tips_id'] = $row['id'];


                $datas['datas'][$key]['member_nickname'] = '昵称：'.$row['member_nickname']."<br/>".'Host ID：'.$row['member_id']."<br/>";
            }
        }

        //table页面参数设置
        $datas['operations'] = [
            '通过' => [
                'style' => 'success',
                'fun' => 'checkout(%id, 1)',
                'condition' => "%status==0"
            ],
            '拒绝' => [
                'style' => 'danger',
                'fun' => 'checkout(%id,0)',
                'condition' => "%status==0"
            ],
            '修改' => "location.href='newsUpdate.html?news_id=%id'",
            '删除' => [
                'condition' => "%status!=0 &&%status!=3",
                'style' => 'danger',
                'fun' => "dataDelete(%id)"
            ]
        ];
        $datas['pages'] = [
            'sum' => D('NewsView')->where($condition)->count('DISTINCT A.id'),
            'count' => 20,
        ];
        $datas['lang'] = [
            'id' => 'ID',
            'title' => '标题',
            'member_nickname' => '发布者',
            'category_name' => '分类',
        ];

        $this->assign($datas);

        $this->view();
    }

    //众筹添加
    public function newsAdd(){
        $this->actname = '新闻添加';
        if(IS_AJAX && IS_POST){
            if(!isset($_POST['submit']) && !empty($_POST['member_id'])) {
                //选择达人并创建新活动
                $data = [
                    'member_id' => I('post.member_id'),
                    'title' => '标题',
                    'category_id' => null,
                    'abstract' => '摘要',
                    'content' => '内容',
                ];
                $id = $this->m2('news')->add($data);
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


        //获取众筹分类
        $categorys = $this->m2('category')->field(['id', 'name'])->where(['type' => 7])->order(['order'])->select();
        $this->assign([
            'categorys' => $categorys,
        ]);
        $this->view();
    }

    //修改
    public function newsUpdate(){
        $this->actname = '新闻修改';
        if (IS_AJAX && IS_POST) {
            if (isset($_POST['submit']) && $_POST['submit'] == 0) {
                //保存并预览
                $this->save();
            }
            $this->error('非法提交');
            exit;
        }
        $news_id = I('get.news_id', null);
        $rs = D('NewsEditView')->where(['id' => $news_id])->find();
        $rs['path'] = thumb($rs['path'],11);

        if (empty($rs)) {
            $this->error('要修改的活动不存在!');
        }

        //获取众筹分类
        $categorys = $this->m2('category')->field(['id', 'name'])->where(['type' => 7])->order(['order'])->select();
        $rs['content']= preg_replace('/\[img(.*?)\/\]/', '<img$1>', $rs['content']);

        $data = [
            'data' => $rs,
            'categorys' => $categorys,
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

    //提交编辑内容
    private function save(){
        $news_id = I('post.news_id');
        $member_id = I('post.member_id');
        $rs = $this->m2('news')->where(['id' => $news_id, 'member_id' => $member_id])->find();
        if (empty($rs)) {
            $this->error('非法提交!');
        }
        $this->m2()->startTrans();
        $data = ['member_id' => $member_id];
        $data['category_id'] = I('post.category_id');
        $data['title'] = I('post.title');
        $data['pic_id'] = I('post.pics_id');
        $data['content'] = I('post.content');
        $data['abstract'] = I('post.abstract');
        $data['content'] = preg_replace(['/<script.*?>.*?<\/script>/', '/<iframe.*?>.*?<\/iframe>/', '/<iframe.*?\/>/', '/<textarea.*?>.*?<\/textarea>/', '/<img(.*?)>/'], ['', '', '', '','[img$1/]'], $_POST['content']);
        if(empty($data['title']))$this->error('众筹标题不能为空！');
        if(empty($data['abstract']))$this->error('众筹摘要不能为空！');

        $this->m2('MemberApply')->where(['member_id'=>$member_id,'type'=>7,'type_id'=>$news_id,'is_pass'=>0])->delete();
        $this->m2('MemberApply')->data(['member_id'=>$member_id,'type'=>7,'type_id'=>$news_id,'is_pass'=>0])->add();

        if (!empty( $data['pic_id'])) {
            $pics = $this->m2('pics')->where(['id' =>  $data['pic_id']])->find();
            if(!empty($pics)){
                $_data = [
                    'type' =>6,
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
            }
        }

        if(!empty($_data)) {

            $this->m2('news')->where(['id' => $news_id])->save($_data);
            $this->m2()->commit();
            $this->success($news_id);
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

    //审核
    public function checkout(){
        if(IS_AJAX){
            $data['id'] = I('post.id');
            $oper = I('post.oper');
            $reason = I('post.reason');
            $select_reason = I('post.select_reason');
            $select_array = array(0=>'活动分类不正确',1=>'活动标签不正确',2=>'图片有第三方水印',3=>'活动标题或小标题有极限词');

            if($oper == '1'){//上架&通过
                $rs = $this->m2('news')->where($data)->find();

                if($rs){
                    $data['status'] = 2;
                    $this->m2('news')->save($data);
                    $this->m2('MemberApply')->where(['type'=>7,'type_id'=>$data['id'],'is_pass'=>0])->data(['is_pass'=>1,'update_time'=>time()])->save();
                    $this->success('活动已通过');
                }else{
                    $this->error('找不到该活动');
                }
            }elseif($oper == '0'){//拒绝
                $rs = $this->m2('news')->where($data)->find();

                if($rs){
                    $data['status'] = 1;
                    //$data['start_buy_time'] = null;
                    $this->m2('news')->save($data);

                    //拼接拒绝理由
                    $select_rs = '';
                    if(!empty($select_reason)){
                        foreach($select_reason as $key=>$row){
                            $select_rs .= ($key+1).':'.$select_array[$row].',';
                        }
                    }
                    //更新提交审核记录
                    $this->m2('MemberApply')->where(['type'=>7,'type_id'=>$data['id'],'is_pass'=>0])->data(['is_pass'=>2,'refusal_reason'=>$select_rs.'-'.$reason,'update_time'=>time()])->save();

                    $this->success('活动已拒绝');
                }else{
                    $this->error('找不到该活动');
                }
            }
        }
    }

    //删除
    Public function delete(){
        if(IS_AJAX){
            $data['id'] = I('post.id');
            $data['status'] = 3;
            $this->m2('news')->save($data);
            $this->success('删除成功！');
            exit;
        }
        $this->error('非法访问！');
    }


    //导出excel表
    public function courseExport(){
        $member_nickname = I('get.member');
        $search_title = I('get.title');
        $search_status = I('get.status');

        $condition ='';
        $search_status? $condition .= " A.status='$search_status' ":"  $condition = 'A.status =1 '";
        $search_title && $condition .= " AND A.title LIKE '%$search_title%'";
        $member_nickname && $condition .= " AND B.nickname LIKE '%$member_nickname%' ";
        $tips_mod = D('NewsExportView');
        $datas = $tips_mod->where($condition)->order('id desc')->order('id')->group('tips_times_id')->select();
        foreach($datas as $key=>$row){

            $datas01[$key]['id'] = $row['id'];
            $datas01[$key]['member_nickname'] = $row['member_nickname'];
            $datas01[$key]['title'] = $row['title'];
            if($datas[$key]['status']==0)$datas01[$key]['status'] = '删除';
            if($datas[$key]['status']==1)$datas01[$key]['status'] = '正常';
            if($datas[$key]['status']==2)$datas01[$key]['status'] = '下架';

            unset($datas[$key]['category_id']);
            unset($datas[$key]['tips_sub_citys_id']);
            unset($datas[$key]['theme_tipsorgoods_type']);
            unset($datas[$key]['theme_id']);
            //unset($datas[$key]['status']);
            unset($datas[$key]['tips_times_id']);
            unset($datas[$key]['citys_id']);
        }
        $title = ['活动ID','用户昵称','活动名称','活动分类','开始时间','结束时间','价格','最小人数','最大人数','购买数','城市名称','活动状态','标签','专题','期数','总期数'];
        toXls($title,$datas01,'活动列表');
    }
}