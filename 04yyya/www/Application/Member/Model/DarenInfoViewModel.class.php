<?php
namespace Member\Model;
use Think\Model\ViewModel;

class DarenInfoViewModel extends ViewModel {

	public $viewFields = [
        'A' => [
            'pic_id'=>'head_pic_id',
            '_table' => '__MEMBER__',
            '_type' => 'LEFT'
        ],
		'B' => [
            'path'=>'pic_path',
			'_on' => 'A.pic_id=B.id',
			'_table' => '__PICS__',
			'_type' => 'LEFT'
		],
        'C' => [
            'cover_pic_id','signature'=>'member_introduce',
            '_on' => 'A.id=C.member_id',
            '_table' => '__MEMBER_INFO__'
        ],
		'D' => [
			'tag_id',
			'_on' => 'A.id=D.member_id',
			'_table' => '__MEMBER_TAG__'
		]
	];

}
