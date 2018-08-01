<?php
namespace Order\Model;
use Think\Model\ViewModel;

class TipsViewModel extends ViewModel {

	public $viewFields = [
		'A' => [
			'id','title','price','discount','status','member_id','is_pass','member_id','category_id','limit_time','datetime','buy_status',
			'_table' => '__TIPS__',
			'_type' => 'LEFT'
		],
		'B' => [
			'path',
			'_on' => 'A.pic_id=B.id',
			'_table' => '__PICS__'
		],
		'C' => [
			'name' => 'catname',
			'_on' => 'A.category_id=C.id',
			'_table' => '__CATEGORY__'
		],
		'D' => [
			'address', 'longitude', 'latitude',
			'_on' => 'A.space_id=D.id',
			'_table' => '__SPACE__'
		],
		'E' => [
			'name' => 'area_name',
			'_on' => 'D.city_id=E.id',
			'_table' => '__CITYS__'
		],
		'F' => [
            'id'=>'times_id','start_time','end_time',
			'min(start_time)' => 'min_start_time',
			'max(end_time)' => 'max_end_time','limit_num',
            'stock','phase','stop_buy_time','start_buy_time','min_num','max_num' => 'restrict_num','lowest_num',
			'_on' => 'A.id=F.tips_id',
			'_table' => '__TIPS_TIMES__'
		],
        'G' => [
            'nickname' => 'member_nickname',
            '_on' => 'A.member_id=G.id',
            '_table' => '__MEMBER__'
		],
		'H' => [
			'id' => 'city_id',
			'name' => 'city_name',
			'_on' => 'E.pid=H.id',
			'_table' => '__CITYS__'
		],
		'I' => [
			'edges',
			'_on' => 'I.tips_id=A.id',
			'_table' => '__TIPS_SUB__'
		]
	];

}
