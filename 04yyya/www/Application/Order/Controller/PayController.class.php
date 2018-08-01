<?php

namespace Order\Controller;
use Order\Common\MainController;

// @className 支付提交
class PayController extends MainController {

	/**
	 * @apiName 获取支付页面的相关信息
	 *
	 * @apiGetParam {string} token: 通信令牌
	 * @apiPostParam {int} order_id: 订单ID
	 *
	 * @apiSuccessResponse
	 * {
	 * 	"sn": "faRvV7kUkbdoGXNlAV", //订单号
	 *  "price": "1.90", //要支付的金额
	 * }
	 * @apiErrorResponse
	 * {
	 * 		"info": "失败原因",
	 * 		"status": 0,
	 * 		"url": "",
	 * 	}
	 */
	Public function index(){
		$order_id = I('post.order_id');
		if(empty($order_id))$this->error('非法访问!');
		$rs = M('order')->where(array('id' => $order_id))->find();
		if(empty($rs))$this->error('订单尚未创建!');
		if($rs['member_id'] != session('member.id'))$this->error('该订单不属于你!');
		if($rs['status'] != 1)$this->error('订单已失效!');
		if($rs['act_status'] != 0)$this->error('订单不属于未支付状态!');

		$data = [
			'sn' => $rs['sn'],
			'price' => $rs['price']
		];
		$this->ajaxReturn($data);
	}

	/**
	 * @apiName 提交统一支付接口并返回jsapi参数
	 *
	 * @apiGetParam {string} token: 通信令牌
	 * @apiPostParam {int} order_id: 订单ID
	 * @apiPostParam {int} type: 0-app(支付宝) 1-app(微信) 2-web(微信)  3-支付宝网页支付  4-小程序支付
	 *
	 * @apiSuccessResponse
	 * {
	 *  "notify_url": "http://xxxxxxxx", //回调地址
	 * 	"out_trade_no": "faRvV7kUkbdoGXNlAV", //订单号
	 *  "total_amount": "1.90", //要支付的金额
	 * 	"subject": "订单商品标题", //订单商品标题
	 *  "timeout_express": "3214315324" //订单超时时间戳
	 * }
	 * @apiErrorResponse
	 * {
	 * 		"info": "失败原因",
	 * 		"status": 0,
	 * 		"url": "",
	 * 	}
	 */
	Public function submit(){
		$order_id = I('post.order_id');
		$type = I('post.type', 2);
		$order = D('OrderWaresView')->where(['order_id' => $order_id])->find();

		if(empty($order))$this->error('订单不存在!');
		if($order['type'] == 0){
			if(!empty($order['piece_originator_id'])){
				$this->MemberPieceId = $order['piece_originator_id'];;
				$this->submitPiece($order_id, $order, $type,$order['type']);
			}else{
				$this->submitTips($order_id, $order, $type);
			}
		}elseif($order['type'] == 1){
			$this->submitGoods($order_id, $order, $type);
		}elseif($order['type'] == 2){
			if(!empty($order['order_pid'])){
				$this->submitNextRaise($order_id, $order, $type);
			}else{
				$this->submitRaise($order_id, $order, $type);
			}
		}
	}

	/**
	 * 拼团订单提交支付
	 * @param $order_id
	 * @param $order
	 * @param $type
	 */
	Private function submitPiece($order_id, $order, $type,$order_type, $token = null, $returnUrl = null){
		$rs = D('TipsView')->where(['id' => $order['ware_id']])->find();
		$limit_time = $order['limit_pay_time'];
		if($limit_time > 0 && !empty($limit_time) ){
			if($limit_time < time())$this->error('支付超时,请重新下单!');
		}else{
			//判断是否可以包场
			if($order['is_book']){
				$book = D('OrderWaresView')->where(['tips_times_id' => $order['tips_times_id'], 'status' => 1, 'act_status' => ['IN', '1,2,3,4']])->count();
				if($book > 0)$this->error('这一期活动已经被售出过,无法包场或定制!');
			}
			//判断库存是否充足
			$num = M('OrderWares')->where(['order_id' => $order_id])->count();
			$stock = M('TipsTimes')->where(['id' => $order['tips_times_id']])->getField('stock');
			//是否开团（$piece有值则开团，反之参团）
			if(!empty($order['piece_originator_id'])){
				$piece =D('PieceView')->where(['member_id'=>$order['member_id'],'id' => $order['piece_originator_id']])->find();

				$PieceOrderView = new \Member\Model\PieceOrderViewModel();
				//该拼团下单的人数
				$piece_count = $PieceOrderView->where(['piece_originator_id' => $order['piece_originator_id'],'act_status'=>['IN',[1,2,3,4]]])->count();

				if(!empty($piece)){
					if($piece['act_status'] == 8)$this->error('该拼团已过期');
					if($piece['act_status'] == 9)$this->error('该拼团已取消');
					if($piece['is_cap'] == 1 && $piece_count>= $piece['count'])$this->error('该拼团人数已满');
				}
			}
			if($num > $order['stock'] || $stock<=0 || $piece['count']>$stock) {
				//调用公共方法取消订单
				$this->cancelOrder($order_id);
				$this->error('本期活动剩余数量不足,无法购买!');
			}
		}
		if((float)$order['price'] <= 0){
			$this->sn = $order['sn'];
			$rs = $this->payCallBack();
			$str = ob_get_clean();
			if($rs)
				$this->success('购买成功!');
			else
				$this->error('购买失败!');
			exit;
		}
		if(!empty($order['pay_context']) && !empty($order['pay_type']) && $order['pay_type'] > 0 && $type == $order['pay_type']) {
			if($type == 3)
				echo $order['pay_context'];
			else
				$this->success(json_decode($order['pay_context'], true));
			exit;
		}else{
			M('OrderPay')->where(['order_id' => $order_id])->delete();
		}

		if($type == 1 || $type == 2 || $type == 4){
			$openid = M('MemberView')->where(['id' => session('member.id'), 'type' => $this->openidType])->getField('openid');

			if(empty($openid)){
				$this->error('open_id_is_null');
			}
			$tail = '[A' . $rs['id'] . '-' . $rs['times_id'] . ']';
			$data = $this->wechat->sendPayInfo($rs['title'] . $tail, $openid, $order['sn'], (float)$order['price'], $limit_time, ($type>1?0:1));

			if(!$data){
				if(strpos($this->wechat->errMsg, 'appid and openid not match')){
					$this->error('open_id_is_null');
				}
				$this->error($this->wechat->errMsg);
			}else{
				M('OrderPay')->add([
					'order_id' => $order_id,
					'type' => $type,
					'context' => json_encode($data)
				]);
				//记录订单快照信息
				$this->SaveSnapshotLogs((int)$order_id,3,$this->framework_id());
				$this->success($data);
			}
		}elseif($type == 0){
			M('order')->where(['id' => $order_id])->save(['pay_type' => $type]);
			$tail = '[A' . $rs['id'] . '-' . $rs['times_id'] . ']';
			$data = [
				'notify_url' => 'http://api.'.DOMAIN.'/Order/Pay/alipay_notify.html?paycode=' . C('payCode'),
				'out_trade_no' => $order['sn'],
				'total_amount' => $order['price'],
				'subject' => $rs['title'] . $tail,
				'body' => $rs['intro']
			];
			if($limit_time > time())$data['timeout_express'] = floor(($limit_time - time())/60) . 'm';

			M('OrderPay')->where(['order_id' => $order_id])->delete();
			M('OrderPay')->add([
				'order_id' => $order_id,
				'type' => $type,
				'context' => json_encode($data)
			]);
			//记录订单快照信息
			$this->SaveSnapshotLogs((int)$order_id,3,$this->framework_id());
			$this->success($data);
		}elseif($type == 3){
			$aop = new \Aop\AopClient ();
			$aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
			$aop->appId = C('ALIPAY.appid');
			$aop->rsaPrivateKeyFilePath = C('ALIPAY.private_key_path');
			$aop->alipayPublicKeyFilePath = C('ALIPAY.ali_public_key_path');
			$aop->apiVersion = '1.0';
			$aop->postCharset='utf-8';
			$aop->format='json';
			$request = new \Aop\request\AlipayTradeWapPayRequest ();
			$tail = '[C' . $rs['id'] . '-' . $rs['times_id'] . ']';
			$data = [
				'body' => $rs['title'],
				'subject' => $rs['title'] . $tail,
				'out_trade_no' => $order['sn'],
				'total_amount' => $order['price'],
				'product_code' => 'QUICK_WAP_PAY'
			];
			if ($limit_time > time()) $data['timeout_express'] = floor(($limit_time - time()) / 60) . 'm';

			$request->setNotifyUrl('http://api.'.DOMAIN.'/Order/Pay/alipay_notify.html?paycode=' . C('payCode'));
			$request->setReturnUrl($returnUrl . '&token=' . $token);
			$request->setBizContent(json_encode($data));
			$result = $aop->pageExecute ($request);

			M('OrderPay')->add([
				'order_id' => $order_id,
				'type' => 3,
				'context' => $result
			]);

			//记录订单快照信息
			$this->SaveSnapshotLogs((int)$order_id,3,$this->framework_id());
			echo $result;
			\Think\Log::write($result);
		}

	}

