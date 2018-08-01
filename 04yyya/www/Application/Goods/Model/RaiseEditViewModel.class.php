<?php
namespace Goods\Model;
use Think\Model\ViewModel;

class RaiseEditViewModel extends ViewModel {

    public $viewFields = [
        'A' => [
            'id','title','total','status','category_id','limit_time','introduction','datetime','start_time','end_time','member_id',
            '_table' => '__RAISE__',
            '_type' => 'LEFT'
        ],
        'B' => [
            'path',
            '_on' => 'A.pic_id=B.id',
            '_table' => '__PICS__',
            '_type' => 'LEFT'
        ],
        'C' => [
            'name' => 'catname',
            '_on' => 'A.category_id=C.id',
            '_table' => '__CATEGORY__',
            '_type' => 'LEFT'
        ],
        'D' => [
            'id'=>'times_id', 'price','prepay','stock','quota','is_address','limit_num','type',
            '_on' => 'A.id=D.raise_id',
            '_table' => '__RAISE_TIMES__'
        ],
        'E' => [
            'nickname',
            '_on' => 'E.id=A.member_id',
            '_table' => '__MEMBER__'
        ],
    ];

}
