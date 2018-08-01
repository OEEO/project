<?php
/**
 * Created by PhpStorm.
 * User: Cherry
 * Date: 2017/5/15 0015
 * Time: 14:44
 */

namespace Admin\Model;
use Think\Model\ViewModel;


class SignExportViewModel extends ViewModel {
    protected $connection = 'DB2';
    protected $tablePrefix = 'ym_';
    public $viewFields = [
        'A' => [
            'id'=> 'sign_id',
            'open_id','title','datetime','status',
            '_table' => '__SIGN__',
            '_type' => 'LEFT'
        ],
        'B' => [
            'path'=>'sign_path',
            '_on' => 'A.pic_id=B.id',
            '_table' => '__PICS__'
        ],
        'C' => [
            'nickname','city_id','sex',
            '_on' => 'C.id=A.open_id',
            '_table' => '__OPENID__'
        ],
        'D' => [
            'path'=>'head_path',
            '_on' => 'C.pic_id=D.id',
            '_table' => '__PICS__'
        ],
    ];
}