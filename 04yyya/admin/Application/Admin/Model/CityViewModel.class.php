<?php
namespace Admin\Model;
use Think\Model\ViewModel;

class CityViewModel extends ViewModel {

	protected $connection = 'DB2';
	protected $tablePrefix = 'ym_';

	public $viewFields = [
		'A'=> [
			'id' => 'district_id',
			'name' => 'district_name',
			'pinyin' => 'district_pinyin',
			'alt' => 'district_alt',
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
