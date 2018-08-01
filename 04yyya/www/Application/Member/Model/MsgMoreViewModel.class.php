<?php
namespace Member\Model;
use Think\Model\ViewModel;

class MsgMoreViewModel extends ViewModel {

	public $viewFields = [
		'A'=> [
			'id', 'member_id', 'is_read','datetime',
			'_table' => '__MEMBER_MESSAGE__',
            '_type' => 'LEFT'
		],
		'B' => [
            'type'=>'message_type','member_id' => 'origin_id', 'content', 'count(B.member_id)' => 'count',
			'_on' => 'A.message_id=B.id',
			'_table' => '__MESSAGE__',
			'_type' => 'LEFT'
		],
		'C' => [
			'nickname',
			'_on' => 'B.member_id=C.id',
			'_table' => '__MEMBER__',
			'_type' => 'LEFT'
		],
		'D' => [
			'path',
			'_on' => 'C.pic_id=D.id',
			'_table' => '__PICS__'
		]
	];


}
