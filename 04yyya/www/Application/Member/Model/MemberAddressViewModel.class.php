<?php
namespace Member\Model;
use Think\Model\ViewModel;

class MemberAddressViewModel extends ViewModel {

	public $viewFields = [
		'A' => [
			'id','member_id','address','zipcode','linkman','telephone','is_default','datetime',
			'_table' => '__MEMBER_ADDRESS__',
			'_type' => 'LEFT'
		],
		'B' => [
			'id' => 'area_id',
			'name' => 'area_name',
			'alt' => 'area_alt',
			'_on' => 'A.citys_id=B.id',
			'_table' => '__CITYS__',
			'_type' => 'LEFT'
		],
		'C' => [
			'id' => 'city_id',
			'name' => 'city_name',
			'alt' => 'city_alt',
			'_on' => 'B.pid=C.id',
			'_table' => '__CITYS__',
			'_type' => 'LEFT'
		],
		'D' => [
			'id' => 'province_id',
			'name' => 'province_name',
			'alt' => 'province_alt',
			'_on' => 'C.pid=D.id',
			'_table' => '__CITYS__'
		]
	];

}
