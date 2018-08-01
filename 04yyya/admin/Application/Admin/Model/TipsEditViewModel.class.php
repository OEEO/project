<?php
namespace Admin\Model;
use Think\Model\ViewModel;

class TipsEditViewModel extends ViewModel {

	protected $connection = 'DB2';
	protected $tablePrefix = 'ym_';

	public $viewFields = [
		'A' => [
			'id','title','price','space_id','discount','status','member_id','is_pass','category_id','pic_id','buy_status',
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
			'name' => 'catname',
			'_on' => 'A.category_id=C.id',
			'_table' => '__CATEGORY__'
		],
		'D' => [
			'citys_id', 'intro','simpleaddress','notice','edges','pics_group_id','menu_pics_group_id','notice','is_public','content',
			'_on' => 'A.id=D.tips_id',
			'_table' => '__TIPS_SUB__',
			'_type' => 'LEFT'
		],

		'E' => [
			'pid' => 'area_pid',
			'name' => 'area_name',
			'alt' => 'area_alt',
			'_on' => 'D.citys_id=E.id',
			'_table' => '__CITYS__',
			'_type' => 'LEFT'
		],
		'F' => [

			'id'=>'space_id',
			'address',
			'longitude',
			'latitude',
			'city_id',
			'pic_group_id' => 'environment_pics_group_id',
			'_on' => 'F.id=A.space_id',
			'_table' => '__SPACE__',
			'_type' => 'LEFT'
		],

	];

}
