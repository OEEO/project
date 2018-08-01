<?php
namespace Order\Model;
use Think\Model\ViewModel;

class MemberCouponViewModel extends ViewModel {

	public $viewFields = [
		'A' => [
			'id','member_id','sn','used_time',
			'_table' => '__MEMBER_COUPON__'
		],
		'B' => [
			'category','name','type','value','content','start_time','end_time','min_amount','status','member_tags','tips_tags','goods_tags',
			'_on' => 'A.coupon_id=B.id',
			'_table' => '__COUPON__'
		]
	];

}
