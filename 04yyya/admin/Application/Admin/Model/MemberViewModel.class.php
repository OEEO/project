<?php
namespace Admin\Model;
use Think\Model\ViewModel;

class MemberViewModel extends ViewModel {
	
	protected $connection = 'DB2';
	protected $tablePrefix = 'ym_'; 
	
	public $viewFields = [
		'A'=> ['id','username','nickname','pic_id','invitecode', 'telephone', 'channel','datetime','register_time','status', '_table'=>'__MEMBER__','_type'=>'LEFT'],
        'B'=> [
			'sex',
			'cover_pic_id',
			'citys_id',
            '_on'=>'B.member_id=A.id',
            '_table'=>'__MEMBER_INFO__',
            '_type'=>'LEFT'
		],
        'C'=> [
            'path'=>'path',
            '_on'=>'A.pic_id=C.id',
            '_table'=>'__PICS__'
		]
	];
	
}