	/**
	 * 活动订单提交支付
	 * @param $order_id
	 * @param $order
	 * @param $type
	 */
	Private function submitTips($order_id, $order, $type, $token = null, $returnUrl = null){
		$rs = D('TipsView')->where(['id' => $order['ware_id']])->find();
		$limit_time = $order['limit_pay_time'];
//		if(!empty($limit_time))$limit_time += $order['create_time'];
		if($limit_time > 0 && !empty($limit_time) ){
			if($limit_time < time())$this->error('支付超时,请重新下单!');
		}else{
			//判断是否可以包场
			if($order['is_book']){
				$book = D('OrderWaresView')->where(['tips_times_id' => $order['tips_times_id'], 'status' => 1, 'act_status' => ['IN', '1,2,3,4']])->count();
				if($book > 0)$this->error('这一期活动已经被售出过,无法包场或定制!');
			}
			//判断库存是否充足
			$num = M('OrderWares')->where(['order_id' => $order_id])->count();
			$stock = M('TipsTimes')->where(['id' => $order['tips_times_id']])->getField('stock');
			if($num > $order['stock'] || $stock<=0 ) {
				//调用公共方法取消订单
				$this->cancelOrder($order_id);
				$this->error('本期活动剩余数量不足,无法购买!');
			}
		}
		if((float)$order['price'] <= 0){
			$this->sn = $order['sn'];
			$rs = $this->payCallBack();
			$str = ob_get_clean();
			if($rs)
				$this->success('购买成功!');
			else
				$this->error('购买失败!');
			exit;
		}
		if(!empty($order['pay_context']) && !empty($order['pay_type']) && $order['pay_type'] > 0 && $type == $order['pay_type']) {
			if($type == 3)
				echo $order['pay_context'];
			else
				$this->success(json_decode($order['pay_context'], true));
			exit;
		}else{
			M('OrderPay')->where(['order_id' => $order_id])->delete();
		}

		if($type == 1 || $type == 2 || $type == 4){
			$openid = M('MemberView')->where(['id' => session('member.id'), 'type' => $this->openidType])->getField('openid');

			if(empty($openid)){
				$this->error('open_id_is_null!');
			}
			$tail = '[A' . $rs['id'] . '-' . $rs['times_id'] . ']';
			/*
			if ($type == 4) {
//				$this->error($openid);
				$data = $this->wechat->sendPayInfo($rs['title'] . $tail, $openid, $order['sn'], (float)$order['price'], $limit_time, 4);
			} else {
				$data = $this->wechat->sendPayInfo($rs['title'] . $tail, $openid, $order['sn'], (float)$order['price'], $limit_time, ($type>1?0:1));
			}
*/
			$data = $this->wechat->sendPayInfo($rs['title'] . $tail, $openid, $order['sn'], (float)$order['price'], $limit_time, ($type>1?0:1));

			if(!$data){
				if(strpos($this->wechat->errMsg, 'appid and openid not match')){
					$this->error('open_id_is_null');
				}
				$this->error($this->wechat->errMsg.'999');
			}else{
				M('OrderPay')->add([
					'order_id' => $order_id,
					'type' => $type,
					'context' => json_encode($data)
				]);
				//记录订单快照信息
				$this->SaveSnapshotLogs((int)$order_id,3,$this->framework_id());
				$this->success($data);
			}
		}elseif($type == 0){
			M('order')->where(['id' => $order_id])->save(['pay_type' => $type]);
			$tail = '[A' . $rs['id'] . '-' . $rs['times_id'] . ']';
			$data = [
				'notify_url' => 'http://api.'.DOMAIN.'/Order/Pay/alipay_notify.html?paycode=' . C('payCode'),
				'out_trade_no' => $order['sn'],
				'total_amount' => $order['price'],
				'subject' => $rs['title'] . $tail,
				'body' => $rs['intro']
			];
			if($limit_time > time())$data['timeout_express'] = floor(($limit_time - time())/60) . 'm';

			M('OrderPay')->where(['order_id' => $order_id])->delete();
			M('OrderPay')->add([
				'order_id' => $order_id,
				'type' => $type,
				'context' => json_encode($data)
			]);
			//记录订单快照信息
			$this->SaveSnapshotLogs((int)$order_id,3,$this->framework_id());
			$this->success($data);
		}elseif($type == 3){
			$aop = new \Aop\AopClient ();
			$aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
			$aop->appId = C('ALIPAY.appid');
			$aop->rsaPrivateKeyFilePath = C('ALIPAY.private_key_path');
			$aop->alipayPublicKeyFilePath = C('ALIPAY.ali_public_key_path');
			$aop->apiVersion = '1.0';
			$aop->postCharset='utf-8';
			$aop->format='json';
			$request = new \Aop\request\AlipayTradeWapPayRequest ();
			$tail = '[C' . $rs['id'] . '-' . $rs['times_id'] . ']';
			$data = [
				'body' => $rs['title'],
				'subject' => $rs['title'] . $tail,
				'out_trade_no' => $order['sn'],
				'total_amount' => $order['price'],
				'product_code' => 'QUICK_WAP_PAY'
			];
			if ($limit_time > time()) $data['timeout_express'] = floor(($limit_time - time()) / 60) . 'm';

			$request->setNotifyUrl('http://api.'.DOMAIN.'/Order/Pay/alipay_notify.html?paycode=' . C('payCode'));
			$request->setReturnUrl($returnUrl . '&token=' . $token);
			$request->setBizContent(json_encode($data));
			$result = $aop->pageExecute ($request);

			M('OrderPay')->add([
				'order_id' => $order_id,
				'type' => 3,
				'context' => $result
			]);

			//记录订单快照信息
			$this->SaveSnapshotLogs((int)$order_id,3,$this->framework_id());
			echo $result;
			\Think\Log::write($result);
		}

	}

	/**
	 * 商品订单提交支付
	 * @param $order_id
	 * @param $order
	 * @param $type
	 */
	Private function submitGoods($order_id, $order, $type, $token = null, $returnUrl = null){
		$rs = M('goods')->where(['id' => $order['ware_id']])->find();
		$limit_time = $order['limit_pay_time'];
//		if(!empty($rs['limit_time']))$limit_time += $order['create_time'];
		if($limit_time > 0 && !empty($limit_time)){
			if($limit_time < time())$this->error('支付超时,请重新下单!');
		}else {
			//判断库存是否充足
			$num = M('OrderWares')->where(['order_id' => $order_id])->count();
			$stock = M('Goods')->where(['id' => $order['ware_id']])->getField('stocks');

			if ($num > $stock) {
				//调用公共方法取消订单
				$this->cancelOrder($order_id);
				$this->error('商品剩余库存不足,无法购买!');
			}
		}
		if((float)$order['price'] <= 0){
			$this->sn = $order['sn'];
			$rs = $this->payCallBack();
			ob_clean();
			if($rs)
				$this->success('购买成功!');
			else
				$this->error('购买失败!');
		}

		if(!empty($order['pay_context']) && !empty($order['pay_type']) && $order['pay_type'] > 0 && $type == $order['pay_type']) {
			if($type == 3)
				echo $order['pay_context'];
			else
				$this->success(json_decode($order['pay_context'], true));
			exit;
		}else{
			M('OrderPay')->where(['order_id' => $order_id,'type'=>$type])->delete();
		}

		if($type == 1 || $type == 2){
//			if(!empty(session('member.openid'))){
//				$openid = session('member.openid');
//			}else{
//				$openid = M('MemberView')->where(['id' => session('member.id'), 'type' => $this->openidType])->getField('openid');
//				if(empty($openid)){
//					$this->error('open_id_is_null');
//				}
//			}
            $openid = M('MemberView')->where(['id' => session('member.id'), 'type' => $this->openidType])->getField('openid');
            if(empty($openid)){
                $this->error('open_id_is_null');
            }
			$tail = '[B' . $rs['id'] . ']';
			$data = $this->wechat->sendPayInfo($rs['title'] . $tail, $openid, $order['sn'], (float)$order['price'], $limit_time, 2-$type);

			if(!$data){
				if(strpos($this->wechat->errMsg, 'appid and openid not match')){
					$this->error('open_id_is_null');
				}
				$this->error($this->wechat->errMsg);
			}else{
				M('OrderPay')->add([
					'order_id' => $order_id,
					'type' => $type,
					'context' => json_encode($data)
				]);
				//记录订单快照信息
				$this->SaveSnapshotLogs((int)$order_id,3,$this->framework_id());
				$this->success($data);
			}
		}elseif($type == 0){
			M('order')->where(['id' => $order_id])->save(['pay_type' => $type]);
			$tail = '[B' . $rs['id'] . ']';
			$data = [
				'notify_url' => 'http://api.'.DOMAIN.'/Order/Pay/alipay_notify.html?paycode=' . C('payCode'),
				'out_trade_no' => $order['sn'],
				'total_amount' => $order['price'],
				'subject' => $rs['title'] . $tail,
				'body' => ''
			];
			if($limit_time > time())$data['timeout_express'] = floor(($limit_time - time())/60) . 'm';
			M('OrderPay')->where(['order_id' => $order_id])->delete();
			M('OrderPay')->add([
				'order_id' => $order_id,
				'type' => $type,
				'context' => json_encode($data)
			]);
			//记录订单快照信息
			$this->SaveSnapshotLogs((int)$order_id,3,$this->framework_id());
			$this->success($data);
		}elseif($type == 3){
			$aop = new \Aop\AopClient ();
			$aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
			$aop->appId = C('ALIPAY.appid');
			$aop->rsaPrivateKeyFilePath = C('ALIPAY.private_key_path');
			$aop->alipayPublicKeyFilePath = C('ALIPAY.ali_public_key_path');
			$aop->apiVersion = '1.0';
			$aop->postCharset='utf-8';
			$aop->format='json';
			$request = new \Aop\request\AlipayTradeWapPayRequest ();
			$tail = '[C' . $rs['id'] . '-' . $rs['times_id'] . ']';
			$data = [
				'body' => $rs['title'],
				'subject' => $rs['title'] . $tail,
				'out_trade_no' => $order['sn'],
				'total_amount' => $order['price'],
				'product_code' => 'QUICK_WAP_PAY'
			];
			if ($limit_time > time()) $data['timeout_express'] = floor(($limit_time - time()) / 60) . 'm';

			$request->setNotifyUrl('http://api.'.DOMAIN.'/Order/Pay/alipay_notify.html?paycode=' . C('payCode'));
			$request->setReturnUrl($returnUrl . '&token=' . $token);
			$request->setBizContent(json_encode($data));
			$result = $aop->pageExecute ($request);

			M('OrderPay')->add([
				'order_id' => $order_id,
				'type' => 3,
				'context' => $result
			]);

			//记录订单快照信息
			$this->SaveSnapshotLogs((int)$order_id,3,$this->framework_id());
			echo $result;
			\Think\Log::write($result);
		}
	}

	/**
	 * 众筹订单提交支付
	 * @param $order_id
	 * @param $order
	 * @param $type
	 */
	Private function submitRaise01($order_id, $order, $type){
		$rs = D('RaiseView')->where(['id' => $order['ware_id']])->find();
		if(empty($order['order_pid'])){
			$limit_time = $rs['limit_time'];
			if(!empty($limit_time))$limit_time += $order['create_time'];
			if($limit_time > 0){
				if($limit_time < time())$this->error('支付超时,请重新下单!');
			}else{
				//判断库存是否充足
				$num = M('OrderWares')->where(['order_id' => $order_id])->count();
				if($num > $order['stock'] && $order['stock'] >= 0) {
					//调用公共方法取消订单
					$this->cancelOrder($order_id);
					$this->error('本期众筹剩余数量不足,无法购买!');
				}
			}
		}else{
			$limit_time = time()+3600;
		}

		if((float)$order['price'] <= 0){
			$this->sn = $order['sn'];
			$rs = $this->payCallBack();
			if($rs)
				$this->success('购买成功!');
			else
				$this->error('购买失败!');
			exit;
		}
		if(!empty($order['pay_context']) && !empty($order['pay_type']) && $order['pay_type'] > 0 && $type == $order['pay_type']) {
			$this->success(json_decode($order['pay_context'], true));
		}else{
			M('OrderPay')->where(['order_id' => $order_id])->delete();
		}
		if($type == 1 || $type == 2){
			$openid = M('MemberView')->where(['id' => session('member.id'), 'type' => $this->openidType])->getField('openid');
			if(empty($openid)){
				$this->error('open_id_is_null');
			}
			$tail = '[C' . $rs['id'] . '-' . $rs['times_id'] . ']';

			$data = $this->wechat->sendPayInfo($rs['title'] . $tail, $openid, $order['sn'], (float)$order['price'], $limit_time);
			if(!$data){
				if(strpos($this->wechat->errMsg, 'appid and openid not match')){
					$this->error('open_id_is_null');
				}
				$this->error($this->wechat->errMsg);
			}else{
				M('OrderPay')->add([
					'order_id' => $order_id,
					'type' => $type,
					'context' => json_encode($data)
				]);
				$this->success($data);
			}

		}elseif($type == 0){
			M('order')->where(['id' => $order_id])->save(['pay_type' => $type]);
			$tail = '[C' . $rs['id'] . '-' . $rs['times_id'] . ']';
			$data = [
				'notify_url' => 'http://api.'.DOMAIN.'/Order/Pay/alipay_notify.html?paycode=' . C('payCode'),
				'out_trade_no' => $order['sn'],
				'total_amount' => $order['price'],
				'subject' => $rs['title'] . $tail,
				'body' => $rs['intro']
			];

			if ($limit_time > time()) $data['timeout_express'] = floor(($limit_time - time()) / 60) . 'm';


			M('OrderPay')->where(['order_id' => $order_id])->delete();
			M('OrderPay')->add([
				'order_id' => $order_id,
				'type' => $type,
				'context' => json_encode($data)
			]);
			$this->success($data);
		}
	}

