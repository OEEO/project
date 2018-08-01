<?php
namespace Daren\Model;
use Think\Model\ViewModel;

class CheckCodeViewModel extends ViewModel {

	public $viewFields = [
		'A' => [
			'id','order_id','ware_id','check_code', 'server_status','tips_times_id',
			'_table' => '__ORDER_WARES__',
		],
		'B' => [
			'sn', 'act_status', 'status',
			'_on' => 'A.order_id=B.id',
			'_table' => '__ORDER__'
		],
		'C' => [
			'title','member_id' => 'host_id',
			'_on' => 'A.type=0 and A.ware_id=C.id',
			'_table' => '__TIPS__'
		],
		'D' => [
			'nickname','telephone',
			'_on' => 'B.member_id=D.id',
			'_table' => '__MEMBER__'
		],
		'E' => [
			'name' => 'address_name',
			'_on' => 'C.space_id=E.id',
			'_table' => '__SPACE__'
		],
		'F' => [
			'start_time','end_time','start_buy_time','stop_buy_time','min_num',
			'_on' => 'A.tips_times_id=F.id',
			'_table' => '__TIPS_TIMES__',
			'_type' => 'LEFT'
		],
		'G' => [
			'path' => 'tips_path',
			'_on' => 'C.pic_id=G.id',
			'_table' => '__PICS__',
			'_type' => 'LEFT'
		],
		'H' => [
			'path' => 'member_path',
			'_on' => 'D.pic_id=H.id',
			'_table' => '__PICS__'
		]
	];
}