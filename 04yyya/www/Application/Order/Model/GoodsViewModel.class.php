<?php
namespace Order\Model;
use Think\Model\ViewModel;

class GoodsViewModel extends ViewModel {

	public $viewFields = [
		'A' => [
			'id','title','price','status','member_id','is_pass','stocks','category_id','datetime','limit_time','limit_num',
			'_table' => '__GOODS__',
			'_type' => 'LEFT'
		],
		'B' => [
			'path',
			'_on' => 'A.pic_id=B.id',
			'_table' => '__PICS__'
		],
		'C' => [
			'name' => 'catname',
			'_on' => 'A.category_id=C.id',
			'_table' => '__CATEGORY__'
		],
		'D' => [
			'edge', 'content', 'shipping', 'notice',
			'_on' => 'A.id=D.goods_id',
			'_table' => '__GOODS_SUB__'
		],
        'E' => [
            'nickname',
            '_on' => 'A.member_id=E.id',
            '_table' => '__MEMBER__'
		]
	];

}
