<?php
namespace Admin\Model;
use Think\Model\ViewModel;

class OrderDetailViewModel extends ViewModel {

    protected $connection = 'DB2';
    protected $tablePrefix = 'ym_';

    public $viewFields = [
        'A'=> ['title','price','_table'=>'__GOODS__','_type'=>'LEFT'],
        'B'=> [
            '_on'=>'B.ware_id=A.id',
            '_table'=>'__ORDER_WARES__',
            '_type'=>'LEFT'
        ],
        'C'=> [
            '_on'=>'C.id=B.order_id',
            '_table'=>'__ORDER__',
            '_type'=>'LEFT'
        ],
        'D'=> [
            'name'=>'category_name',
            '_on'=>'D.id=A.category_id',
            '_table'=>'__CATEGORY__',
            '_type'=>'LEFT'
        ],
        'E'=> [
            'path'=>'pics_path',
            '_on'=>'E.id=A.pic_id',
            '_table'=>'__PICS__',
            '_type'=>'LEFT'
        ]
    ];
}