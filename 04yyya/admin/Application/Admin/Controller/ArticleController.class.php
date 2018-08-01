<?php
namespace Admin\Controller;
use Admin\Controller\MainController;

class ArticleController extends MainController{
    Protected $pagename = '文章管理';

    public function add(){
        $this->actname = '添加';

        if (IS_POST && $_FILES) {
            $data = $this->upload(1,1);  //1:goods文件夹  1:640x420缩略图  4:208x208副缩略图(商品)
            exit;
        }
        //录入数据库
        if(IS_POST){
            print_r($_POST);exit;
        }

        $this->view();
    }


    public function index(){
        $this->actname = '食报列表';

        $pageSize = 20;

        $member_mod = D('BangView');
        $datas['datas'] = $member_mod->page(I('get.page'), $pageSize)->select();
        foreach($datas['datas'] as $row){
            $pic_groups[] = $row['pic_group_id'];
        }
        $pic_groups = join(',',$pic_groups);
        //找图组
        $group = $this->m2('pics')->where(['group_id'=>['IN',$pic_groups]])->field('group_id,path')->select();
        foreach($datas['datas'] as $key=>$row){
            foreach($group as $row2){
                if($row['pic_group_id'] == $row2['group_id']){
                    $datas['datas'][$key]['group_path'] .= "<img src='".pathFormat($row2['path'])."' width='50px' height='50px'>";
                }
            }
            switch($row['type']){
                case 0:
                    $datas['datas'][$key]['bang_type'] = '普通食报';
                    break;
                case 1:
                    $datas['datas'][$key]['bang_type'] = '文章食报';
                    break;
                case 2:
                    $datas['datas'][$key]['bang_type'] = '活动食报';
            }
            $datas['datas'][$key]['send_time'] = ($row['send_time']==0)?$row['datetime']:date('Y-m-d H:i:s');

        }


        $datas['operations'] = array(
            '查看文章' => array(
                'style' => 'success',
                'fun' => 'showArticle(%type_id)',
                'condition' => "%type == 1"
            ),
            '查看活动' => array(
                'style' => 'success',
                'fun' => 'showTips(%type_id)',
                'condition' => "%type == 2"
            ),
            '删除' => "dataDelete(%id)",
        );
        $datas['pages'] = array(
            'sum' => $member_mod->count(),
            'count' => $pageSize,
        );

        $datas['lang'] = array(
            'id' => 'ID',
            'nickname'=> '发布者',
            'content' => '食报内容',
            'bang_type'=> '类型',
            'path'=> array('主图', '<img src="http://yummy194.cn/%*%" width="50px" height="50px" />'),
            'group_path' =>  '图组',
            'send_time' => '发送时间'
        );

        $this->assign($datas);
        $this->view();

    }

    Public function articleList(){
        $this->actname = '文章列表';

        $pageSize = 20;

        $datas['datas'] = D('ArticleListView')->page(I('get.page'), $pageSize)->select();

        foreach($datas['datas'] as $key=>$row){
            $datas['datas'][$key]['path'] = pathFormat($row['path']);
        }

        $datas['operations'] = array(
            '文章详情' => "articleDetail(%id)",
            '删除' => "dataDelete(%id)",
        );
        $datas['pages'] = array(
            'sum' => D('ArticleListView')->count(),
            'count' => $pageSize,
        );

        $datas['lang'] = array(
            'id' => 'ID',
            'catname'=>'分类',
            'nickname'=> '发布者',
            'author' => '原作者',
            'title'=> '标题',
            'path'=> array('主图', '<img src="%*%" width="50px" height="50px" />'),
            'datetime' => '发表时间'
        );

        $this->assign($datas);
        $this->view();
    }

    Public function articleDelete(){
        if(IS_AJAX){
            $id = I('post.id');
            $rs = $this->m2('article')->where(['id'=>$id])->delete();
            if($rs !== false){
                $this->success('删除成功');
            }else{
                $this->error('删除失败');
            }
        }else{
            $this->error('非法访问');
        }
    }

    Public function delete(){
        if(IS_AJAX){
            $id = I('post.id');

            $re = $this->m2('bang')->where(['id'=>$id])->delete();
            if($re!==false){
                $this->success('删除成功！');
            }else{
                $this->error('删除失败！');
            }

            exit;
        }
        $this->error('非法访问！');
    }

    Public function parameter(){
        $this->actname = '食报参数查改';

        $operation = I('post.operation',1);//1查数据，2改数据
        if(IS_AJAX){
            if($operation == 1){
                $data = $this->m2('config')->where('type=1')->field('threshold,value')->select();
                $this->ajaxReturn($data);
            }elseif($operation == 2){
                $data = I('post.data');
                foreach($data as $row){
                    $this->m2('config')->data(array('value'=>$row['value']))->where(array('type'=>1,'threshold'=>$row['threshold']))->save();
                }
                $this->success('修改成功');
            }
        }
    }

}