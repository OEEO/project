<?php
namespace Home\Model;
use Think\Model\ViewModel;

class KitchenTagViewModel extends ViewModel {

	public $viewFields = [
		'A' => [
			'id','space_id','tag_id',
			'_table' => '__SPACE_TAG__',
			'_type' => 'LEFT'
		],
		'B' => [
			'name' => 'tag_name',
			'_on' => 'A.tag_id=B.id',
			'_table' => '__TAG__'
		]
	];

}
