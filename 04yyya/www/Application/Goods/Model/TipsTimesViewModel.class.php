<?php
namespace Goods\Model;
use Think\Model\ViewModel;

class TipsTimesViewModel extends ViewModel {

	public $viewFields = array(
		'A' => array(
			'count(tips_times_id)' => 'count',
			'_table' => '__ORDER_WARES__',
			'_type' => 'RIGHT'
		),
		'B' => array(
			'start_time', 'end_time',
			'_on' => 'B.id=A.tips_times_id',
			'_table' => '__TIPS_TIMES__',
		)
	);

}
