<?php
namespace Doc\Model;
use Think\Model\ViewModel;

class RaiseViewModel extends ViewModel {

	public $viewFields = [
        'A' => [
            'id','title',
            '_table'=>'__RAISE__'
        ],
		'B' => [
			'id' => 'times_id',
			'_on' => 'A.id=B.raise_id',
			'_table' => '__RAISE_TIMES__',
			'_type' => 'LEFT'
		],
		'C' => [
			'nickname',
			'_on' => 'C.id=A.member_id',
			'_table' => '__MEMBER__',
			'_type' => 'LEFT'
		],
	];

}
