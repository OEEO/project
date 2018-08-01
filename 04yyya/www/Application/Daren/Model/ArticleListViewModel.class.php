<?php
namespace Daren\Model;
use Think\Model\ViewModel;

class ArticleListViewModel extends ViewModel {

	public $viewFields = array(
		'A' => array(
			'id' => 'article_id', 'member_id', 'title','author', 'datetime',
			'_table' => '__ARTICLE__'
		),
		'B' => array(
			'nickname',
			'_on' => 'A.member_id=B.id',
			'_table' => '__MEMBER__',
            '_type' => 'LEFT'
		),
        'C' => array(
            'path'=>'article_path',
            '_on' => 'A.pic_id=C.id',
            '_table'=> '__PICS__'
        ),
        'D' => array(
            'path' => 'member_path',
            '_on' => 'A.member_id=D.id',
            '_table'=> '__PICS__'
        )
	);

}
