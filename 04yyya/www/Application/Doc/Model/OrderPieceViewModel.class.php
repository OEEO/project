<?php
namespace Doc\Model;
use Think\Model\ViewModel;

class OrderPieceViewModel extends ViewModel {
    protected $viewFields = [
        'A' => [
            'piece_id', 'act_status' => 'piece_act_status',
            'member_id' => 'piece_member_id',
            '_table' => '__MEMBER_PIECE__'
        ],
        'B' => [
            'order_id',
            '_table' => '__ORDER_PIECE__',
            '_type' => 'LEFT',
            '_on' => 'B.piece_originator_id=A.id'
        ],
        'C' => [
            'ware_id',
            '_on' => 'B.order_id=C.order_id',
            '_table' => '__ORDER_WARES__',
            '_type' => 'LEFT'
        ],
        'D' => [
            'type',
            'type_id',
            '_on' => 'A.piece_id=D.id',
            '_type' => 'LEFT',
            '_table' => '__PIECE__'
        ],
        'F' => [
            'price' => 'total',
            '_on' => 'B.order_id=F.id',
            '_type' => 'LEFT',
            '_table' => '__ORDER__'
        ]
    ];
}