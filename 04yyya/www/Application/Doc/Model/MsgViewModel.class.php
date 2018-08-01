<?php
namespace Doc\Model;
use Think\Model\ViewModel;

class MsgViewModel extends ViewModel {
	
	public $viewFields = [
		'A'=> ['id' => 'message_id', 'content', 
			'sms_send','ios_push','params','code_type', 'isMass', 'sendtime', 
			'type', 'type_id',
			'_table'=>'__MESSAGE__'
		],
		'B'=> [
			'id', 'is_sms', 'member_id','is_ios_push',
			'_on'=>'A.id=B.message_id and sms_send>is_sms',
			'_table'=>'__MEMBER_MESSAGE__'
		],
		'C' => [
			'telephone',
			'_on' => 'B.member_id=C.id',
			'_table' => '__MEMBER__'
		]
	];
	
}