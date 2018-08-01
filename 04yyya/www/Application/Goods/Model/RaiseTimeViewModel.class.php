<?php
namespace Goods\Model;
use Think\Model\ViewModel;

class RaiseTimeViewModel extends ViewModel {
    public $viewFields = [
        "A" => [
            "id","raise_id","title","price","content",
            "_type" => "LEFT",
            "_table" => "__RAISE_TIMES__"
        ],
        "B" => [
            "id" => "raise_item_id",
            "pic_id",
            "title" => "raise_title",
            "status","end_time",
            "total","totaled",
            "_on" => "A.raise_id=B.id",
            "_type" => "LEFT",
            "_table" => "__RAISE__"
        ],
        "C" => [
            "surname",
            "_on" => "B.member_id=C.member_id",
            "_table" => "__MEMBER_INFO__"
        ],
        "D" => [
            'path',
            '_on' => 'B.pic_id=D.id',
            '_table' => '__PICS__',
        ]
    ];
}