	/**
	 * 众筹订单提交支付(修改的)
	 * @param $order_id
	 * @param $order
	 * @param $type
	 */
	Private function submitRaise($order_id, $order, $type, $token = null, $returnUrl = null){
		$rs = D('RaiseView')->where(['id' => $order['ware_id']])->find();
		if(empty($order['order_pid'])){
			$limit_time = $order['limit_pay_time'];
//			if(!empty($limit_time))$limit_time += $order['create_time'];
			if($limit_time > 0 && !empty($limit_time) ){
				if($limit_time < time()){
					$this->error('支付超时,请重新下单!');
					$privilege_id = session('privilege.privilege_id');
					$member_privilege_id = session('privilege.member_privilege_id');
					M('MemberPrivilege')->where(['privilege_id'=>$privilege_id,'member_id'=>$member_privilege_id])->save(['order_id'=>['EXP','IS NULL']]);
				}
			}
			// else{
			// 	//判断库存是否充足
			// 	$num = M('OrderWares')->where(['order_id' => $order_id])->count();
			// 	$is_privilege = session('?privilege.privilege_id');
			// 	if($is_privilege&&$rs['start_time']>time()){
			// 		$privilege_id = session('privilege.privilege_id');
			// 		$privilege_info = M('Privilege')->where(['id'=>$privilege_id])->find();
			// 		if($privilege_info['number']<$num&&$privilege_info['number']>=0){
			// 			//调用公共方法取消订单
			// 			$this->cancelOrder($order_id);
			// 			$this->error('优先众筹剩余数量不足,无法购买!');
			// 		}
			// 	}else{
			// 		if($num > $order['stock'] && $order['stock'] >= 0) {
			// 			//调用公共方法取消订单
			// 			$this->cancelOrder($order_id);
			// 			$this->error('本期众筹剩余数量不足,无法购买!');
			// 		}
			// 	}
			// }
		}else{
			$limit_time = time()+3600;
		}


		if((float)$order['price'] <= 0){
			$this->sn = $order['sn'];
			$rs = $this->payCallBack();
			if($rs)
				$this->success('购买成功!');
			else
				$this->error('购买失败!');
			exit;
		}

		if(!empty($order['pay_context']) && !empty($order['pay_type']) && $order['pay_type'] > 0 && $type == $order['pay_type']) {
			if($type == 3)
				echo $order['pay_context'];
			else
				$this->success(json_decode($order['pay_context'], true));
			exit;
		}else{
			M('OrderPay')->where(['order_id' => $order_id,'type'=>$type])->delete();
		}
		if($type == 1 || $type == 2 || $type == 4){
			$openid = M('MemberView')->where(['id' => session('member.id'), 'type' => $this->openidType])->getField('openid');
			\Think\Log::write('openid=>'.$openid);
			if(empty($openid)){
				$this->error('open_id_is_null');
			}
			$tail = '[C' . $rs['id'] . '-' . $rs['times_id'] . ']';

			$data = $this->wechat->sendPayInfo($rs['title'] . $tail, $openid, $order['sn'], (float)$order['price'], $limit_time);

			if(!$data){
				\Think\Log::write('openid123456=>'.strpos($this->wechat->errMsg, 'appid and openid not match'));
				if(strpos($this->wechat->errMsg, 'appid and openid not match')){
					$this->error('open_id_is_null');
				}
				$this->error($this->wechat->errMsg);
			}else{

			    $old = M('OrderPay')->where(['order_id' => $order_id])->find();

			    if (!empty($old)) {
			        M('OrderPay')->where(['order_id' => $order_id])->save(['type' => $type, 'context' => json_encode($data)]);
                } else {
                    M('OrderPay')->add([
                        'order_id' => $order_id,
                        'type' => $type,
                        'context' => json_encode($data)
                    ]);
                }

				//记录订单快照信息
				$this->SaveSnapshotLogs((int)$order_id,3,$this->framework_id());
				$this->success($data);
			}
		}elseif($type == 0){
			M('order')->where(['id' => $order_id])->save(['pay_type' => $type]);
			$tail = '[C' . $rs['id'] . '-' . $rs['times_id'] . ']';
			$data = [
				'notify_url' => 'http://api.'.DOMAIN.'/Order/Pay/alipay_notify.html?paycode=' . C('payCode'),
				'out_trade_no' => $order['sn'],
				'total_amount' => $order['price'],
				'subject' => $rs['title'] . $tail,
				'body' => $rs['intro']
			];

			if ($limit_time > time()) $data['timeout_express'] = floor(($limit_time - time()) / 60) . 'm';


			M('OrderPay')->where(['order_id' => $order_id ,'type'=>$type])->delete();
			M('OrderPay')->add([
				'order_id' => $order_id,
				'type' => $type,
				'context' => json_encode($data)
			]);
			//记录订单快照信息
			$this->SaveSnapshotLogs((int)$order_id,3,$this->framework_id());
			$this->success($data);
		}elseif($type == 3){
			$aop = new \Aop\AopClient();
			$aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
			$aop->appId = C('ALIPAY.appid');
			$aop->rsaPrivateKeyFilePath = C('ALIPAY.private_key_path');
			$aop->alipayPublicKeyFilePath = C('ALIPAY.ali_public_key_path');
			$aop->apiVersion = '1.0';
			$aop->postCharset='utf-8';
			$aop->format='json';
			$request = new \Aop\request\AlipayTradeWapPayRequest ();
			$tail = '[C' . $rs['id'] . '-' . $rs['times_id'] . ']';
			$data = [
				'body' => $rs['title'],
				'subject' => $rs['title'] . $tail,
				'out_trade_no' => $order['sn'],
				'total_amount' => $order['price'],
				'product_code' => 'QUICK_WAP_PAY'
			];
			if ($limit_time > time()) $data['timeout_express'] = floor(($limit_time - time()) / 60) . 'm';

			$request->setNotifyUrl('http://api.'.DOMAIN.'/Order/Pay/alipay_notify.html?paycode=' . C('payCode'));
			$request->setReturnUrl($returnUrl . '&token=' . $token);
			$request->setBizContent(json_encode($data));
			$result = $aop->pageExecute ($request);

            $old = M('OrderPay')->where(['order_id' => $order_id])->find();

            if (!empty($old)) {
                M('OrderPay')->where(['order_id' => $order_id])->save(['type' => 3, 'context' => $result]);
            } else {
                M('OrderPay')->add([
                    'order_id' => $order_id,
                    'type' => 3,
                    'context' => $result
                ]);
            }



			//记录订单快照信息
			$this->SaveSnapshotLogs((int)$order_id,3,$this->framework_id());
			echo $result;
			\Think\Log::write($result);
		}
	}

	/**
	 * 众筹订单二次支付提交
	 * @param $order_id
	 * @param $order
	 * @param $type
	 */
	Private function submitNextRaise01($order_id, $order, $type){
		$rs = D('RaiseView')->where(['id' => $order['ware_id']])->find();
		$limit_time = time()+3600;
		if((float)$order['price'] <= 0){
			$this->sn = $order['sn'];
			$rs = $this->payCallBack();
			if($rs)
				$this->success('购买成功!');
			else
				$this->error('购买失败!');
			exit;
		}
		if(!empty($order['pay_context']) && !empty($order['pay_type']) && $order['pay_type'] > 0 && $type == $order['pay_type']) {
			$this->success(json_decode($order['pay_context'], true));
		}else{
			M('OrderPay')->where(['order_id' => $order_id])->delete();
		}

		if($type == 1 || $type == 2 || $type == 4){
			$openid = M('MemberView')->where(['id' => session('member.id'), 'type' => $this->openidType])->getField('openid');
			if(empty($openid)){
				$this->error('open_id_is_null');
			}
			$tail = '[C' . $rs['id'] . '-' . $rs['times_id'] . ']';
			$data = $this->wechat->sendPayInfo($rs['title'].'【二次支付】' . $tail, $openid, $order['sn'], (float)$order['price'],$limit_time);
			if(!$data){
				if(strpos($this->wechat->errMsg, 'appid and openid not match')){
					$this->error('open_id_is_null');
				}
				$this->error($this->wechat->errMsg);
			}else{
				M('OrderPay')->add([
					'order_id' => $order_id,
					'type' => $type,
					'context' => json_encode($data)
				]);
				$this->success($data);
			}
		}elseif($type == 0){
			M('order')->where(['id' => $order_id])->save(['pay_type' => $type]);
			$tail = '[C' . $rs['id'] . '-' . $rs['times_id'] . ']';
			$data = [
				'notify_url' => 'http://api.'.DOMAIN.'/Order/Pay/alipay_notify.html?paycode=' . C('payCode'),
				'out_trade_no' => $order['sn'],
				'total_amount' => $order['price'],
				'subject' => $rs['title'] . $tail,
				'body' => $rs['intro']
			];

			M('OrderPay')->where(['order_id' => $order_id])->delete();
			M('OrderPay')->add([
				'order_id' => $order_id,
				'type' => $type,
				'context' => json_encode($data)
			]);
			$this->success($data);
		}
	}

