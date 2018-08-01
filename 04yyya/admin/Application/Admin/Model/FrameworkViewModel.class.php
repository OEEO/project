<?php
namespace Admin\Model;
use Think\Model\ViewModel;

class FrameworkViewModel extends ViewModel {
	
	protected $connection = 'DB1';
	protected $tablePrefix = 'admin_'; 
	
	public $viewFields = array(
		'A'=>array('id', 'sign', 'datetime','_table'=>'__FRAMEWORK__'),
		'B'=>array(
			'sign'=>'p_sign',
			'_on'=>'A.pid=B.id',
			'_table'=>'__FRAMEWORK__'
		)
	);
	
}
