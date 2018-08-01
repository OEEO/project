<?php
namespace Admin\Model;
use Think\Model\ViewModel;

class HomeViewModel extends ViewModel {

	public $viewFields = array(
		'A' => array(
			'id','t_id','r_id','type','weight',
			'_table' => '__HOME__'
		)
	);

}
