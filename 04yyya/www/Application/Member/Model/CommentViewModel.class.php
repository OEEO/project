<?php
namespace Member\Model;
use Think\Model\ViewModel;

class CommentViewModel extends ViewModel {

	public $viewFields = [
		'A' => [
			'id','stars','type','type_id','content','pics_group_id','datetime',
			'_table' => '__MEMBER_COMMENT__'
		],
		'B' => [
			'nickname',
			'_on' => 'A.member_id=B.id',
			'_table' => '__MEMBER__'
		],
		'C' => [
			'path',
			'_on' => 'B.pic_id=C.id',
			'_table' => '__PICS__',
			'_type' => 'LEFT'
		],
		'D' => [
			'title','member_id','price',
			'_on' => 'A.type=0 and A.type_id=D.id',
			'_table' => '__TIPS__',
			'_type' => 'LEFT'
		],
		'E' => [
			'title','member_id','price',
			'_on' => 'A.type=1 and A.type_id=E.id',
			'_table' => '__GOODS__',
			'_type' => 'LEFT'
		],
		'F' => [
			'content', 'member_id',
			'_on' => 'A.type=2 and A.type_id=F.id',
			'_table' => '__BANG__',
			'_type' => 'LEFT'
		],
		'G' => [
			'member_id' => 'at_id', 'is_read',
			'_on' => 'A.id=G.comment_id',
			'_table' => '__MEMBER_COMMENT_AT__',
			'_type' => 'LEFT'
		],
		'H' => [
			'path',
			'_on' => 'D.pic_id=H.id or E.pic_id=H.id or F.pic_id=H.id',
			'_table' => '__PIC__'
		]
	];

}
