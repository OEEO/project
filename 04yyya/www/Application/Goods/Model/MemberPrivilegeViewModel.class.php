<?php
namespace Goods\Model;
use Think\Model\ViewModel;

class MemberPrivilegeViewModel extends ViewModel {

	public $viewFields = array(

		'A' => array(
			'id'=>'member_privilege_id','member_id','order_id','privilege_id',
			'_table' => '__MEMBER_PRIVILEGE__'
		),

		'B' => array(
			'member_id'=>'prilege_member_id','type','type_id','number','tips_times_id',
			'_on' => 'A.privilege_id=B.id',
			'_table' => '__PRIVILEGE__',
			'_type' => 'LEFT'
		),
		'C' => array(
			'nickname'=>'member_nickname',
			'pic_id'=>'member_pic_id',
			'_on' => 'A.member_id=B.id',
			'_table' => '__MEMBER__',
			'_type' => 'LEFT'

		),
		'D' => array(
			'nickname'=>'privilege_nickname',
			'pic_id'=>'privilege_picid',
			'_on' => 'B.member_id=D.id',
			'_table' => '__MEMBER__',
			'_type' => 'LEFT'

		),
		'E' => array(
			'title','pic_id'=>'raise_pic_id',
			'_on' => 'B.type_id=E.id',
			'_table' => '__RAISE__',
			'_type' => 'LEFT'
		),
		'F' => array(
			'title'=>'raise_times_title',
			'price'=>'raise_times_price',
			'prepay'=>'raise_times_prepay',
			'_on' => 'B.tips_times_id=F.id',
			'_table' => '__RAISE_TIMES__',
			'_type' => 'LEFT'

		),

		'G' => array(
			'path'=>'member_path',
			'_on' => 'C.pic_id=G.id',
			'_table' => '__PICS__',
			'_type' => 'LEFT'
		),
		'I' => array(
			'path'=>'privilege_path',
			'_on' => 'I.id=D.pic_id',
			'_table' => '__PICS__',
			'_type' => 'LEFT'

		),
		'J' => array(
			'path'=>'raise_path',
			'_on' => 'J.id=E.pic_id',
			'_table' => '__PICS__',
			'_type' => 'LEFT'

		),

	);


}
