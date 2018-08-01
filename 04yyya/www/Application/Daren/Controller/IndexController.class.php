<?php

namespace Daren\Controller;
use Daren\Common\MainController;

// @className 常规工具
class IndexController extends MainController {

    /**
     * @apiName 收支与银行卡
     *
     * @apiGetParam {string} token: 通信令牌
     *
     *
     * @apiSuccessResponse
     * {
     *     "income": {
     *     "get": 100,
     *     "take": -60
     *     },
     *     "bank": [
     *       {
     *       "name": "工商银行",
     *       "pic_id": "1",
     *       "color": "#FFC0CB",
     *       "number": "6222222222222222222",
     *       "status": "1"  //0-未选用，1-当前使用
     *       },
     *       {
     *       "name": "农业银行",
     *       "pic_id": "2",
     *       "color": "#008000",
     *       "number": "6333333333333333333",
     *       "status": "0"
     *     }
     *   ]
     * }
     */
    public function DarenWealth(){
        //获取余额数量
        //M('MemberWealth')->where(['member_id'=>session('member.id'),'wealth'=>1])->getField('quantity');
        $data = array();
        $wealthView = new \Member\Model\WealthViewModel();
        $data['income']['get'] = $wealthView->where(['member_id' => session('member.id'), 'type' => ['IN','chongzhi,huoqu,shoumai'], 'wealth' => 1])->getField('sum');
        $data['income']['get'] = $data['income']['get']==null?0:(int)$data['income']['get'];
        $data['income']['take'] = $wealthView->where(['member_id' => session('member.id'), 'type' => ['IN','tuikuan,tixian'], 'wealth' => 1])->getField('sum');
        $data['income']['take'] = $data['income']['take'] = null?0:(int)$data['income']['take'];

        //获取财富记录5条
        /*$member_wealth_id = M('MemberWealth')->where(['member_id'=>session('member.id'),'wealth'=>1])->getField('id');
        $data['log'] = M('MemberWealthLog')->where(['member_wealth_id'=>$member_wealth_id])->field('type,quantity,datetime')->limit(5)->order('id desc')->select();
        foreach($data['log'] as $key=>$row){
            switch($row['type']){
                case 'chongzhi':
                    $data['log'][$key]['type'] = '充值';
                    break;
                case 'tixian':
                    $data['log'][$key]['type'] = '体现';
                    break;
                case 'tuikuan':
                    $data['log'][$key]['type'] = '退款';
                    break;
                case 'xiaofei':
                    $data['log'][$key]['type'] = '消费';
                    break;
                case 'zengsong':
                    $data['log'][$key]['type'] = '赠送';
                    break;
                case 'huoqu':
                    $data['log'][$key]['type'] = '被赠送';
                    break;
                case 'shoumai':
                    $data['log'][$key]['type'] = '售卖收入';
                    break;
            }
        }*/

        //银行卡
        $data['bank'] = D('BankView')->where(['B.member_id'=>session('member.id')])->select();
        foreach($data['bank'] as $key=>$row){
            $data['bank'][$key]['path'] = thumb($row['path']);
            $num_head = strlen($row['number']) % 4;

            $part = null;
            $num = substr($row['number'],0,$num_head).' ';
            for($i=$num_head;$i<strlen($row['number'])-$num_head;$i=$i+4){
                $part = substr($row['number'],$i,4);
                $num .= $part.' ';
            }
            $data['bank'][$key]['num'] = trim($num);
        }

        $this->ajaxReturn($data);
    }

    /**
     * @apiName 详细账单
     *
     * @apiGetParam {string} token: 通信令牌
     * @apiGetParam {int} page: 页数（默认1）
     *
     *
     * @apiSuccessResponse
     * [
     *     {
     *     "type": "退款",
     *     "quantity": "-10.00",
     *     "datetime": "2016-04-18 19:05:11"
     *     },
     *     {
     *     "type": "提现",
     *     "quantity": "-50.00",
     *     "datetime": "2016-04-18 19:04:37"
     *     },
     *     {
     *     "type": "充值",
     *     "quantity": "100.00",
     *     "datetime": "2016-04-18 19:04:00"
     *     }
     * ]
     */
    Public function billDetail(){
        $page = I('get.page',1);
        $pageSize = 5;
        //获取财富记录
        $member_wealth_id = M('MemberWealth')->where(['member_id'=>session('member.id'),'wealth'=>1])->getField('id');
        $data = M('MemberWealthLog')->where(['member_wealth_id'=>$member_wealth_id])->field('type,quantity,datetime')->limit($page,$pageSize)->order('id desc')->select();
        foreach($data as $key=>$row){
            switch($row['type']){
                case 'chongzhi':
                    $data[$key]['type'] = '充值';
                    break;
                case 'tixian':
                    $data[$key]['type'] = '提现';
                    break;
                case 'tuikuan':
                    $data[$key]['type'] = '退款';
                    break;
                case 'xiaofei':
                    $data[$key]['type'] = '消费';
                    break;
                case 'zengsong':
                    $data[$key]['type'] = '赠送';
                    break;
                case 'huoqu':
                    $data[$key]['type'] = '被赠送';
                    break;
                case 'shoumai':
                    $data[$key]['type'] = '售卖收入';
                    break;
            }
        }
        $this->ajaxReturn($data);
    }

