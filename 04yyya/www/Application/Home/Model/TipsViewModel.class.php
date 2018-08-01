<?php
namespace Home\Model;
use Think\Model\ViewModel;

class TipsViewModel extends ViewModel {

	public $viewFields = [
		'A' => [
			'id','is_top','title','price','status','member_id',
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
			'start_time','end_time','start_buy_time','stop_buy_time','min_num','max_num' => 'restrict_num',
			'_on' => 'A.id=C.tips_id',
			'_table' => '__TIPS_TIMES__',
			'_type' => 'LEFT'
		],
		'D' => [
			'edges','is_public',
			'_on' => 'A.id=D.tips_id',
			'_table' => '__TIPS_SUB__'
		],
		'E' => [
			'nickname',
			'_on' => 'A.member_id=E.id',
			'_table' => '__MEMBER__'
		],
		'F' => [
			'name' => 'catename',
			'_on' => 'A.category_id=F.id',
			'_table' => '__CATEGORY__'
		],
		'G' => [
			'path' => 'headpic',
			'_on' => 'E.pic_id=G.id',
			'_table' => '__PICS__',
			'_type' => 'LEFT'
		],
		'I' => [
			'city_id' => 'citys_id','name','address','latitude','longitude',
			'_on' => 'A.space_id=I.id',
			'_table' => '__SPACE__'
		],
		'H' => [
			'name' => 'city_name',
			'_on' => 'I.city_id=H.id',
			'_table' => '__CITYS__'
		]
	];

}
