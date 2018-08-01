<?php
namespace Order\Model;
use Think\Model\ViewModel;

class RaiseOrderWaresViewModel extends ViewModel {

	public $viewFields = [
		'A' => [
			'order_id',
			'ware_id'=>'raise_id',
			'tips_times_id',
			'_table' => '__ORDER_WARES__',
			'_type' => 'LEFT'
		],
		'B' => [
			'price','order_pid',
			'_on' => 'A.order_id=B.id',
			'_table' => '__ORDER__',
			'_type' => 'LEFT'
		],
		'C' => [
			'title'=>'raise_title',
			'_on' => 'A.ware_id=C.id',
			'_table' => '__RAISE__',
			'_type' => 'LEFT'
		],
		'E' => [
			'title'=>'raise_times_title',
			'price'=>'raise_times_price',
			'prepay'=>'raise_times_prepay',
			'_on' => 'A.tips_times_id=E.id',
			'_table' => '__RAISE_TIMES__',
			'_type' => 'LEFT'
		],
	];

}
