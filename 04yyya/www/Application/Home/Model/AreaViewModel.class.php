<?php
namespace Home\Model;
use Think\Model\ViewModel;

class AreaViewModel extends ViewModel {

	public $viewFields = array(
		'A' => array(
			'id' => 'area_id',
			'name' => 'area_name',
			'_table' => '__CITYS__',
			'_type' => 'LEFT'
		),
		'B' => array(
			'id' => 'city_id',
			'name' => 'city_name',
			'_on' => 'A.pid=B.id',
			'_table' => '__CITYS__',
			'_type' => 'LEFT'
		),
		'C' => array(
			'id' => 'province_id',
			'name' => 'province_name',
			'_on' => 'B.pid=C.id',
			'_table' => '__CITYS__'
		)
	);

}
