<?php
namespace Goods\Model;
use Think\Model\ViewModel;

class RaiseOrderDeatilViewModel extends ViewModel {

	public $viewFields = [
		'A' => [
			'id','title','start_time', 'end_time',
			'_table' => '__RAISE__',
			'_type' => 'LEFT'
		],
		'B' => [
			'id'=>'raise_times_id',
			'price'=>'raise_times_price',
			'prepay'=>'raise_times_prepay',
			'stock'=>'raise_times_stock',
			'quota'=>'raise_times_quota',
			'_on' => 'A.id=B.raise_id',
			'_table' => '__RAISE_TIMES__',
		],
		'C' => [
			'_on' => 'C.tips_times_id=B.id',
			'_table' => '_ORDER_WARES__',
		],
		'D' => [
			'order_pid',
			'_on' => 'D.id=C.order_id',
			'_table' => '_ORDER__',
		],
	];

}
