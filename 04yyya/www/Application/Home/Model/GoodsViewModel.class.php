<?php
namespace Home\Model;
use Think\Model\ViewModel;

class GoodsViewModel extends ViewModel {

	public $viewFields = array(
		'A' => array(
			'id','category_id','member_id', 'title', 'stocks', 'price','status',
			'_table' => '__GOODS__',
			'_type' => 'LEFT'
		),
		'B' => array(
			'path' => 'mainpic',
			'_on' => 'A.pic_id=B.id',
			'_table' => '__PICS__',
			'_type' => 'LEFT'
		),
        'C' => array(
            'content', 'shipping','pics_group_id',
            '_on' => 'C.goods_id=A.id',
            '_table' => '__GOODS_SUB__',
        )
	);

}
