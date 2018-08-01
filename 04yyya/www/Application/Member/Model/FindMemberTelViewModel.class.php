<?php
namespace Member\Model;
use Think\Model\ViewModel;

class FindMemberTelViewModel extends ViewModel {

	public $viewFields = [
		
		'A'=> [
			'telephone',
			//'_on'=>'B.id=A.order_id',
			'_table' => '__MEMBER__'
		],

	];


}
