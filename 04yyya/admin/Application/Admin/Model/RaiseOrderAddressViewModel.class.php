<?php
namespace Admin\Model;
use Think\Model\ViewModel;

class RaiseOrderAddressViewModel extends ViewModel{
    protected $connection = 'DB2';
    protected $tablePrefix = 'ym_';

    public $viewFields = [
        'A' => [
            'id' => 'raise_order_id',
            '_type'=> 'LEFT',
            '_table' => '__ORDER__'
        ],
        'B' => [
            'address',
            'linkman',
			'telephone' => 'address_phone',
            'citys_id' => 'order_city_id',
            '_on' => 'A.member_address_id=B.id',
            '_table' => '__MEMBER_ADDRESS__'
        ]
    ];
}
