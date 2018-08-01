<?php
namespace Goods\Model;
use Think\Model\ViewModel;

class RaiseViewModel extends ViewModel {

	public $viewFields = [
		'A' => [
			'id','title','content','total', 'totaled','status','category_id','datetime','start_time', 'city_id', 'end_time','video_url','introduction','totaled','buyer_num','content1','content2','content3','content4','content5','title1','title2','title3','title4','title5','is_preview',
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
		], 
		'F' => [
			'name' => 'city_name',
			'_on' => 'A.city_id=F.id',
			'_table' => '__CITYS__',
			'_type' => 'LEFT'
		],
		'G' => [
			'weight',
			'_on' => 'A.id=G.r_id',
            '_table' => '__HOME__',
            '_type' => 'LEFT'
        ]
	];

}
