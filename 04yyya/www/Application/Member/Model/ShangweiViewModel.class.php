<?php
namespace Member\Model;
use Think\Model\ViewModel;

class ShangweiViewModel extends ViewModel {

	public $viewFields = [
		'A' => [
			'id','member_id',
			'_table' => '__TIPS__'
		],
		'B' => [
			'count(*)' => 'count',
			'_on' => 'B.type=0 and A.id=B.ware_id',
			'_table' => '__ORDER_WARES__'
		],
		'C' => [
			'act_status','status',
			'_on' => 'B.order_id=C.id',
			'_table' => '__ORDER__'
		]
	];

}
