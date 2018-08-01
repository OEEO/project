<?php
namespace Home\Model;
use Think\Model\ViewModel;

class OpenIdViewModel extends ViewModel {

	public $viewFields = [
		'A' => [
			'id','member_id','openid','type','nickname','sex','city_id','pic_id','first_login','subscribe_time','datetime',
			'_table' => '__OPENID__',
			'_type' => 'LEFT'
		],
		'B' => [
			'path',
			'original_path',
			'_on' => 'A.pic_id=B.id',
			'_table' => '__PICS__',
			'_type' => 'LEFT'
		],
		'C' => [
			'name'=>'city_name',
			'_on' => 'A.city_id=C.id',
			'_table' => '__CITYS__'
		]
	];

}
