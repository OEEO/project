<?php
namespace Goods\Model;
use Think\Model\ViewModel;

class CommentViewModel extends ViewModel {

	public $viewFields = [
		'A' => [
			'id','stars','type','type_id','content','pics_group_id','datetime','status','pid',
			'_table' => '__MEMBER_COMMENT__'
		],
		'B' => [
			'nickname',
			'_on' => 'A.member_id=B.id',
			'_table' => '__MEMBER__',
			'_type' => 'LEFT'
		],
		'C' => [
			'path'=>'head_path',
			'_on' => 'B.pic_id=C.id',
			'_table' => '__PICS__',
			'_type' => 'LEFT'
		],
		'D' => [
			'member_id' => 'tips_member_id',
			'_on' => 'A.type=0 and A.type_id=D.id',
			'_table' => '__TIPS__',
			'_type' => 'LEFT'
		],
		'E' => [
			'member_id' => 'goods_member_id',
			'_on' => 'A.type=1 and A.type_id=E.id',
			'_table' => '__GOODS__'
		]
	];

}
