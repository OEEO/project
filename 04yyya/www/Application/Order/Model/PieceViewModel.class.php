<?php
/**
 * Created by PhpStorm.
 * User: Cherry
 * Date: 2017/3/31 0031
 * Time: 19:15
 */

namespace Order\Model;
use Think\Model\ViewModel;


class PieceViewModel extends ViewModel {

    public $viewFields = [
        'A' => [
            'id'=>'piece_originator_id',
            'member_id','piece_id','end_time',
            'act_status'=>'piece_act_status',
            'status'=>'piece_status',
            '_table' => '__MEMBER_PIECE__'
        ],
        'B' => [
           'id'=>'piece_id', 'type','type_id','type_times_id','phase','price','count','limit_time','is_cap','status',
            '_on' => 'A.piece_id=B.id',
            '_table' => '__PIECE__'
        ]
    ];

}