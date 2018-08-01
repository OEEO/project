<?php
namespace Admin\Model;
use Think\Model\ViewModel;

class ApplyTypeViewModel extends ViewModel {
	
	protected $connection = 'DB2';
	protected $tablePrefix = 'ym_'; 
	
	public $viewFields = array(
		'A'=>array('id'=>'apply_id','member_id','is_pass','refusal_reason','_table'=>'__MEMBER_APPLY__'),
        'D'=>array(
            'id' => 'category_id',
            'name'=>'apply_type',
            '_on'=>'A.type=2 and type_id=D.id',
            '_table'=>'__CATEGORY__'
        )
	);
	
}
