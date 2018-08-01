<?php
namespace Home\Model;
use Think\Model\ViewModel;

class MemberDarenViewModel extends ViewModel {

	public $viewFields = array(
		'A'=>array('member_id', 'introduce','contact', 'status', '_table'=>'__MEMBER_DAREN__'),
		'B'=>array(
			'nickname','status' => 'state',
			'_on'=>'B.id=A.member_id',
			'_table'=>'__MEMBER__',
            '_type'=>'LEFT'
		),
		'C'=>array(
			'path',
			'_on'=>'B.pic_id=C.id',
			'_table'=>'__PICS__',
            '_type'=>'LEFT'
		),
        'D'=>array(
            '_on'=>'A.member_id=D.member_id',
            '_table'=>'__MEMBER_TAG__',
            '_type'=>'LEFT'
        ),
        'E'=>array(
            'signature'=>'member_introduce',
            '_on'=>'A.member_id=E.member_id',
            '_table'=>'__MEMBER_INFO__',
            '_type'=>'LEFT'
        ),
        'F'=>array(
            'name'=>'city_name',
            'alt'=>'name_type',
            '_on'=>'F.id=E.citys_id',
            '_table'=>'__CITYS__',
        )

	);


}
