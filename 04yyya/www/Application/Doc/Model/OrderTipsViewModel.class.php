<?php
namespace Doc\Model;
use Think\Model\ViewModel;

class OrderTipsViewModel extends ViewModel {

	public $viewFields = [
        'A' => [
            'id'=>'order_id','sn',
            'member_id',
            'act_status',
            'status','price' => 'total',
			'channel',
            '_table' => '__ORDER__',
			'_type' => 'LEFT'
		],
		'B' => [
			'id','type','ware_id', 'price',
            '_on' => 'B.order_id=A.id',
			'_table' => '__ORDER_WARES__',
			'_type' => 'LEFT'
		],
        'C' => [
            'id'=>'tips_times_id',
            'end_time','stop_buy_time','min_num',
            '_on'=>'B.tips_times_id=C.id',
            '_table'=>'__TIPS_TIMES__',
			'_type' => 'LEFT'
		],
        'D' => [
            'title',
            '_on'=>'B.type=0 and B.ware_id=D.id',
            '_table'=>'__TIPS__'
        ]
	];

}
