<?php
namespace Home\Model;
use Think\Model\ViewModel;

class RaiseViewModel extends ViewModel {

	public $viewFields = [
		'A' => [
			'id','title','introduction','start_time','end_time',
			'_table' => '__RAISE__',
			'_type' => 'LEFT'
		],
		'B' => [
			'nickname',
			'_on' => 'A.member_id=B.id',
			'_table' => '__MEMBER__',
			'_type' => 'LEFT'
		],
		'C' => [
			'path',
			'_on' => 'A.pic_id=C.id',
			'_table' => '__PICS__',
			'_type' => 'LEFT'
		],
	];

}
