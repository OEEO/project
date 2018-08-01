<?php
namespace Admin\Model;
use Think\Model\ViewModel;

class MemberPieceViewModel extends ViewModel {
	
	protected $connection = 'DB2';
	protected $tablePrefix = 'ym_'; 
	
	public $viewFields = [
		'A'=> ['id','member_id','piece_id','end_time','act_status', 'status', '_table'=>'__MEMBER_PIECE__'],
        'B'=> [
			'type',
			'type_times_id',
			'phase'=>'piece_phase',
			'price'=>'piece_price',
			'count'=>'piece_count',
			'limit_num'=>'piece_limit_num',
			'is_cap',
			'status'=>'piece_status',
            '_on'=>'A.piece_id=B.id',
            '_table'=>'__PIECE__',
            '_type'=>'LEFT'
		],
		'C'=> [
			'nickname',
			'_on'=>'A.member_id = C.id',
			'_table'=>'__MEMBER__',
			'_type'=>'LEFT',
		],
	];
	
}