	/**
	 * 众筹订单二次支付提交（修改）
	 * @param $order_id
	 * @param $order
	 * @param $type
	 */
	Private function submitNextRaise($order_id, $order, $type, $token = null, $returnUrl = null){
		$rs = D('RaiseView')->where(['id' => $order['ware_id']])->find();
		$limit_pay_time = M('Order')->where(['id'=>$order_id])->getField('limit_pay_time');
		$limit_time = $limit_pay_time;
		if((float)$order['price'] <= 0){
			$this->sn = $order['sn'];
			$rs = $this->payCallBack();
			if($rs)
				$this->success('购买成功!');
			else
				$this->error('购买失败!');
			exit;
		}
		if(!empty($order['pay_context']) && !empty($order['pay_type']) && $order['pay_type'] > 0 && $type == $order['pay_type']) {
			if($type == 3)
				echo $order['pay_context'];
			else
				$this->success(json_decode($order['pay_context'], true));
			exit;
		}else{
			M('OrderPay')->where(['order_id' => $order_id,'type'=>$type])->delete();
		}

		if($type == 1 || $type == 2 || $type == 4){
			$openid = M('MemberView')->where(['id' => session('member.id'), 'type' => $this->openidType])->getField('openid');
			if(empty($openid)){
				$this->error('open_id_is_null');
			}
			$tail = '[C' . $rs['id'] . '-' . $rs['times_id'] . ']';
			$data = $this->wechat->sendPayInfo($rs['title'].'【二次支付】' . $tail, $openid, $order['sn'], (float)$order['price'],$limit_time);
			if(!$data){
				if(strpos($this->wechat->errMsg, 'appid and openid not match')){
					$this->error('open_id_is_null');
				}
				$this->error($this->wechat->errMsg);
			}else{
				M('OrderPay')->add([
					'order_id' => $order_id,
					'type' => $type,
					'context' => json_encode($data)
				]);

				//记录订单快照信息
				$this->SaveSnapshotLogs((int)$order_id,3,$this->framework_id());
				$this->success($data);
			}
		}elseif($type == 0){

			M('order')->where(['id' => $order_id])->save(['pay_type' => $type]);
			$tail = '[C' . $rs['id'] . '-' . $rs['times_id'] . ']';
			\Think\Log::write('tail'.$tail);
			$data = [
				'notify_url' => 'http://api.'.DOMAIN.'/Order/Pay/alipay_notify.html?paycode=' . C('payCode'),
				'out_trade_no' => $order['sn'],
				'total_amount' => $order['price'],
				'subject' => $rs['title'] . $tail,
				'body' => $rs['intro']
			];

			M('OrderPay')->where(['order_id' => $order_id,'type'=>$type])->delete();
			M('OrderPay')->add([
				'order_id' => $order_id,
				'type' => $type,
				'context' => json_encode($data)
			]);
			//记录订单快照信息
			$this->SaveSnapshotLogs((int)$order_id,3,$this->framework_id());
			$this->success($data);
		}elseif($type == 3){
			$aop = new \Aop\AopClient ();
			$aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
			$aop->appId = C('ALIPAY.appid');
			$aop->rsaPrivateKeyFilePath = C('ALIPAY.private_key_path');
			$aop->alipayPublicKeyFilePath = C('ALIPAY.ali_public_key_path');
			$aop->apiVersion = '1.0';
			$aop->postCharset='utf-8';
			$aop->format='json';
			$request = new \Aop\request\AlipayTradeWapPayRequest ();
			$tail = '[C' . $rs['id'] . '-' . $rs['times_id'] . ']';
			$data = [
				'body' => $rs['title'],
				'subject' => $rs['title'] . $tail,
				'out_trade_no' => $order['sn'],
				'total_amount' => $order['price'],
				'product_code' => 'QUICK_WAP_PAY'
			];
			if ($limit_time > time()) $data['timeout_express'] = floor(($limit_time - time()) / 60) . 'm';

			$request->setNotifyUrl('http://api.'.DOMAIN.'/Order/Pay/alipay_notify.html?paycode=' . C('payCode'));
			$request->setReturnUrl($returnUrl . '&token=' . $token);
			$request->setBizContent(json_encode($data));
			$result = $aop->pageExecute ($request);

			M('OrderPay')->add([
				'order_id' => $order_id,
				'type' => 3,
				'context' => $result
			]);

			//记录订单快照信息
			$this->SaveSnapshotLogs((int)$order_id,3,$this->framework_id());
			echo $result;
			\Think\Log::write($result);
		}
	}

	/**
	 * 众筹订单提交支付宝网页支付
	 * @param $order_id
	 * @param $order
	 * @param $type
	 */
	Public function submitAlipay01(){
		$order_id = I('get.order_id');
		$token = I('get.token');

		$returnUrl = 'http://' . DOMAIN . '/?page=choice-ucenter-myRaiseOrder-orderRaiseDetail&order_id=' . $order_id;
		//判断是否用微信浏览器打开的
		if ( strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false ) {
			echo '<body style="width:100%;background:#fff url(/images/toAlipay.png) no-repeat center center / 100% auto;"></body>';
			echo '<script>window.onfocus = function(){window.confirm("您支付成功了吗?"); window.location.href="'. $returnUrl .'";};</script>';
			exit;
		}

		$order = D('OrderWaresView')->where(['order_id' => $order_id])->find();
		if(empty($order))$this->error('订单不存在!');

		if($order['type'] == 0){
			$this->submitTips($order_id, $order, 3, $token, $returnUrl);
		}elseif($order['type'] == 1){
			$this->submitGoods($order_id, $order, 3, $token, $returnUrl);
		}elseif($order['type'] == 2){
			if(!empty($order['order_pid'])){
				$this->submitNextRaise($order_id, $order, 3, $token, $returnUrl);
			}else{
				$this->submitRaise($order_id, $order, 3, $token, $returnUrl);
			}
		}
//		$rs = D('RaiseView')->where(['id' => $order['ware_id']])->find();
//		if(empty($order['order_pid'])){
//			$limit_time = $rs['limit_time'];
//			if(!empty($limit_time))$limit_time += $order['create_time'];
//			if($limit_time > 0){
//				if($limit_time < time())$this->error('支付超时,请重新下单!');
//			}
//		}else{
//			$limit_time = time()+3600;
//		}
//
//		if((float)$order['price'] <= 0){
//			$this->sn = $order['sn'];
//			$rs = $this->payCallBack();
//			if($rs)
//				$this->success('购买成功!');
//			else
//				$this->error('购买失败!');
//			exit;
//		}
//
//		if(!empty($order['pay_context']) && !empty($order['pay_type']) && $order['pay_type'] > 0 && 3 == $order['pay_type']) {
//			echo $order['pay_context'];
//			exit;
//		}else{
//			M('OrderPay')->where(['order_id' => $order_id])->delete();
//		}
//
//		$aop = new \Aop\AopClient ();
//		$aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
//		$aop->appId = C('ALIPAY.appid');
//		$aop->rsaPrivateKeyFilePath = C('ALIPAY.private_key_path');
//		$aop->alipayPublicKeyFilePath = C('ALIPAY.ali_public_key_path');
//		$aop->apiVersion = '1.0';
//		$aop->postCharset='utf-8';
//		$aop->format='json';
//		$request = new \Aop\request\AlipayTradeWapPayRequest ();
//		$tail = '[C' . $rs['id'] . '-' . $rs['times_id'] . ']';
//		$data = [
//			'body' => $rs['title'],
//			'subject' => $rs['title'] . $tail,
//			'out_trade_no' => $order['sn'],
//			'total_amount' => $order['price'],
//			'product_code' => 'QUICK_WAP_PAY'
//		];
//		if ($limit_time > time()) $data['timeout_express'] = floor(($limit_time - time()) / 60) . 'm';
//
//		$request->setNotifyUrl('http://api.'.DOMAIN.'/Order/Pay/alipay_notify.html?paycode=' . C('payCode'));
//		$request->setReturnUrl($returnUrl . '&token=' . $token);
//		$request->setBizContent(json_encode($data));
//		$result = $aop->pageExecute ($request);
//
//		M('OrderPay')->add([
//			'order_id' => $order_id,
//			'type' => 3,
//			'context' => $result
//		]);
//		echo $result;
//		\Think\Log::write($result);
////		var_dump($result);
//		exit;
	}

	/**
	 * 订单提交支付宝网页支付(修改2017-1-11)
	 * @param $order_id
	 * @param $order
	 * @param $type
	 */
	Public function submitAlipay(){
		$order_id = I('get.order_id');
		$type = (string)I('get.type', '2');
		$token = I('get.token');
		$pieceType = (int)I('get.pieceType', -1); // -1 -- 不是拼团， 0 -- 参团， 1 -- 开团
        $group_id = I('get.group_id');

		switch ($type) {
            case '0':
                $returnUrl = 'http://' . DOMAIN . '/?page=choice-ucenter-myOrder-orderDetail&order_id=' . $order_id;
                break;
            case '1':
                $returnUrl = 'http://' . DOMAIN . '/?page=choice-ucenter-myGoodsOrder-orderGoodsDetail&order_id=' . $order_id;
                break;
            default:
                $returnUrl = 'http://' . DOMAIN . '/?page=choice-ucenter-myRaiseOrder-orderRaiseDetail&order_id=' . $order_id;
		}

		if ($pieceType >= 0) {
		    $returnUrl = 'http://' . DOMAIN . '/?page=choice-ucenter-groupsDetail&groups_id=' . $group_id;
		    $returnUrl = $returnUrl . '&pieceType=' . $pieceType;
        }
		//判断是否用微信浏览器打开的
		if ( strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false ) {
			echo '<body style="width:100%;background:#fff url(/images/toAlipay.png) no-repeat center center / 100% auto;"></body>';
			echo '<script>window.onfocus = function(){window.confirm("您支付成功了吗?"); window.location.href="'. $returnUrl .'";};</script>';
			exit;
		}

		$order = D('OrderWaresView')->where(['order_id' => $order_id])->find();
		if(empty($order))$this->error('订单不存在!');
//		if(!empty($order['piece_originator_id'])){
//			$returnUrl = 'http://' . DOMAIN . '/?page=groupsDetail&piece_originator_id=' . $order['piece_originator_id'];
//		}else{
//			$returnUrl = 'http://' . DOMAIN . '/?page=choice-ucenter-myRaiseOrder-orderRaiseDetail&order_id=' . $order_id;
//		}

		if($order['type'] == 0){
			$this->submitTips($order_id, $order, 3, $token, $returnUrl);
		}elseif($order['type'] == 1){
			$this->submitGoods($order_id, $order, 3, $token, $returnUrl);
		}elseif($order['type'] == 2){
			if(!empty($order['order_pid'])){
				$this->submitNextRaise($order_id, $order, 3, $token, $returnUrl);
			}else{
				$this->submitRaise($order_id, $order, 3, $token, $returnUrl);
			}
		}

//		$rs = D('RaiseView')->where(['id' => $order['ware_id']])->find();
//		if(empty($order['order_pid'])){
//			$limit_time = $order['limit_pay_time'];
////			if(!empty($limit_time))$limit_time += $order['create_time'];
//			if($limit_time > 0 && !empty($limit_time) ){
//				if($limit_time < time()){
//					$this->error('支付超时,请重新下单!');
//					$privilege_id = session('privilege.privilege_id');
//					$member_privilege_id = session('privilege.member_privilege_id');
//					M('MemberPrivilege')->where(['privilege_id'=>$privilege_id,'member_id'=>$member_privilege_id])->save(['order_id'=>['EXP','IS NULL']]);
//				}
//			}
//		}else{
//			$limit_time = time()+3600;
//		}
//
//		if((float)$order['price'] <= 0){
//			$this->sn = $order['sn'];
//			$rs = $this->payCallBack();
//			if($rs)
//				$this->success('购买成功!');
//			else
//				$this->error('购买失败!');
//			exit;
//		}
//
//		if(!empty($order['pay_context']) && !empty($order['pay_type']) && $order['pay_type'] > 0 && 3 == $order['pay_type']) {
//			echo $order['pay_context'];
//			exit;
//		}else{
//			M('OrderPay')->where(['order_id' => $order_id,'type'=>3])->delete();
//		}
//
//		$aop = new \Aop\AopClient ();
//		$aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
//		$aop->appId = C('ALIPAY.appid');
//		$aop->rsaPrivateKeyFilePath = C('ALIPAY.private_key_path');
//		$aop->alipayPublicKeyFilePath = C('ALIPAY.ali_public_key_path');
//		$aop->apiVersion = '1.0';
//		$aop->postCharset='utf-8';
//		$aop->format='json';
//		$request = new \Aop\request\AlipayTradeWapPayRequest ();
//		$tail = '[C' . $rs['id'] . '-' . $rs['times_id'] . ']';
//		$data = [
//			'body' => $rs['title'],
//			'subject' => $rs['title'] . $tail,
//			'out_trade_no' => $order['sn'],
//			'total_amount' => $order['price'],
//			'product_code' => 'QUICK_WAP_PAY'
//		];
//		if ($limit_time > time()) $data['timeout_express'] = floor(($limit_time - time()) / 60) . 'm';
//
//		$request->setNotifyUrl('http://api.'.DOMAIN.'/Order/Pay/alipay_notify.html?paycode=' . C('payCode'));
//		$request->setReturnUrl($returnUrl . '&token=' . $token);
//		$request->setBizContent(json_encode($data));
//		$result = $aop->pageExecute ($request);
//
//		M('OrderPay')->add([
//			'order_id' => $order_id,
//			'type' => 3,
//			'context' => $result
//		]);
//
//		//记录订单快照信息
//		$this->SaveSnapshotLogs((int)$order_id,3,$this->framework_id());
//		echo $result;
//		\Think\Log::write($result);
////		var_dump($result);
//		exit;
	}

