<?php
namespace Member\Model;
use Think\Model\ViewModel;

class OrderLogisticsViewModel extends ViewModel {

	public $viewFields = [
		'A'=> [
			'id', 'sn', 'member_id', 'act_status',
			'_table'=>'__ORDER__'
		],
		'B' => [
			'number',
			'_on' => 'A.id=B.order_id',
			'_table' => '__ORDER_LOGISTICS__'
		],
		'C' => [
			'name', 'key' => 'logkey',
			'_on' => 'B.logistics_id=C.id',
			'_table' => '__LOGISTICS__'
		],
		'D' => [
			'_on' => 'A.id=D.order_id',
			'_table' => '__ORDER_WARES__'
		],
		'E' => [
			'_on' => 'D.ware_id=E.id',
			'_table' => '__GOODS__'
		],
		'F' => [
			'path',
			'_on' => 'E.pic_id=F.id',
			'_table' => '__PICS__'
		]
	];


}
