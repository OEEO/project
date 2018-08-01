<?php
namespace Daren\Controller;
use Daren\Common\MainController;

// @className 订单管理
class OrderController extends MainController {
	
	/**
	 * @apiName 获取订单列表列表
	 *
	 * @apiGetParam {string} token: 通信令牌
	 * @apiGetParam {int} page: 页码（每页5条数据）
	 * @apiPostParam {int} times_id: 活动时间段ID（可忽略）
	 * @apiPostParam {int} sn: 订单号（可忽略）
	 * @apiPostParam {int} title: 商品标题（模糊搜索，可忽略）
	 * @apiPostParam {int} telephone: 手机号（可忽略）
	 * @apiPostParam {int} start_time: 时间范围筛选-开始时间（可忽略）
	 * @apiPostParam {int} end_time: 时间范围筛选-结束时间（可忽略）
	 * @apiPostParam {int} act_status: 状态 0-未支付 1-未参加 23-已参加 4-已完成 5-退款中 6-退款完成 7-已取消（可忽略）
	 *
	 * @apiSuccessResponse
	 * [
	 *     {
	 *         "id": "24114", //订单ID
	 *         "sn": "20150906211159140", //订单号
	 *         "create_time": "1441545119", //订单创建时间
	 *         "price": "288.00", //最终价格
	 *         "context": null, //订单备注
	 *         "act_status": "2", //订单状态 0-未支付 1-未参加 23-已参加 4-已完成 5-退款中 6-退款完成 7-已取消
	 *         "tips_times_id": "8486",
	 *         "start_time": "1440243000", //订单活动的开始时间
	 *         "end_time": "1440250200", //订单活动的结束时间
	 *         "phase": "1", //期数
	 *         "name": "邀请注册优惠券", //优惠券名称
	 *         "value": "20.00", //优惠券额度
	 *         "coupon_sn": "68421793", //优惠券sn码
	 *         "nickname": "弓长广", //购买订单会员昵称
	 *         "telephone": "13466554433", //会员手机号
	 *         "title": "吖咪分享会 | 小花×Magic cici的奇幻塔罗美食夜", //订单商品标题
	 *         "member_id": "9982",
	 *         "catname": "已售罄", //活动分类
	 *         "wares": [
	 *             {
	 *                 "price": "288.00", //单条商品价格
	 *                 "server_status": "0" //单条商品状态
	 *             }
	 *         ]
	 *     },
	 * ]
	 */
	public function getList(){
		$page = I('get.page', 1);
		$times_id = I('post.times_id', null);
		$sn = I('post.sn');
		$title = I('post.title');
		$telephone = I('post.telephone');
		$start_time = I('post.start_time');
		$end_time = I('post.end_time');
		$act_status = I('post.act_status', null);
		
		$condition = [];
		$condition['G.member_id'] = session('member.id');
		if(!empty($times_id)){
			$condition['tips_times_id'] = times_id;
		}
		if(!empty($sn)){
			$condition['sn'] = $sn;
		}
		if($act_status !== null){
			$condition['act_status'] = $act_status;
			if($act_status == 0){
				$condition['act_status'] = ['in', '0,5,6,7'];
			}
			if($act_status == 2){
				$condition['act_status'] = ['in', '2,3,4'];
			}
		}
		if(!empty($title)){
			$condition['title'] = ['LIKE', "%{$title}%"];
		}
		if(!empty($telephone)){
			$condition['telephone'] = $telephone;
		}
		if(!empty($start_time)){
			$condition['start_time'] = ['GT', $start_time];
		}
		if(!empty($end_time)){
			$condition['end_time'] = ['LT', $end_time];
		}
		$rs = D('OrderView')->where($condition)->page($page, 5)->order('A.id desc')->select();

		if(empty($rs))$this->ajaxReturn(array());
		
		//查询单价、数量、消费码状态
		$data = array();
		foreach($rs as $row){
			$row['wares'] = M('OrderWares')->field('price, server_status')->where(['order_id' => $row['id']])->select();
			$data[] = $row;
		}
		$this->ajaxReturn($data);
	}