	//接收微信端的支付返回,并更改订单的活动状态
	Public function wx_notify(){
		if(empty($this->sn))$this->error('非法访问!');
		if($this->refund === false){ //微信支付
			$piece_originator_id = D('OrderWaresView')->where(['sn' => $this->sn])->group('B.id')->getField('piece_originator_id');
			if(!empty($piece_originator_id)){
				$this->payPieceCallBack($piece_originator_id);
			}else{
				$this->payCallBack();
			}
        }else{
			$order_sn = $this->sn;
			$order_id = M('order')->where(['sn' => $order_sn])->getField('id');
			$this->refundCallBack($order_id);
        }
		echo 'success';
	}

	//接收支付宝的支付返回
	Public function alipay_notify(){
		if(empty($this->sn))$this->error('非法访问!');
		//判断是否为退款
		if($this->refund === false){

			$piece_originator_id = D('OrderWaresView')->where(['sn' => $this->sn])->group('B.id')->getField('piece_originator_id');
			if(!empty($piece_originator_id)){
				$this->payPieceCallBack($piece_originator_id);
			}else{
				$this->payCallBack();
			}
		}else{
			$rs = M('OrderPay')->where(['trade_no' => $this->trade_no])->find();
			$this->refundCallBack($rs['order_id']);
		}
		echo 'success';
	}

