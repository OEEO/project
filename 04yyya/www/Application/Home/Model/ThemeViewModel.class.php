<?php
namespace Home\Model;
use Think\Model\ViewModel;

class ThemeViewModel extends ViewModel {

	public $viewFields = [
        'A' => [
			'id','title','url','sort','type','content','datetime','pic_group_id',
            '_table' => '__THEME__'
		],
		'B' => [
			'path',
			'_on' => 'A.pic_id=B.id',
			'_table' => '__PICS__'
		]
	];

}
