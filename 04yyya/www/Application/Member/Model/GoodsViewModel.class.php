<?php
namespace Member\Model;
use Think\Model\ViewModel;

class GoodsViewModel extends ViewModel {

    public $viewFields = [
        'A'=> [
            'id',
            'price',
            'title',
            'stocks',
            'is_pass',
            'status','limit_time',
            '_table'=>'__GOODS__',
            '_type' => 'LEFT'
        ],
        'B'=> [
            'name'=>'catname',
            '_on'=>'B.id=A.category_id',
            '_table'=>'__CATEGORY__',
            '_type' => 'LEFT'
        ],
        'C'=> [
            'path'=>'path',
            '_on'=>'A.pic_id=C.id',
            '_table'=>'__PICS__',
            '_type' => 'LEFT'
        ],
        'D' => [
            'nickname',
            '_on' => 'A.member_id=D.id',
            '_table' => '__MEMBER__',
            '_type' => 'LEFT'
        ],
        'E' => [
            'shipping',
            '_on' => 'A.id=E.goods_id',
            '_table' => '__GOODS_SUB__'
        ]
    ];


}
