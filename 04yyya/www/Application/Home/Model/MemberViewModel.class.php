<?php
namespace Home\Model;
use Think\Model\ViewModel;

class MemberViewModel extends ViewModel {

	public $viewFields = array(
		'A'=> ['id','username','nickname', 'telephone',  'datetime', '_table'=>'__MEMBER__','_type' => 'LEFT'],
		'B'=> [
			'vip_intro','signature'=>'member_introduce','sex',
			'_on'=>'B.member_id=A.id',
			'_table'=>'__MEMBER_INFO__',
			'_type' => 'LEFT',
		],
		'C'=> [
			'path',
			'_on'=>'A.pic_id=C.id',
			'_table'=>'__PICS__',
			'_type' => 'LEFT',
		],
		'D' => [
			'name'=>"city_name",
			'_on' => 'B.citys_id=D.id',
			'_table' => '__CITYS__'
		],
		'E' => [
			'introduce',
			'_on' => 'A.id=E.member_id',
			'_table'  => '__MEMBER_DAREN__'
		]
	);


}
