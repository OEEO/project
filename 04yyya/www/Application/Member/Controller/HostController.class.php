<?php
namespace Member\Controller;
use Member\Common\MainController;

// @className HOST实名认证
class HostController extends MainController {

                                                          
    public function getBankList(){
        $rs = M('Bank')->field(['id' => 'bank_id', 'name'])->where(['is_use'=>1])->select();
        $this->put($rs);
    }

    /**
     * @apiName 申请实名认证
     *
     * @apiGetParam {string} token: 通信令牌
     * @apiPostParam {string} surname: 真实姓名(银行开户人)
     * @apiPostParam {int} sex: 性别(1-男 2-女)
     * @apiPostParam {string} identity: 身份证号码
     * @apiPostParam {int} contact: 联系电话
     * @apiPostParam {int} bank_id: 银行卡开户行ID
     * @apiPostParam {string} number: 银行卡号码(支付宝/微信账号)
     *
     * @apiSuccessResponse
     * {
     *	 "status": "1",
     *	 "info": "申请成功"
     * }
     *
     * @apiErrorResponse
     * {
     *	 "status": "0",
     *	 "info": "失败原因"
     * }
     */
    public function apply(){
        $member_id = session('member.id');
        $rs = D('HostView')->where(['id' => $member_id, 'tag_id' => 18])->find();

        // TODO
        //补充实名信息
        $data = [];
        $data['surname'] = $_POST['surname'];
        $data['sex'] = I('post.sex');
        $data['identity'] = strtoupper(I('post.identity'));
        $data['contact'] = I('post.contact');

        //添加/修改银行卡
        $_data = [];
        $_data['bank_id'] = I('post.bank_id');
        $_data['name'] = $_POST['surname'];
        $_data['number'] = I('post.number');

        //判断是否符合规范
        if(empty($data['surname']) || empty($data['sex']) || empty($data['contact']) || empty($_data['bank_id']) || empty($_data['number'])){
            $this->error('请填写完整的实名信息!');
        }
        if(!preg_match('/^(\d{15}$|^\d{18}$|^\d{17}(\d|X))$/', $data['identity']) ){
            $this->error('身份证号码格式不正确!');
        }

        M('MemberInfo')->where(['member_id' => $member_id])->save($data);
        $bank = M('MemberBank')->where(['member_id' => $member_id])->find();
        if(!empty($bank)){
            M('MemberBank')->where(['member_id' => $member_id])->save($_data);
        }else{
            $_data['member_id'] = $member_id;
            M('MemberBank')->where(['member_id' => $member_id])->add($_data);
        }

        if(empty($rs)){
            M('MemberApply')->where(['member_id' => $member_id, 'type' => 5])->delete();
            M('MemberApply')->add([
                'member_id' => $member_id,
                'channel' => $this->channel,
                'type' => 5,
                'is_pass' =>1,
                'type_id' => $member_id
            ]);
            M('MemberTag')->add([
                'member_id' => $member_id,
                'tag_id' => 18
            ]);
            $member = session('member');
            $member['tags'][] = 18;
            session('member', $member);
        }
        $this->success('申请成功!');
    }

    /**
     * @apiName 获取实名认证信息
     *
     * @apiGetParam {string} token: 通信令牌
     *
     * @apiSuccessResponse
     * {
     *     "surname": "李俊杰",
     *     "sex": "1",
     *     "identity": "42108719870416003X",
     *     "contact": "18664861856",
     *     "bank_id": "2",
     *     "bank_number": "3219302103213021"
     * }
     */
    public function getApplyInfo(){
        $member_id = session('member.id');
        $rs = D('HostView')->where(['id' => $member_id, 'tag_id' => 18])->find();
        if(empty($rs))$this->error('尚未提交申请!');
        $this->put([
            'surname' => $rs['surname'],
            'sex' => $rs['sex'],
            'identity' => $rs['identity'],
            'contact' => $rs['contact'],
            'bank_id' => $rs['bank_id'],
            'bank_number' => $rs['bank_number']
        ]);
    }

}