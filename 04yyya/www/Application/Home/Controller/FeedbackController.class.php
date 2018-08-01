<?php
namespace Home\Controller;
use Home\Common\MainController;

//@className 问答系统
Class FeedbackController extends MainController {

    /**
     * @apiName 获取问答列表(适应3月21日之前的接口-IOS)
     *
     * @apiGetParam {string} token: 通信令牌
     * @apiGetParam {int} page: 分页页码
     *
     * @apiPostParam {int} raise_id: 众筹ID
     *
     * @apiSuccessResponse
     * {
     *     "info": "提交成功!",
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
    public function getlist(){
        $page = I('get.page', 1);
        $raise_id = I('post.raise_id');

        if(!empty($raise_id)){
            $type = 3;
            $type_id = $raise_id;
        }

        $rs = D('FeedbackView')->where(['type' => $type, 'type_id' => $type_id])->order('A.id desc')->page($page, 10)->select();
        if(empty($rs))$this->put([]);

        $data = [];
        foreach($rs as $row){
            $row['path'] = thumb($row['path'], 2);
            $data[] = $row;
        }
        $this->put($data);
    }

    /**
     * @apiName 获取问答列表（最新的-2017-3-21）
     *
     * @apiGetParam {string} token: 通信令牌
     * @apiGetParam {int} page: 分页页码
     *
     * @apiPostParam {int} raise_id: 众筹ID
     *
     * @apiSuccessResponse
     * {
     *     "info": "提交成功!",
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
    public function getfeedlist(){
        $page = I('get.page', 1);
        $raise_id = I('post.raise_id');
        $member_id = session('member.id');
        $is_reply = 0;
        if(!empty($member_id)){
            $tag_id = M('Tag')->where(['name'=>['LIKE','%众筹【ID：'.$raise_id.'】%']])->getField('id');
            $reply_count = M('member_tag')->where(['member_id'=>$member_id,'tag_id'=>$tag_id])->count();
            if(isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'm.yami.ren') === false) {
                if ($reply_count>0 || session('member.id') == 55) $is_reply=1;
            }else{
                if ($reply_count>0 || in_array(session('member.id'),[278514,278518,34593]) == true) $is_reply=1;
            }
        }
        if(!empty($raise_id)){
            $type = 3;
            $type_id = $raise_id;
        }

        $rs = D('FeedbackView')->where(['type' => $type, 'type_id' => $type_id])->order('A.id desc')->page($page, 10)->select();
        if(empty($rs)){
            $data['list'] = $rs;
            $data['is_reply'] = $is_reply;
            $this->put($data);
        }

        $data = [];
        foreach($rs as $row){
            $row['path'] = thumb($row['path'], 2);
            if(!empty($row['answer'])){
                if(isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'm.yami.ren') === false) {
                    if ($row['answer_member_id'] == 55 || $row['answer_member_id'] == '' ) $row['answer_nickname']='吖咪酱';
                }else{
                    if ($row['answer_member_id'] == 34593 || $row['answer_member_id'] == '' ) $row['answer_nickname']='吖咪酱';
                }
            }
            $data['list'][] = $row;
        }
        $data['is_reply'] = $is_reply;
        $this->put($data);
    }

    /**
     * @apiName 提交问答
     *
     * @apiGetParam {string} token: 通信令牌
     *
     * @apiPostParam {int} raise_id: 众筹ID
     * @apiPostParam {int} feedback_id: 问答ＩＤ
     * @apiPostParam {int} content: 提交的问题内容
     *
     * @apiSuccessResponse
     * {
     *     "info": "提交成功!",
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
    public function submit(){
        $raise_id = I('post.raise_id');
        $feedback_id = I('post.feedback_id',0);
        $content = trim(strip_tags(I('post.content')));

        if (!session('?member')) $this->error('请先登录!');
        if (empty($content)) $this->error('请输入内容!');

        if (!empty($raise_id)) {
            $type = 3;
            $type_id = $raise_id;

            $rs = M('Raise')->where(['raise_id' => $raise_id])->select();
            if (empty($rs)) $this->error('非法提交!');
        }

        if(empty($feedback_id)){
            M('Feedback')->add([
                'member_id' => session('member.id'),
                'type' => $type,
                'type_id' => $type_id,
                'content' => $content
            ]);
            $this->success('提交成功!');
        }else{
            $tag_id = M('Tag')->where(['name'=>['LIKE','%众筹【ID：'.$raise_id.'】%']])->getField('id');
            $reply = M('member_tag')->where(['member_id'=>session('member.id'),'tag_id'=>$tag_id])->find();

            if(isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'm.yami.ren') === false) {
                if (empty($reply) && session('member.id') != 55) $this->error('没权限修改或者添加问答回复内容');
            }else{
                if (empty($reply) && in_array(session('member.id'),[278514,278518,34593]) == false) $this->error('您暂无回复、修改评论权限!');
            }

            $feedback = M('Feedback')->where(['id'=>$feedback_id])->find();
            if(empty($feedback)) $this->error('您暂无回复、修改评论权限');
            if($feedback['member_id'] == session('member.id')) $this->error('不能回复或者修改自己的问答');
            M('Feedback')->where(['id'=>$feedback_id])->save([
                'answer_member_id' => session('member.id'),
                'answer' => $content
            ]);
            $this->success('提交成功!');
        }

    }


}