	//支付回调处理
	Private function payCallBack(){
		$model = M();
		$model->startTrans();//开启事务
		//验证库存
		$order = D('OrderWaresView')->where(['sn' => $this->sn, 'act_status' => 0, 'status' => 1])->find();
        //订单状态不为未支付状态
        if(empty($order)){
            return false;
        }
		$channel = 0;
		if(in_array($order['channel'], [7,8,9]))$channel = 1;
		if($order['act_status'] != 0){
			$model->rollback();//回滚
			return false;
		}
		//查询购买数量
		$num = M('OrderWares')->where(['order_id' => $order['order_id']])->count();

		\Think\Log::write($this->trade_no);
		\Think\Log::write($this->pay_type);

		//记录商家订单
		if(!empty($this->trade_no))M('OrderPay')->where(['order_id' => $order['order_id'],'type'=>$this->pay_type])->save(['trade_no' => $this->trade_no,'success_pay_time'=>time()]);

		\Think\Log::write('订单金额 = ：'.$this->price);

		//对比支付的金额
		if($order['price'] > 0 && $this->price != $order['price']){
			//支付金额不准确直接退款并取消订单
			$this->refundOrder($order['order_id'], 2);
			$model->rollback();//回滚
//			$this->pushMessage($order['member_id'], "您支付的金额与订单价位不符,我们正在将款项退回给您,请您稍等...", 'wx|sms', 3, $order['order_id'], 0, $channel);
			$this->push_Message($order['member_id'], [], 'SMS_35925149', 'wx|sms|ios', 3,null, $order['order_id'], 0, $channel);
			return false;
		}

		if($order['type'] == 0) {

			$tips = D('TipsView')->where(['times_id' => $order['tips_times_id']])->find();
			$p_tags_id = M('TipsTag')->where(['tips_id' => $tips['id'],'tag_id'=>76])->getField('tag_id');

			if ($tips['limit_time'] == 0) {
				//对活动时间表加上行锁防止超卖
				$times = M('TipsTimes')->where(['id' => $order['tips_times_id']])->lock(true)->find();

				//对比库存是否充足
				if ($num > $times['stock']) {
					//库存不足,直接退款并取消订单
					$this->refundOrder($order['order_id'], 1);
					$model->rollback();//回滚
//					$this->pushMessage($order['member_id'], "您购买的活动已售罄或剩余数量不足,我们正在将款项退回给您,请您稍等...", 'wx|sms', 3, $order['order_id'], 0, $channel);
					$this->push_Message($order['member_id'], [], 'SMS_36075312', 'wx|sms',null, 3, $order['order_id'], 0, $channel);
					return false;
				}

				//判断是否可以包场
				if ($order['is_book']) {
					$book = D('OrderWaresView')->where(['tips_times_id' => $order['tips_times_id'], 'status' => 1, 'act_status' => ['IN', '1,2,3,4']])->count();
					if ($book > 0) {
						$this->refundOrder($order['order_id'], 1);
						$model->rollback();//回滚
//						$this->pushMessage($order['member_id'], "您购买的包场活动已被别人买进,我们正在将款项退回给您,请您稍等...", 'wx|sms', 3, $order['order_id'], 0, $channel);
						$this->push_Message($order['member_id'], [], 'SMS_36020221', 'wx|sms|ios', null,3, $order['order_id'], 0, $channel);
						return false;
					} else {
						M('TipsTimes')->where(['id' => $order['tips_times_id']])->save(['stock' => 0]);
					}
				} else {
					M('TipsTimes')->where(['id' => $order['tips_times_id']])->setDec('stock', $num);
				}
			}

			M('Order')->where(['sn' => $this->sn])->save(['act_status' => 1, 'status' => 1]);

			//注销微信卡券
//		$memberCouponId = M('order')->where(['sn' => $this->sn])->getField('member_coupon_id');
//		if(!empty($memberCouponId)){
//			$rs = M('member_coupon')->join('__COUPON__ ON __MEMBER_COUPON__.coupon_id=__COUPON__.id')->where('__MEMBER_COUPON__.id='.$memberCouponId)->find();
//			//如果使用了微信卡券，则进行核销
//			if(!empty($rs['wx_sn'])){
//				$access_token = getAccessToken();
//				$post_data = ['code'=>$rs['sn'],'card_id'=>$rs['wx_sn']];
//				$url = "https://api.weixin.qq.com/card/code/consume?access_token=$access_token";
//				$rs = $this->curl_post($url, json_encode($post_data));
//				if($rs['errcode']!=0){
//					$str = json_encode($rs);
//					\Think\Log::write('微信卡券核销失败：'.$str);
//				}
//			}
//		}
			$model->commit();//事务提交

			$w = date('w', $tips['start_time']);
			$week = ['周日', '周一', '周二', '周三', '周四', '周五', '周六'];
			$date = date('Y年m月d日', $tips['start_time']) . " ({$week[$w]}) " . date('H:i', $tips['start_time']);
			$_date = date('H:i', $tips['end_time']);
			if (date('m-d', $tips['start_time']) != date('m-d', $tips['end_time'])) {
				$_date = date('m月d日', $tips['end_time']) . ' ' . $_date;
			}

			//根据不同城市指定客服
			$server = C('WX_SERVICE');
			$citys_id = array_key_exists($tips['city_id'], $server) ? $tips['city_id'] : 224;
			$wx = $server[$citys_id];
//			if(in_array($order['channel'], [7,8,9])){
//				$context = "感谢小主购买了“{$tips['title']}”，小主的入席时间是{$date} - {$_date}，地址：{$tips['address']}。Host会尽快与您联系，如有疑问请添加我有饭客服微信号：{$wx}，随时保持联系!";
//				$this->pushMessage($order['member_id'], $context, 'wx|sms', 3, $order['order_id'], 0, $channel);
//			}else{
//				$context = "感谢小主购买了【{$tips['title']}】，小主的入席时间是{$date} - {$_date}，地址：{$tips['address']}。达人会尽快与您联系。如有疑问请添加吖咪客服微信号：{$wx}，随时保持联系! ^_^";
//				$this->pushMessage($order['member_id'], $context, 'wx|sms', 3, $order['order_id']);
//			}

			// 达人信息
			$member_rs = M('member')->field(['nickname', 'telephone'])->where(['id' => $order['member_id']])->find();

			//2016-12-27
			if (in_array($order['channel'], [7, 8, 9])) {
				$params = [
					'title' => $tips['title'],
					'sdate' => $date,
					'edate' => $_date,
					'address' => $tips['address'],
					'platform_member' => 'Host',
					'platform' => '我有饭',
					'wx' => $wx,
				];
			} elseif($p_tags_id != 76) {
				$params = [
					'title' => $tips['title'],
					'sdate' => $date,
					'edate' => $_date,
					'address' => $tips['address'],
					'platform_member' => '达人',
					'platform' => '吖咪',
					'wx' => $wx,
				];
			} else {
				$params = [
					'title' => $tips['title'],
					'num' => $num,
					'sn' => $this->sn,
					'date' => date('m月d日', $tips['end_time']),
					'address' => $tips['address'],
					'phone' => $member_rs['telephone']
				];
			}


			if($p_tags_id != 76) {
				$this->push_Message($order['member_id'], $params, 'SMS_36105291', 'wx|sms|ios',null, 3, $order['order_id'], 0, $channel);
			}else {
				// 私房菜成功购买
				$this->push_Message($order['member_id'], $params, 'SMS_77360070', 'wx|sms|ios',null, 3, $order['order_id'], 0, $channel);
			}

			//发短信给达人
			//if(in_array($tips['city_id'], [35, 37])){
//			\Think\Log::write('留言：'.$order['context']);
//			if(!empty($order['context'])){
//				$context = "您所发布的活动『{$tips['title']}』已有用户购买，活动时间{$date}，昵称：{$member_rs['nickname']}，手机号：{$member_rs['telephone']}，购买数量{$num}份，用户留言：{$order['context']}，请您尽快与她(他)联系。如有疑问请加客服微信：{$server[224]}";
//			}else{
//			$context = "您所发布的活动『{$tips['title']}』已有用户购买，活动时间{$date}，昵称：{$member_rs['nickname']}，手机号：{$member_rs['telephone']}，购买数量{$num}份，请您尽快与她(他)联系。如有疑问请加客服微信：{$server[224]}";
//			}
//			$this->pushMessage($tips['member_id'], $context, 'sms', 0, 0, 0, 1);


			//2016-12-27
			if(!empty($order['context'])){
				$params = [
					'title' => $tips['title'],
					'datetime' => $date,
					'nickname' => $member_rs['nickname'],
					'telephone' => $member_rs['telephone'],
					'num' => $num,
					'leavemessage' => '用户留言：' . $order['context'] . ',',
					'wx' => $server[224],
				];
			}else{
				$params = [
					'title' => $tips['title'],
					'datetime' => $date,
					'nickname' => $member_rs['nickname'],
					'telephone' => $member_rs['telephone'],
					'num' => $num,
					'leavemessage' => '',
					'wx' => $server[224],
				];
			}
			$this->push_Message($tips['member_id'], $params,'SMS_36385054', 'sms|wx|ios',null, 0, 0, 0, 0);
			$this->SaveSnapshotLogs((int)$order['order_id'],3,$this->framework_id());
			return true;
		}
		elseif($order['type'] == 1){
			//对商品表加上行锁防止超卖
			$goods = M('Goods')->where(['id' => $order['ware_id']])->lock(true)->find();
			if($goods['limit_time'] == 0){
				//对比库存是否充足
				if($num > $goods['stocks']){
					//库存不足,直接退款并取消订单
					$this->refundOrder($order['order_id'], 1);
					$model->rollback();//回滚
//					$this->pushMessage($order['member_id'], "您购买的商品已售罄或剩余数量不足,我们正在将款项退回给您,请您稍等...", 'wx|sms', 3, $order['order_id'], 0, $channel);

					//2013-12-27
					$this->push_Message($order['member_id'], array(), 'SMS_36075312', 'wx|sms|ios',null, 3, $order['order_id'], 0, $channel);
					return false;
				}

				M('Goods')->where(['id' => $order['ware_id']])->setDec('stocks', $num);
			}

			M('Order')->where(['sn' => $this->sn])->save(['act_status' => 1, 'status' => 1]);

			//注销微信卡券
//		$memberCouponId = M('order')->where(['sn' => $this->sn])->getField('member_coupon_id');
//		if(!empty($memberCouponId)){
//			$rs = M('member_coupon')->join('__COUPON__ ON __MEMBER_COUPON__.coupon_id=__COUPON__.id')->where('__MEMBER_COUPON__.id='.$memberCouponId)->find();
//			//如果使用了微信卡券，则进行核销
//			if(!empty($rs['wx_sn'])){
//				$access_token = getAccessToken();
//				$post_data = ['code'=>$rs['sn'],'card_id'=>$rs['wx_sn']];
//				$url = "https://api.weixin.qq.com/card/code/consume?access_token=$access_token";
//				$rs = $this->curl_post($url, json_encode($post_data));
//				if($rs['errcode']!=0){
//					$str = json_encode($rs);
//					\Think\Log::write('微信卡券核销失败：'.$str);
//				}
//			}
//		}
			$model->commit();//事务提交

//			$context = "感谢小主购买了“{$goods['title']}”， 订单号为{$this->sn}，已支付成功。卖家将依照订单顺序陆续发货，请耐心等待，谢谢。";
//			$this->pushMessage($order['member_id'], $context, 'wx|sms', 3, $order['order_id'], 0, $channel);

			//2016-12-27
			$params = [
				'title' =>$goods['title'],
				'sn' =>$this->sn,
			];
			$this->push_Message($order['member_id'], $params,'SMS_36190100', 'wx|sms|ios', null,3, $order['order_id'], 0, $channel);




			$is_profit = M('ProfitList')->where(['type' => 1, 'goods_id' => $goods['id']])->find();
			$invite_member_id = M('Order')->alias('a')->join('__MEMBER__ as b on a.invite_member_id=b.id')->where(['a.id' => $order['order_id']])->getField('b.id');
			if (isset($is_profit)) {
				$member = M('Member')->where(['id'=>$order['member_id']])->find();
				$num = M('OrderWares')->where(['order_id' => $order['order_id']])->count();
				$params2 = [
					'nickname' => $member['nickname'],
					'price' => $is_profit['share_money'] * $num,
				];
				/*
				$params2 = [
					'nickname' => '333',
					'price' => '444',
				];*/
				$this->push_Message($invite_member_id, $params2,'SMS_127150515', 'wx|sms|ios', null,3, $order['order_id'], 0, $channel);
				/*
				$d['id'] = 1;
				M('ProfitStatement')->add($d);
				*/
			}


            //记录订单快照信息
			\Think\Log::write('商品订单缓存：'.$order['order_id']);
			$this->SaveSnapshotLogs((int)$order['order_id'],3,$this->framework_id());

			return true;
		}
		elseif($order['type'] == 2){

			$raise = D('RaiseView')->where(['times_id' => $order['tips_times_id']])->find();

			M('Order')->where(['sn' => $this->sn])->save(['act_status' => 1, 'status' => 1]);

			$model->commit();//事务提交

//			if(in_array($order['channel'], [7,8,9])){
//				$context = "感谢小主购买了“{$raise['title']}”。Host会尽快与您联系，如有疑问请添加我有饭客服微信号：{$wx}，随时保持联系!";
//				$this->pushMessage($order['member_id'], $context, 'wx|sms', 3, $order['order_id'], 0, $channel);
//			}else{
			$rs = D('RaiseOrderWaresView')->where(['A.type' => 2, 'A.ware_id' => $order['ware_id'], 'B.status' => 1, 'B.act_status' => ['in', '1,2,3,4']])->group('A.id')->select();
			$_rs['totaled'] = $_rs['count'] = 0;
			foreach($rs as $row){
				$_rs['totaled'] += $row['raise_times_price'];
				$_rs['count'] ++;
			}
			$_rs['percent'] = sprintf("%01.1f", ($_rs['totaled']/$raise['total'])*100).'%';
//			if($raise['prepay']>0){
//				if(!empty($order['order_pid'])){//二次支付
//					$context = "感谢小主支持了“{$raise['title']}”，您可以在我的订单中查询到您所支持的众筹项目。如有疑问请添加吖咪客服微信号：yami194，随时保持联系! ^_^";
//				}else{//预付款
//					$context = "感谢您的支持！我是尹江波，已收到您支持我发起的众筹项目《始于1880年的传奇茶楼 首次众筹》共{$order['price']}元。请进入吖咪公众号或APP查看详情 (风险提示：众筹期间请积极关注项目进展，您可以在项目评论区和吖咪发起的微信群组联系到发起人，请勿加入非项目发起人以及非吖咪官方创建的微信群，谨防受骗。)客服微信：yami194（工作时间：9:00-20:00）有问题随时保持联络！";
//				}
//			}else{//全额支付
//				$context = "感谢您的支持！我是尹江波，已收到您支持我发起的众筹项目《始于1880年的传奇茶楼 首次众筹》共{$order['price']}元。请进入吖咪公众号或APP查看详情 (风险提示：众筹期间请积极关注项目进展，您可以在项目评论区和吖咪发起的微信群组联系到发起人，请勿加入非项目发起人以及非吖咪官方创建的微信群，谨防受骗。)客服微信：yami194（工作时间：9:00-20:00）有问题随时保持联络！";
//			}
//			$this->pushMessage($order['member_id'], $context, 'wx|sms', 3, $order['order_id'],0,0);

			//2016-12-27
			if($raise['prepay']>0){
				if(!empty($order['order_pid'])){//二次支付
                    $params = [
                        'name' =>$raise['nickname'],
                        'title' => '《' . $raise['title'] . '》”' . $raise['times_title'] . '”',
                        'money' => $order['price'],
                    ];
					$this->push_Message($order['member_id'], $params,'SMS_84655050', 'wx|sms|ios',null, 3, $order['order_id'],0,$channel);
				}else{//预付款
//					$context = "感谢您的支持！我是尹江波，已收到您支持我发起的众筹项目《始于1880年的传奇茶楼 首次众筹》共{$order['price']}元。请进入吖咪公众号或APP查看详情 (风险提示：众筹期间请积极关注项目进展，您可以在项目评论区和吖咪发起的微信群组联系到发起人，请勿加入非项目发起人以及非吖咪官方创建的微信群，谨防受骗。)客服微信：yami194（工作时间：9:00-20:00）有问题随时保持联络！";
                    $params = [
						'name' =>$raise['nickname'],
						'title' => '《' . $raise['title'] . '》',
						'times' => $raise['times_title'],
						'money' => $order['price'] . '元',
						'project_name' => '众筹',
                    ];
                    $params2 = [
                        'name' => $raise['nickname'],
                        'title' => $raise['title'],
                        'time' => $raise['times_title']
                    ];
					$this->push_Message($order['member_id'], $params,'SMS_85395035', 'wx|sms|ios',null, 3, $order['order_id'],0,$channel);
                    $this->push_Message($order['member_id'], $params2, 'WEIXIN_20180731', 'wx', null, 3, $order['order_id'], 0, $channel);
                }
			}else{//全额支付
//				$context = "感谢您的支持！我是尹江波，已收到您支持我发起的众筹项目《始于1880年的传奇茶楼 首次众筹》共{$order['price']}元。请进入吖咪公众号或APP查看详情 (风险提示：众筹期间请积极关注项目进展，您可以在项目评论区和吖咪发起的微信群组联系到发起人，请勿加入非项目发起人以及非吖咪官方创建的微信群，谨防受骗。)客服微信：yami194（工作时间：9:00-20:00）有问题随时保持联络！";
				$params = [
					'name' =>$raise['nickname'],
					'title' => '《' . $raise['title'] . '》',
					'times' => $raise['times_title'],
					'money' => $order['price'] . '元',
					'project_name' => '众筹',
				];

				$this->push_Message($order['member_id'], $params,'SMS_85395035', 'wx|sms|ios',null, 3, $order['order_id'],0,$channel);
			}

			$member = M('Member')->where(['id'=>$order['member_id']])->find();
//			if($order['price'] < 10)
//				$str = "您发起的众筹项目《始于1880年的传奇茶楼 首次众筹》已有用户无条件打赏支持，金额：{$order['price']} 昵称：{$member['nickname']} ，手机号：{$member['telephone']} 。呼朋引伴分享转发，让更多人知道你的项目有多棒。";
//			else
//				$str = "您发起的众筹项目《始于1880年的传奇茶楼 首次众筹》已有用户支持，金额：{$order['price']} 昵称：{$member['nickname']} ，手机号：{$member['telephone']} ，购买数量1份。请您尽快与她(他)联系。如有疑问请加客服微信：yami194。";
//			$this->pushMessage(12466, $str, 'sms', 0, 0, 0, 0);
//			$this->pushMessage(13063, $str, 'sms', 0, 0, 0, 0);

			//2016-12-27
			if($order['price'] < 10){
				$params = [
					'project'=>'众筹',
					'title'=>$raise['title'],
					'price'=>(string)$order['price'],
					'nickname'=>$member['nickname'],
					'telephone'=>(string)$member['telephone'],
				];
				if(isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'm.yami.ren') === false && !strrpos($raise['title'], '测试')) {
//					$this->push_Message(12466, $params, 'SMS_36355042', 'wx|sms|ios', null, 0, 0, 0, 0);
//					$this->push_Message(13063, $params, 'SMS_36355042', 'wx|sms|ios', null, 0, 0, 0, 0);
//					$this->push_Message(269188, $params, 'SMS_36355042', 'wx|sms|ios', null, 0, 0, 0, 0);
				}elseif(!strrpos($raise['title'], '测试')){
					$this->push_Message($raise['member_id'], $params,'SMS_36355042', 'wx|sms|ios',null, 0, 0, 0, 0);
				}

			}else {
				$params = [
					'project'=>'众筹',
					'title'=> $raise['title'],
					'price'=>(string)$order['price'],
					'nickname'=>$member['nickname'],
					'telephone'=>(string)$member['telephone'],
					'wx'=>'yami194'
				];

				if(isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'm.yami.ren') === false){
//					$this->push_Message(12466, $params,'SMS_55720155', 'wx|sms|ios',null, 0, 0, 0, 0);
					$this->push_Message(13063, $params,'SMS_55720155', 'wx|sms|ios',null, 0, 0, 0, 0); //amy
					$this->push_Message(2374, $params,'SMS_55720155', 'wx|sms|ios',null, 0, 0, 0, 0); //xiwen
					$this->push_Message($raise['member_id'], $params,'SMS_55720155', 'wx|sms|ios',null, 0, 0, 0, 0);
				}else{
					$this->push_Message($raise['member_id'], $params,'SMS_55720155', 'wx|sms|ios',null, 0, 0, 0, 0);
					$this->push_Message(13063, $params,'SMS_55720155', 'wx|sms|ios',null, 0, 0, 0, 0); //amy
					$this->push_Message(2374, $params,'SMS_55720155', 'wx|sms|ios',null, 0, 0, 0, 0); //xiwen
				}
			}

