<?php
namespace Member\Model;
use Think\Model\ViewModel;

class OrderWareViewModel extends ViewModel {

	public $viewFields = [
		'A'=> [
			'id' => 'wid','type', 'ware_id','inviter_id','tips_times_id',
			'count(order_id)' => 'count',
			'_table' => '__ORDER_WARES__'
		],
		'B'=> [
			'id', 'sn','member_id', 'price', 'act_status', 'postage', 'comment_id' ,'status','create_time','order_pid','limit_pay_time',
			'is_free',
            '_on'=>'B.id=A.order_id',
			'_table'=>'__ORDER__',
			'_type' => 'LEFT',
		],
		'C'=> [
			'start_time',
			'end_time',
			'_on'=>'A.tips_times_id=C.id',
			'_table'=>'__TIPS_TIMES__',
			'_type' => 'LEFT',
		],
		'D'=> [
			//'title' => 'title_0',
			'title','limit_time',
			'_on' => 'A.ware_id=D.id and A.type=0',
			//'_on' => 'A.ware_id=D.id',
			'_table' => '__TIPS__',
			'_type' => 'LEFT',
		],
		'E'=> [
			'title'=>'goods_title','limit_time' => 'goods_limit_time',
			'_on' => 'A.ware_id=E.id and A.type=1',
			'_table' => '__GOODS__',
			'_type' => 'LEFT',
		],
		'F'=> [
			'path',
//			'_on' => 'D.pic_id=F.id or E.pic_id=F.id',
			'_on' => 'D.pic_id=F.id and A.type=0',
			'_table' => '__PICS__',
			'_type' => 'LEFT',
		],
		'G' => [
			'name' => 'catname',
//			'_on' => 'D.category_id=G.id or E.category_id=G.id',
			'_on' => 'D.category_id=G.id and A.type=0',
			'_table' => '__CATEGORY__',
			'_type' => 'LEFT',
		],
		'H'=> [
			'path'=>'goods_path',
//			'_on' => 'D.pic_id=F.id or E.pic_id=F.id',
			'_on' => 'E.pic_id=H.id and A.type=1',
			'_table' => '__PICS__',
			'_type' => 'LEFT',
		],
		'I' => [
			'name'=>'goods_catname',
//			'_on' => 'D.category_id=G.id or E.category_id=G.id',
			'_on' => 'D.category_id=I.id and A.type=1',
			'_table' => '__CATEGORY__',
			'_type' => 'LEFT',
		]
	];


}
