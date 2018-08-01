<?php
namespace Admin\Model;
use Think\Model\ViewModel;

class OrderPieceViewModel extends ViewModel {
	protected $connection = 'DB2';
	protected $tablePrefix = 'ym_';
	public $viewFields = [
		'A'=> [
			'id', 'sn','member_id', 'price', 'act_status','status','create_time',
			'_table'=>'__ORDER__',
		],
//		'B'=> [
//			'id' => 'wid','type', 'ware_id','inviter_id','tips_times_id',
//			'_on'=>'A.id = B.order_id',
//			'_table' => '__ORDER_WARES__',
//			'_type' => 'LEFT',
//		],
		'C'=> [
			'nickname',
			'_on'=>'A.member_id = C.id',
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
			'_on'=>'E.order_id = A.id',
			'_table'=>'__ORDER_PIECE__',
			'_type' => 'LEFT',
		],
		'F'=> [
			'member_id'=>'member_originator_id',
			'_on'=>'E.piece_originator_id = F.id',
			'_table'=>'__MEMBER_PIECE__'
		]
	];
}