    /**
     * @apiName 添加银行卡
     *
     * @apiGetParam {string} token: 通信令牌
     *
     * @apiPostParam {int} bank_id: 银行卡ID
     * @apiPostParam {int} number: 银行卡号
     * @apiPostParam {string} name: 持卡人真实姓名
     * @apiPostParam {int} code: 验证码
     *
     *
     * @apiSuccessResponse
     * {
     *     "info": "添加成功",
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
    public function addCard(){
        $bank_id = I('post.bank_id');
        $number = I('post.number');
        $name = I('post.name');


        if(!is_numeric($number))$this->error('非法卡号');
        if(empty($bank_id))$this->error('未指定银行卡种类');
        if(empty($number))$this->error('卡号不能为空');

        if(empty($name))$this->error('姓名不能为空');

        $rs = M('MemberBank')->where(['member_id'=>session('member.id'),'number'=>$number])->find();
        if(!empty($rs))$this->error('该卡已经被添加过了');
        M('MemberBank')->data(['member_id'=>session('member.id'),'bank_id'=>$bank_id,'number'=>$number,'name'=>$name])->add();
        $this->success('添加成功');


    }

    /**
     * @apiName 移除银行卡
     *
     * @apiGetParam {string} token: 通信令牌
     *
     * @apiPostParam {int} member_bank_id: 会员银行卡ID
     *
     *
     * @apiSuccessResponse
     * {
     *     "info": "删除成功",
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
    public function delCard(){
        $id = I('post.member_bank_id');

        $rs = M('MemberBank')->where(['id'=>$id,'member_id'=>session('member.id')])->find();
        if(empty($rs))$this->error('该卡不属于你，无法删除');
        M('MemberBank')->where(['id'=>$id])->delete();
        $this->success('删除成功');
    }

    /**
     * @apiName 选择银行卡
     *
     * @apiGetParam {string} token: 通信令牌
     *
     * @apiPostParam {int} member_bank_id: 会员银行卡ID
     *
     *
     * @apiSuccessResponse
     * {
     *     "info": "操作成功",
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
    public function selectcard(){
        $id = I('post.member_bank_id');

        $rs = M('MemberBank')->where(['id'=>$id,'member_id'=>session()])->find();
        if(empty($rs))$this->error('该卡不属于你，无法操作');

        M('MemberBank')->where(['member_id'=>session(),'status'=>0])->save();
        M('MemberBank')->where(['id'=>$id,'member_id'=>session(),'status'=>1])->save();
        $this->success('操作成功');
    }

    /**
     * @apiName 余额提现
     *
     * @apiGetParam {string} token: 通信令牌
     *
     * @apiPostParam {int} quantity: 数量
     * @apiPostParam {int} card_num: 提款卡号
     *
     *
     * @apiSuccessResponse
     * {
     *     "info": "操作成功",
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
    public function takeMoney(){
        $quantity = I('post.quantity');
        $card_num = I('post.card_num');


        if(empty($quantity))$this->error('提取金额不能为空');
        if(!is_numeric($quantity) || $quantity < 0 )$this->error('非法输入');
        $rs = M('MemberWealth')->where(['member_id'=>session('member.id'),'wealth'=>1])->find();
        if($quantity > $rs['quantity'])$this->error('余额不足');
        $result = M('MemberBank')->where(['number'=>$card_num,'member_id'=>session('member.id')])->find();
        if(empty($result))$this->error('未找到该卡');
        $LogId = M('MemberWealthLog')->data(['member_wealth_id'=>$rs['id'],'type'=>'tixian','quantity'=>'-'.$quantity,'content'=>'提款卡号:'.$card_num.' 持卡人:'.$result['name']])->add();
        M('MemberWealth')->where(['member_id'=>session('member_id'),'wealth'=>'2'])->setDec('quantity',$quantity);

        //申请表记录提现申请操作
        M('MemberApply')->data(['member'=>session('member.id'),'type'=>3,'type_id'=>$LogId,'is_pass'=>0])->add();

        $this->success('操作成功');
    }

    /**
     * @apiName 获取银行卡列表
     *
     * @apiGetParam {string} token: 通信令牌
     *
     *
     * @apiSuccessResponse
     * [
     *     {
     *     "id": "1",
     *     "name": "工商银行",
     *     "pic_id": "1",
     *     "color": "#FFC0CB",
     *     "datetime": "2016-04-25 10:07:47"
     *     },
     *     {
     *     "id": "2",
     *     "name": "农业银行",
     *     "pic_id": "2",
     *     "color": "#008000",
     *     "datetime": "2016-04-25 10:10:01"
     *     }
     * ]
     *
     */
    Public function getBankList(){
        $data = M('Bank')->select();
        $this->ajaxReturn($data);
    }

