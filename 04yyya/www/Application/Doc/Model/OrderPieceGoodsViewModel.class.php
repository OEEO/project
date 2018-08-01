<?php
namespace Doc\Model;
use Think\Model\ViewModel;

class OrderPieceGoodsViewModel extends ViewModel {
    protected $viewFields = [
        'A' => [
            'piece_id', 'act_status' => 'piece_act_status',
            'member_id' => 'piece_member_id',
            'id' => 'piece_originator_id',
            '_table' => '__MEMBER_PIECE__'
        ],
        'B' => [
            'order_id',
            '_table' => '__ORDER_PIECE__',
            '_type' => 'LEFT',
            '_on' => 'B.piece_originator_id=A.id'
        ],
        'C' => [
            'nickname',
            '_on' => 'A.member_id=C.id',
            '_table' => '__MEMBER__',
            '_type' => 'LEFT'
        ],
        'D' => [
            'type',
            'type_id',
            'price' => 'piece_price',
            'count' => 'piece_count',
            '_on' => 'A.piece_id=D.id',
            '_type' => 'LEFT',
            '_table' => '__PIECE__'
        ],
        'F' => [
            'price' => 'total',
            'count(F.id)' => 'order_count',
            '_on' => 'B.order_id=F.id',
            '_type' => 'LEFT',
            '_table' => '__ORDER__'
        ],
        'G' => [
            'title',
            'id' => 'goods_id',
            '_on' => 'D.type_id=G.id',
            '_table' => '__GOODS__'
        ]
    ];
}