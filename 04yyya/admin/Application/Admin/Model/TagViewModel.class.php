<?php
namespace Admin\Model;
use Think\Model\ViewModel;

class TagViewModel extends ViewModel {
	protected $connection = 'DB2';
	protected $tablePrefix = 'ym_';
	public $viewFields = [
		'A' => [
			'id', 'name', 'type', 'official',
			'_table' => '__TAG__',
			'_type' => 'LEFT'
		],
		'B' => [
			'tips_id',
			'_on' => 'A.id=B.tag_id',
			'_table' => '__TIPS_TAG__',
			'_type' => 'LEFT'
		],
		'C' => [
			'goods_id',
			'_on' => 'A.id=C.tag_id',
			'_table' => '__GOODS_TAG__'
		]
	];

}
