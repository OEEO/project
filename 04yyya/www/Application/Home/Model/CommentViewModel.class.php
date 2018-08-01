<?php
namespace Home\Model;
use Think\Model\ViewModel;

class CommentViewModel extends ViewModel {

	public $viewFields = [
		'A' => [
			'id','stars','type','type_id','content','pics_group_id','datetime','member_id','pid',
			'_table' => '__MEMBER_COMMENT__'
		],
		'B' => [
			'nickname',
			'_on' => 'A.member_id=B.id',
			'_table' => '__MEMBER__',
			'_type' => 'LEFT'
		],
		'C' => [
			'path'=>'head_path',
			'_on' => 'B.pic_id=C.id',
			'_table' => '__PICS__',
            '_type' => 'LEFT'
		],
        'D' => [
            'title'=>'tips_title',
            '_on' => 'A.type=0 and A.type_id=D.id',
            '_table' => '__TIPS__',
            '_type' => 'LEFT'
        ],
        /*'E' => [
            'title' => 'goods_title',
            '_on' => 'A.type=1 and A.type_id=E.id',
            '_table' => '__GOODS__',
            '_type' => 'LEFT'
        ],*/
        'F'=> [
            'name'=>'category_name_tips',
            '_on' => 'A.type=0 and D.category_id=F.id',
            '_table' => '__CATEGORY__',
            '_type' => 'LEFT'
		],
        /*'G'=>array(
            'name'=>'category_name_goods',
            '_on' => 'A.type=1 and E.category_id=G.id',
            '_table' => '__CATEGORY__',
            '_type' => 'LEFT'
        )*/
	];

}
