<?php
namespace Home\Model;
use Think\Model\ViewModel;

class SignViewModel extends ViewModel {

	public $viewFields = [
		'A' => [
			'id'=> 'sign_id',
			'open_id','title','pic_id','datetime',
			'_table' => '__SIGN__',
			'_type' => 'LEFT'
		],
		'B' => [
			'path'=>'sign_path',
			'_on' => 'A.pic_id=B.id',
			'_table' => '__PICS__'
		]
	];

}
