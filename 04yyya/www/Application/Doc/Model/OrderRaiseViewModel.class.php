<?php
namespace Doc\Model;
use Think\Model\ViewModel;

class OrderRaiseViewModel extends ViewModel {

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
            '_on'=>'B.tips_times_id=C.id',
            '_table'=>'__RAISE_TIMES__',
			'_type' => 'LEFT'
		],
        'D' => [
            'title','end_time',
            '_on'=>'B.ware_id=D.id',
            '_table'=>'__RAISE__',
			'_type' => 'LEFT'
        ],
        'E' => [
            'nickname'=>'host_nickname',
            '_on'=>'E.id=D.member_id',
            '_table'=>'__MEMBER__'
        ]
	];

}
