<?php
namespace Home\Model;
use Think\Model\ViewModel;

class CityViewModel extends ViewModel {

	public $viewFields = array(
		'A'=>array(
			'id' => 'district_id',
			'name' => 'district_name',
			'pinyin' => 'district_pinyin',
			'alt' => 'district_alt',
			'_table'=>'__CITYS__',
			'_type' => 'LEFT'
		),
		'B'=>array(
			'id' => 'city_id',
			'name' => 'city_name',
			'pinyin' => 'city_pinyin',
			'alt' => 'city_alt',
			'_type' => 'LEFT',
			'_on'=>'B.id=A.pid',
			'_table'=>'__CITYS__'
		),
		'C'=>array(
			'id' => 'province_id',
			'name' => 'province_name',
			'pinyin' => 'province_pinyin',
			'alt' => 'province_alt',
			'_on'=>'C.id=B.pid',
			'_table'=>'__CITYS__'
		)
	);


}
