<?php
namespace Daren\Model;
use Think\Model\ViewModel;

class BankViewModel extends ViewModel {

	public $viewFields = array(
		'A' => array(
			'name','color',
			'_table' => '__BANK__'
		),
		'B' => array(
			'id','number','status',
			'_on' => 'A.id=B.bank_id',
			'_table' => '__MEMBER_BANK__',
		),
        'C' => array(
            'path',
            '_on' => 'A.pic_id=C.id',
            '_table' => '__PICS__'
        )
	);

}
