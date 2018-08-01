<?php
namespace Admin\Model;
use Think\Model\ViewModel;

class UserViewModel extends ViewModel {
	
	protected $connection = 'DB1';
	protected $tablePrefix = 'admin_'; 
	
	public $viewFields = array(
		'A'=>array('id','username', 'group_id','datetime','email','telephone','_table'=>'__USER__'),
		'B'=>array(
			'name'=>'groupname',
			'_on'=>'A.group_id=B.id',
			'_table'=>'__GROUP__'
		)
	);
	
}
