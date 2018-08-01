<?php
namespace Admin\Model;
use Think\Model\ViewModel;

class ArticleListViewModel extends ViewModel {

	protected $connection = 'DB2';
	protected $tablePrefix = 'ym_';

	public $viewFields = array(
        'A'=>array('id', 'title', 'author', 'datetime','_table'=>'__ARTICLE__','_type'=>'LEFT'),
        'B'=>array(
            'path',
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
            'nickname',
            '_on'=>'A.member_id=D.id',
            '_table'=>'__MEMBER__'
        )
	);

}
