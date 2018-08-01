<?php
namespace Home\Model;
use Think\Model\ViewModel;

class RecieveCodeOrderViewModel extends ViewModel {

	public $viewFields = [
		'A' => [
			'id','type','ware_id','code','create_time',
			'_table' => '__RECIEVE_CODE__',
			'_type' => 'LEFT'
		],
		'B' => [
            'rid',
            'name',
            'address',
            'telephone',
            'member_id',
            'order_id',
			'_on' => 'A.id=B.rid',
			'_table' => '__RECIEVE_ORDER__',
			'_type' => 'LEFT'
		],
		'C' => [
            'username',
            'nickname',
            'telephone',
			'_on' => 'B.member_id=C.id',
			'_table' => '__MEMBER__',
			'_type' => 'LEFT'
		],
	];

}
