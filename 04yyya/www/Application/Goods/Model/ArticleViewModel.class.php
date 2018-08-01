<?php
namespace Goods\Model;
use Think\Model\ViewModel;

class ArticleViewModel extends ViewModel {

	public $viewFields = [
		'A' => [
			'id','title','author','category_id','content','pic_id','status','member_id','datetime',
			'_table' => '__ARTICLE__',
			'_type' => 'LEFT'
		],
		'B' => [
			'name' => 'catname',
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
