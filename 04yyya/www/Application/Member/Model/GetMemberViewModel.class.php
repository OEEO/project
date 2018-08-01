<?php
namespace Member\Model;
use Think\Model\ViewModel;

class GetMemberViewModel extends ViewModel {

	public $viewFields = array(
		'A'=>array('id','username','nickname', 'telephone',  'datetime', '_table'=>'__MEMBER__','_type' => 'LEFT'),
		'B'=>array(
			'vip_intro','signature','sex',
			'_on'=>'B.member_id=A.id',
			'_table'=>'__MEMBER_INFO__',
			'_type' => 'LEFT',
		),
		'C'=>array(
			'path',
			'_on'=>'B.portrait=C.id',
			'_table'=>'__PICS__',
			'_type' => 'LEFT',
		) ,
		'D' => array(
			'name'=>"city_name",
			'_on' => 'B.citys_id=D.id',
			'_table' => '__CITYS__'
		),
	);


}
