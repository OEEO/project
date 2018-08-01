<?php
namespace Goods\Model;
use Think\Model\ViewModel;

class GoodsViewModel extends ViewModel {

	public $viewFields = [
		'A' => [
			'id','title','price','status','member_id','is_pass','member_id','category_id','datetime','stocks','is_top',
            'limit_num',
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
			'_table' => '__CATEGORY__',
			'_type' => 'LEFT'
		],
		'D' => [
			'shipping', 'content', 'pics_group_id', 'edge', 'notice', 'is_public', 'add_notice',
			'_on' => 'A.id=D.goods_id',
			'_table' => '__GOODS_SUB__'
		]
	];

}
