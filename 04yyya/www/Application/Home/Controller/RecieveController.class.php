<?php

namespace Home\Controller;
use Home\Common\MainController;
use Common\Util\Cache;
use Symfony\Component\Finder\Expression\Expression;

// @className 页面常规接口
class RecieveController extends MainController {

    public function submitRecieve() {
        $code = I('post.code');
        $name = I('post.name');
        $address = I('post.address');
        $telephone = I('post.telephone');
        $weixincode = I('post.weixincode');
        $type = I('post.type');
        $ware_id = I('post.ware_id');
        $select = I('post.select');
        $member_id = session('member.id');

        if (empty($member_id)) {
            $this->error('请登录');
            return;
        }

        $old = D('RecieveCodeOrderView')->where(['code' => $code, 'type' => $type, 'ware_id' => $ware_id])->find();

        if (empty($old)) {
            $this->error('此礼券不存在');
        } else if (!empty($old['member_id'])) {
            $this->error('此优惠券已被领取');
        } else {
            $data['rid'] = $old['id'];
            $data['name'] = $name;
            $data['address'] = $address;
            $data['telephone'] = $telephone;
            $data['weixin_code'] = $weixincode;
            $data['member_id'] = $member_id;
            $data['select'] = $select;

            M('RecieveOrder')->add($data);
            $this->sendMessage($name, $address, $telephone, $weixincode);
            $this->success('领取成功');
        }
    }

    private function sendMessage($name, $address, $telephone, $weixincode) {
        $member_ids = array(['11008']);

        $params = [
            'customer' => '主人，有人领取礼券了，姓名：' . $name . ';   地址：' . $address . '; 电话：' . $telephone . ';   微信号：' . $weixincode
        ];
        
        $this->push_message(11008, $params, 'SMS_35180078', 'wx|sms',null, 0, 0, 0, 0);
        $this->push_message(1714, $params, 'SMS_35180078', 'wx|sms',null, 0, 0, 0, 0);
        $this->push_message(268857, $params, 'SMS_35180078', 'wx|sms',null, 0, 0, 0, 0);
    }
}