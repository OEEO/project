<?php
namespace Home\Model;
use Think\Model\ViewModel;

class OverTipsViewModel extends ViewModel {

	public $viewFields = [
		'A' => [
			'id',
			'_table' => '__TIPS__',
			'_type' => 'LEFT'
		],

		'B' => [
			'end_time',
			'_on' => 'A.id=B.tips_id',
			'_table' => '__TIPS_TIMES__',
			'_type' => 'LEFT'
		],

		'C' => [
			'is_public',
			'_on' => 'A.id=C.tips_id',
			'_table' => '__TIPS_SUB__'
		]

	];

}