			if ($raise['type'] == 1) {
				// 抽奖挡位,插入一条抽奖记录
                $memberId = session('member.id');
                $lastRaiseLuckyItem = M('RaiseLucky')->where(['raise_times_id' => $raise['times_id']])->order('id desc')->limit(1)->find();
                $lastLuckyNumber = $lastRaiseLuckyItem ? $lastRaiseLuckyItem['lucky_num'] + 1 : 1;

                $luckyItem['member_id'] = $order['member_id'];
                $luckyItem['lucky_status'] = 0;
                $luckyItem['raise_times_id'] = $raise['times_id'];
                $luckyItem['lucky_num'] = $lastLuckyNumber;
                $luckyItem['type'] =  1; // 付费抽奖
                $luckyItem['order_id'] = $order['order_id'];
                M('RaiseLucky')->add($luckyItem);
			}
			//记录订单快照信息
			\Think\Log::write('众筹订单缓存：'.$order['order_id']);
			$this->SaveSnapshotLogs((int)$order['order_id'],3,$this->framework_id());
			return true;
		}
	}

	//支付拼团回调处理
	Private function payPieceCallBack($piece_originator_id){
		$model = M();
		$model->startTrans();//开启事务
		//验证库存
		$order = D('OrderWaresView')->where(['sn' => $this->sn, 'act_status' => 0, 'status' => 1])->find();
		//订单状态不为未支付状态
		if(empty($order)){
			return false;
		}
		$channel = 0;
		if(in_array($order['channel'], [7,8,9]))$channel = 1;
		if($order['act_status'] != 0){
			$model->rollback();//回滚
			return false;
		}
		//查询购买数量
		$num = M('OrderWares')->where(['order_id' => $order['order_id']])->count();

		\Think\Log::write('开团信息-$num：'. json_encode($num));
		//记录商家订单
		if(!empty($this->trade_no))M('OrderPay')->where(['order_id' => $order['order_id'],'type'=>$this->pay_type])->save(['trade_no' => $this->trade_no,'success_pay_time'=>time()]);

		//对比支付的金额
		if($order['price'] > 0 && $this->price != $order['price']){
			//支付金额不准确直接退款并取消订单
			$this->refundOrder($order['order_id'], 2);
			$model->rollback();//回滚
			$this->push_Message($order['member_id'], [], 'SMS_35925149', 'wx|sms|ios', 3,null, $order['order_id'], 0, $channel);
			return false;
		}

		if($order['type'] == 0) {
			$tips = D('TipsView')->where(['times_id' => $order['tips_times_id']])->find();
			\Think\Log::write('开团信息-$tips：'. json_encode($tips));

			if ($tips['limit_time'] == 0) {
				//对活动时间表加上行锁防止超卖
				$times = M('TipsTimes')->where(['id' => $order['tips_times_id']])->lock(true)->find();

				//对比库存是否充足
				if ($num > $times['stock']) {
					//库存不足,直接退款并取消订单
					$this->refundOrder($order['order_id'], 1);
					$model->rollback();//回滚
					$this->push_Message($order['member_id'], [], 'SMS_36075312', 'wx|sms',null, 3, $order['order_id'], 0, $channel);
					return false;
				}

				//判断是否可以包场
				if ($order['is_book']) {
					$book = D('OrderWaresView')->where(['tips_times_id' => $order['tips_times_id'], 'status' => 1, 'act_status' => ['IN', '1,2,3,4']])->count();
					if ($book > 0) {
						$this->refundOrder($order['order_id'], 1);
						$model->rollback();//回滚
						$this->push_Message($order['member_id'], [], 'SMS_36020221', 'wx|sms|ios', null,3, $order['order_id'], 0, $channel);
						return false;
					} else {
						M('TipsTimes')->where(['id' => $order['tips_times_id']])->save(['stock' => 0]);
					}
				} else {
					M('TipsTimes')->where(['id' => $order['tips_times_id']])->setDec('stock', $num);
				}
			}

			M('Order')->where(['sn' => $this->sn])->save(['act_status' => 1, 'status' => 1]);
			\Think\Log::write('开团信息-Order：'. M('Order')->getLastSql());
			//设置拼团状态
			M('member_piece')->where(['id'=>$order['piece_originator_id']])->save(['act_status'=>1]);
			\Think\Log::write('开团信息-OrderPiece：'. M('member_piece')->getLastSql());

			$model->commit();//事务提交

			$w = date('w', $tips['start_time']);
			$week = ['周日', '周一', '周二', '周三', '周四', '周五', '周六'];
			$date = date('Y年m月d日', $tips['start_time']) . " ({$week[$w]}) " . date('H:i', $tips['start_time']);
			$_date = date('H:i', $tips['end_time']);
			if (date('m-d', $tips['start_time']) != date('m-d', $tips['end_time'])) {
				$_date = date('m月d日', $tips['end_time']) . ' ' . $_date;
			}

			//根据不同城市指定客服
			$server = C('WX_SERVICE');
			$citys_id = array_key_exists($tips['city_id'], $server) ? $tips['city_id'] : 224;
			$wx = $server[$citys_id];
			//2016-12-27
			if (in_array($order['channel'], [7, 8, 9])) {
				$params = [
					'title' => $tips['title'],
					'sdate' => $date,
					'edate' => $_date,
					'address' => $tips['address'],
					'platform_member' => 'Host',
					'platform' => '我有饭',
					'wx' => $wx,
				];
			} else {
				$params = [
					'title' => $tips['title'],
					'sdate' => $date,
					'edate' => $_date,
					'address' => $tips['address'],
					'platform_member' => '达人',
					'platform' => '吖咪',
					'wx' => $wx,
				];
			}
			$this->push_Message($order['member_id'], $params, 'SMS_36105291', 'wx|sms|ios',null, 3, $order['order_id'], 0, $channel);

			//发短信给达人
			$member_rs = M('member')->field(['nickname', 'telephone'])->where(['id' => $order['member_id']])->find();

			//2016-12-27
			if(!empty($order['context'])){
				$params = [
					'title' => $tips['title'],
					'datetime' => $date,
					'nickname' => $member_rs['nickname'],
					'telephone' => $member_rs['telephone'],
					'num' => $num,
					'leavemessage' => '用户留言：' . $order['context'] . ',',
					'wx' => $server[224],
				];
			}else{
				$params = [
					'title' => $tips['title'],
					'datetime' => $date,
					'nickname' => $member_rs['nickname'],
					'telephone' => $member_rs['telephone'],
					'num' => $num,
					'leavemessage' => '',
					'wx' => $server[224],
				];
			}
			$this->push_Message($tips['member_id'], $params,'SMS_36385054', 'sms|wx|ios',null, 0, 0, 0, 0);
			$this->SaveSnapshotLogs((int)$order['order_id'],3,$this->framework_id());
			return true;
		}
		elseif($order['type'] == 1){
			//对商品表加上行锁防止超卖
			$goods = M('Goods')->where(['id' => $order['ware_id']])->lock(true)->find();
			if($goods['limit_time'] == 0){
				//对比库存是否充足
				if($num > $goods['stocks']){
					//库存不足,直接退款并取消订单
					$this->refundOrder($order['order_id'], 1);
					$model->rollback();//回滚
					//2013-12-27
					$this->push_Message($order['member_id'], array(), 'SMS_36075312', 'wx|sms|ios',null, 3, $order['order_id'], 0, $channel);
					return false;
				}

				M('Goods')->where(['id' => $order['ware_id']])->setDec('stocks', $num);
			}

			M('Order')->where(['sn' => $this->sn])->save(['act_status' => 1, 'status' => 1]);
			M('member_piece')->where(['id'=>$order['piece_originator_id']])->save(['act_status'=>1]);
			\Think\Log::write('开团信息-OrderPiece：'. M('member_piece')->getLastSql());
			$order_wares = M('order_wares')->where(['order_id'=>$order['id']])->select();
			$count = 0;
			$money = 0;
			foreach($order_wares as $val){
				M('OrderPiece')->add(['order_wares_id'=>$val['id'],'member_piece_id'=>$this->MemberPieceId]);
				$count += 1;
				$money += $val['price'];
			}

			$model->commit();//事务提交

			//2016-12-27
//			$params = [
//				'title' =>$goods['title'],
//				'sn' =>$this->sn,
//			];
//			$this->push_Message($order['member_id'], $params,'SMS_36190100', 'wx|sms|ios', null,3, $order['order_id'], 0, $channel);


			$orderPiece = D('OrderPieceView')->where(['A.id' => $piece_originator_id, 'D.act_status' => ['IN', [1,2,3,4]]])->select();
//            $pieceOrderNum = M('OrderPiece')->where(['piece_originator_id' => $orderPiece['piece_originator_id']])->count();
            $pieceOrderNum = count($orderPiece);

            if ($pieceOrderNum >= $orderPiece[0]['count']) {
                // 拼团成功
                if ($orderPiece[0]['is_cap'] == 1) {
                    // 封顶
                    M('MemberPiece')->where(['id' => $orderPiece[0]['piece_originator_id']])->save(['act_status' => 3]);
                } else {
                    // 不封顶
                    M('MemberPiece')->where(['id' => $orderPiece[0]['piece_originator_id']])->save(['act_status' => 2]);
                }

                $primaryMember = $orderPiece[0]['nickname']; // 团长名
                $count = count($orderPiece);

                $params_piece = [
                    'title' => $goods['title'],
                    'wxtemplate' => [
                        'first' => '您的匹配商品名拼团已成功！',
                        'keyword1' => $goods['title'],
                        'keyword2' => $primaryMember,
                        'keyword3' => $count,
                        'remark' => '您可登录吖咪APP或者吖咪yummy公众号查询订单及物流信息'
                    ],
                    '$url' => 'http://' . DOMAIN . '/'.'?page=orderGoodsDetail&order_id='
                ];


                forEach($orderPiece as $item) {
                    $params_piece['$url'] = $params_piece['url'] . $item['order_id'];
                    if ($item['member_id'] !== $item['order_member_id']) {
                        // 团员
                        $this->push_Message($item['order_member_id'], $params_piece, 'SMS_99590011', 'wx|sms|ios', null, 3, $item['order_id'], 0, $channel);
                    } else {
                        // 团长
                        $this->push_Message($item['order_member_id'], $params_piece, 'SMS_99115059', 'wx|sms|ios', null, 3, $item['order_id'], 0, $channel);
                    }
                }

            } else {
                $orderPiece = D('OrderPieceView')->where(['D.id' => $order['order_id'], 'D.act_status' => ['IN', [1,2,3,4]]])->find();

                // 拼团进行中
                $params_piece = [
                    'title' => $goods['title'],
                    'time' => date('Y-m-d H:i:s', $orderPiece['end_time']),
                    'num' => '还差' . ($orderPiece['count'] - $pieceOrderNum > 0 ? $orderPiece['count'] - $pieceOrderNum : 0) . '人',
                    'wxtemplate' => [
                        'keyword1' => $goods['title'],
                        'keyword2' => $count . '份 | ' . $money . '元',
                        'keyword3' => $orderPiece['nickname'],
                        'keyword4' => '还差' . ($orderPiece['count'] - $pieceOrderNum > 0 ? $orderPiece['count'] - $pieceOrderNum : 0) . '人',
                        'keyword5' => date('Y-m-d h:i', $orderPiece['end_time'])
                    ],
                    '$url' => 'http://' . DOMAIN . '/'.'?page=ucenter-groupsDetail&groups_id=' . $orderPiece['piece_originator_id']
                ];

                if ($orderPiece['member_id'] === $order['member_id']) {
                    // 团长
                    $params_piece['wxtemplate']['first'] = '恭喜您完成支付，开团成功！';
                    $params_piece['wxtemplate']['remark'] = '快点击进入拼单页面，分享给好友一起拼团吧！>>';
                    $this->push_Message($order['member_id'], $params_piece, 'SMS_99240068', 'wx|sms|ios', null, 3, $order['order_id'], 0, $channel);
                } else {
                    $pieceOrderNum['num'] = $orderPiece['count'] - $pieceOrderNum > 0 ? $orderPiece['count'] - $pieceOrderNum : 0;
                    $params_piece['wxtemplate']['first'] = '恭喜您完成支付，参团成功！';
                    $params_piece['wxtemplate']['remark'] = '点此邀请好友一起拼团，更快成团哦！>>';
                    $this->push_Message($order['member_id'], $params_piece, 'SMS_99245077', 'wx|sms|ios', null, 3, $order['order_id'], 0, $channel);
                }
            }

            //记录订单快照信息
			\Think\Log::write('商品订单缓存：'.$order['order_id']);
			$this->SaveSnapshotLogs((int)$order['order_id'],3,$this->framework_id());

			return true;
		}
		elseif($order['type'] == 2){

			$raise = D('RaiseView')->where(['times_id' => $order['tips_times_id']])->find();

			M('Order')->where(['sn' => $this->sn])->save(['act_status' => 1, 'status' => 1]);

			$order_wares = M('order_wares')->where(['order_id'=>$order['id']])->select();
			foreach($order_wares as $val){
				M('OrderPiece')->add(['order_wares_id'=>$val['id'],'member_piece_id'=>$this->MemberPieceId]);
			}
			$model->commit();//事务提交

			$rs = D('RaiseOrderWaresView')->where(['A.type' => 2, 'A.ware_id' => $order['ware_id'], 'B.status' => 1, 'B.act_status' => ['in', '1,2,3,4']])->group('A.id')->select();
			$_rs['totaled'] = $_rs['count'] = 0;
			foreach($rs as $row){
				$_rs['totaled'] += $row['raise_times_price'];
				$_rs['count'] ++;
			}
			$_rs['percent'] = sprintf("%01.1f", ($_rs['totaled']/$raise['total'])*100).'%';
			//2016-12-27
			if($raise['prepay']>0){
				if(!empty($order['order_pid'])){//二次支付
                    $params = [
                        'name' =>$raise['nickname'],
                        'title' => '《' . $raise['title'] . '》”' . $raise['times_title'] . '”',
                        'money' => $order['price'],
                    ];
					$this->push_Message($order['member_id'], $params,'SMS_84655050', 'wx|sms|ios',null, 3, $order['order_id'],0,0);
				}else{//预付款
//					$context = "感谢您的支持！我是尹江波，已收到您支持我发起的众筹项目《始于1880年的传奇茶楼 首次众筹》共{$order['price']}元。请进入吖咪公众号或APP查看详情 (风险提示：众筹期间请积极关注项目进展，您可以在项目评论区和吖咪发起的微信群组联系到发起人，请勿加入非项目发起人以及非吖咪官方创建的微信群，谨防受骗。)客服微信：yami194（工作时间：9:00-20:00）有问题随时保持联络！";
                    $params = [
                        'name' =>$raise['nickname'],
                        'project_name' => '众筹',
                        'title' => '《' . $raise['title'] . '》',
                        'money' => $order['price'] . '元',
                        'times' => $raise['times_title'],
                    ];
                    $params2 = [
                        'name' => $raise['nickname'],
                        'title' => $raise['title'],
                        'time' => $raise['times_title']
                    ];
					$this->push_Message($order['member_id'], $params,'SMS_85395035', 'wx|sms|ios',null, 3, $order['order_id'],0,0);
                    $this->push_Message($order['member_id'], $params2, 'WEIXIN_20180731', 'wx', null, 3, $order['order_id'], 0, 0);
                }
			}else{//全额支付
				$params = [
					'name' =>$raise['nickname'],
					'project_name' => '众筹',
					'title' => '《' . $raise['title'] . '》',
					'money' => $order['price'] . '元',
					'times' => $raise['times_title'],
				];
				$this->push_Message($order['member_id'], $params,'SMS_85395035', 'wx|sms|ios',null, 3, $order['order_id'],0,0);
			}

			$member = M('Member')->where(['id'=>$order['member_id']])->find();
			//2016-12-27
			if($order['price'] < 10){
				$params = [
					'project'=>'众筹',
					'title'=>$raise['title'],
					'price'=>(string)$order['price'],
					'nickname'=>$member['nickname'],
					'telephone'=>(string)$member['telephone'],
				];
				if(isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'm.yami.ren') === false) {
//					$this->push_Message(12466, $params, 'SMS_36355042', 'wx|sms|ios', null, 0, 0, 0, 0);//季总
					$this->push_Message(13063, $params, 'SMS_36355042', 'wx|sms|ios', null, 0, 0, 0, 0);//amy
					if($raise['id'] == 41){
						$this->push_Message(265671, $params, 'SMS_36355042', 'wx|sms|ios', null, 0, 0, 0, 0);//众筹：广州最牛的创业咖啡，下一个撞见张小龙的就是你
					}
				}else{
					$this->push_Message($raise['member_id'], $params,'SMS_36355042', 'wx|sms|ios',null, 0, 0, 0, 0);
				}

			}else {
				$params = [
					'project'=>'众筹',
					'title'=> $raise['title'],
					'price'=>(string)$order['price'],
					'nickname'=>$member['nickname'],
					'telephone'=>(string)$member['telephone'],
					'wx'=>'yami194'
				];

				if(isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'm.yami.ren') === false){
//					$this->push_Message(12466, $params, 'SMS_55720155', 'wx|sms|ios', null, 0, 0, 0, 0);//季总
					$this->push_Message(13063, $params, 'SMS_55720155', 'wx|sms|ios', null, 0, 0, 0, 0);//amy
					if($raise['id'] == 41){
						$this->push_Message(265671, $params, 'SMS_55720155', 'wx|sms|ios', null, 0, 0, 0, 0);//众筹：广州最牛的创业咖啡，下一个撞见张小龙的就是你
					}
				}else{
					$this->push_Message($raise['member_id'], $params,'SMS_55720155', 'wx|sms|ios',null, 0, 0, 0, 0);
				}
			}

			//记录订单快照信息
			\Think\Log::write('众筹订单缓存：'.$order['order_id']);
			$this->SaveSnapshotLogs((int)$order['order_id'],3,$this->framework_id());
			return true;
		}
	}

	//退款回调处理
	Private function refundCallBack($order_id){
		$result = M('OrderRefund')->where(['order_id' => $order_id, 'is_allow' => 0])->find();
		//更新退款表
		if(!empty($result)){
			$data = [
				'id' => $result['id'],
				'is_allow' => 1
			];
			M('order_refund')->data($data)->save();
			//更新订单表
			$data = [
				'id' => $order_id,
				'act_status' => 6
			];
			M('order')->data($data)->save();
			$member_coupon_id = M('order')->where(['id'=>$order_id])->getField('member_coupon_id');
			M('MemberCoupon')->data(['id'=>$member_coupon_id,'used_time'=>0])->save();
		}else{
			//更新订单表
			$data = [
				'id' => $order_id,
				'act_status' => 1
			];
			M('order')->data($data)->save();
		}
		//记录订单快照信息
		$this->SaveSnapshotLogs((int)$order_id,3,$this->framework_id());
	}

}