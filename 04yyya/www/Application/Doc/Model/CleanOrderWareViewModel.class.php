<?php
namespace Doc\Model;
use Think\Model\ViewModel;

class CleanOrderWareViewModel extends ViewModel {

	public $viewFields = [
        'A' => [
            'id'=>'order_id',
            'act_status',
            'status',
            '_table' => '__ORDER__'
		],
		'B' => [
			'id','type','ware_id', 'price',
            '_on' => 'B.order_id=A.id',
			'_table' => '__ORDER_WARES__'
		],
        'C' => [
            'id'=>'tips_times_id',
            'end_time',
            'start_time',
            'start_buy_time',
            'stop_buy_time',
            '_on'=>'B.tips_times_id=C.id',
            '_table'=>'__TIPS_TIMES__'
		]
	];

}
