<?php
namespace Admin\Model;
use Think\Model\ViewModel;

class OrdersStatisticsViewModel extends ViewModel {
	
	protected $connection = 'DB2';
	protected $tablePrefix = 'ym_'; 
	
	public $viewFields = [
		'A'=> [ 'FROM_UNIXTIME(create_time, "%Y-%m-%d")'=> 'create_time' , '_table'=>'__ORDER__','_type'=>'LEFT'],
        'B'=> [
			"count(ware_id)" =>"count_id",
            '_on'=>'B.order_id=A.id',
            '_table'=>'__ORDER_WARES__',
            '_type'=>'LEFT',
		],
        'C'=> [
            '_on'=>'B.ware_id=C.id',
            '_table'=>'__TIPS__',
            '_type'=>'LEFT'
		],
        'D'=> [
            '_on'=>'B.ware_id=D.id',
            '_table'=>'__GOODS__',
            '_type'=>'LEFT'
		],
        'E'=> [
            '_on'=>'B.ware_id=E.id',
            '_table'=>'__RAISE__',
            '_type'=>'LEFT'
		]
	];
	
}
