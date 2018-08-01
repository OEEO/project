<?php
namespace Order\Model;
use Think\Model\ViewModel;

class OrderWaresViewModel extends ViewModel {

	public $viewFields = [
		'A' => [
			'id','order_id','type','ware_id','datetime','tips_times_id',
			'_table' => '__ORDER_WARES__',
		],
		'B' => [
			'sn','price','act_status','status','create_time','member_coupon_id','member_id','is_book','channel','order_pid','context','limit_pay_time',
			'_on' => 'A.order_id=B.id',
			'_table' => '__ORDER__',
			'_type' => 'LEFT'
		],
		'C' => [
			'start_time','end_time','stock',
			'_on' => 'A.tips_times_id=C.id',
			'_table' => '__TIPS_TIMES__',
			'_type' => 'LEFT'
		],
		'D' => [
			'type' => 'pay_type', 'context' => 'pay_context', 'trade_no',
			'_on' => 'A.order_id=D.order_id',
			'_table' => '__ORDER_PAY__',
			'_type' => 'LEFT'
		],
		'E' => [
			'piece_originator_id',
			'_on' => 'B.id=E.order_id',
            '_type' => 'LEFT',
			'_table' => '__ORDER_PIECE__'
		]
	];

}
