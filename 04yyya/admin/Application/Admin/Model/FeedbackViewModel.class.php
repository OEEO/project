<?php
namespace Admin\Model;
use Think\Model\ViewModel;

class FeedbackViewModel extends ViewModel {
	
	protected $connection = 'DB2';
	protected $tablePrefix = 'ym_'; 
	
	public $viewFields = [
		'A'=> ['id','type','type_id','content','answer','answer_member_id','datetime','_table'=>'__FEEDBACK__'],
		'B'=> [
			'nickname',
			'telephone',
			'_on'=>'A.member_id=B.id',
			'_table'=>'__MEMBER__',
			'_type'=>'LEFT'
		],
		'C'=> [
			'nickname'=>'answer_member_nickname',
			'_on'=>'A.answer_member_id=C.id',
			'_table'=>'__MEMBER__'
		]
	];
	
}
