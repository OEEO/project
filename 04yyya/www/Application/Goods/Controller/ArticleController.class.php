<?php
namespace Goods\Controller;
use Goods\Common\MainController;

//@className 文章类商品接口
Class ArticleController extends MainController {

    /**
     * @apiName 获取文章列表
     *
     * @apiGetParam {string} token: 通信令牌
     * @apiGetParam {int} member_id: 会员ID
     *
     * @apiSuccessResponse
     * [
     *     {
     *         "id": "2",
     *         "title": "测试文章发布",
     *         "author": "不是我原创",
     *         "category_id": "0",
     *         "content": null,
     *         "pic_id": "13546",
     *         "status": "1",
     *         "member_id": "6395",
     *         "datetime": "2016-04-05 16:28:04",
     *         "catname": null,
     *         "path": "http://img.m.yami.ren/public/20160331/56fcc4464c27d_640x420.jpg"
     *     },
     *     {
     *         "id": "2",
     *         "title": "测试文章发布",
     *         "author": "不是我原创",
     *         "category_id": "0",
     *         "content": null,
     *         "pic_id": "13546",
     *         "status": "1",
     *         "member_id": "6395",
     *         "datetime": "2016-04-05 16:28:04",
     *         "catname": null,
     *         "path": "http://img.m.yami.ren/public/20160331/56fcc4464c27d_640x420.jpg"
     *     }
     * ]
     */
    Public function getList(){
        $member_id = I('post.member_id', null);
        if(!empty($member_id))
            $rs = D('ArticleView')->where(['member_id' => $member_id, 'status' => 1])->select();
        else
            $rs = D('ArticleView')->where(['member_id' => session('member.id'), 'status' => ['IN', [0,1]]])->select();
        $data = [];
        foreach($rs as $row){
            $row['path'] = thumb($row['path'], 6);
            $data[] = $row;
        }
        $this->ajaxReturn($data);
    }

    /**
     * @apiName 获取文章详情
     *
     * @apiGetParam {string} token: 通信令牌
     * @apiGetParam {int} article_id: 文章ID
     *
     * @apiSuccessResponse
     * {
     *     "id": "2",
     *     "title": "测试文章发布",
     *     "author": "不是我原创",
     *     "category_id": "0",
     *     "content": null,
     *     "pic_id": "13546",
     *     "status": "1",
     *     "member_id": "6395",
     *     "datetime": "2016-04-05 16:28:04",
     *     "catname": null,
     *     "path": "http://img.m.yami.ren/public/20160331/56fcc4464c27d_640x420.jpg",
     *     "comment_count" : "22",
     *     "give_count" : "24514.12",
     *     "tips": [
     *         {
     *             "id": "3042",
     *             "nickname": "kl",
     *             "wealth": 999,
     *             "customers": "315",
     *             "member_id": "6395",
     *             "headpic": "http://yummy194.cn/uploads/member/18565765105/55e51332d73ef.jpg",
     *             "mainpic": "http://yummy194.cn/uploads/20160309/56df96d630bd5.jpg",
     *             "catname": "其他",
     *             "tagname": "中餐",
     *             "title": "吖咪分享会| 小花×Magic cici的奇幻塔罗",
     *             "price": "88.00",
     *             "start_time": "1459999999",
     *             "end_time": "1459999999",
     *             "address": "广州市越秀区建设六马路47号201（C家美食工作室"
     *         }
     *     ]
     * }
     */
    Public function getDetail(){
        $article_id = I('post.article_id');
        if(empty($article_id))$this->error('非法访问!');
        $rs = D('ArticleView')->where(['id' => $article_id])->find();
        if(empty($rs))$this->ajaxReturn([]);
        if($rs['status'] != 1 && session('member.id') != $rs['member_id'])$this->error('文章不存在或尚未发布!');

        $rs['path'] = thumb($rs['path'], 6);

        //文章的评论数量
        $rs['comment_count'] = M('MemberComment')->where(['type' => 3, 'type_id' => $article_id])->count();

        //引用过的食报ID
        $bang_ids = M('bang')->where(['type' => 1, 'type_id' => $article_id])->getField('id', true);
        //根据食报ID查询出评论数量
        if(!empty($bang_ids)){
            $rs['comment_count'] += M('MemberComment')->where(['type' => 2, 'type_id' => ['IN', $bang_ids]])->count();
        }

        //根据文章ID查询出打赏次数
        $rs['give_count'] = M('Give')->where(['target' => 1, 'target_id' => $article_id, 'type' => 1])->count();

        //查询出关联的活动ID列表
        $ids = M('ArticleRelation')->field(['tips_id' => 'id'])->where(['article_id' => $article_id])->select();
        $rs['tips'] = [];
        if(!empty($ids)){
            $tips = new TipsController();
            $rs['tips'] = $tips->getlist($ids);
        }
        $this->ajaxReturn($rs);
    }

}

