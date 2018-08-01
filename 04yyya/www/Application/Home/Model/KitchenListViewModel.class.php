<?php
namespace Home\Model;
use Think\Model\ViewModel;

class KitchenListViewModel extends ViewModel {

	public $viewFields = array(
		'A' => array(
			'id','name' => 'space_name','address','proportion','volume',
			'_table' => '__SPACE__',
			'_type' => 'LEFT'
		),
		'B' => array(
			'name' => 'area_name',
			'_on' => 'A.city_id=B.id',
			'_table' => '__CITYS__',
			'_type' => 'LEFT'
		),
		'C' => array(
			'name' => 'city_name',
			'_on' => 'B.pid=C.id',
			'_table' => '__CITYS__'
		),
        'D' => array(
            'name'=>'category_name',
            '_on'=>'A.category_id=D.id',
            '_table'=>'__CATEGORY__'
        ),
        'E' => array(
            'path',
            '_on' => 'A.pic_id=E.id',
            '_table'=>'__PICS__'
        )
	);

}
