<?php
namespace Home\Controller;
use Home\Common\MainController;

//@className 活动&食报&文章评论
Class CommentController extends MainController {

    /**
     * @apiName 获取评论列表
     *
     * @apiGetParam {string} token: 通信令牌
     * @apiGetParam {int} page: 分页页码
     *
     * @apiPostParam {int} tips_id: 活动ID
     * @apiPostParam {int} article_id: 文章ID
     * @apiPostParam {int} bang_id: 食报ID
     * @apiPostParam {int} member_id: 达人ID
     *
     * @apiSuccessResponse
     * [
     *     {
     *         "id": "23",
     *         "stars": "5",
     *         "type": "0",
     *         "type_id": "3042",
     *         "content": "不能吃芒果，有特别照顾吗？",
     *         "pics_group_id": "1",
     *         "datetime": "2016-03-31 11:55:03",
     *         "nickname": "? 妍妍  ?",
     *         "head_path": "http://img.m.yami.ren/themes/default/images/4_320x320.png",
     *         "tips_title": "吖咪分享会| 小花×Magic cici的奇幻塔罗",
     *         "category_name_tips": "其他",
     *         "pics": [
     *             "http://yummy194.cn/uploads/member/18565765105/55e51332d73ef.jpg",
     *             "http://yummy194.cn/uploads/member/13750344681/559a94a63853e.jpg"
     *         ]
     *     },
     *     {
     *         "id": "22",
     *         "stars": "5",
     *         "type": "0",
     *         "type_id": "3042",
     *         "content": "来深圳做活动一定通知我，一定捧场到底！",
     *         "pics_group_id": "1",
     *         "datetime": "2016-03-31 11:55:03",
     *         "nickname": "孤单过客?",
     *         "head_path": "http://wx.qlogo.cn/mmopen/cLNHynV9yoGAGyNdiarKmYyic8mQnP4ia4PJGJQl49q73a6icllNj8cibjtUorQKPeOYsa9NU3EDwXh8u1AJg1ajm8sYkDUH11Pps/0",
     *         "tips_title": "吖咪分享会| 小花×Magic cici的奇幻塔罗",
     *         "category_name_tips": "其他",
     *         "pics": [
     *             "http://yummy194.cn/uploads/member/18565765105/55e51332d73ef.jpg",
     *             "http://yummy194.cn/uploads/member/13750344681/559a94a63853e.jpg"
     *         ],
     *         "reply": []
     *     }
     * ]
     */
    Public function getList(){
        $tips_id = I('post.tips_id','');
        $goods_id = I('post.goods_id','');
        $article_id = I('post.article_id','');
        $bang_id = I('post.bang_id','');
        $member_id = I('post.member_id','');

        if(empty($tips_id) && empty($goods_id) && empty($article_id) && empty($bang_id) && empty($member_id))$this->error('非法访问！');
        $page = I('get.page', 1);

        if(!empty($tips_id)){
            $type = 0;
            $type_id = $tips_id;
        }
        if(!empty($goods_id)){
            $type = 1;
            $type_id = $goods_id;
        }
        if(!empty($article_id)){
            $type = 3;
            $type_id = $article_id;
        }
        if(!empty($bang_id)){
            $type = 2;
            $type_id = $bang_id;
        }
        if(isset($type))
            $condition = ['type' => $type, 'type_id' => $type_id,'A.status'=>1];
        else
            $condition = ['A.status'=>1];
        if(!empty($member_id)){
            //找出该达人的活动和商品的评论
            $daren_rs = M('MemberTag')->where(['member_id'=>$member_id, 'tag_id' => 18])->find();
            if(empty($daren_rs))$this->error('你查看的用户并非达人');
            $tipsIds = M('Tips')->where(['member_id'=>$member_id])->getField('id',true);
            $goodsIds = M('Goods')->where(['member_id'=>$member_id])->getField('id',true);
            if(!empty($tipsIds) && !empty($goodsIds)){
                $condition = "(A.type=0 and A.type_id in(".join(',',$tipsIds).")) or (A.type=1 and A.type_id in(".join(',',$goodsIds)."))";
            }elseif(!empty($tipsIds)){
                $condition = "(A.type=0 and A.type_id in(".join(',',$tipsIds)."))";
            }elseif(!empty($goodsIds)){
                $condition = "(A.type=1 and A.type_id in(".join(',',$goodsIds)."))";
            }else{
                $this->ajaxReturn([]);
            }
        }

        //$data = D('CommentView')->where(['type' => $type, 'type_id' => $type_id])->page($page, 10)->select();
        $data = D('CommentView')->where($condition)->page($page, 10)->order('A.id desc')->select();
        \Think\Log::write('微信授权commen：'.D('CommentView')->getLastSql());
        if(!empty($data)){
            $ids = $pic_ids = [];
            foreach($data as $k=>$row){
                $data[$k]['head_path'] = thumb($row['head_path'], 2);
                if(!empty($row['pics_group_id'])){
                    $pic_ids[] = $row['pics_group_id'];
                }
                $ids[] = $row['id'];
                //初始化pics图组
                $data[$k]['pics'] = [];
                $data[$k]['stars'] = (int)$row['stars'];
            }

            if(!empty($pic_ids) || !empty($ids)){
                //评论中的图组
                $pics = M('pics')->where(['group_id' => ['IN', join(',', $pic_ids)]])->select();
                //评论中的@
                //$ats = D('CommentAtView')->where(['comment_id' => ['IN', join(',', $ids)]])->select();
                //评论中的举报
                $reports = M('feedback')->where(['type' => 3, 'type_id' => ['IN', join(',', $ids)], 'member_id' => session('member.id')])->getField('type_id', true);

                foreach($data as $key => $row){
                    //评论中的图组
                    $_pics = [];
                    foreach($pics as $pic){
                        if($row['pics_group_id'] == $pic['group_id']){
                            $_pics[] = thumb($pic['path'], 5);
                        }
                    }
                    $data[$key]['pics'] = $_pics;
                    /*//评论中的@
                    $_ats = [];
                    foreach($ats as $at){
                        if($at['comment_id'] == $row['id']){
                            $_ats[] = [
                                'member_id' => $at['member_id'],
                                'nickname' => $at['nickname']
                            ];
                        }
                    }
                    $data[$key]['ats'] = $_ats;*/

                    //评论是否被举报
                    $data[$key]['is_report'] = '0';
                    if(session('?member') && !empty($reports)){
                        if(in_array($row['id'], $reports))$data[$key]['is_report'] = '1';
                    }
                }

                //获取回复评论
                $reply = M('MemberComment')->where(['pid'=>['IN',join(',',$ids)]])->select();

                $delKey = [];
                foreach($data as $key=>$row){
                    if(!empty($row['pid']))$delKey[] = $key;
                    $reply_rs = null;
                    foreach($reply as $row2){
                        if($row['id'] == $row2['pid']){
                            $reply_rs = $row2;
                        }
                    }
                    $rs = empty($reply_rs)? [] :[$reply_rs];
                    $data[$key]['reply'] = $rs;
                    /*$reply = null;
                    foreach($data as $key2=>$row2){
                        if($row['id'] == $row2['pid']){
                            $reply = $row2;
                            $del_key[] = $key2;
                        }
                    }
                    $reply = empty($reply)?array():$reply;
                    $data[$key]['reply'] = $reply;*/
                }
                foreach($delKey as $r){
                    unset($data[$r]);
                }

                /*foreach($del_key as $r){
                    unset($data[$r]);
                }*/
            }
        }

        $datas = [];
        foreach($data as $row){
            $datas[] = $row;
        }

        $this->ajaxReturn($datas);
    }

    /**
     * @apiName 提交订单评论
     *
     * @apiGetParam {string} token: 通信令牌
     * @apiPostParam {int} order_id: 要操作的订单ID
     * @apiPostParam {int} stars: 评分星级(1-5)
     * @apiPostParam {int} article_id: 文章ID
     * @apiPostParam {int} bang_id: 食报ID
     * @apiPostParam {string} content: 评论内容
     * @apiPostParam {string} pic_ids: 评论图片id(多个id用逗号隔开)
     * @apiPostParam {int} reply_id: 回复的评论ID
     *
     * @apiSuccessResponse
     * {
     *    "info" : "评论成功!",
     *    "status" : 1,
     *    "url" : ""
     * }
     * @apiErrorResponse
     * {
     *    "info" : "错误原因",
     *    "status" : 0,
     *    "url" : ""
     * }
     */
    Public function add(){
        if(!session('?member'))$this->error('尚未登录,无法访问!');
        $order_id = I('post.order_id');
        $stars = (int)I('post.stars', 5);
        $article_id = I('post.article_id');
        $bang_id = I('post.bang_id');
        $bb = strstr(I('post.content'),"text = '")?explode("'; clipsToBounds = YES;",str_replace("text = '",'',strstr(I('post.content'),"text = '"))):I('post.content');
        if(strstr(I('post.content'),"text = '") !==false){
            $content =$bb[0];
        }else{
            $content =$bb;
        }
        $pic_ids = I('post.pic_ids');
        $reply_id = I('post.reply_id', null);

        if(empty($order_id) && empty($article_id) && empty($bang_id))$this->error('非法访问！');
        if(empty($content))$this->error('评论内容不能为空!');

        if(!empty($order_id)){
            if($stars > 5 || $stars < 1)$this->error('评论星级必须是1~5的数值!');
            //判断订单是否属于该用户
            $rs = M('order')->where(['id' => $order_id, 'member_id' => session('member.id'), 'status' => 1])->find();
            if(empty($rs))$this->error('订单不属于你,不能评论!');
            //判断订单状态
            if(!in_array($rs['act_status'], [2,3,4]))$this->error('该订单不属于已完成状态,无法评论!');
            //判断是否已评论
            if(!empty($rs['comment_id']))$this->error('您已经评论过该订单!');

            $ware = M('OrderWares')->where(['order_id' => $order_id])->find();

            $type = $ware['type'];
            $type_id = $ware['ware_id'];
        }
        if(!empty($article_id)){
            $type = 3;
            $type_id = $article_id;
        }
        if(!empty($bang_id)){
            $type = 2;
            $type_id = $bang_id;
        }

        //插入图组
        $pics_group_id = '';
        if(!empty($pic_ids)){
            $pics_group_id = M('PicsGroup')->add(['type' => 2]);
            M('pics')->where(['id' => ['IN', $pic_ids], 'member_id' => session('member.id')])->save(['group_id' => $pics_group_id]);
        }

        $data = [
            'member_id' => session('member.id'),
            'stars' => $stars,
            'type' => $type,
            'type_id' => $type_id,
            'content' => $content,
            'pid' => $reply_id
        ];
        if(!empty($pics_group_id))$data['pics_group_id'] = $pics_group_id;

        //插入评论
        $comment_id = M('MemberComment')->add($data);

        if(!empty($order_id)){
            //更改订单状态
            M('order')->where(['id' => $order_id])->save(['act_status' => 4, 'comment_id' => $comment_id]);
            //通知该活动达人
            /*$messageId = M('message')->data(['member_id'=>'','type'=>0,'content'=>'有人评价了你的活动'])->add();
            if($type == 0){
                $member_id = M('Tips')->where(['id'=>$type_id])->getField('member_id');
            }elseif($type == 1){
                $member_id = M('Goods')->where(['id'=>$type_id])->getField('member_id');
            }
            M('MemberMessage')->data(['member_id'=>$member_id,'message_id'=>$messageId])->add();*/
        }/*else{
            $replys = [];
            if(!empty($reply_id))$replys[] = $reply_id;
            //从评论内容中分析出@
            preg_match_all('/@(.+?) /', $content , $arr);
            if(!empty($arr[1])){
                $names = [];
                $rs = $arr[1];
                foreach($rs as $row){
                    $names[] = $row;
                }
                $ids = M('member')->where(['nickname'=>['IN', join(',', $names)]])->getField('id', true);
                $replys = array_merge($replys, $ids);
            }
            foreach($replys as $r){
                M('MemberCommentAt')->add(['comment_id' => $comment_id, 'member_id' => $r]);
            }
        }*/

        if($rs){
            $this->success('评论成功!');
        }else{
            $this->error('评论失败!');
        }
    }

}