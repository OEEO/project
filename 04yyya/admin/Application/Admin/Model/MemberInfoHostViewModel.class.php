<?php
namespace Admin\Model;
use Think\Model\ViewModel;

class MemberInfoHostViewModel extends ViewModel {
	
	protected $connection = 'DB2';
	protected $tablePrefix = 'ym_'; 
	
	public $viewFields = [
		'A'=> ['id','username','nickname', 'telephone','pic_id', '_table'=>'__MEMBER__','_type'=>'LEFT'],
        'B'=> [
			'contact',
			'citys_id',
			'identity',
			'sex',
			'is_pass',
            '_on'=>'B.member_id=A.id',
            '_table'=>'__MEMBER_INFO__',
            '_type'=>'LEFT'
		],
        'C'=> [
            'path'=>'path',
            '_on'=>'A.pic_id=C.id',
            '_table'=>'__PICS__'
		],
		'E'=> [
			'tag_id',
			'_on'=>'A.id=E.member_id',
			'_table'=>'__MEMBER_TAG__'
		],
	];
	
}
