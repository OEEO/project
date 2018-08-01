<?php
namespace Home\Model;
use Think\Model\ViewModel;

class SpaceListViewModel extends ViewModel {

	public $viewFields = array(
		'A' => array(
			'id','name','introduction','address','pic_group_id','latitude','longitude','proportion','volume','opening_time','context',
			'_table' => '__SPACE__'
		),
        'B' => array(
            'name'=> 'category_name',
            '_on' => 'A.category_id=B.id',
            '_table' => '__CATEGORY__'
        ),
        'C' => array(
            'name' => 'area_name',
            '_on'=>'A.city_id=C.id',
            '_table' => '__CITYS__'
        ),
        'D' => array(
            'path',
            '_on'=>'A.pic_id=D.id',
            '_table'=>'__PICS__',
			'_type'=>'LEFT'
        ),
		'E'=>array(
			'name' => 'city_name',
			'_on' => 'E.id=C.pid',
			'_table' => '__CITYS__'
		)

	);

}
