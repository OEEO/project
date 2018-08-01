<?php
namespace Member\Model;
use Think\Model\ViewModel;

class HostViewModel extends ViewModel {

	public $viewFields = [
        'A' => [
            'id','pic_id',
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
            'surname','contact','identity','cover_pic_id','signature','sex',
            '_on' => 'A.id=C.member_id',
            '_table' => '__MEMBER_INFO__',
			'_type' => 'LEFT'
        ],
		'D' => [
			'number' => 'bank_number',
			'bank_id',
			'_on' => 'A.id=D.member_id',
			'_table' => '__MEMBER_BANK__',
			'_type' => 'LEFT'
		],
		'E' => [
			'name' => 'bank_name',
			'_on' => 'D.bank_id=E.id',
			'_table' => '__BANK__'
		],
		'F' => [
			'tag_id',
			'_on' => 'A.id=F.member_id',
			'_table' => '__MEMBER_TAG__'
		]
	];

}
