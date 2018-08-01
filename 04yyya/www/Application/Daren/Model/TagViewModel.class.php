<?php
namespace Daren\Model;
use Think\Model\ViewModel;

class TagViewModel extends ViewModel {

	public $viewFields = array(
		'A' => array(
			'id', 'name', 'type', 'official',
			'_table' => '__TAG__'
		),
		'B' => array(
			'tips_id',
			'_on' => 'A.id=B.tag_id',
			'_table' => '__TIPS_TAG__'
		)
	);

}
