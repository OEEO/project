<?php
namespace Member\Model;
use Think\Model\ViewModel;

class BangListViewModel extends ViewModel {

	public $viewFields = array(
		'A' => array(
			'id'=>'bang_id','member_id','pic_id','pic_group_id', 'content', 'type','type_id','send_time','datetime',
			'_table' => '__BANG__'
		),
		'B' => array(
			'nickname',
			'_on' => 'A.member_id=B.id',
			'_table' => '__MEMBER__',
			'_type' => 'LEFT'
		),
		'C' => array(
			'path'=>'head_pic_path',
			'_on' => 'B.pic_id=C.id',
			'_table' => '__PICS__',
            '_type' => 'LEFT'
		),
        'D' => array(
            'sum(amount)'=>'count',
            '_on' => 'A.id=D.target_id',
            '_table' => '__GIVE__'
        )
	);

}
