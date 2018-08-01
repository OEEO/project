<?php
namespace Daren\Model;
use Think\Model\ViewModel;

class TipsViewModel extends ViewModel {

	public $viewFields = [
		'A' => [
			'id','category_id','title','price','buy_status','discount','status','member_id','is_pass','member_id','pic_id',
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
			'citys_id','notice','edges','pics_group_id','menu_pics_group_id','is_public',
			'_on' => 'A.id=C.tips_id',
			'_table' => '__TIPS_SUB__',
			'_type' => 'LEFT'
		],
		'D' => [
			'id' => 'space_id','name','city_id','address','pic_group_id','latitude','longitude','facility',
			'_on' => 'A.space_id=D.id',
			'_table' => '__SPACE__'
		]
	];

}
