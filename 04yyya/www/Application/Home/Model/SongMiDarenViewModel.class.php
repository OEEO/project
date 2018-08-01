<?php
namespace Home\Model;
use Think\Model\ViewModel;

class SongMiDarenViewModel extends ViewModel {

	public $viewFields = array(
		'A'=>array('member_id', '_table'=>'__MEMBER_WEALTH__','_type'=>'LEFT'),
		'B'=>array(
			'_on'=>'B.member_wealth_id=A.id',
			'_table'=>'__MEMBER_WEALTH_LOG__'
		),
        'C'=>array(
            'nickname',
            '_on'=>'A.member_id=C.id',
            '_table'=>'__MEMBER__'
        ),
		'D'=>array(
			'path',
			'_on'=>'C.pic_id=D.id',
			'_table'=>'__PICS__',
		)
	);


}
