<?php
/**
 * Created by PhpStorm.
 * User: Cherry
 * Date: 2017/3/30 0030
 * Time: 10:12
 */

namespace Member\Model;
use Think\Model\ViewModel;


class PieceViewModel extends viewModel
{

    public $viewFields = [
        'A' => [
            'id','type','type_id',
            'type_times_id',
            'phase',
            'price',
            'count',
            'limit_time',
            'status',
            'is_cap',
            '_table' => '__PIECE__',
            '_type' => 'LEFT',
        ],
        'B' => [
            'title',
            '_on' => 'B.id = A.type_id and A.type=0',
            '_table' => '__TIPS__',
            '_type' => 'LEFT',
        ],
        'C' => [
            'path'=>'type_path',
            '_on' => 'B.pic_id = C.id',
            '_table' => '__PICS__',
            '_type' => 'LEFT',
        ],
        'D' => [
            'stop_buy_time',
            '_on' => 'D.id = A.type_times_id ',
            '_table' => '__TIPS_TIMES__',
            '_type' => 'LEFT',
        ],
        'E' => [
            'nickname',
            '_on' => 'E.id = B.member_id',
            '_table' => '__MEMBER__',
            '_type' => 'LEFT',
        ],
        'F' => [
            'path' => 'headpath',
            '_on' => 'E.pic_id = F.id',
            '_table' => '__PICS__'
        ],
    ];

}