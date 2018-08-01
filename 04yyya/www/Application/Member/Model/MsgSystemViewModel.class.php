<?php
namespace Member\Model;
use Think\Model\ViewModel;

class MsgSystemViewModel extends ViewModel {

	public $viewFields = [
		'A' => [
			'id'=>'message_id','member_id' => 'origin_id', 'content', 'type', 'isMass','datetime',
			'_table' => '__MESSAGE__',
			'_type' => 'LEFT'
		],
		'B'=> [
			'id', 'member_id','is_read','datetime',
			'_on' => 'B.message_id=A.id',
			'_table' => '__MEMBER_MESSAGE__'
		]
	];


}
