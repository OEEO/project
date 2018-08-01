<?php

namespace Home\Controller;
use Think\Controller;

class TestController extends Controller
{

    public function downpic(){
        $path = I('get.path');

        $newname = getPicAndSave($path);

        var_dump($newname);
    }

}