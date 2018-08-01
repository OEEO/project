<?php
namespace Home\Model;
use Think\Model\ViewModel;

class OrderWareViewModel extends ViewModel {

	public $viewFields = array(
		'A' => array(
			'id','type','ware_id', 'price',
			'_table' => '__ORDER_WARES__'
		),
		'B' => array(
			'act_status','status','tips_times_id',
			'_on' => 'A.order_id=B.id',
			'_table' => '__ORDER__'
		)
	);

}
