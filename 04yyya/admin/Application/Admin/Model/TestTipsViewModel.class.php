<?php
namespace Admin\Model;
use Think\Model\ViewModel;

class TestTipsViewModel extends ViewModel {
	
	protected $connection = 'DB3';
    protected $tablePrefix = null;

    public $viewFields = array(
		'A'=>array('id','user_id','content','keyword','last_update_time','create_time','is_hide','status_is','tips_flag','top_time','service_status','catalog_id','original_catalog_id','is_draft','is_top','belong_user_id','course_identity','issue','intro','notice','is_featured','region','last_edit_admin_id','targetid','principal','spl','title','start_time1','end_time1','address1','price1','restrict_num1','address_type1','checkornot','tid','tel','checkbyadm','couseorcanstarttime','fcbl','fromvip','simpleaddress','author','checkpasstime','on_sell', '_table'=>'tips'),
		'B'=>array(
            'yamiid',
			'_on'=>'A.user_id=B.id',
			'_table'=>'member'
		),
        'C'=>array(
            'file_name',
            'ympicsid',
            '_on'=>'A.id=C.tips_id',
            '_table'=>'tips_album'
        )
	);
	
}
