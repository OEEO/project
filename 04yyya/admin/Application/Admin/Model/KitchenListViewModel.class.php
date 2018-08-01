<?php
namespace Admin\Model;
use Think\Model\ViewModel;

class KitchenListViewModel extends ViewModel {

	protected $connection = 'DB2';
	protected $tablePrefix = 'ym_';

	public $viewFields = array(
        'A'=>array('id', 'name', 'introduction','nickname','telephone','weixincode', 'address','pic_id','pic_group_id','volume','opening_time','facility','context','_table'=>'__SPACE__','_type'=>'LEFT'),
        'B'=>array(
            'path'=>'main_path',
            '_on'=>'A.pic_id=B.id',
            '_table'=>'__PICS__',
            '_type'=>'LEFT'
        ),
        'C'=>array(
            'name' => 'catname',
            '_on'=>'A.category_id=C.id',
            '_table'=>'__CATEGORY__',
            '_type'=>'LEFT'
        ),
        'D'=>array(
            'name'=>'city_name',
            '_on'=>'A.city_id=D.id',
            '_table'=>'__CITYS__',
			'_type'=>'LEFT'
        ),
//        'E'=>array(
//            'nickname','telephone',
//            '_on'=>'A.member_id=E.id',
//            '_table'=>'__MEMBER__',
//			'_type'=>'LEFT'
//        )
	);

}
