<?php
namespace Website\Model;
use Think\Model\ViewModel;

class RaiseViewModel extends ViewModel {

	public $viewFields = [
		'A' => [
			'id','title','content','total','status','category_id','datetime','start_time', 'end_time','video_url','introduction','totaled','buyer_num',
			'_table' => '__RAISE__',
			'_type' => 'LEFT'
		],
		'B' => [
			'path',
			'_on' => 'A.pic_id=B.id',
			'_table' => '__PICS__',
            '_type' => 'LEFT'
		],
		'C' => [
			'name' => 'catname',
			'_on' => 'A.category_id=C.id',
			'_table' => '__CATEGORY__',
			'_type' => 'LEFT'
		],
		'D' => [
			'nickname',
			'_on' => 'A.member_id=D.id',
			'_table' => '__MEMBER__',
			'_type' => 'LEFT'
		],
		'E' => [
			'path' => 'headpath',
			'_on' => 'D.pic_id=E.id',
			'_table' => '__PICS__',
			'_type' => 'LEFT'
		]
	];

}
