<?php
namespace Member\Model;
use Think\Model\ViewModel;

class MemberViewModel extends ViewModel {

	public $viewFields = [
		'A'=> ['id','username','password','nickname','pic_id','telephone', 'invitecode', 'register_time', 'datetime', 'status', '_table'=>'__MEMBER__','_type' => 'LEFT'],
		'B'=> [
			'signature'=>'dr_introduce','sex','birth','citys_id','contact' => 'dr_contact','weixincode',
			'_on'=>'B.member_id=A.id',
			'_table'=>'__MEMBER_INFO__',
			'_type' => 'LEFT',
		],
		'C'=> [
			'path',
			'_on'=>'A.pic_id=C.id',
			'_table'=>'__PICS__',
			'_type' => 'LEFT',
		]
	];


}
