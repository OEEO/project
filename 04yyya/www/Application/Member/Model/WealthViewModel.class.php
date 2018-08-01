<?php
namespace Member\Model;
use Think\Model\ViewModel;

class WealthViewModel extends ViewModel {

	public $viewFields = [
		'A' => [
			'id','type','sum(A.quantity)' => 'sum',
			'_table' => '__MEMBER_WEALTH_LOG__'
		],
		'B' => [
			'member_id','wealth',
			'_on' => 'A.member_wealth_id=B.id',
			'_table' => '__MEMBER_WEALTH__'
		]
	];

}
