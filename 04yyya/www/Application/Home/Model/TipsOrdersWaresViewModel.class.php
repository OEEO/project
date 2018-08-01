<?php
namespace Home\Model;
use Think\Model\ViewModel;

class TipsOrdersWaresViewModel extends ViewModel {

	public $viewFields = [
		'A' =>[
			'id','member_id',
			'_table' => '__ORDER__',
			'_type' => 'LEFT'

		],
		'B' =>[
			'id'=>'order_wares_id',
			'ware_id'=>'tips_id',
			'tips_times_id',
			'check_code',
			'type',
			'_on' => 'B.order_id=A.id',
			'_table' => '__ORDER_WARES__',
			'_type' => 'LEFT'

		],
		'C' => [
			'title'=>'tips_title',
			'member_id'=>'host_id',
			'_on' => 'C.id=B.ware_id',
			'_table' => '__TIPS__',
			'_type' => 'LEFT'
		],
		'E' => [
			'name'=>'simpleaddress',
			'address', 'longitude', 'latitude',
			'_on' => 'C.space_id=E.id',
			'_table' => '__SPACE__',
			'_type' => 'LEFT'
		],
		'F' => [
			'name' => 'area_name',
			'alt' => 'area_alt',
			'_on' => 'E.city_id=F.id',
			'_table' => '__CITYS__',
			'_type' => 'LEFT'
		],
		'G' => [
           'start_time','end_time',
			'_on' => 'G.id=B.tips_times_id',
			'_table' => '__TIPS_TIMES__',
			'_type' => 'LEFT'
		],
        'H' => [
            'nickname' => 'member_nickname',
            '_on' => 'A.member_id=H.id',
            '_table' => '__MEMBER__',
			'_type' => 'LEFT'
		],
		'I' => [
			'id' => 'city_id',
			'name' => 'city_name',
			'alt' => 'city_alt',
			'_on' => 'F.pid=I.id',
			'_table' => '__CITYS__',
			'_type' => 'LEFT'
		],
		'O' => [
			'path'=>'tips_pic_path',
			'_on' => 'C.pic_id=O.id',
			'_table' => '__PICS__',
			'_type' => 'LEFT'
		],

		'M' => [
			'path'=>'member_pic_path',
			'_on' => 'H.pic_id=M.id',
			'_table' => '__PICS__',
			'_type' => 'LEFT'
		],
	];

}
