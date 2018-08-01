<?php
namespace Home\Model;
use Think\Model\ViewModel;

class GpsTipsViewModel extends ViewModel {

	public $viewFields = array(
		'A' => array(
			'id','status',
			'_table' => '__TIPS__'
		),
		'B' => array(
			'citys_id','latitude','longitude',
			'_on' => 'A.id=B.tips_id',
			'_table' => '__TIPS_SUB__'
		),
		'C' => array(
			'start_time',
			'_on' => 'A.id=C.tips_id',
			'_table' => '__TIPS_TIMES__'
		)
	);

}
