<?php
namespace Member\Model;
use Think\Model\ViewModel;

class GetOrderWareViewModel extends ViewModel {

	public $viewFields = [
		'A' => [
			'id','type','ware_id', 'price','check_code','server_status','order_id',
			'_table' => '__ORDER_WARES__'
		],
		'B' => [
			'act_status','status','member_id','create_time',
			'_on' => 'A.order_id=B.id',
			'_table' => '__ORDER__',
			'_type' => 'LEFT',
		],
		'C'=> [
			'_on'=>'B.member_id = C.id',
			'_table'=>'__MEMBER__',
			'_type' => 'LEFT',
		],
		'D'=> [
			'path' => 'joiner_path',
			'_on'=>'C.pic_id = D.id',
			'_table'=>'__PICS__'
		],
	];

}
