<?php
namespace Goods\Model;
use Think\Model\ViewModel;

class RaiseMemberViewModel extends ViewModel {

	public $viewFields = [
		'A'=>array(
			'is_identification',
			'_table' => '__MEMBER__',
		),
		'B'=>array(
			'surname','identity','weixincode',
			'_on' => 'A.id=B.member_id',
			'_table' => '__MEMBER_INFO__',
			'_type' => 'LEFT'
		),
	];

}
