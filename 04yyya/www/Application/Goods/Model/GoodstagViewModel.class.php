<?php
namespace Goods\Model;
use Think\Model\ViewModel;

class GoodstagViewModel extends ViewModel {

	public $viewFields = [
        'A' => [
            'goods_id',
            '_table' => '__GOODS_TAG__',
            '_type'=>'Left'
        ],
        'B' => [
            'name',
            '_on' => 'B.id=A.tag_id',
            '_table' => '__TAG__'
        ]
    ];

}
