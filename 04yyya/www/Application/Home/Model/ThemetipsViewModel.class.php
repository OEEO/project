<?php
namespace Home\Model;
use Think\Model\ViewModel;

class ThemetipsViewModel extends ViewModel {

	public $viewFields = [
		'A' => [
			'id','title','price','datetime','status','is_pass',
			'_table' => '__TIPS__',
			'_type' => 'LEFT'
		],
		'B' => [
			'path',
			'_on' => 'A.pic_id=B.id',
			'_table' => '__PICS__',
			'_type' => 'LEFT'
		],
		'C' => [
			'_on' => 'A.id=C.tips_id',
			'_table' => '__TIPS_SUB__'
		],
        'D' => [
            'theme_id' => 'theme_theme_id',
            'type' => 'theme_type',
            '_on' => 'A.id=D.type_id',
            '_table' => '__THEME_ELEMENT__',
            '_type' => 'LEFT'
		],
        'E' => [
            'name' => 'catname',
            '_on' => 'A.category_id=E.id',
            '_table' => '__CATEGORY__',
			'_type' => 'LEFT'
		],
		'F' => [
			'id' => 'theme_id',
			'_on' => 'A.category_id=F.url',
			'_table' => '__THEME__',
			'_type' => 'LEFT'
		],
		'G' => [
			'address' => 'simpleaddress',
			'_on' => 'A.space_id=G.id',
			'_table' => '__SPACE__'
		]
	];

}
