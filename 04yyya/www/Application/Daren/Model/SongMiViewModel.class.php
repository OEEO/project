<?php
namespace Daren\Model;
use Think\Model\ViewModel;

class SongMiViewModel extends ViewModel {

	public $viewFields = array(
		'A' => array(
            'quantity',
			'_table' => '__MEMBER_WEALTH_LOG__'
		),
		'B' => array(
			'member_id'=>'wealth_member_id',
			'_on' => 'A.member_wealth_id=B.id',
			'_table' => '__MEMBER_WEALTH__'
		),
        'C' => array(
            '_on' => 'B.member_id=C.id',
            '_table' => '__MEMBER__'
        ),
		'D' => array(
			'path'=>'head_pic_path',
			'_on' => 'C.pic_id=D.id',
			'_table' => '__PICS__',
		)

	);

}
