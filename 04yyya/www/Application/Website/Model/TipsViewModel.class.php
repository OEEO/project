<?php
namespace Website\Model;
use Think\Model\ViewModel;

class TipsViewModel extends ViewModel {

	public $viewFields = [
		'A' => [
			'id','title','price','buy_status','discount','status','member_id','is_pass','category_id','datetime','is_top',
			'_table' => '__TIPS__',
			'_type' => 'LEFT'
		],
		'B' => [
			'path',
			'_on' => 'A.pic_id=B.id',
			'_table' => '__PICS__',
            '_type' => 'LEFT'
		],
		'C' => [
			'name' => 'catname',
			'_on' => 'A.category_id=C.id',
			'_table' => '__CATEGORY__'
		],
		'D' => [
			'edges','intro','pics_group_id','menu_pics_group_id','notice','is_public','content',
			'_on' => 'A.id=D.tips_id',
			'_table' => '__TIPS_SUB__',
            '_type' => 'LEFT'
		],
//		'E' => [
//			'name' => 'area_name',
//			'_on' => 'D.citys_id=E.id',
//			'_table' => '__CITYS__',
//            '_type' => 'LEFT'//
//		],
//        'Z' => [
//            'name' => 'parent_area_name',
//            '_on' => 'E.pid=Z.id',
//            '_table' => '__CITYS__'
//        ],
		'F' => [
			'start_time', 'end_time','start_buy_time','stop_buy_time','min_num','max_num','stock',
			'_on' => 'A.id=F.tips_id',
			'_table' => '__TIPS_TIMES__',
            '_type' => 'LEFT'
		],
		'G' => [
			'nickname',
			'telephone',
			'_on' => 'A.member_id=G.id',
			'_table' => '__MEMBER__',
            '_type' => 'LEFT'
		],
		'H' => [
			'path' => 'headpic',
			'_on' => 'G.pic_id=H.id',
			'_table' => '__PICS__',
			'_type' => 'LEFT'
		],
//		'I' => [
//			'_on' => 'A.member_id=I.member_id',
//			'_table' => '__MEMBER_DAREN__',
//            '_type' => 'LEFT'
//		],
//        'J' => [
//            '_on' => 'A.id=J.tips_id',
//            '_table' => '__TIPS_TAG__',
//            '_type' => 'LEFT'
//		],
//        'K' => [
//            'name' => 'tag_name',
//            '_on' => 'K.id=J.tag_id',
//            '_table' => '__TAG__',
//			'_type' => 'LEFT'
//		],
		'M' => [
			'name' => 'simpleaddress',
			'address', 'city_id', 'longitude', 'latitude', 'pic_group_id' => 'environment_pics_group_id',
			'_on' => 'A.space_id=M.id',
			'_table' => '__SPACE__'
		]
	];

}
