<?php
/**
 * Created by PhpStorm.
 * User: Cherry
 * Date: 2017/3/30 0030
 * Time: 10:08
 */

namespace Member\Controller;
use Member\Common\MainController;

//@className 拼团管理
class PieceController extends MainController
{
    /**
     * @apiName 获取拼团相关信息
     *
     * @apiGetParam {string} token: 通信令牌
     * @apiPostParam {int} piece_originator_id: 要购买的活动ID
     *
     * @apiSuccessResponse
     *{
     *    "id": "6",
     *    "member_id": "242629",
     *    "end_time": "1490960198",
     *    "status": "0",
     *    "nickname": "Ada",
     *    "headpath": "http://img.m.yami.ren/20161208/M5YTBlOTlmMGVmYThhOTkzMWE0MDAw.jpg",
     *    "piece_type": "0",
     *    "piece_type_id": "7750",
     *    "piece_phase": "1",
     *    "piece_price": "0.01",
     *    "piece_count": "20",
     *    "piece_limit_time": "2",
     *    "piece_status": "1",
     *    "title": "测试短信达人和用户",
     *    "type_path": "http://img.m.yami.ren/20161229/e64be1f25439ec97e7295051e05dd9c2b5cff5ba.jpg",
     *    "is_buy": "1", // 能否购买
     *    "buyer_num": "1",
     *    "joiner": [
     *        {
     *            "wid": "26138",
     *            "type": "0",
     *            "ware_id": "7756",
     *            "inviter_id": "",
     *            "tips_times_id": "16469",
     *            "id": "26012",
     *            "sn": "113569161295890807",
     *            "member_id": "242629",
     *            "price": "0.01",
     *            "act_status": "1",
     *            "status": "1",
     *            "create_time": "2017-03-31 17:11:04",
     *            "nickname": "Ada",
     *            "joiner_path": "http://img.m.yami.ren/20161208/M5YTBlOTlmMGVmYThhOTkzMWE0MDAw.jpg",
     *            "is_colonel": "1"
     *        }
     *    ]
     *}
     *
     * @apiErrorResponse
     * {
     *    "info" : "错误原因",
     *    "status" : 0,
     *    "url" : ""
     * }
     */
    public function index(){
        $piece_originator_id = I('post.piece_originator_id');
        $member_id = session('member.id');
        if(empty($member_id)) $this->error('请登录！');
        $model = I('post.type') == 1 ? D('MemberPieceGoodsView') : D('MemberPieceView');
        $where['id'] = ['EQ', $piece_originator_id];
        $where['piece_status'] = ['EQ', 1];
        $where['p_status'] = ['IN', [1,2]];
        $data = $model->where($where)->find();
        if(empty($data)) $this->error('不存在该ID的拼团');
        if(empty($data['end_time']>time()) && $data['p_act_status'] == 8) $this->error('该拼团已过期');
        if(in_array($data['p_act_status'],[9,10])) $this->error('该拼团已取消');
        $data['headpath'] = thumb($data['headpath']);
        $data['type_path'] = thumb($data['type_path']);
        $buyer = D('PieceOrderView')->where(['piece_originator_id'=>$piece_originator_id,'status'=> ['IN', [1,2]],'act_status'=>['IN',[1,2,3,4]]])->group('B.id')->order('A.id asc')->select();
        if(empty($buyer))$this->error('该拼团未发起！');
        if($data['end_time'] > time()){
            if(in_array($data['p_act_status'],[1,2])){
                if($data['is_cap'] == 0){
//                    if(((int)$data['piece_count'] - count($buyer))<=0){
                        $data['is_buy'] = 1; // 可购买
//                    }else{
//                        $data['is_buy'] = 0;
//                    }
                }else{
                    if(((int)$data['piece_count'] - count($buyer))>0){
                        $data['is_buy'] = 1;
                    }else{
                        $data['is_buy'] = 0;
                    }
                }
            }else{
                $data['is_buy'] = 0;
            }
        }else{
            $data['is_buy'] = 0;
        }

        $data['self_member_id'] = $member_id;
        $data['status'] = $data['p_status'];
        $data['act_status'] = $data['p_act_status'];
        unset($data['p_status']);
        unset($data['p_act_status']);
        $data['buyer_num'] = count($buyer);
        $data['joiner'] = $buyer;
        foreach($data['joiner'] as $key => $val){
            if($key == 0){
                $data['joiner'][$key]['is_colonel'] = 1;
            }else{
                $data['joiner'][$key]['is_colonel'] = 0;
            }
            if(session('member.id') != $val['member_id']){
                $data['joiner'][$key]['id'] = '';
            }
            $data['joiner'][$key]['joiner_path'] = thumb($val['joiner_path']);
            $data['joiner'][$key]['create_time'] = date('Y-m-d H:i:s',$val['create_time']);

            if ($data['joiner'][$key]['member_id'] === $member_id) {
                // 如果参与者中，用户已经购买过，则不能再进行购买此团
                $data['is_buy'] = 0;
            }
        }
        $this->put($data);

    }

    

}