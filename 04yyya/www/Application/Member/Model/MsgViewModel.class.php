<?php
namespace Member\Model;
use Think\Model\ViewModel;

class MsgViewModel extends ViewModel {

	public $viewFields = [
		'A' => [
			'id' => 'message_id', 'member_id' => 'origin_id', 'content', 'type', 'sms_send','wx_send','sendtime','type_id','isMass','code_type','datetime' => 'message_datetime',
			'_table' => '__MESSAGE__',
			'_type' => 'LEFT'
		],
		'B'=> [
			'id', 'member_id', 'is_sms', 'is_read', 'is_wx', 'feedback', 'datetime',
			'_on' => 'B.message_id=A.id',
			'_table' => '__MEMBER_MESSAGE__',
			'_type' => 'LEFT'
		],
		'C' => [
			'nickname',
			'_on' => 'A.member_id=C.id',
			'_table' => '__MEMBER__',
			'_type' => 'LEFT'
		],
		'D' => [
			'path',
			'_on' => 'C.pic_id=D.id',
			'_table' => '__PICS__',
            '_type' => 'LEFT'
		],
        'E' => [
            'path' => 'member_path',
            '_on' => 'C.pic_id=E.id',
            '_table' => '__PICS__'
        ]
	];


}
