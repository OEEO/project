<?php
namespace Admin\Model;
use Think\Model\ViewModel;

class MemberDetailViewModel extends ViewModel {
	
	protected $connection = 'DB2';
	protected $tablePrefix = 'ym_'; 
	
	public $viewFields = array(
        'A'=>array(
            'id'=>'apply_id','is_pass','refusal_reason', 'datetime','channel',
            '_table'=>'__MEMBER_APPLY__'
        ),

        'D'=>array('id','username','nickname', 'telephone', '_on'=>'A.member_id=D.id','_table'=>'__MEMBER__','_type'=>'LEFT'),
        'C'=>array(
            'path',
            '_on'=>'D.pic_id=C.id',
            '_table'=>'__PICS__',
            '_type'=>'LEFT'
        ),
        'B'=>array(
            'interest',
            '_on'=>'B.member_id=D.id',
            '_table'=>'__MEMBER_INFO__',
            '_type'=>'LEFT'
        ),
	);
	
}
