<?php
namespace Home\Model;
use Think\Model\ViewModel;

class FacilityListViewModel extends ViewModel {

	public $viewFields = array(
		'A' => array(
            'id','name',
			'_table' => '__FACILITY__'
		),
        'B' => array(
            '_on'=>'A.id=B.facility_id',
            '_table'=>'__SPACE_FACILITY__'
        )
	);

}
