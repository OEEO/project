<?php
namespace Daren\Model;
use Think\Model\ViewModel;

class OrderViewModel extends ViewModel {

	public $viewFields = [
		'A' => [
			'id','sn','create_time','price','context','act_status','member_id','member_coupon_id','channel',
			'_table' => '__ORDER__',
		],
		'B' => [
			'tips_times_id','type','ware_id',
			'_on' => 'A.id=B.order_id',
			'_table' => '__ORDER_WARES__',
			'_type' => 'LEFT'
		],
		'C' => [
			'start_time',
			'end_time',
			'phase','stop_buy_time',
			'_on'=>'B.tips_times_id=C.id',
			'_table'=>'__TIPS_TIMES__',
			'_type' => 'LEFT'
		],
		'D' => [
			'sn' => 'coupon_sn',
			'_on' => 'A.member_coupon_id=D.id',
			'_table' => '__MEMBER_COUPON__',
			'_type' => 'LEFT'
		],
		'E' => [
			'name','member_id'=>'coupon_member_id','type','value',
			'_on' => 'D.coupon_id=E.id',
			'_table' => '__COUPON__'
		],
		'F' => [
			'nickname','telephone',
			'_on' => 'A.member_id=F.id',
			'_table' => '__MEMBER__'
		],
		'G' => [
			'title','member_id' => 'tips_member_id',
			'_on' => 'B.ware_id=G.id',
			'_table' => '__TIPS__'
		],
		'H' => [
			'name' => 'catname',
			'_on' => 'G.category_id=H.id',
			'_table' => '__CATEGORY__'
		]
	];
}


