<?php
namespace Doc\Model;
use Think\Model\ViewModel;

class TipsViewModel extends ViewModel {

	public $viewFields = [
        'A' => [
            'id','title','member_id',
            '_table'=>'__TIPS__'
        ],
		'B' => [
			'city_id','address',
			'_on' => 'A.space_id=B.id',
			'_table' => '__SPACE__',
			'_type' => 'LEFT'
		],
		'C' => [
			'id' => 'times_id','stop_buy_time','start_buy_time','min_num','is_finish','start_time',
			'_on' => 'A.id=C.tips_id',
			'_table' => '__TIPS_TIMES__',
			'_type' => 'LEFT'
		],
		'D' => [
			'pid' => 'citys_id',
			'_on' => 'B.city_id=D.id',
			'_table' => '__CITYS__',
			'_type' => 'LEFT'
		]
	];

}
