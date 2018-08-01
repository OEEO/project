<?php
namespace Goods\Model;
use Think\Model\ViewModel;

class ArticleRelationViewModel extends ViewModel {

	public $viewFields = [
		'A' => [
			'article_id',
			'_table' => '__ARTICLE_RELATION__'
		],
		'B' => [
			'title',
			'_on' => 'A.tips_id=B.id',
			'_table' => '__TIPS__'
		],
		'C' => [
			'catname',
			'_on' => 'B.category_id=C.id',
			'_table' => '__CATEGORY__'
		],
		'D' => [
			'path',
			'_on' => 'B.pic_id=D.id',
			'_table' => '__PICS__'
		]
	];

}
