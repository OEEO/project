<?php
/**
 * Created by PhpStorm.
 * User: Cherry
 * Date: 2017/3/31 0031
 * Time: 19:15
 */

namespace Order\Model;
use Think\Model\ViewModel;


class OrderPieceViewModel extends ViewModel {

    public $viewFields = [
        'A' => [
            'id'=>'piece_originator_id',
            'member_id',
            'end_time',
            'act_status'=>'piece_act_status',
            'status'=>'piece_status',
            '_table' => '__MEMBER_PIECE__'
        ],
        'B' => [
           'id'=>'piece_id', 'type','type_id','type_times_id','phase','price','count','limit_time','is_cap','status', 'limit_num',
            '_on' => 'A.piece_id=B.id',
            '_table' => '__PIECE__',
            '_type' => 'LEFT'
        ],
        'C' => [
            '_on' => 'A.id=C.piece_originator_id',
            '_table' => '__ORDER_PIECE__',
            '_type' => 'LEFT'
        ],
        'D' => [
            'id' => 'order_id',
            'member_id' => 'order_member_id',
            'status'=>'order_status',
            '_on' => 'C.order_id=D.id',
            '_table' => '__ORDER__',
            '_type' => 'LEFT'
        ],
        'E' => [
            'nickname',
            '_on' => 'A.member_id=E.id',
            '_table' => '__MEMBER__'
        ]
    ];

}