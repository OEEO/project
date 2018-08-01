<?php
namespace Home\Model;
use Think\Model\ViewModel;

class SpaceViewModel extends ViewModel {

	public $viewFields = [
		'A' => [
			'id','member_id','name','introduction','address','pic_group_id','latitude','longitude','facility','proportion','volume','opening_time','context','status',
			'_table' => '__SPACE__'
		],
		'B' => [
			'path',
			'_on' => 'A.pic_id=B.id',
			'_table' => '__PICS__'
		],
		'C'=> [
			'id' => 'area_id',
			'name' => 'area_name',
			'_on' => 'A.city_id=C.id',
			'_table'=>'__CITYS__'
		],
		'D'=> [
			'id' => 'city_id',
			'name' => 'city_name',
			'_on'=>'D.id=C.pid',
			'_table'=>'__CITYS__'
		],
		'E'=> [
			'id' => 'province_id',
			'name' => 'province_name',
			'_on' => 'E.id=D.pid',
			'_table' => '__CITYS__',
			'_type' => 'left'
		],
		'F' => [
			'name'=> 'category_name',
			'_on' => 'A.category_id=F.id',
			'_table' => '__CATEGORY__'
		],
	];

}
