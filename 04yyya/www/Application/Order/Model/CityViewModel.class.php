<?php
namespace Order\Model;
use Think\Model\ViewModel;

class CityViewModel extends ViewModel {

	public $viewFields = [
		'A'=> [
			'id' => 'area_id',
			'name' => 'area_name',
			'pinyin' => 'area_pinyin',
			'alt' => 'area_alt',
			'_table'=>'__CITYS__',
			'_type' => 'LEFT'
		],
		'B'=> [
			'id' => 'city_id',
			'name' => 'city_name',
			'pinyin' => 'city_pinyin',
			'alt' => 'city_alt',
			'_type' => 'LEFT',
			'_on'=>'B.id=A.pid',
			'_table'=>'__CITYS__'
		],
		'C'=> [
			'id' => 'province_id',
			'name' => 'province_name',
			'pinyin' => 'province_pinyin',
			'alt' => 'province_alt',
			'_on'=>'C.id=B.pid',
			'_table'=>'__CITYS__'
		]
	];


}
