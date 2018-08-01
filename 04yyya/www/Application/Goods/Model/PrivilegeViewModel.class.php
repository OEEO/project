<?php
namespace Goods\Model;
use Think\Model\ViewModel;

class PrivilegeViewModel extends ViewModel {

	public $viewFields = array(
		'A' => array(
			'member_id','type','type_id','number','tips_times_id',
			'_table' => '__PRIVILEGE__'
		),
		'B' => array(
			'nickname',
			'pic_id'=>'head_pic_id',
			'_on' => 'A.member_id=B.id',
			'_table' => '__MEMBER__',
			'_type' => 'LEFT'

		),
		'C' => array(
			'title','pic_id',
			'_on' => 'A.type_id=C.id',
			'_table' => '__RAISE__',
			'_type' => 'LEFT'
		),
		'D' => array(
			'title'=>'raise_times_title',
			'price'=>'raise_times_price',
			'prepay'=>'raise_times_prepay',
			'content'=>'raise_times_content',
			'_on' => 'D.raise_id=C.id',
			'_table' => '__RAISE_TIMES__',
			'_type' => 'LEFT'

		),

		'E' => array(
			'path'=>'pic_path',
			'_on' => 'C.pic_id=E.id',
			'_table' => '__PICS__',
			'_type' => 'LEFT'
		),
		'F' => array(
			'path'=>'head_pic_path',
			'_on' => 'F.id=B.pic_id',
			'_table' => '__PICS__',
			'_type' => 'LEFT'

		),

	);


}
