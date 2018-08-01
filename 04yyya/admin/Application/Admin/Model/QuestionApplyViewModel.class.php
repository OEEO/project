<?php
namespace Admin\Model;
use Think\Model\ViewModel;

class QuestionApplyViewModel extends ViewModel {

	protected $connection = 'DB2';
	protected $tablePrefix = 'ym_';

	public $viewFields = [
			'A'=> ['id', 'user_id', 'get' => 'gt', 'post' => 'pt', 'datetime','_table'=>'__ACT_LOG__'],
			'B'=> [
				'name',
				'_on'=>'A.framework_id=B.id',
				'_table'=>'__FRAMEWORK__'
			],
			'C'=> [
				'name' => 'pname',
				'_on'=>'B.pid=C.id',
				'_table'=>'__FRAMEWORK__'
			]
	];

}
