<?php
namespace Admin\Model;
use Think\Model\ViewModel;

class CommentListViewModel extends ViewModel {

    protected $connection = 'DB2';
    protected $tablePrefix = 'ym_';

    public $viewFields = [
        'A'=> [
            'id','stars','content','pics_group_id','status','type','type_id','pid',
            '_table'=>'__MEMBER_COMMENT__'
        ],
        'B'=> [
            'nickname'=>'member_nickname',
            '_on'=>'A.member_id=B.id',
            '_table'=>'__MEMBER__',
            '_type' => 'LEFT'
        ],
        'C'=> [
            'title' => 'tips_title',
            '_on'=>'A.type=0 and C.id=A.type_id',
            '_table'=>'__TIPS__',
            '_type'=>'LEFT'
        ],
        'D'=> [
            'nickname'=>'reply_nickname',
            '_on'=>'C.member_id=D.id',
            '_table'=>'__MEMBER__',
            '_type'=>'LEFT'
        ],
        'E'=> [
            'title' => 'goods_title',
            '_on'=>'A.type=1 and C.id=A.type_id',
            '_table'=>'__GOODS__'
        ]
    ];

}
