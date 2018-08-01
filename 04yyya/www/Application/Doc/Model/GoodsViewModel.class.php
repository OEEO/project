<?php
namespace Doc\Model;
use Think\Model\ViewModel;

class GoodsViewModel extends ViewModel {
    protected $viewFields = [
        'A' => [
            'id', 'status', 'act_status', 'limit_pay_time',
            '_table' => '__ORDER__'
        ],
        'B' => [
            'id' => 'order_wares_id',
            'price',
            'type' => 'wares_type',
            'ware_id', 'server_status',
            'count(B.ware_id)' => 'total_count',
            '_on' => 'A.id=B.order_id',
            '_type' => 'LEFT',
            '_table' => '__ORDER_WARES__'
        ]
    ];
}