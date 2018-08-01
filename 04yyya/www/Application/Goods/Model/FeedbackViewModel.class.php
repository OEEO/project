<?php
namespace Goods\Model;
use Think\Model\ViewModel;

class FeedbackViewModel extends ViewModel {

	public $viewFields = array(
		'A' => array(
			'id','member_id','tips_id','content','answer','datetime',
			'_table' => '__FEEDBACK__'
		),
		'B' => array(
			'nickname',
			'_on' => 'A.member_id=B.id',
			'_table' => '__MEMBER__'
		),
		'C' => array(
			'path',
			'_on' => 'B.pic_id=C.id',
			'_table' => '__PICS__'
		)
	);

}
