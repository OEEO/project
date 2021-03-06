<?php
namespace Goods\Model;
use Think\Model\ViewModel;

class OrderViewModel extends ViewModel {

	public $viewFields = [
		'A' => [
            'id', 'type', 'ware_id', 'tips_times_id', 'datetime', 'price',
            // 'sum(A.price)' => 'total_selled',
            // 'count(*)' => 'total_count',
			'_table' => '__ORDER_WARES__',
			'_type' => 'LEFT'
		],
		'B' => [
			'member_id','act_status','status',
			'_on' => 'A.order_id=B.id',
			'_table' => '__ORDER__',
            '_type'=>'LEFT'
		],
        'C' => [
            // 'nickname',
            // 'pic_id',
            '_on' => 'B.member_id=C.id',
            '_table' => '__MEMBER__',
            '_type'=>'LEFT'
		],
        'D' => [
            // 'path',
            '_on' => 'D.id=C.pic_id',
            '_table' => '__PICS__'
		]
	];

}
