<?php
namespace Admin\Model;
use Think\Model\ViewModel;

class TipsOrderViewModel extends ViewModel {

	protected $connection = 'DB2';
	protected $tablePrefix = 'ym_';

	public $viewFields = [
		'A'=> ['ware_id','order_id','tips_times_id','server_status','_table'=>'__ORDER_WARES__'],
		'B'=> [
			'member_id','price',
			'act_status',
			'invite_member_id',
			'status'=>'order_status',
			'_on'=>'B.id=A.order_id',
			'_table'=>'__ORDER__'
		],
	];
	
}
