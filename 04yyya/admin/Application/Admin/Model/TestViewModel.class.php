<?php
namespace Admin\Model;
use Think\Model\ViewModel;

class TestTipsViewModel extends ViewModel {
	
	protected $connection = 'DB3';
    protected $tablePrefix = null;

    public $viewFields = array(
		'A'=>array( '_table'=>'tips'),
		'B'=>array(
            'yamiid',
			'_on'=>'A.user_id=B.id',
			'_table'=>'member'
		),
        'C'=>array(
            'file_name',
            '_on'=>'A.id=C.tips_id',
            '_table'=>'tips_album'
        )
	);
	
}
