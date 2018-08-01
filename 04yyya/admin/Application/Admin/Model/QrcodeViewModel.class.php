<?php
namespace Admin\Model;
use Think\Model\ViewModel;

class QrcodeViewModel extends ViewModel {

	protected $connection = 'DB2';
	protected $tablePrefix = 'ym_';

	public $viewFields = [
		'A'=> ['id', 'channel', 'title', 'url', 'status', 'media_id', 'context', 'datetime','_table'=>'__QRCODE__','_type'=>'LEFT'],
		'B'=> [
			'path',
			'_on'=>'A.pic_id=B.id',
			'_table'=>'__PICS__'
		]
	];

}
