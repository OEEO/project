<?php
namespace Member\Controller;
use Member\Common\MainController;

// @className 达人相关
class DarenController extends MainController {

    /**
     * @apiName 申请达人(废弃)
     *
     * @apiGetParam {string} token: 通信令牌
     * @apiPostParam {int} category_id：19-美食达人，18-主厨达人
     * @apiPostParam {int} city_id: 城市ID（apply表里的ID）
     * @apiPostParam {string} job: 职业
     * @apiPostParam {int} contact：联系方式(客服电话)
     * @apiPostParam {int} age：年龄段（apply表里的ID）
     * @apiPostParam {int} pic_group_id：拿手菜图ID (多个逗号连接)（主厨必填）（美食不填）
     * @apiPostParam {string} wechat：微信号 (美食达人选填)（主厨不填）
     * @apiPostParam {string} weibo：微博号 (美食达人选填)（主厨不填）
     * @apiPostParam {string} foodname: 食物名称（主厨必填）（美食不填）
     *
     * @apiSuccessResponse
     * {
     *	 "status": 1,
     *	 "info": "申请成功"
     * }
     *
     * @apiErrorResponse
     * {
     *	 "status": 状态码,
     *	 "info": "失败原因"
     * }
     */
    public function apply(){
        $member_id = session('member.id');
        $category_id = I('post.category_id', 18);
        $data = I('post.');
        extract($data);
        if(!empty($pic_group_id))$pic_id = $pic_group_id;
        $pic_group_id = '';

        $md_rs = M('MemberApply')->where(['member_id'=>$member_id,'type'=>2,'type_id'=>$category_id])->find();
        if(!empty($md_rs)){
            if($md_rs['is_pass'] == 1)$this->error('申请已经通过了!');
            M('MemberApply')->where(['member_id'=>$member_id,'type'=>2,'type_id'=>$category_id])->save(['is_pass'=>0, 'channel' => $this->channel]);
            $model = M();
            $model->startTrans();//开启事务
            //清空旧答卷表里的记录
            $re = M('ApplyAnswer')->where(['member_apply_id'=>$md_rs['id']])->delete();
            if($re == false)$model->rollback();
            //弄个新图组,把图片装进去
            if(!empty($pic_id)){
                $pic_group_id = M('PicsGroup')->data(['type'=>3])->add();
                M('Pics')->data(['id'=>$pic_id, 'group_id'=>$pic_group_id])->save();
            }
            //把记录添加到答案表
            $question = M('Apply')->where(['category_id'=>$category_id])->select();

            foreach($question as $row){
                if($row['pid'] != null)continue;
                if($row['type']==0){
                    $rs = M('ApplyAnswer')->data(['member_apply_id'=>$md_rs['id'],'ask_id'=>$row['id'],'content'=>$$row['value']])->add();
                }else{
                    $rs = M('ApplyAnswer')->data(['member_apply_id'=>$md_rs['id'],'ask_id'=>$row['id'],'answer_id'=>$$row['value']])->add();
                }
                if($rs == false){
                    $model->rollback();
                    $this->error('提交失败，请稍后重试！');
                }
            }
            $model->commit();//提交事务
            session('member.dr_status', 0);
            $this->success('申请内容已更新提交，请等待审核');
        }else{
            $model = M();
            $model->startTrans();//开启事务
            $memberApplyId = M('MemberApply')->data(['member_id'=>$member_id,'type'=>2,'type_id'=>$category_id,'is_pass'=>0,'channel' => $this->channel])->add();
            if($memberApplyId == false)$model->rollback();
            //弄个新图组,把图片装进去
            if(!empty($pic_ids)){
                $pic_group_id = M('PicsGroup')->data(['type'=>3])->add();
                M('pics')->data(['id'=>$pic_id, 'group_id'=>$pic_group_id])->save();
            }

            //把记录添加到答案表
            $question = M('Apply')->where(['category_id'=>$category_id])->select();
            foreach($question as $row){
                if($row['pid'] != null)continue;
                if($row['type']==0){
                    $rs = M('ApplyAnswer')->data(['member_apply_id'=>$memberApplyId,'ask_id'=>$row['id'],'content'=>$$row['value']])->add();
                }else{
                    $rs = M('ApplyAnswer')->data(['member_apply_id'=>$memberApplyId,'ask_id'=>$row['id'],'answer_id'=>$$row['value']])->add();
                }
                if($rs == false){
                    $model->rollback();
                    $this->error('提交失败，请稍后重试！');
                }
            }
            $model->commit();//提交事务
            session('member.dr_status', 0);
        }
        $this->success('申请已经提交，请等待审核');
    }


