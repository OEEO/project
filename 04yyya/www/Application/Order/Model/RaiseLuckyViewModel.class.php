<?php
namespace Order\Model;
use Think\Model\ViewModel;

class RaiseLuckyViewModel extends ViewModel {

	public $viewFields = [
		'A' => [
			'id','member_id','lucky_status','lucky_num','raise_time_id','type',
			'_table' => '__RAISE_LUCKY__',
			'_type' => 'LEFT'
		]
	];

}
