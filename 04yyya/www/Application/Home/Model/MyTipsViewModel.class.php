<?php
namespace Home\Model;
use Think\Model\ViewModel;

class MyTipsViewModel extends ViewModel {

	public $viewFields = array(
		'A' => array(
			'id', 'title', 'price','restrict_num','status',
			'_table' => '__TIPS__',
			'_type' => 'LEFT'
		),
		'B' => array(
			'path' => 'mainpic',
			'_on' => 'A.pic_id=B.id',
			'_table' => '__PICS__'
		)
	);

}
