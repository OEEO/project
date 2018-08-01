<?php
namespace Member\Controller;
use Member\Common\MainController;

// @className 我的优惠券
class CouponController extends MainController {
	
	/**
	 * @apiName 获取优惠券列表
	 * 
	 * @apiGetParam {string} token: 通信令牌
	 * @apiPostParam {string} tips_id: 根据活动ID筛选出可用优惠券(默认全部)
	 * @apiPostParam {string} goods_id: 根据商品ID筛选出可用优惠券(默认全部)
	 * 
	 * @apiSuccessResponse
	 * [
	 * 	{
	 * 	"id": "4982",
	 * 	"member_id": "9982",
	 * 	"sn": "201601051215423201", //优惠券号码
	 * 	"used_time": "0", //优惠券被使用时间
	 * 	"category": "0", //0-营销券 1-邀请券 2-注册券 3-被邀请券 4-微信自定义券 5-微信手工券
	 * 	"name": "测试抵价券", //优惠券名称
	 * 	"type": "0", //0-抵价券 1-折扣券 2-礼品券
	 * 	"value": "5.00", //优惠面值，如果是折扣券60代表6折（原价100折后60）
	 * 	"content": "", //礼品券的礼品内容
	 * 	"start_time": "1453271640", //开始时间
	 * 	"end_time": "1454169600",  //结束时间
	 * 	"min_amount": "5",  //最低消费限制
	 * 	"status": "1",  //优惠券是否已发布
	 * 	"member_tags": "*", //可使用的会员标签ID *:全部会员
	 * 	"tips_tags": "*", //可使用的活动标签 *:所有活动，留空则不允许活动使用
	 * 	"goods_tags": "*" //可使用的商品标签 *:所有标签，留空则不允许商品使用
     *  "can_use":1 //1-可用于当前商品或活动，0-不可用
	 * 	},
	 * 	{
	 * 	"id": "4983",
	 * 	"member_id": "9982",
	 * 	"sn": "201601051215427432",
	 * 	"used_time": "0",
	 * 	"category": "0",
	 * 	"name": "测试优惠券二",
	 * 	"type": "0",
	 * 	"value": "8.00",
	 * 	"content": "",
	 * 	"start_time": "1453271640",
	 * 	"end_time": "1454169600",
	 * 	"min_amount": "8",
	 * 	"status": "1",
	 * 	"member_tags": "*",
	 * 	"tips_tags": "*",
	 * 	"goods_tags": "*"
	 * 	},
	 * ]
	 */
	Public function getList(){
		$tips_id = I('post.tips_id', null);
		$goods_id = I('post.goods_id', null);

		$data = [];
		//查询出会员标签
		$member_tags = M('MemberTag')->where(['member_id' => session('member.id')])->getField('tag_id', true);
        if(empty($member_tags))$member_tags = [];
		//查询出商品标签
        $goods_tags = [];
		if(!empty($goods_id))$goods_tags = M('GoodsTag')->where(['goods_id' => $goods_id])->getField('tag_id', true);
		//查询出活动标签
        $tips_tags = [];
		if(!empty($tips_id))$tips_tags = M('TipsTag')->where(['tips_id' => $tips_id])->getField('tag_id', true);

		//查询可使用的优惠券
		$where = [
			'member_id' => session('member.id'),
            'status' => 1,
            'used_time' => 0
        ];

		$data = D('MemberCouponView')->where($where)->select();
        foreach($data as $key=>$row){
            if(in_array($row['category'], [1,2,3])){
                $data[$key]['end_time'] = (string)(strtotime($row['datetime']) + $row['limit_time']);
                if($data[$key]['end_time'] < time() || $row['used_time'] > 0){
                    $data[$key]['can_use'] = 0;
                }else{
                    $data[$key]['can_use'] = 1;
                }
                continue;
            }
            $can_use = false;
            if(!empty($tips_id)) {
                if ($row['member_tags'] == '*' && $row['tips_tags'] == '*') {
                    $can_use = true;
                }else{
                    $allow_member = false;
                    if($row['member_tags'] == '*' || !empty(array_intersect($member_tags, explode(',', $row['member_tags'])))){
                        $allow_member = true;
                    }

                    $allow_tips = false;
                    if($row['tips_tags'] == '*' || !empty(array_intersect($tips_tags, explode(',', $row['tips_tags'])))){
                        $allow_tips = true;
                    }

                    if ($allow_member && $allow_tips) $can_use = true;
                }
            }elseif(!empty($goods_id)){
                if ($row['member_tags'] == '*' && $row['goods_tags'] == '*') {
                    $can_use = true;
                }else{
                    $allow_member = false;
                    if($row['member_tags'] == '*' || !empty(array_intersect($member_tags, explode(',', $row['member_tags'])))){
                        $allow_member = true;
                    }

                    $allow_goods = false;
                    if($row['goods_tags'] == '*' || !empty(array_intersect($goods_tags, explode(',', $row['goods_tags'])))){
                        $allow_goods = true;
                    }

                    if ($allow_member && $allow_goods) $can_use = true;
                }
            }else{
                $can_use = true;
            }
            if($row['end_time']>time() && $can_use && $row['used_time'] == 0){
                $data[$key]['can_use'] = 1;
            }else{
                $data[$key]['can_use'] = 0;
            }
        }

		$this->ajaxReturn($data);
	}

    /**
     * @apiName 输入领取优惠券
     *
     * @apiGetParam {string} token: 通信令牌
     * @apiPostParam {int} sn: 优惠券号
     *
     * @apiSuccessResponse
     * {
     *     "info": "领取成功"
     *     "status": 1,
     * }
     * @apiErrorResponse
     * {
     *	 "status": 状态码,
     *	 "info": "失败原因"
     * }
     */
    public function getCoupon(){
        $coupon_sn = I('post.sn',null);
        $telephone = I('post.telephone', null);
        if(empty($coupon_sn) || !is_numeric($coupon_sn))$this->error('非法券码');

        if(IS_LOGIN){
            $member_id = session('member.id');
        }else{
            if(!preg_match('/1\d{10}/', $telephone))$this->error('手机号的格式不正确!');
            $member_id = M('member')->where(['telephone' => $telephone])->getField('id');
            if(empty($member_id)){
                $nickname = '手机号_' . preg_replace('/^(\d{3})(\d{4})(\d{4})$/', '${1}****$3', $telephone);
                $data = [
                    'username' => $telephone,
                    'telephone' => $telephone,
                    'nickname' => $nickname,
                    'register_time' => time(),
                    'invitecode' => createCode(32, false)
                ];
                $member_id = M('member')->add($data);
            }
        }

        $num = \Common\Util\Cache::getInstance()->get('getCouponNum_' . $member_id);
        if(empty($num))$num = 0;

        if($num > 5){
            $this->error('频繁失败次数过多,请在5分钟后再试!');
        }

        $memberCoupon = M('MemberCoupon')->where(['sn'=>$coupon_sn])->find();
        if(empty($memberCoupon)){
            $num ++;
            \Common\Util\Cache::getInstance()->set('getCouponNum_' . $member_id, $num, 300);
            $this->error('该券码不存在');
        }else{
            if(!empty($memberCoupon['member_id'])){
                $this->error('该券码已经被领取');
            }else{
                M('MemberCoupon')->data(['member_id' => $member_id])->where(['sn'=>$coupon_sn,'used_time'=>0])->save();
                $this->success('领取成功');
            }
        }
    }

}
