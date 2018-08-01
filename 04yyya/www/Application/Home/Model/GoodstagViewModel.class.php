<?php
namespace Home\Model;
use Think\Model\ViewModel;

class GoodstagViewModel extends ViewModel {

	public $viewFields = array(
        'A' => array(
            'Goods_id',
            '_table' => '__GOODS_TAG__',
            '_type'=>'Left'
        ),
        'B' => array(
            'name',
            '_on' => 'B.id=A.tag_id',
            '_table' => '__TAG__'
	    )
    );

}
