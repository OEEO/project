<?php
namespace Member\Model;
use Think\Model\ViewModel;

class TipsViewModel extends ViewModel {

	public $viewFields = [
		'A' => [
			'id','title','price','is_pass','buy_status','status','limit_time',
			'_table' => '__TIPS__',
			'_type' => 'LEFT'
		],
		'B' => [
			'path',
			'_on' => 'A.pic_id=B.id',
			'_table' => '__PICS__',
			'_type' => 'LEFT'
		],
		'C' => [
			'id' => 'times_id','start_time','end_time','stop_buy_time',
			'_on' => 'A.id=C.tips_id',
			'_table' => '__TIPS_TIMES__',
			'_type' => 'LEFT'
		],
		'D' => [
			'address','name' => 'simpleaddress',
			'_on' => 'A.space_id=D.id',
			'_table' => '__SPACE__',
            '_type'=>'LEFT'
		],
		'E' => [
			'nickname',
			'_on' => 'A.member_id=E.id',
			'_table' => '__MEMBER__',
            '_type'=>'LEFT'
		],
		'F' => [
			'name' => 'catname',
			'_on' => 'A.category_id=F.id',
			'_table' => '__CATEGORY__',
            '_type'=>'LEFT'
		],
		'G' => [
			'path' => 'headpic',
			'_on' => 'E.pic_id=G.id',
			'_table' => '__PICS__',
            '_type'=>'LEFT'
		],
		'H' => [
			'name' => 'cityname',
			'_on' => 'D.city_id=H.id',
			'_table' => '__CITYS__'
		]
	];

}
