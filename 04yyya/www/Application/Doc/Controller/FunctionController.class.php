<?php
namespace Doc\Controller;
use Think\Controller;

class FunctionController extends Controller {
    function insertArea() {
        $citys = M('Citys')->where(['alt' => '市'])->select();

        echo json_encode($citys);

        for ($i = 0, $num = count($citys); $i < $num; $i++) {
            M('Citys')->add([
                'name' => '其他区',
                'pinyin' => 'Other',
                'pid' => $citys[$i]['id'],
                'alt' => '县'
            ]);
        }
    }
}
