<?php
namespace Daren\Model;
use Think\Model\ViewModel;

class TimesViewModel extends ViewModel {

	public $viewFields = [
		'A'=> [
			'type', 'ware_id', 'server_status','tips_times_id',
			'count(tips_times_id)' => 'count',
			'_table' => '__ORDER_WARES__'
		],
		'B'=> [
			'start_time',
			'end_time',
			'_on'=>'A.tips_times_id=B.id',
			'_table'=>'__TIPS_TIMES__',
			'_type' => 'LEFT',
		]
	];
}


