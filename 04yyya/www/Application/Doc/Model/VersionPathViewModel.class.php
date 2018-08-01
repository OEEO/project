<?php
namespace Doc\Model;
use Think\Model\ViewModel;

class VersionPathViewModel extends ViewModel {
	
	public $viewFields = array(
		'A'=>array('id','title', 'sign','datetime','_table'=>'__VERSION_PATH__'),
		'B'=>array(
			'version_id',
			'_on'=>'A.id=B.path_id',
			'datetime' => 'updatetime',
			'_table'=>'__VERSION_CORRELATION_PATH__'
		),
		'C'=>array(
			'title' => 'controller_title',
			'sign' => 'controller_sign',
			'_on' => 'A.pid=C.id',
			'_table'=>'__VERSION_PATH__'
		),
		'D'=>array(
			'title' => 'module_title',
			'sign' => 'module_sign',
			'_on' => 'C.pid=D.id',
			'_table'=>'__VERSION_PATH__'
		),
		'E'=>array(
			'url','getparams','postparams','success','error',
			'_on'=>'B.id=E.api_id',
			'_table'=>'__VERSION_DOC__'
		)
	);
	
}