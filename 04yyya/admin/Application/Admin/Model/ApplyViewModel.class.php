<?php
namespace Admin\Model;
use Think\Model\ViewModel;

class ApplyViewModel extends ViewModel {
	
	protected $connection = 'DB2';
	protected $tablePrefix = 'ym_'; 
	
	public $viewFields = [
        'A'=> [
			'type',
			'sort',
			'value' => 'ask_value',
            'content' => 'ask_content',
            '_table'=>'__APPLY__',
            '_type'=>'LEFT'
		],
        'B'=>[
			'type'=>'answer_type',
			'value' => 'answer_value',
            'content'=>'answer_content',
            '_on'=>'B.type=3 and B.pid=A.id',
            '_table'=>'__APPLY__'
        ]
	];
	
}
