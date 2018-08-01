<?php
namespace Home\Model;
use Think\Model\ViewModel;

class FeedbackViewModel extends ViewModel {

	public $viewFields = [
		'A'=> [
			'id', 'member_id', 'type', 'type_id', 'content', 'answer', 'answer_member_id','datetime',
			'_table'=>'__FEEDBACK__'
		],
		'B' => [
			'nickname',
			'_on' => 'A.member_id=B.id',
			'_table' => '__MEMBER__',
			'_type' => 'left'
		],
		'C' => [
			'path',
			'_on' => 'B.pic_id=C.id',
			'_table' => '__PICS__',
			'_type' => 'left'
		],
		'D' => [
			'nickname'=>'answer_nickname',
			'_on' => 'A.answer_member_id=D.id',
			'_table' => '__MEMBER__'
		]
	];


}