	/**
	 * @apiName 获取退款详情
	 *
	 * @apiGetParam {string} token: 通信令牌
	 * @apiPostParam {int} order_id: 要操作的订单ID
	 *
	 * @apiSuccessResponse
	 * {
	 * 	"cause": "7天无理由退款",
	 * 	"count": 1,
	 * 	"title": "吖咪餐厅| 气质和菓子俘虏你的心",
	 * 	"catname": "餐厅",
	 * 	"start_time": "1454155200",
	 * 	"end_time": "1454162400",
	 * 	"phase": "1"
	 * }
	 */
	Public function refund(){
		$order_id = I('post.order_id');
		if(empty($order_id))$this->error('非法访问!');

		$rs = M('OrderRefund')->where(['order_id' => $order_id, 'is_allow' => 0])->find();
		if(empty($rs))$this->error('该订单的退款申请不存在,或已处理!');
		$data = [
			'cause' => $rs['cause']
		];

		//查出商品及其分类
		$ware = M('OrderWares')->where(['order_id' => $order_id])->select();
		$data['count'] = count($ware);
		if($ware[0]['type'] == 0){
			$_rs = M('tips t')->join('__CATEGORY__ a on category_id=a.id')->where(array('t.id'=>$ware[0]['ware_id'], 't.member_id' => session('member.id')))->find();
			if(empty($_rs))$this->error('该活动不属于你,不能操作退款申请!');
			$data['title'] = $_rs['title'];
			$data['catname'] = $_rs['name'];
			$times = M('TipsTimes')->where(['id' => $ware[0]['tips_times_id']])->find();
			$data['start_time'] = $times['start_time'];
			$data['end_time'] = $times['end_time'];
			$data['phase'] = $times['phase'];
		}else{
			$_rs = M('goods g')->join('__CATEGORY__ a on category_id=a.id')->where(array('g.id'=>$ware[0]['ware_id'], 'g.member_id' => session('member.id')))->find();
			if(empty($_rs))$this->error('该商品不属于你,不能操作退款申请!');
			$data['title'] = $_rs['title'];
			$data['catname'] = $_rs['name'];
		}
		//查出申请退款的会员信息
		/*$member = M('member')->where(['id' => $rs['member_id']])->find();
		$data['nickname'] = $member['nickname'];
		$data['telephone'] = $member['telephone'];*/

		$this->ajaxReturn($data);
	}

