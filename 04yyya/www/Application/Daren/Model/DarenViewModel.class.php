<?php
namespace Daren\Model;
use Think\Model\ViewModel;

Class DarenViewModel extends ViewModel {
	
	public $viewFields = [
		'A' => [
			'id' => 'member_id','nickname','telephone',
			'_table' => '__MEMBER__',
			'_type' => 'LEFT'
		],
		'B' => [
			'path',
			'_on' => 'A.pic_id=B.id',
			'_table' => '__PICS__',
			'_type' => 'LEFT'
		],
		'C' => [
            'surname','sex','contact','cover_pic_id','signature'=>'member_introduce',
			'_on' => 'A.id=C.member_id',
			'_table' => '__MEMBER_INFO__',
			'_type' => 'LEFT'
		],
		'D' => [
			'path' => 'cover_path',
			'_on' => 'C.cover_pic_id=D.id',
			'_table' => '__PICS__'
		],
		'E' => [
			'tag_id',
			'_on' => 'A.id=E.member_id',
			'_table' => '__MEMBER_TAG__'
		]
	];
	
}


