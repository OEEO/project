<?php
namespace Member\Model;
use Think\Model\ViewModel;

class TipstagViewModel extends ViewModel {

	public $viewFields = array(
        'A' => array(
            'tips_id',
            '_table' => '__TIPS_TAG__',
            '_type'=>'Left'
        ),
        'B' => array(
            'name',
            '_on' => 'B.id=A.tag_id',
            '_table' => '__TAG__'
	    )
    );

}
