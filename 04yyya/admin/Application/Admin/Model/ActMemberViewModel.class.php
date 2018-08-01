<?php
namespace Admin\Model;
use Think\Model\ViewModel;

class ActMemberViewModel extends ViewModel {

	protected $connection = 'DB1';
	protected $tablePrefix = 'admin_';

	public $viewFields = [
			'A'=> ['id', 'get' => 'gt', 'post' => 'pt', 'datetime', '_table'=>'__ACT_LOG__'],
			'B'=> [
				'username',
				'_on'=>'A.user_id=B.id',
				'_table'=>'__USER__'
			]
	];

}
