<?php
namespace Daren\Model;
use Think\Model\ViewModel;

class TmpViewModel extends ViewModel {

	public $viewFields = array(
		'A' => array (
			'id' => 'use_id', 'type', 'type_id', 'status',
			'_table' => '__TEMPLATE_USE__'
		),
		'B' => array (
			'header','content','notice','ask','comment',
			'_on' => 'A.template_id=B.id',
			'_table' => '__TEMPLATE__'
		)
	);

}
