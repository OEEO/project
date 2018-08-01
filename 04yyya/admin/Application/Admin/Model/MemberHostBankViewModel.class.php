<?php
namespace Admin\Model;
use Think\Model\ViewModel;

class MemberHostBankViewModel extends ViewModel {
	
	protected $connection = 'DB2';
	protected $tablePrefix = 'ym_'; 
	
	public $viewFields = [
        'A'=> [
			'member_id',
			'name'=>'realname',
            'number'=>'bank_number',
			'bank_id',
            '_table'=>'__MEMBER_BANK__'
		],
		'B'=> [
			'name'=>'bank_name',
			'_on'=>'A.bank_id=B.id',
			'_table'=>'__BANK__'
		],
	];
	
}
