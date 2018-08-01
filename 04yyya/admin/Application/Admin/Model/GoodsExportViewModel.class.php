<?php
namespace Admin\Model;
use Think\Model\ViewModel;

class GoodsExportViewModel extends ViewModel {

    protected $connection = 'DB2';
    protected $tablePrefix = 'ym_';

    public $viewFields = [
        'A'=> ['id','title', 'price','stocks','is_pass','status', '_table'=>'__GOODS__'],
        'B'=> [
            'nickname'=>'member_nickname',
            '_on'=>'B.id=A.member_id',
            '_table'=>'__MEMBER__',
            '_type' => 'LEFT'
        ],
        'C'=> [
            'type' => 'marketing_type',
            'title' => 'marketing_title',
            'start_time' => 'marketing_start_time',
            'end_time' => 'marketing_end_time',
            'discount' => 'marketing_price',
            'num' => 'marketing_num',
            'allow_coupon' => 'marketing_allow_coupon',
            '_on' => 'C.type_id=A.id and C.type=1',
            '_table' => '__MARKETING__',
            '_type' => 'LEFT'
        ],
        'D' => [
            'type' => 'theme_tipsorgoods_type',
            '_on'=>'D.type_id=A.id and D.type=1',
            '_table'=>'__THEME_ELEMENT__',
            '_type' => 'LEFT'
        ],
        'E' => [
            'title' => 'theme_title',
            '_on'=>'E.id=D.theme_id',
            '_table'=>'__THEME__'
        ]
    ];

}
