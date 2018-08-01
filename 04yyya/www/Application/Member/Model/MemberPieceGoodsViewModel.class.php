<?php
/**
 * Created by PhpStorm.
 * User: Cherry
 * Date: 2017/3/30 0030
 * Time: 10:12
 */

namespace Member\Model;
use Think\Model\ViewModel;


class MemberPieceGoodsViewModel extends viewModel
{

    public $viewFields = [
        'A' => [
            'id',
            'member_id',
            'end_time',
            'act_status'=>'p_act_status',
            'status'=>'p_status',
            '_table' => '__MEMBER_PIECE__'
        ],
        'B' => [
            'nickname',
            '_on' => 'B.id = A.member_id',
            '_table' => '__MEMBER__',
            '_type' => 'LEFT',
        ],
        'C' => [
            'path' => 'headpath',
            '_on' => 'B.pic_id = C.id',
            '_table' => '__PICS__',
            '_type' => 'LEFT',
        ],
        'D' => [
            'id'=>'piece_id',
            'type'=>'piece_type',
            'type_id'=>'piece_type_id',
            'type_times_id'=>'piece_type_times_id',
            'phase'=>'piece_phase',
            'price'=>'piece_price',
            'count'=>'piece_count',
            'is_cap',
            'limit_time'=>'piece_limit_time',
            'status'=>'piece_status',
            '_on' => 'D.id = A.piece_id',
            '_table' => '__PIECE__',
            '_type' => 'LEFT',
        ],
        'E' => [
            'title','price',
            '_on' => 'E.id = D.type_id and D.type=1',
            '_table' => '__GOODS__',
            '_type' => 'LEFT',
        ],
        'F' => [
            'path'=>'type_path',
            '_on' => 'E.pic_id = F.id',
            '_table' => '__PICS__',
            '_type' => 'LEFT',
        ],
        'G' => [
            'stop_buy_time'=>'tips_stop_buy_time',
            '_on' => 'G.id = D.type_times_id ',
            '_table' => '__TIPS_TIMES__'
        ],
    ];

}