	/**
	 * @apiName 接受或拒绝退款接口
	 *
	 * @apiGetParam {string} token: 通信令牌
	 * @apiPostParam {int} order_id: 要操作的订单ID
	 * @apiPostParam {int} allow: 是否允许退款(0-拒绝退款 1-允许退款)
	 * @apiPostParam {string} context: 拒绝退款原因(允许退款可忽略该参数)
	 *
	 * @apiSuccessResponse
	 * {
	 *    "info" : "操作成功!",
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
	Public function confirmRefund(){
		$order_id = I('post.order_id');
		$allow = I('post.allow', 0);
		$context = I('post.context');

		if(empty($order_id))$this->error('非法访问!');
		if($allow == 0 && empty($context))$this->error('拒绝原因不能为空!');

		$rs = M('OrderRefund')->where(['order_id' => $order_id, 'is_allow' => 0])->find();
		if(empty($rs))$this->error('该订单的退款申请不存在,或已处理!');

		$ware = M('OrderWares')->where(['order_id' => $order_id])->find();
		if($ware['type'] == 0){
			$_rs = M('tips')->where(['id' => $ware['ware_id'], 'member_id' => session('member.id')])->find();
		}elseif($ware['type'] == 1){
			$_rs = M('goods')->where(['id' => $ware['ware_id'], 'member_id' => session('member.id')])->find();
		}
		if(empty($_rs))$this->error('该活动不属于你,不能操作退款申请!');

		if($allow){
			$type = M('order_pay')->where(['order_id' => $order_id])->getField('type');
			$this->refundOrder($order_id, $type);

			//如果使用了优惠券则返还
			$member_coupon_id = M('order')->where(['id'=>$order_id])->getField('member_coupon_id');
			M('MemberCoupon')->data(['id'=>$member_coupon_id, 'used_time'=>0])->save();
		}else{
			M('OrderRefund')->data(['id' => $rs['id'], 'is_allow' => 2, 'refusal_reason' => $context])->save();
			M('order')->data(['id'=>$order_id, 'act_status'=>1])->save();
		}
		$this->success('操作成功!');
	}



    /**
     * @apiName 达人回复订单评论
     *
     * @apiGetParam {string} token: 通信令牌
     * @apiPostParam {string} content: 评论内容
     * @apiPostParam {string} pic_ids: 评论图片id(多个id用逗号隔开)
     * @apiPostParam {int} comment_id: 回复的评论ID
     *
     * @apiSuccessResponse
     * {
     *    "info" : "操作成功!",
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
    public function darenReply(){
        if(!session('?member'))$this->error('尚未登录,无法访问!');
        //$order_id = I('post.order_id');
        $stars = (int)I('post.stars', 5);
        $content = I('post.content');
        $pic_ids = I('post.pic_ids');
        $comment_id = I('post.comment_id', null);

        //if(empty($order_id) && empty($article_id) && empty($bang_id))$this->error('非法访问！');
        if(empty($content))$this->error('评论内容不能为空!');
		if(empty($comment_id))$this->error('未指定要回复的评论');


		if($stars > 5 || $stars < 1)$this->error('评论星级必须是1~5的数值!');
		//判断订单是否属于该达人
		$result = M('MemberComment')->where(['id'=>$comment_id])->find();
		if($result['type'] == 0){
			$mid = M('Tips')->where(['id'=>$result['type_id']])->getField('member_id');
			if($mid != session('member.id'))$this->error('订单不属于你,不能评论!');
		}elseif($result['type'] == 1){
			$mid = M('Goods')->where(['id'=>$result['type_id']])->getField('member_id');
			if($mid != session('member.id'))$this->error('订单不属于你,不能评论!');
		}

        //只能回复一次
        $rs = M('MemberComment')->where(['member_id'=>session('member.id'),'type'=>$result['type'],'type_id'=>$result['type_id'],'pid'=>$comment_id])->find();
        if(!empty($rs))$this->error('不能重复回复该评论！');

		//$rs = D('OrderView')->where(['A.id'=>$order_id,'G.member_id'=>session('member.id')])->find();
		//$rs = M('order')->where(['id' => $order_id, 'member_id' => session('member.id'), 'status' => 1])->find();
		//if(empty($rs))$this->error('订单不属于你,不能评论!');
		//判断订单状态
		//if(!in_array($rs['act_status'], [2,3,4]))$this->error('该订单不属于已完成状态,无法评论!');

		//$ware = M('OrderWares')->where(['order_id' => $order_id])->find();

		$type = $result['type'];
		$type_id = $result['type_id'];

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
			'pid' => $comment_id
        ];
        if(!empty($pics_group_id))$data['pics_group_id'] = $pics_group_id;

        //插入评论
        $comment_id = M('MemberComment')->add($data);
        //通知原评论者
        /*$messageId = M('message')->data(['member_id'=>'','type'=>1,'content'=>'达人回复了你的评论'])->add();
        M('MemberMessage')->data(['member_id'=>$result['member_id'],'message_id'=>$messageId])->add();*/

        /*$replys = [];
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
        }*/

        if($comment_id){
            $this->success('评论成功!');
        }else{
            $this->error('评论失败!');
        }
    }

	/**
	 * @apiName 修改订单价格
	 *
	 * @apiGetParam {string} token: 通信令牌
	 * @apiPostParam {int} order_id: 要修改的订单ID
	 * @apiPostParam {float} price: 新的价格
	 *
	 * @apiSuccessResponse
	 * {
	 *    "info" : "价格修改成功!",
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
	Public function modifyPrice(){
		$order_id = I('post.order_id');
		$price = I('post.price');
		$member_id = session('member.id');

		$rs = D('OrderView')->where(['id' => $order_id])->find();
		if($member_id != $rs['tips_member_id'])$this->error('该订单不是您的活动,无法修改订单价格!');
		if(!is_numeric($price) || $price < 0)$this->error('价格必须是一个非负数字!');
		if($rs['act_status'] != 0)$this->error('该订单不属于未支付状态,无法修改!');
		M('order')->where(['id' => $order_id])->save(['price' => $price]);
		$this->success('价格修改成功!');
	}
}