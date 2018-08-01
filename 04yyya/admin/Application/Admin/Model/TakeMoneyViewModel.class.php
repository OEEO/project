<?php
namespace Admin\Model;
use Think\Model\ViewModel;

class TakeMoneyViewModel extends ViewModel {

	protected $connection = 'DB2';
	protected $tablePrefix = 'ym_';

	public $viewFields = [
        'A'=> ['id','member_id'=>'apply_member_id', 'type', 'type_id', 'is_pass','refusal_reason','update_time','_table'=>'__MEMBER_APPLY__'],
        'B'=> [
            'id'=>'settlement_id','pay_id','type'=>'s_type','type_id'=>'times_id','amount','originator_id','handle_id','sn','content',
            '_on'=>'A.type=3 and A.type_id=B.id',
            '_table'=>'__SETTLEMENT__',
            '_type'=>'LEFT'
		],
		'D' => [
			'id'=> 'times_id','start_time','stop_buy_time','stock',
			'_on' => 'B.type_id=D.id',
			'_table' => '__TIPS_TIMES__'
		],
		'C' => [
			'id'=>'tips_id','member_id','price','title','is_pass'=>'tips_is_pass','status',
			'_on' => 'D.tips_id=C.id',
			'_table'=>'__TIPS__'
		],
		'E' => [
			'nickname','telephone',
			'_on' => 'C.member_id=E.id',
			'_table' => '__MEMBER__',
			'_type'=>'LEFT'
		],
		'F' => [
			'name'=>'pay_realname',
			'code',
			'_on' => 'F.member_id=E.id',
			'_table' => '__MEMBER_PAYWAY__'
		]
	];

}
