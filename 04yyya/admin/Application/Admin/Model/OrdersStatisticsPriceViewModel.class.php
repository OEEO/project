<?php
namespace Admin\Model;
use Think\Model\ViewModel;

class OrdersStatisticsPriceViewModel extends ViewModel {
	
	protected $connection = 'DB2';
	protected $tablePrefix = 'ym_'; 
	
	public $viewFields = [
		'A'=> [ 'FROM_UNIXTIME(create_time, "%Y-%m-%d")'=> 'create_time' ,'price','channel', '_table'=>'__ORDER__','_type'=>'LEFT'],
        'B'=> [
			"type" =>"order_type",
			'price'=>'act_wares_price',
            '_on'=>'B.order_id=A.id',
            '_table'=>'__ORDER_WARES__',
            '_type'=>'LEFT'
		],
		'C'=> [
            'sn' => 'member_coupon_sn',
            '_on'=>'A.member_coupon_id=C.id',
            '_table'=>'__MEMBER_COUPON__',
            '_type'=>'LEFT'
        ],
        'D'=> [
            'type'=>'coupon_type',
            'value'=>'coupon_value',
            '_on'=>'C.coupon_id=D.id',
            '_table'=>'__COUPON__',
            '_type'=>'LEFT'
        ],
		'E'=> [
			'_on'=>'B.ware_id=E.id AND B.type = 0',
			'_table'=>'__TIPS__',
			'_type'=>'LEFT'
		],
		'F'=> [
			'_on'=>'B.ware_id=F.id AND B.type = 1',
			'_table'=>'__GOODS__',
			'_type'=>'LEFT'
		],
		'G'=> [
			'_on'=>'B.ware_id=G.id AND B.type = 2',
			'_table'=>'__RAISE__',
			'_type'=>'LEFT'
		]
	];
	
}
