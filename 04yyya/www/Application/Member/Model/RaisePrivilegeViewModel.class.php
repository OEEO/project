<?php
namespace Member\Model;
use Think\Model\ViewModel;

class RaisePrivilegeViewModel extends ViewModel {

	public $viewFields = array(

		'A' => array(
			'id',
			'member_id'=>'receive_id',
			'start_time','end_time','order_id',
			'_table' => '__MEMBER_PRIVILEGE__',
			'_type' => 'LEFT'
		),
		'B' => array(
			'id'=>'privilege_id',
			'member_id'=>'originate_id',
			'type',
			'type_id',
			'tips_times_id',
			'_on' => 'A.privilege_id=B.id',
			'_table' => '__PRIVILEGE__',
			'_type' => 'LEFT'
		),
		'C' => array(
			'nickname'=>'receive_nickname',
			'_on' => 'A.member_id=C.id',
			'_table' => '__MEMBER__',
			'_type' => 'LEFT'

		),
		'D' => array(
			'nickname'=>'originate_nickname',
			'_on' => 'B.member_id=D.id',
			'_table' => '__MEMBER__',
			'_type' => 'LEFT'

		),
		'E' => array(
			'title',
			'_on' => 'B.type_id=E.id',
			'_table' => '__RAISE__',
			'_type' => 'LEFT'
		),
		'F' => array(
			'is_address',
			'title'=>'raise_times_title',
			'_on' => 'B.tips_times_id=F.id',
			'_table' => '__RAISE_TIMES__'
		),
	);

}
