<?php
namespace Daren\Controller;
use Daren\Common\MainController;

// @className 我的文章
Class ArticleController extends MainController {

    /**
     * @apiName 获取文章分类列表
     *
     * @apiGetParam {string} token: 通信令牌
     *
     * @apiSuccessResponse
     * [
     *     {
     *         "id": "16",
     *         "name": "分类一"
     *     },
     *     {
     *         "id": "17",
     *         "name": "分类二"
     *     }
     * ]
     */
    Public function catlist(){
        $rs = M('category')->field(['id', 'name'])->where(['type' => 2])->order('`order` asc')->select();
        $this->ajaxReturn($rs);
    }

    /**
     * @apiName 保存文章草稿
     *
     * @apiGetParam {string} token: 通信令牌
     *
     * @apiPostParam {int} id: 要保存的文章ID(忽略则为添加)
     * @apiPostParam {int} pic_id: 文章封面图片ID
     * @apiPostParam {string} title: 文章标题
     * @apiPostParam {string} author: 文章作者(忽略则为原创)
     * @apiPostParam {string} content: 文章内容
     * @apiPostParam {int} category_id: 分类ID
     *
     * @apiSuccessResponse
     * {
     *     "info": {
     *         "id": "12345"
     *     },
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
        $id = I('post.id', null);
        $data['pic_id'] = I('post.pic_id', null);
        $data['category_id'] = I('post.category_id', null);
        $data['title'] = I('post.title', null);
        $data['author'] = I('post.author', null);
        $data['content'] = $_POST['context'];
        $data['member_id'] = session('member.id');

        if(empty($id)){
            $id = M('article')->add($data);
        }else{
            M('article')->where(['id' => $id])->save($data);
        }
        $this->success([
            'id' => $id
        ]);
    }

    /**
     * @apiName 保存文章草稿
     *
     * @apiGetParam {string} token: 通信令牌
     *
     * @apiPostParam {int} article_id: 要发布的文章ID
     *
     * @apiSuccessResponse
     * {
     *     "info": "文章发布成功!",
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
    Public function submit(){
        $article_id = I('post.article_id');
        $member_id = session('member.id');
        $rs = M('Article')->where(['member_id' => $member_id, 'id' => $article_id])->find();
        if(empty($rs))$this->error('活动不存在!');
        if($rs['status'] > 1)$this->error('文章不处于草稿状态!');
        $data = ['id' => $article_id];
        if(empty($rs['category_id']))$this->error('文章分类不能为空!');
        if(empty($rs['title']))$this->error('文章标题不能为空!');
        if(empty($rs['author']))$data['author'] = session('member.nickname');
        if(empty($rs['content']))$this->error('文章内容不能为空!');
        if(empty($rs['pic_id']))$this->error('文章封面图不能为空!');
        $data['status'] = 1;
        M('article')->save($data);
        $this->success('文章发布成功!');
    }

    /**
     * @apiName 保存文章草稿
     *
     * @apiGetParam {string} token: 通信令牌
     *
     * @apiPostParam {int} article_id: 要关联的文章ID
     * @apiPostParam {int} tips_id: 要关联的活动ID
     *
     * @apiSuccessResponse
     * {
     *     "info": "关联成功!"|"取消关联成功!",
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
    Public function relationTips(){
        $article_id = I('post.article_id');
        $tips_id = I('post.tips_id');
        $rs = M('article')->where(['id' => $article_id, 'member_id' => session('member.id')])->find();
        if(empty($rs))$this->error('该文章不属于你,无权操作!');
        $rs = M('ArticleRelation')->where(['article_id' => $article_id, 'tips_id' => $tips_id])->find();
        if(empty($rs)){
            M('ArticleRelation')->add(['article_id' => $article_id, 'tips_id' => $tips_id]);
            $this->success('关联成功!');
        }else{
            M('ArticleRelation')->where(['article_id' => $article_id, 'tips_id' => $tips_id])->delete();
            $this->success('取消关联成功!');
        }
    }


    /**
     * @apiName 文章详细页
     *
     * @apiGetParam {string} token: 通信令牌
     * @apiPostParam {int} member_id: 会员ID
     *
     * @apiSuccessResponse
     *  {
     *    "id": "2",
     *    "name": null,
     *    "title": "测试文章发布",
     *    "author": "不是我原创",
     *    "content": null,
     *    "datetime": "2016-04-05 16:28:04"
     *   }
     *
     */
    public function articleDetail(){
        $article_id = I('post.article_id',null);

        if(empty($article_id))$this->ajaxReturn(array());

        $data = M('Article')->join('__CATEGORY__ ON __ARTICLE__.category_id=__CATEGORY__.id','LEFT')->where(['ym_article.id'=>$article_id,'status'=>1])->field('ym_article.id,name,title,author,content,pic_id,ym_article.datetime')->find();
        $data['path'] = M('pics')->where(['id'=>$data['pic_id']])->getField('path');
        $data['path'] = thumb($data['path'],6);
        $this->ajaxReturn($data);
    }

    /**
     * @apiName 文章列表
     *
     * @apiGetParam {string} token: 通信令牌
     * @apiPostParam {int} member_id: 会员ID（默认当前登录者ID）
     * @apiPostParam {int} category: 分类ID（默认null）
     *
     * @apiSuccessResponse
     *  {
     *    "article_id": "3",
     *    "member_id": "6395",
     *    "title": "天天看到你",
     *    "author": "阿杜",
     *    "datetime": "2016-04-08 10:11:24",
     *    "nickname": "kl",
     *    "article_path": "http://img.m.yami.ren/public/20160331/56fce6a26056d_640x420.png",
     *    "member_path": "http://wx.qlogo.cn/mmopen/jZUIEF2vTww9jc6eZXw3TbPyFk3wrRPkffRGZPDGueQmYic91sIPta5QFyOmkeZQ4LqsoYYGIkhH2Kgo8fibk7GaNLUVJ2QrKo/0"
     *  }
     *
     */
    public function articleList(){
        $member_id = I('post.member_id',session('member.id'));
        $category = I('post.category',null);


        $where = ['A.member_id'=>$member_id];
        if(!empty($category))$where['A.category_id'] = $category;

        $data = D('ArticleListView')->where(['A.member_id'=>$member_id])->select();
        foreach($data as $key=>$row){
            $data[$key]['article_path'] = thumb($data[$key]['article_path'],6);
            $data[$key]['member_path'] = thumb($data[$key]['member_path'],2);
        }
        $this->ajaxReturn($data);
    }

}