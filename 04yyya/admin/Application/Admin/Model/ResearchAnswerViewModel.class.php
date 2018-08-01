<?php
namespace Admin\Model;
use Think\Model\ViewModel;

class ResearchAnswerViewModel extends ViewModel {
	
	protected $connection = 'DB2';
	protected $tablePrefix = 'ym_'; 
	
	public $viewFields = [
        'A'=> [
			'id','member_id' ,'datetime',
            '_table'=>'__MEMBER_APPLY__',
			'_type'=>'LEFT'
		],
        'B'=> [
			'nickname',
            '_on'=>'A.member_id=B.id',
            '_table'=>'__MEMBER__'
		],
	];
	
}