    /**
     * @apiName 验证会员的活动消费码
     *
     * @apiGetParam {string} token: 通信令牌
     * @apiPostParam {int} code: 8位的订单商品消费码
     * @apiPostParam {int} is_check: 确认验票
     *
     * @apiSuccessResponse
     * //获取信息
     * {
     *     "title": "测试活动，支付",
     *     "tips_path": "http://img.m.yami.ren/20160907/cb2074e52a473fe63fed2272fa43e8df1140a4a1_640x420.jpg",
     *     "start_time": "2016-09-08 01:00",
     *     "address_name": "建中路50号",
     *     "nickname": "小江",
     *     "member_path": "http://img.m.yami.ren/20160919/c38fcb561b71a39a881a7309244dd2e1eceb6c8f.jpg",
     *     "telephone": "18271628434",
     *     "order_sn": "772216628538444671",
     *     "count": "1",
     *     "check_count": "0"
     * }
     * //确认验票
     * {
     *    "info" : "消费码验证成功!",
     *    "status" : 1
     * }
     * @apiErrorResponse
     * {
     *    "info" : "失败原因",
     *    "status" : 0
     * }
     */
    public function checkCode(){
        $code = I('post.code');
        $host_id = session('member.id');
        $is_check = I('post.is_check', 0);
        if(!is_numeric($code))$this->error('非法访问!');

        $rs = D('CheckCodeView')->where(['check_code' => $code, 'host_id' => $host_id, 'status' => 1, 'act_status' => ['IN', '1,2,3,4']])->find();
        if(empty($rs))$this->error('无效的消费码!');
        if($rs['start_time'] > time())$this->error('活动未开始，不能验票!');
        $count = D('OrderView')->where(['ware_id'=>$rs['ware_id'],'tips_times_id'=>$rs['tips_times_id'],'act_status'=>['IN',[1,2,3,4]]])->group('B.id')->count();
        if(($count>0 && $rs['min_num']>$count ) || $count<0)$this->error('活动未成局，不能验票！');
        if($rs['server_status'] == 1)$this->error('该消费码已经验证过了!');

        if($is_check){
            M('order')->where(['id' => $rs['order_id']])->save(['act_status' => 2]);
            M('OrderWares')->where(['id' => $rs['id']])->save(['server_status' => 1]);
            //记录订单修改快照信息
            $this->SaveSnapshotLogs($rs['order_id'],3);
            $this->success('消费码验证成功!');
        }else{
            //总购买数
            $count = M('OrderWares')->where(['order_id' => $rs['order_id']])->count();
            //已验证数
            $check_count = M('OrderWares')->where(['order_id' => $rs['order_id'], 'server_status' => 1])->count();

            $data = [
                'title' => $rs['title'],
                'tips_path' => thumb($rs['tips_path'], 1),
                'start_time' => date('Y-m-d H:i', $rs['start_time']),
                'address_name' => $rs['address_name'],
                'nickname' => $rs['nickname'],
                'member_path' => thumb($rs['member_path'], 2),
                'telephone' => $rs['telephone'],
                'order_sn' => $rs['sn'],
                'count' => $count,
                'check_count' => $check_count
            ];

            $this->put($data);
        }
    }
}