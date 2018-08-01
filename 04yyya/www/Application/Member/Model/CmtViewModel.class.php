<?php
namespace Member\Model;
use Think\Model\ViewModel;

class CmtViewModel extends ViewModel {

	public $viewFields = array(
		'A' => [
			'id','type','type_id','content','datetime','count(1)' => 'count',
			'_table' => '__MEMBER_COMMENT__'
		],
		'B' => [
			'nickname',
			'_on' => 'A.member_id=B.id',
			'_table' => '__MEMBER__',
			'_type' => 'LEFT'
		],
		'C' => [
			'title','member_id',
			'_on' => 'A.type=0 and A.type_id=C.id',
			'_table' => '__TIPS__',
			'_type' => 'LEFT'
		],
		'D' => [
			'title','member_id',
			'_on' => 'A.type=1 and A.type_id=D.id',
			'_table' => '__GOODS__',
			'_type' => 'LEFT'
		],
		'E' => [
			'content','member_id','pic_id'=>'bang_pic_id',
			'_on' => 'A.type=2 and A.type_id=E.id',
			'_table' => '__BANG__',
			'_type' => 'LEFT'
		],
		'F' => [
			'member_id' => 'at_id',
			'is_read',
			'_on' => 'A.id=F.comment_id',
			'_table' => '__MEMBER_COMMENT_AT__'
		]
	);

}
