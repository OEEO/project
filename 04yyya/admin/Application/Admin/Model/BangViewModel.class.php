<?php
namespace Admin\Model;
use Think\Model\ViewModel;

class BangViewModel extends ViewModel {
	
	protected $connection = 'DB2';
	protected $tablePrefix = 'ym_'; 
	
	public $viewFields = array(
        'A'=>array(
            'id','content','pic_group_id','type','type_id','send_time','datetime',
            '_table'=>'__BANG__',
            '_type'=>'LEFT'
        ),
        'B'=>array(
            'path',
            '_on'=>'A.pic_id=B.id',
            '_table'=>'__PICS__',
            '_type'=>'LEFT'
        ),
        'C'=>[
            'nickname',
            '_on'=>'A.member_id=C.id',
            '_table'=>'__MEMBER__',
            '_type'=>'LEFT'
        ],
        /*'D'=>[
            'id'=>'tips_id',
            '_on'=>'A.type=2 and A.type_id=D.id',
            '_table'=>'__TIPS__',
            '_type'=>'LEFT'
        ],
        'E'=>[
            'id'=>'article_id',
            '_on'=>'A.type=1 and A.type_id=E.id',
            '_table'=>'__ARTICLE__',
            '_type'=>'LEFT'
        ]*/
	);
	
}
