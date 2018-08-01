<?php
namespace Admin\Model;
use Think\Model\ViewModel;

class SignViewModel extends ViewModel {
	protected $connection = 'DB2';
	protected $tablePrefix = 'ym_';
	public $viewFields = [
		'A' => [
			'id'=> 'sign_id',
			'open_id','title','pic_id','datetime','status',
			'_table' => '__SIGN__',
			'_type' => 'LEFT'
		],
		'B' => [
			'path'=>'sign_path',
			'_on' => 'A.pic_id=B.id',
			'_table' => '__PICS__'
		],
		'C' => [
			'nickname','city_id','pic_id','sex',
			'_on' => 'C.id=A.open_id',
			'_table' => '__OPENID__'
		],
		'D' => [
			'path'=>'head_path',
			'_on' => 'C.pic_id=D.id',
			'_table' => '__PICS__'
		],
	];

}
