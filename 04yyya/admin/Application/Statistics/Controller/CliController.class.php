<?php
namespace Statistics\Controller;
use Statistics\Common\Controller\MainController;

Class CliController extends MainController {

    Public function wwwLogs(){
        $file = I('get.file') . '.log';
        $this->splitAccessLogs($file);
    }

    //批量添加
    private function addAll($table, $dataAll){
        if(empty($dataAll))return 0;
        $arrKey = $arrVal = $chars = [];

        //组装数据
        $arrKey = $arrVal = [];
        foreach($dataAll as $datas){
            $arr = $vals = [];
            foreach($datas as $k => $v){
                $arr[] = $k;
                if($v === null)
                    $vals[] = "null";
                else
                    $vals[] = "'" . addslashes($v) . "'";
            }
            if(empty($arrKey))$arrKey = $arr;
            if($arrKey != $arr)continue;
            $arrVal[] = "(" . join(",", $vals) . ")";
        }
        $sql = "Insert into `ym_{$table}` (`". join('`,`', $arrKey) ."`) values ". join(',', $arrVal) .";";
        return M()->execute($sql);
    }

    Private function splitAccessLogs($file){
        $filePath = C('logDirPath') . $file;
        if(!is_file($filePath))return false;
        set_time_limit(0);
        $fp = fopen($filePath, 'r');
        $datas = [];

        $num = 0;
        while(($line = fgets($fp)) !== false) {
            if(preg_match('/^(\d+\.\d+\.\d+\.\d+).+?\[(.+?) \+0800\] "([A-Z]{3,4}) (.+?) (.+?)" (\d+) (\d+) "(.*?)" "(.*?)".*$/', $line, $arr)){
                $num ++;
                $date = \DateTime::createFromFormat('d/M/Y:H:i:s', $arr[2]);
                $datetime = $date->format('Y-m-d H:i:s');

                $datas[] = [
                    'ip' => ip2long($arr[1]),
                    'datetime' => $datetime,
                    'method' => $arr[3]=='POST'?1:0,
                    'url' => $arr[4],
                    'protocol' => $arr[5],
                    'status' => $arr[6],
                    'size' => $arr[7],
                    'referer' => $arr[8],
                    'browser' => $arr[9]
                ];

                if($num % 1000 == 0){
                    $this->addAll('accesslogs', $datas);
                    $datas = [];
                    echo "已成功处理 {$num} 条数据\n";
                    ob_flush();
                    flush();
                }
            }
        }

        echo "全部完成!\n";

        //备份并创建新的log文件
        $dir = C('logBakPath') . date('Y_m_d') . '/';
        if(!is_dir($dir))mkdir($dir);
        $newfile = date('H_i_s') . $file;
        rename($filePath, $dir . $newfile);
        $fp=fopen($filePath, "w");
        fclose($fp);
    }

}