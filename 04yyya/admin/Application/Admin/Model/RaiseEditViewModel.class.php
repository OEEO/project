<?php
namespace Admin\Model;
use Think\Model\ViewModel;

class RaiseEditViewModel extends ViewModel {

	protected $connection = 'DB2';
	protected $tablePrefix = 'ym_';

	public $viewFields = [
		'A' => [
			'id','member_id','category_id','title','pic_id','total','content','city_id','introduction','start_time','end_time','is_public',
			'_table' => '__RAISE__',
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
			'_table' => '__CATEGORY__',
			'_type' => 'LEFT'
		],
		'D' => [
			'is_address',
			'_on' => 'D.raise_id=A.id',
			'_table' => '__RAISE_TIMES__'

		]

	];

}
