<?php
namespace Daren\Model;
use Think\Model\ViewModel;

Class TipsCommentViewModel extends ViewModel {
	
	public $viewFields = array(
		'A'=>array(
			'id','member_id','type_id','content','pics_group_id','datetime',
			'_table'=>'__MEMBER_COMMENT__'
		),
		'B'=>array(
            'nickname',
            '_on' => 'A.member_id=B.id',
            '_table'=>'__MEMBER__'
        ),
        'C'=>array(
            'path',
            '_on' => 'B.pic_id=C.id',
            '_table' => '__PICS__'
        )
	);
	
}


