<?php
namespace Home\Controller;
use Home\Common\MainController;

//@className 问卷调查或申请
Class ApplyController extends MainController {

    /**
     * @apiName 获取问卷问题
     *
     * @apiGetParam {string} token: 通信令牌
     *
     * @apiPostParam {int} catid: 调查主题ID
     *
     * @apiSuccessResponse
     * [
     *     {
     *         "id": "69",
     *         "category_id": "32",
     *         "type": "0",
     *         "content": "姓名",
     *         "value": "name",
     *         "pid": "",
     *         "sort": "0",
     *         "datetime": "2016-10-18 11:18:14"
     *     },
     *     {
     *         "id": "70",
     *         "category_id": "32",
     *         "type": "0",
     *         "content": "性别",
     *         "value": "sex",
     *         "pid": "",
     *         "sort": "1",
     *         "datetime": "2016-10-18 11:18:52"
     *     },
     *     {
     *         "id": "71",
     *         "category_id": "32",
     *         "type": "0",
     *         "content": "电话",
     *         "value": "phone",
     *         "pid": "",
     *         "sort": "2",
     *         "datetime": "2016-10-18 11:19:24"
     *     },
     *     {
     *         "id": "72",
     *         "category_id": "32",
     *         "type": "0",
     *         "content": "行业",
     *         "value": "trade",
     *         "pid": "",
     *         "sort": "3",
     *         "datetime": "2016-10-18 11:21:05"
     *     },
     *     {
     *         "id": "73",
     *         "category_id": "32",
     *         "type": "0",
     *         "content": "职业",
     *         "value": "job",
     *         "pid": "",
     *         "sort": "4",
     *         "datetime": "2016-10-18 11:21:48"
     *     },
     *     {
     *         "id": "74",
     *         "category_id": "32",
     *         "type": "0",
     *         "content": "请问您有啥忌口的不？",
     *         "value": "avoid",
     *         "pid": "",
     *         "sort": "5",
     *         "datetime": "2016-10-18 11:23:25"
     *     }
     * ]
     */
    Public function getQuestion(){
        $catid = I('post.catid');

        $rs = M('apply')->where(['category_id' => $catid, 'pid' => ['exp', 'is null']])->order('sort asc')->select();
        if(empty($rs))$this->error('目标调查不存在!');

        $data = [];
        foreach($rs as $row){
            if($row['type'] == 1)
                $row['option'] = M('apply')->field(['content' => 'name', 'value'])->where(['pid' => $row['id']])->order('sort asc')->select();
            $data[] = $row;
        }
        $this->put($data);
    }

    /**
     * @apiName 提交问卷答案
     *
     * @apiGetParam {string} token: 通信令牌
     *
     * @apiPostParam {int} catid: 调查主题ID
     * @apiPostParam {int} ...: 提交的数据
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
    Public function submit(){
        $post = I('post.');
        $catid = $post['catid'];
        if(empty($post))$this->error('请完善资料再提交数据');
        $rs = M('apply')->where(['category_id' => $catid,'is_show'=>1])->select();
        if(empty($rs))$this->error('目标调查不存在!');
        $ym_category = M('category')->where(['id' => $catid])->find();

        $member_apply_id = null;
        $data = ['channel' => $this->channel, 'type' =>$ym_category['type'], 'type_id' => $catid];
        if(session('?member')){
            //M('MemberApply')->where(['member_id' => session('member.id'), 'type' => 99, 'type_id' => $catid])->delete();
            $data['member_id'] = session('member.id');
        }
        $member_apply_id = M('MemberApply')->add($data);
        $datas = [];
        foreach($post as $name => $value){
            $data = [];

            foreach($rs as $row){
                if($row['value'] == $name && empty($row['pid'])){
                    $data['ask_id'] = $row['id'];
                }
            }

            foreach($rs as $row) {
                if ($row['value'] == $value && $row['pid'] == $data['ask_id']) {
                    $data['answer_id'] = $row['id'];
                }
            }

            if(empty($data['answer_id'])){
                $data['answer_id'] = ['exp', 'null'];
                $data['content'] = $value;
            }else{
                $data['content'] = ['exp', 'null'];
            }
            if(!empty($member_apply_id)){
                $data['member_apply_id'] = $member_apply_id;
            }else{
                $data['member_apply_id'] = ['exp', 'null'];
            }
            if(!empty($data['ask_id'])) $datas[] = $data;
        }
        M('ApplyAnswer')->addAll($datas);
        $this->success('提交成功!');

    }

    Public function getResult(){
        $catid = I('get.catid');

        $_rs = M('apply')->where(['category_id' => $catid, 'pid' => ['exp', 'is null']])->order('sort')->getField('content', true);
        if(empty($_rs))$this->error('目标调查不存在!');

        $sql = "Select a.member_apply_id as 'apply_id',b.content as 'question', a.content as 'answer1', b.type as 'type', c.content as 'answer2' from ym_apply_answer a join ym_apply b on a.ask_id=b.id left join ym_apply c on a.answer_id=c.id where b.category_id='{$catid}' order by b.sort,a.id";
        $rs = M()->query($sql);
        if(empty($rs))$this->error('暂无结果!');

        $data = [];
        foreach($rs as $row){
            if(!array_key_exists($row['apply_id'], $data)){
                foreach($_rs as $name)$data[$row['apply_id']][$name] = '';
            }
            $data[$row['apply_id']][$row['question']] = $row['type']==0 ? $row['answer1'] : $row['answer2'];
        }

        //$this->put($data);

        $title = $value = [];
        foreach($data as $row){
            if(empty($title))$title = array_keys($row);
            $value[] = array_values($row);
        }

        toXls($title, $value, '问卷结果');
    }

}