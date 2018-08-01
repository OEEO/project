<?php
namespace Admin\Model;
use Think\Model\ViewModel;

class SpaceApplyViewModel extends ViewModel {
    protected $connection = 'DB2';
    protected $tablePrefix = 'ym_';
	public $viewFields = array(
		'A' => array(
			'id','aim','num','budget','month','day','time','contacts','telephone','context','remark','status',
			'_table' => '__SPACE_APPLY__',
			'_type' => 'LEFT'
		),
		'B' => array(
            'name',
            '_on' => 'A.space_id=B.id',
            '_table'=>'__SPACE__',
            '_type' => 'LEFT'
        ),
        'C' => array(
            'path',
            '_on' => 'B.pic_id=C.id',
            '_table' => '__PICS__'
        )
	);

}
