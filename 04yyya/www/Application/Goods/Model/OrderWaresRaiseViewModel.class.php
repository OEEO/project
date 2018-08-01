<?php
namespace Goods\Model;
use Think\Model\ViewModel;

class OrderWaresRaiseViewModel extends ViewModel {

	public $viewFields = [
		'A' => [
			'id','order_id','type','ware_id','datetime','tips_times_id',
			'_table' => '__ORDER_WARES__',
		],
		'B' => [
			'sn','price','act_status','order_pid','status','create_time','member_coupon_id','member_id','is_book','channel',
			'_on' => 'A.order_id=B.id',
			'_table' => '__ORDER__',
			'_type' => 'LEFT'
		],
		'C' => [
			'start_time','end_time',
			'_on' => 'A.ware_id=C.id',
			'_table' => '__RAISE__',
			'_type' => 'LEFT'
		],
		'D' => [
			'type' => 'pay_type', 'context' => 'pay_context', 'trade_no',
			'_on' => 'A.order_id=D.order_id',
			'_table' => '__ORDER_PAY__',
			'_type' => 'LEFT'
		],
		'E' => [
			'stock','quota',
			'price'=>'raise_times_price',
			'prepay'=>'raise_times_prepay',
			'_on' => 'A.tips_times_id=E.id',
			'_table' => '__RAISE_TIMES__',
			'_type' => 'LEFT'
		],
	];

}
