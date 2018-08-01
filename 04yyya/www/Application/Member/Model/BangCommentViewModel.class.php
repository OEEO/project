<?php
namespace Member\Model;
use Think\Model\ViewModel;

class BangCommentViewModel extends ViewModel {

	public $viewFields = [
		'A' => [
			'id','stars','type','type_id','content','pics_group_id','datetime',
			'_table' => '__MEMBER_COMMENT__'
		],
		'B' => [
			'id'=>'member_id','nickname',
			'_on' => 'A.member_id=B.id',
			'_table' => '__MEMBER__'
		],
		'C' => [
            'id'=>'bang_id',
			'_on' => 'A.type=2 and A.type_id=C.id',
			'_table' => '__BANG__',
			'_type' => 'LEFT'
		],
		/*'D' => [
			'member_id' => 'at_id', 'is_read',
			'_on' => 'A.id=D.comment_id',
			'_table' => '__MEMBER_COMMENT_AT__',
			'_type' => 'LEFT'
		]*/
	];

}
