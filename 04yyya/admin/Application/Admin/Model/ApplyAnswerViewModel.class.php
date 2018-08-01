<?php
namespace Admin\Model;
use Think\Model\ViewModel;

class ApplyAnswerViewModel extends ViewModel {
	
	protected $connection = 'DB2';
	protected $tablePrefix = 'ym_'; 
	
	public $viewFields = [
        'A'=> [
            'member_apply_id' => 'apply_id','answer_id',
            'content',
            '_table'=>'__APPLY_ANSWER__'
		],
        'B'=> [
			'type',
			'sort',
			'value' => 'ask_value',
            'content' => 'ask_content',
            '_on'=>'A.ask_id=B.id',
            '_table'=>'__APPLY__',
            '_type'=>'LEFT'
		],
        'C'=>[
			'value' => 'answer_value',
            'content'=>'answer_content',
            '_on'=>'C.type=1 and A.answer_id=C.id',
            '_table'=>'__APPLY__'
        ]
	];
	
}
