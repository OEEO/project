<?php
namespace Home\Model;
use Think\Model\ViewModel;

class CommentAtViewModel extends ViewModel {

	public $viewFields = array(
		'A' => array(
			'member_id','comment_id',
			'_table' => '__MEMBER_COMMENT_AT__'
		),
		'B' => array(
			'nickname',
			'_on' => 'A.member_id=B.id',
			'_table' => '__MEMBER__'
		)
	);

}
