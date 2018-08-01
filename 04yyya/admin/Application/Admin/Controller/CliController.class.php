<?php
namespace Admin\Controller;
use Admin\Controller\MainController;

class CliController extends MainController{
    Public function __construct()
    {

    }

    public function orderExport() {
        $tags = $this->m2('TipsTag')->field('tips_id')->where(['tag_id' => 65])->buildSql();
        $where[] = "A.id in " . $tags;


    }
}
