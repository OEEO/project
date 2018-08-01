<?php
namespace Daren\Model;
use Think\Model\ViewModel;

class AskViewModel extends ViewModel {

	public $viewFields = array(
		'A' => array(
			'id' => 'ask_id', 'content', 'answer', 'datetime',
			'_table' => '__FEEDBACK__'
		),
		'B' => array(
			'nickname',
			'_on' => 'A.member_id=B.id',
			'_table' => '__MEMBER__',
			'_type' => 'LEFT'
		),
		'C' => array(
			'path',
			'_on' => 'B.pic_id=C.id',
			'_table' => '__PICS__',
		),
		'D' => array(
			'title','member_id' => 'tips_member_id',
			'_on' => 'A.tips_id=D.id',
			'_table' => '__TIPS__',
			'_type' => 'LEFT'
		),
		'E' => array(
			'name' => 'catname',
			'_on' => 'D.category_id=E.id',
			'_table' => '__CATEGORY__'
		)
	);

}
