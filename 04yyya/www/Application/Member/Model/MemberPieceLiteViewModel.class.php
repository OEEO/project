<?php

namespace Member\Model;
use Think\Model\ViewModel;

class MemberPieceLiteViewModel extends ViewModel {
    protected $viewFields = [
        'A' => [
            'order_id', 'piece_originator_id',
            '_table' => '__ORDER_PIECE__'
        ],

        'B' => [
            'piece_id', 'act_status', 'status',
            '_on' => 'A.piece_originator_id=B.id',
            '_type' => 'LEFT',
            '_table' => '__MEMBER_PIECE__'
        ]
    ];
}