<?php
namespace Admin\Model;
use Think\Model\ViewModel;

class MemberInformationViewModel extends ViewModel {
	
	protected $connection = 'DB2';
	protected $tablePrefix = 'ym_'; 
	
	public $viewFields = [
		'A'=> ['id','username','nickname', 'telephone','channel','datetime','register_time','status', '_table'=>'__MEMBER__','_type'=>'LEFT'],
        'B'=> [
			'sex',
			'birth',
			'address',
			'weixincode','identity','surname',
            '_on'=>'B.member_id=A.id',
            '_table'=>'__MEMBER_INFO__',
            '_type'=>'LEFT'
		],
        'C'=> [
            'path'=>'path',
            '_on'=>'A.pic_id=C.id',
            '_table'=>'__PICS__',
			'_type'=>'LEFT'
		],
		'D'=> [
			'bank_id'=>'bank_id',
			'name'=>'member_real_name',
			'number'=>'bank_number',
			'_on'=>'A.id=D.member_id',
			'_table'=>'__MEMBER_BANK__',
			'_type'=>'LEFT'
		],
		'E'=> [
			'name'=>'bank_name',
			'_on'=>'E.id=D.bank_id',
			'_table'=>'__BANK__',
			'_type'=>'LEFT'
		],
		'F' => [
			'name' => 'city_name',
			'_on' => 'B.citys_id=F.id',
			'_table' => '__CITYS__'

		],
	];
	
}
