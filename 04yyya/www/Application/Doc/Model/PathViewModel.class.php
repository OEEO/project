<?php
namespace Doc\Model;
use Think\Model\ViewModel;

class PathViewModel extends ViewModel {
	
	public $viewFields = array(
		'A'=>array('id','title', 'sign','type','datetime','_table'=>'__VERSION_PATH__'),
		'B'=>array(
			'version_id',
			'datetime' => 'updatetime',
			'_on'=>'A.id=B.path_id',
			'_table'=>'__VERSION_CORRELATION_PATH__'
		),
		'C'=>array(
			'id' => 'p_id',
			'title' => 'p_title',
			'sign' => 'p_sign',
			'_on'=>'A.pid=C.id',
			'_table' => '__VERSION_PATH__'
		),
		'D' => array(
			'id' => 'm_id',
			'title' => 'm_title',
			'sign' => 'm_sign',
			'_on'=>'C.pid=D.id',
			'_table' => '__VERSION_PATH__'
		)
	);
	
}