    /**
     * @apiName 获取达人申请资料及答案(废弃)
     *
     * @apiGetParam {string} token: 通信令牌
     * @apiPostParam {int} category_id：19-美食达人，18-主厨达人
     *
     * @apiSuccessResponse
     * {
     *     "items": [
     *         {
     *             "question": {
     *             "id": "26",
     *                 "name": "sex",
     *                 "text": "性别"
     *             },
     *             "type": "1",
     *             "answer": "",
     *             "option": [
     *                 {
     *                     "id": "27",
     *                     "value": "保密",
     *                     "text": "保密"
     *                 },
     *                 {
     *                     "id": "29",
     *                     "value": "女",
     *                     "text": "女"
     *                 },
     *                 {
     *                     "id": "28",
     *                     "value": "男",
     *                     "text": "男"
     *                 }
     *             ]
     *         },
     *         {
     *             "question": {
     *             "id": "19",
     *                 "name": "age",
     *                 "text": "年龄段"
     *             },
     *             "type": "1",
     *             "answer": "",
     *             "option": [
     *                 {
     *                     "id": "20",
     *                     "value": "90后",
     *                     "text": "90后"
     *                 },
     *                 {
     *                     "id": "21",
     *                     "value": "80后",
     *                     "text": "80后"
     *                 },
     *                 {
     *                     "id": "23",
     *                     "value": "60后",
     *                     "text": "60后"
     *                 },
     *                 {
     *                     "id": "22",
     *                     "value": "70后",
     *                     "text": "70后"
     *                 }
     *             ]
     *         },
     *         {
     *             "question": {
     *             "id": "4",
     *                 "name": "city_id",
     *                 "text": "服务城市"
     *             },
     *             "type": "1",
     *             "answer": "",
     *             "option": [
     *                 {
     *                     "id": "37",
     *                     "value": "112",
     *                     "text": "杭州"
     *                 },
     *                 {
     *                     "id": "6",
     *                     "value": "234",
     *                     "text": "深圳"
     *                 },
     *                 {
     *                     "id": "36",
     *                     "value": "37",
     *                     "text": "上海"
     *                 },
     *                 {
     *                     "id": "35",
     *                     "value": "35",
     *                     "text": "北京"
     *                 },
     *                 {
     *                     "id": "5",
     *                     "value": "224",
     *                     "text": "广州"
     *                 }
     *             ]
     *         },
     *         {
     *             "question": {
     *             "id": "46",
     *                 "name": "industry",
     *                 "text": "行业"
     *             },
     *             "type": "1",
     *             "answer": "",
     *             "option": [
     *                 {
     *                     "id": "47",
     *                     "value": "互联网",
     *                     "text": "互联网"
     *                 },
     *                 {
     *                     "id": "48",
     *                     "value": "餐饮业",
     *                     "text": "餐饮业"
     *                 },
     *                 {
     *                     "id": "49",
     *                     "value": "金融业",
     *                     "text": "金融业"
     *                 },
     *                 {
     *                     "id": "50",
     *                     "value": "文化传媒",
     *                     "text": "文化传媒"
     *                 },
     *                 {
     *                     "id": "57",
     *                     "value": "其他",
     *                     "text": "其他"
     *                 },
     *                 {
     *                     "id": "56",
     *                     "value": "能源",
     *                     "text": "能源"
     *                 },
     *                 {
     *                     "id": "55",
     *                     "value": "制造业",
     *                     "text": "制造业"
     *                 },
     *                 {
     *                     "id": "54",
     *                     "value": "教育科研",
     *                     "text": "教育科研"
     *                 },
     *                 {
     *                     "id": "53",
     *                     "value": "海外贸易",
     *                     "text": "海外贸易"
     *                 },
     *                 {
     *                     "id": "51",
     *                     "value": "咨询业",
     *                     "text": "咨询业"
     *                 },
     *                 {
     *                     "id": "52",
     *                     "value": "房地产",
     *                     "text": "房地产"
     *                 }
     *             ]
     *         },
     *         {
     *             "question": {
     *             "id": "7",
     *                 "name": "job",
     *                 "text": "从事职业"
     *             },
     *             "type": "1",
     *             "answer": "",
     *             "option": [
     *                 {
     *                     "id": "64",
     *                     "value": "行政",
     *                     "text": "行政"
     *                 },
     *                 {
     *                     "id": "66",
     *                     "value": "人力技术研发",
     *                     "text": "人力技术研发"
     *                 },
     *                 {
     *                     "id": "67",
     *                     "value": "其他",
     *                     "text": "其他"
     *                 },
     *                 {
     *                     "id": "63",
     *                     "value": "财务",
     *                     "text": "财务"
     *                 },
     *                 {
     *                     "id": "62",
     *                     "value": "编辑记者",
     *                     "text": "编辑记者"
     *                 },
     *                 {
     *                     "id": "61",
     *                     "value": "运营",
     *                     "text": "运营"
     *                 },
     *                 {
     *                     "id": "60",
     *                     "value": "市场",
     *                     "text": "市场"
     *                 },
     *                 {
     *                     "id": "59",
     *                     "value": "销售",
     *                     "text": "销售"
     *                 },
     *                 {
     *                     "id": "65",
     *                     "value": "设计",
     *                     "text": "设计"
     *                 }
     *             ]
     *         },
     *         {
     *             "question": {
     *             "id": "38",
     *                 "name": "site",
     *                 "text": "您举办饭局的场地在哪里？"
     *             },
     *             "type": "1",
     *             "answer": "",
     *             "option": [
     *                 {
     *                     "id": "42",
     *                     "value": "无场地",
     *                     "text": "无场地"
     *                 },
     *                 {
     *                     "id": "41",
     *                     "value": "餐厅",
     *                     "text": "餐厅"
     *                 },
     *                 {
     *                     "id": "40",
     *                     "value": "美食工作室",
     *                     "text": "美食工作室"
     *                 },
     *                 {
     *                     "id": "39",
     *                     "value": "自己家",
     *                     "text": "自己家"
     *                 }
     *             ]
     *         },
     *         {
     *             "question": {
     *             "id": "43",
     *                 "name": "habit",
     *                 "text": "您平时有招待朋友吃饭的习惯么？"
     *             },
     *             "type": "1",
     *             "answer": "",
     *             "option": [
     *                 {
     *                     "id": "45",
     *                     "value": "0",
     *                     "text": "无"
     *                 },
     *                 {
     *                     "id": "44",
     *                     "value": "1",
     *                     "text": "有"
     *                 }
     *             ]
     *         },
     *         {
     *             "question": {
     *             "id": "34",
     *                 "name": "foodname",
     *                 "text": "请介绍一道拿手菜"
     *             },
     *             "type": "0",
     *             "answer": ""
     *         },
     *         {
     *             "question": {
     *             "id": "13",
     *                 "name": "pic_group_id",
     *                 "text": "您拿手菜的图片"
     *             },
     *             "type": "2",
     *             "answer": {
     *             "pic_id": "",
     *                 "path": ""
     *             }
     *         },
     *         {
     *             "question": {
     *             "id": "3",
     *                 "name": "contact",
     *                 "text": "客服电话"
     *             },
     *             "type": "0",
     *             "answer": ""
     *         }
     *     ],
     *     "is_pass": "1",
     *     "refusal_reason": ""
     * }
     *
     * @apiErrorResponse
     * {
     *     "info": "您已经是达人了!",
     *     "status": 0
     * }
     */
    Public function getApplyInfo(){
        $category_id = I('post.category_id', 18);
        $member_id = session('member.id');
        //判断是否已经是达人了
        $status = M('MemberTag')->where(['member_id' => $member_id, 'tag_id' => $category_id])->count();
        if($status > 0){
            $this->error('您已经是达人了!');
        }
        //获取申请信息
        $rs = M('MemberApply')->field(['id', 'is_pass', 'refusal_reason'])->where(['member_id' => $member_id, 'type' => 2, 'type_id' => $category_id])->find();
        $_rs = M('Apply')->where(['category_id' => $category_id])->order('sort desc')->select();
        $items = [];
        foreach($_rs as $row){
            //问题
            if(empty($row['pid'])){
                $items[$row['id']]['question'] = [
                    'id' => $row['id'],
                    'name' => $row['value'],
                    'text' => $row['content']
                ];
                //问题类型
                $items[$row['id']]['type'] = $row['type'];
                //问题答案
                if(!empty($rs)){
                    if($row['type'] == 1) {
                        $items[$row['id']]['answer'] = M('ApplyAnswer')->where(['member_apply_id' => $rs['id'], 'ask_id' => $row['id']])->getField('answer_id')?:'';
                    }elseif($row['type'] == 2){
                        $pic_group_id = M('ApplyAnswer')->where(['member_apply_id' => $rs['id'], 'ask_id' => $row['id']])->getField('answer_id');
                        if(empty($pic_group_id))$items[$row['id']]['answer'] = '';
                        $pic = M('pics')->field(['id', 'path'])->where(['group_id' => $pic_group_id])->find();
                        $items[$row['id']]['answer'] = ['pic_id' => $pic['id'], 'path' => thumb($pic['path'])];
                    }else{
                        $items[$row['id']]['answer'] = M('ApplyAnswer')->where(['member_apply_id' => $rs['id'], 'ask_id' => $row['id']])->getField('content')?:'';
                    }
                }else{
                    $items[$row['id']]['answer'] = '';
                }
            //选项
            }elseif($row['type'] == 1){
                $items[$row['pid']]['option'][] = [
                    'id' => $row['id'],
                    'value' => $row['value'],
                    'text' => $row['content']
                ];
            }
        }
        $data = [
            'items' => array_values($items),
            'is_pass' => !empty($rs) ? $rs['is_pass'] : '-1',
            'refusal_reason' => !empty($rs['refusal_reason']) ? $rs['refusal_reason'] : ''
        ];

        $this->ajaxReturn($data);
    }
}


