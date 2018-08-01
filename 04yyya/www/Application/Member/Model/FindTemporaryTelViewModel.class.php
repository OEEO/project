<?php
namespace Member\Model;
use Think\Model\ViewModel;

class FindTemporaryTelViewModel extends ViewModel {

	public $viewFields = [
		
		'A'=> [
			'tel',
			//'_on'=>'B.id=A.order_id',
			'_table' => '__TEMPORARY_TEL__'
		],

	];


}
