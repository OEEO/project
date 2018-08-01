<?php
namespace Order\Model;
use Think\Model\ViewModel;

class OrderWaresCountViewModel extends ViewModel {

	public $viewFields = [
		'A' => [
			'id', 'type', 'ware_id', 'count(tips_times_id)' => 'count', 'tips_times_id', 'datetime', 'price',
			'_table' => '__ORDER_WARES__',
			'_type' => 'LEFT'
		],
		'B' => [
			'member_id','act_status','status','order_pid',
			'_on' => 'A.order_id=B.id',
			'_table' => '__ORDER__',
            '_type'=>'LEFT'
		]
	];

}
