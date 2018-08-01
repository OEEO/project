<?php
namespace Member\Model;
use Think\Model\ViewModel;

class AnswerViewModel extends ViewModel {

	public $viewFields = [
		'A' => [
			'type','content','value',
			'_table' => '__APPLY__',
            '_type' => 'LEFT'
		],
		'B' => [
			'answer_id'=>'select_answer',
			'_on' => 'A.type in (1,2) and B.ask_id=A.id',
			'_table' => '__APPLY_ANSWER__',
            '_type' => 'LEFT'
		],
		'C' => [
            'content'=>'content_answer',
			'_on' => 'A.type=0 and A.id=C.ask_id',
			'_table' => '__APPLY_ANSWER__',
			'_type' => 'LEFT'
		]
	];

}
