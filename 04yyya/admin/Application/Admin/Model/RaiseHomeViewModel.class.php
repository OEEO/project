<?php
namespace Admin\Model;
use Think\Model\ViewModel;

class RaiseHomeViewModel extends ViewModel {

    protected $connection = 'DB2';
    protected $tablePrefix = 'ym_';

    public $viewFields = [
        'A'=>['id','category_id','title','total','pic_id','start_time','end_time','status','datetime','city_id','_table'=>'__RAISE__', '_type' => 'LEFT'],
        'B'=>[
            'id'=>'member_id',
            'nickname',
            'telephone',
            '_on'=>'B.id = A.member_id',
            '_table'=>'__MEMBER__',
            '_type' => 'LEFT'
        ],
        'C'=>[
            'id'=>'raise_times_id',
            'title'=>'raise_times_title',
            'prepay'=>'raise_times_prepay',
            'price'=>'raise_times_price',
            'is_address','is_buy','is_realname',
            '_on'=>'C.raise_id = A.id',
            '_table'=>'__RAISE_TIMES__',
            '_type' => 'LEFT'
        ],
        'D'=>[
            'id'=>'raise_id',
            'title'=>'raise_title',
            'content',
            'total',
            'city_id',
            '_on'=>'D.id = C.raise_id',
            '_table'=>'__RAISE__',
            '_type' => 'LEFT'
        ],
        'E' => [
            'name' => 'catname',
            '_on' => 'D.category_id=E.id',
            '_table' => '__CATEGORY__'
        ],
        'F' => [
            'weight',
            '_on' => 'A.id=F.r_id',
            '_table' => '__HOME__',
            '_type' => 'RIGHT'
        ]
    ];

}
