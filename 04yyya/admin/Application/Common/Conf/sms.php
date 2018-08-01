<?php
return [
	'DX_SMS' => [
		//活动公开（后台）
		'SMS_36360302' => [
			'content' => '您的活动公开申请已通过，请留意下单情况，做好准备哦~',
			'params' => []
		],
		//申请活动公开不成功发送通知用户（后台）
		'SMS_36240319' => [
			'content' => '很抱歉，吖咪酱认真审核了您的活动，发现个别信息还需完善，你可在活动管理中修改后再来申请哦！(审核未通过原因：${select_rs}${reason})',
			'params' => ['select_rs','reason']
		],
		//消费验证短信通知（后台）
		'SMS_36270252' => [
			'content' => '您参与的『${title}』已经结束，现场气氛如何？${host_1}手艺棒不棒？和${host_2}互动愉快吗？快来给${host_3}评分吧！',
			'params' => ['title','host_1','host_2','host_3']
		],
		//发货信息通知用户（后台）
		'SMS_36285323' => [
			'content' => '小主购买的“${goods_title}”已于${datetime}使用${logistics_name}发货，快递单号为${order_logistics_number}，喜欢请在收货之后点亮五颗星奖励我，评价有福利哦！',
			'params' => ['goods_title','datetime','logistics_name','order_logistics_number']
		],
		//项目结束后发送短信给购买的用户(后台)
		'SMS_36305199' => [
			'content' => '您支持的${project_name}项目《${title}》认筹金额已达成目标，项目${project_name_1}成功。谢谢您的支持，也希望您能把这个项目告诉更多人。客服微信：${wx}（工作时间：9:00-18:00）有问题随时保持联络！',
			'params' => ['project_name','title','project_name_1','wx']
		],
		//审核活动失败发送短信通知达人（后台）
		'SMS_37025113' => [
			'content' => '很抱歉，吖咪酱认真审核了您的活动『${title}』，发现个别信息还需完善，你可在活动管理中修改后再来申请哦！（审核未通过原因：${select_reason}）',
			'params' => ['title','select_reason']
		],
		//未成局退款信息通知给购买用户（后台）
		'SMS_36185354' => [
			'content' => '非常抱歉通知您，你购买的“${tips_title}”，由于未达到成局人数，默认不成局，会在3个工作日内给您退款！',
			'params' => ['tips_title']
		],
		//活动结算金额方式_1（后台）
		'SMS_36875001' => [
			'content' => '您所发布的活动『${title}』(${start_time})已有${num}位用户参与，菜金合计${price_num}元，已为您账号为${number}的${name} 存入${money}元，请笑纳。如有疑问请咨询小助理!',
			'params' => ['title','start_time','num','price_num','number','name','money']
		],
		//商品结算金额方式_1（后台）
		'SMS_36855001' => [
			'content' => '您所发布的活动『${title}』(${start_time})已有${num}位用户参与，菜金合计${price_num}元，已为您账号为${number}的${name} 存入${money}元，请笑纳。如有疑问请咨询小助理!',
			'params' => ['title','start_time','num','price_num','number','name','money']
		],
		//活动结算金额方式_2（后台）
		'SMS_36500034' => [
			'content' => '您所发布的活动『${title}』(${start_time})已有${num}位用户参与，菜金合计${price_num}元，已存入您尾号${number}的${name}卡${money}元，请笑纳。如有疑问请咨询小助理!',
			'params' => ['title','start_time','num','price_num','number','name','money']
		],
		//商品结算金额方式_2（后台）
		'SMS_36570037' => [
			'content' => '您所发布的商品『${title}』已有${num}份被卖出，金额合计"${price_num}元，已存入您尾号${number}的${name}卡${money}元，请笑纳。如有疑问请咨询小助理!',
			'params' => ['title','num','price_num','number','name','money']
		],
		//二次订单生成邀请支付（后台）
		'SMS_39710001' => [
			'content' => '恭喜您成功入选${project_name}项目《${title}》的共建人。请进入吖咪公众号或APP查看详情，并于${limit_day}前补齐余额（过期视为自动放弃，名额将释放给其他候选人），谢谢您的支持！客服微信：${wx}（工作时间：9:00-18:00）有问题随时保持联络！',
			'params' => ['project_name','title','limit_day','wx']
		],
		//给未筛选成功的用户发送短信（后台）
		'SMS_56100067' => [
			'content' => '您支持的${project_name}项目《${project_title}》很遗憾通知您，您未能通过共建人筛选，我们将于${limit_day}个工作日内将预约金退回到您的支付账户。新一期项目即将启动，敬请期待。客服微信：${wx}（工作时间：9:00-18:00）有问题随时保持联络！',
			'params' => ['project_name','project_title','limit_day','wx']
		],
	]
];