<?php
namespace Member\Model;
use Think\Model\ViewModel;

class PrivilegeViewModel extends ViewModel {

	public $viewFields = array(
		'A' => array(
			'id','member_id','type','type_id',
			'_table' => '__PRIVILEGE__'
		),
		'B' => array(
			'nickname',
			'_on' => 'A.member_id=B.id',
			'_table' => '__MEMBER__',
			'_type' => 'LEFT'

		),
		'C' => array(
			'title',
			'_on' => 'A.type_id=C.id AND A.type=2',
			'_table' => '__RAISE__',
			'_type' => 'LEFT'
		),
		'D' => array(
			'title',
			'_on' => 'A.type_id=D.id AND A.type=1',
			'_table' => '__GOODS__',
			'_type' => 'LEFT'
		),
		'E' => array(
			'title',
			'_on' => 'A.type_id=E.id AND A.type=0',
			'_table' => '__TIPS__',
			'_type' => 'LEFT'
		),

	);

}
