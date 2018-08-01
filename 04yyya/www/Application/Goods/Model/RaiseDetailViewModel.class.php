<?php
namespace Goods\Model;
use Think\Model\ViewModel;

class RaiseDetailViewModel extends ViewModel {

	public $viewFields = [
		'A' => [
			'id','start_time', 'end_time','title',
			'_table' => '__RAISE__',
			'_type' => 'LEFT'
		],

		'B' => [
			'id'=>'raise_times_id',
			'title'=>'raise_title',
			'price'=>'raise_times_price',
			'prepay'=>'raise_times_prepay',
			'stock'=>'raise_times_stock',
			'quota'=>'raise_times_quota',
			'content'=>'raise_times_content',
			'is_address','is_realname','type',
			'_on' => 'A.id=B.raise_id',
			'_table' => '__RAISE_TIMES__',
		]
	];

}
