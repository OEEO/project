<?php
namespace Doc\Model;
use Think\Model\ViewModel;

class MsgIOSViewModel extends ViewModel {
	
	public $viewFields = [
		'A'=> ['id' => 'message_id', 'content', 'sms_send','ios_push','params','code_type', 'isMass', 'sendtime', '_table'=>'__MESSAGE__'],
		'B'=> [
			'id', 'is_sms', 'member_id','is_ios_push',
			'_on'=>'A.id=B.message_id and ios_push>is_ios_push ',
			'_table'=>'__MEMBER_MESSAGE__'
		],
		'C' => [
			'telephone',
			'_on' => 'B.member_id=C.id',
			'_table' => '__MEMBER__'
		]
	];
	
}