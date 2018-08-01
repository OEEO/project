<?php
namespace Admin\Model;
use Think\Model\ViewModel;

class RaiseFeedbackViewModel extends ViewModel {

	protected $connection = 'DB2';
	protected $tablePrefix = 'ym_';

	public $viewFields = [
		'A' => [
			'id','member_id','type_id','content','answer',
			'_table' => '__FEEDBACK__',
			'_type' => 'LEFT'
		],
		'B' => [
			'title',
			'_on' => 'A.type_id=B.id',
			'_table' => '__RAISE__',
			'_type' => 'LEFT'
		]

	];

}
