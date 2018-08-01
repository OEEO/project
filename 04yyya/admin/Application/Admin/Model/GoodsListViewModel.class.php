<?php
namespace Admin\Model;
use Think\Model\ViewModel;

class GoodsListViewModel extends ViewModel {

    protected $connection = 'DB2';
    protected $tablePrefix = 'ym_';

    public $viewFields = [
        'A'=> ['id','id' => 'goods_id','price','title','is_pass','stocks','status', '_table'=>'__GOODS__',
            '_type'=>'LEFT'],
        'B'=> [
            'path'=>'pics_path',
            '_on'=>'A.pic_id=B.id',
            '_table'=>'__PICS__',
            '_type'=>'LEFT'
        ],
        'C'=> [
            'name' => 'category_name',
            '_on' => 'A.category_id=C.id',
            '_table' => '__CATEGORY__',
            '_type'=>'LEFT'
        ],
        'D'=> [
            'nickname' => 'member_nickname',
            'telephone' => 'member_telephone',
            '_on' => 'A.member_id=D.id',
            '_table' => '__MEMBER__'
        ]
    ];
}