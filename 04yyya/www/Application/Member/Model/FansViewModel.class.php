<?php
namespace Member\Model;
use Think\Model\ViewModel;

class FansViewModel extends ViewModel {

	public $viewFields = [
		'A' => [
            'member_id',
			'_table' => '__MEMBER_FOLLOW__',
            '_type' => 'LEFT'
		],
		'B' => [
			'nickname'=>'fans_nickname',
			'_on' => 'A.member_id=B.id',
			'_table' => '__MEMBER__',
            '_type' => 'LEFT'
		],
		'C' => [
            'path',
			'_on' => 'B.pic_id=C.id',
			'_table' => '__PICS__',
			'_type' => 'LEFT'
		]
	];

}
