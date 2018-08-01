<?php
namespace Home\Controller;
use Home\Common\MainController;

// @className 调查问卷
Class ResearchController extends MainController {

    /**
     * @apiName 获取调查问卷列表
     *
     * @apiGetParam {string} token: 通信令牌

     * @apiSuccessResponse
     * [
     *     {
     *         "id": "35",
     *         "type": "6",
     *         "name": "非共和国",
     *         "sign": "research_5",
     *         "pid": "",
     *         "order": "1",
     *         "datetime": "2017-02-16 14:51:00"
     *     },
     *     {
     *         "id": "34",
     *         "type": "6",
     *         "name": "255就看见的开始1测试题一",
     *         "sign": "research_4",
     *         "pid": "",
     *         "order": "1",
     *         "datetime": "2017-02-16 14:50:58"
     *     }
     * ]
     *
     */
    public function GetList(){
        $rs = M('Category')->where(['type'=>6])->order('order desc id desc ')->select();
        $this->put($rs);
    }

    /**
     * @apiName 获取调查问卷详情
     *
     * @apiGetParam {string} token: 通信令牌
     * @apiPostParam {int} research_id: 调查问卷ID

     * @apiSuccessResponse
     *[
     *    {
     *        "id": "107",//调查问卷题目ID
     *        "category_id": "35",//调查问卷ID
     *        "type": "0",//调查问卷题目类型 (0-问答题，1-选项题)
     *        "content": "姓名",//调查问卷题目
     *        "answer": []//调查问卷题目所对应的答卡（0-如果当类型为0时，该字段为空数组，如果类型是1时，该字段是一个有值的数组）
     *    },
     *    {
     *        "id": "108",
     *        "category_id": "35",
     *        "type": "0",
     *        "content": "手机号码",
     *        "answer": []
     *    },
     *    {
     *        "id": "109",
     *        "category_id": "35",
     *        "type": "1",
     *        "content": "性别",
     *        "answer": [
     *            {
     *                "id": "110",//调查问卷题目选项类型的选项ＩＤ
     *                "type": "0",//调查问卷题目选项类型的选项答案（如果该调查问卷是有对错可言，那么当type = 1时，该选项为正确答案，否则为错误，如果这是一个普通的问卷调查，这里的type，可有可无）
     *                "content": "男",//调查问卷题目选项类型的选项内容
     *                "pid": "109"//调查问卷题目选项类型的选项对应的是那个题目的ID
     *            },
     *            {
     *                "id": "111",
     *                "category_id": "35",
     *                "type": "0",
     *                "content": "女",
     *                "pid": "109"
     *            }
     *        ]
     *    }
     *]
     *
     * @apiErrorResponse
     * {
     *     "info": "失败原因",
     *     "status": 0,
     *     "url": ""
     * }
     */
    public function GetDetail(){
        $research_id = I('post.research_id');
        $rs = M('Category')->where(['type'=>6,'id'=>$research_id])->find();
        if(empty($rs)) $this->error('不存在该调查问卷');
        $research_title = M('Apply')->field('id,value,category_id,type,content')->where(['category_id'=>$research_id,'pid'=>['EXP','IS NULL'],'is_show'=>1])->select();
        $research_answer = M('Apply')->field('id,value,type,content,pid')->where(['category_id'=>$research_id,'pid'=>['EXP',' IS NOT NULL'],'is_show'=>1])->order('sort asc')->select();
        $data =[];
        foreach($research_title as $key =>$val){
            $data[$key] = $val;
            $data[$key]['answer'] = [];
            if($val['type'] == '1'){
                foreach($research_answer as $k=>$v){
                    if($v['pid'] == $val['id']){
                        $data[$key]['answer'][]= $v;
                    }
                }
            }
        }
        $this->put($data);
    }
}
