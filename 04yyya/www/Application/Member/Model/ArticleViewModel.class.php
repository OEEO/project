<?php
namespace Member\Model;
use Think\Model\ViewModel;

class ArticleViewModel extends ViewModel {

	public $viewFields = [
		'A' => [
			'id','member_id','title','author','content','datetime','pic_id','category_id',
			'_table' => '__ARTCLE__'
		],
		'B' => [
			'name'=>'category_name',
			'_on' => 'A.category_id=B.id',
			'_table' => '__CATEGORY__',
            '_type' => 'LEFT'
		],
		'C' => [
            'path',
			'_on' => 'A.pic_id=C.id',
			'_table' => '__PICS__',
			'_type' => 'LEFT'
		]
	];

}
