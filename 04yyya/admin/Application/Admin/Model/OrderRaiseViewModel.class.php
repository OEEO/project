<?php
namespace Admin\Model;
use Think\Model\ViewModel;

class OrderRaiseViewModel extends ViewModel {
	protected $connection = 'DB2';
	protected $tablePrefix = 'ym_';


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
			'id','type','ware_id', 'price','tips_times_id',
            '_on' => 'B.order_id=A.id',
			'_table' => '__ORDER_WARES__',
			'_type' => 'LEFT'
		],
        'D' => [
            'title','end_time',
            '_on'=>' D.id=B.ware_id',
            '_table'=>'__RAISE__',
			'_type' => 'LEFT'
        ],
        'E' => [
            'title','end_time',
            '_on'=>' D.id=B.ware_id',
            '_table'=>'__RAISE__',
			'_type' => 'LEFT'
        ]
	];

}
