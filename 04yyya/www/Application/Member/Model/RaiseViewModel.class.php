<?php
namespace Member\Model;
use Think\Model\ViewModel;

class RaiseViewModel extends ViewModel {

	public $viewFields = [
		'A' => [
			'id','title','total','status','category_id','start_time','end_time','limit_time','datetime','content','introduction',
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
            'id'=>'times_id',
			'title'=>'raise_times_title',
			'price',
			'prepay',
			'stock',
			'quota',
            'type',
            'content' => 'raise_times_content',
			'_on' => 'A.id=D.raise_id',
			'_table' => '__RAISE_TIMES__',
			'_type' => 'LEFT'
		],
		'E' => [
			'nickname',
			'_on' => 'A.member_id=E.id',
			'_table' => '__MEMBER__',
			'_type' => 'LEFT',
		],
	];

}
