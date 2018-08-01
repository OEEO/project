<?php
namespace Admin\Model;
use Think\Model\ViewModel;

class GoodsEditViewModel extends ViewModel {

	protected $connection = 'DB2';
	protected $tablePrefix = 'ym_';

	public $viewFields = [
		'A' => [
			'id','title','price','stocks','status','member_id','is_pass','category_id','pic_id',
			'_table' => '__GOODS__',
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
			'_table' => '__CATEGORY__'
		],
		'D' => [
			'notice','edge','pics_group_id','is_public','content','shipping',
			'_on' => 'A.id=D.goods_id',
			'_table' => '__GOODS_SUB__',
			'_type' => 'LEFT'
		]
	];

}
