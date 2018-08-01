<?php
namespace Admin\Model;
use Think\Model\ViewModel;

class NewsEditViewModel extends ViewModel {

	protected $connection = 'DB2';
	protected $tablePrefix = 'ym_';

	public $viewFields = [
		'A' => [
			'id','member_id','category_id','title','pic_id','content','abstract',
			'_table' => '__NEWS__',
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
			'nickname',
			'_on' => 'A.member_id=D.id',
			'_table' => '__MEMBER__'

		]

	];

}
