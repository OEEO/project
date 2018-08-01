<?php
namespace Member\Model;
use Think\Model\ViewModel;

class MemberCouponViewModel extends ViewModel {

	public $viewFields = array(
		'A' => array(
			'id','member_id','sn','used_time','coupon_id','datetime',
			'_table' => '__MEMBER_COUPON__'
		),
		'B' => array(
			'category','name','type','value','content','start_time','end_time','min_amount','status','member_tags','tips_tags','goods_tags','limit_time',
			'_on' => 'A.coupon_id=B.id',
			'_table' => '__COUPON__'
		)
	);

}
