<?php
namespace Member\Model;
use Think\Model\ViewModel;

class PieceOrderViewModel extends ViewModel {

	public $viewFields = [
		'A'=> [
			'id' => 'wid','type', 'ware_id','inviter_id','tips_times_id',
			'_table' => '__ORDER_WARES__',
			'_type' => 'LEFT',
		],
		'B'=> [
			'id', 'sn','member_id', 'price', 'act_status','status','create_time',
			'_on'=>'A.order_id = B.id',
			'_table'=>'__ORDER__',
		],
		'C'=> [
			'nickname',
			'_on'=>'B.member_id = C.id',
			'_table'=>'__MEMBER__',
			'_type' => 'LEFT',
		],
		'D'=> [
			'path' => 'joiner_path',
			'_on'=>'C.pic_id = D.id',
			'_table'=>'__PICS__',
			'_type' => 'LEFT',
		],
		'E'=> [
			'piece_originator_id',
			'_on'=>'E.order_id = B.id',
			'_table'=>'__ORDER_PIECE__'
		]
	];


}
