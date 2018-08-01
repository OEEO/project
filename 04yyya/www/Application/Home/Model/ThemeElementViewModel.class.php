<?php
namespace Home\Model;
use Think\Model\ViewModel;

class ThemeElementViewModel extends ViewModel {

	public $viewFields = array(
		'A' => array(
			'id', 'theme_id', 'type', 'type_id', 'sort',
			'_table' => '__THEME_ELEMENT__',
			//'_type' => 'LEFT'
		),
		'B' => array(
			'price', 'start_time', 'end_time',
			'_on' => 'A.type=B.type and A.type_id=B.type_id',
			'_table' => '__MARKETING__'
		)
	);

}
