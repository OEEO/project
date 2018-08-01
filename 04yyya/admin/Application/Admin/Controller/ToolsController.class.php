<?php
/**
 * Created by PhpStorm.
 * User: Dean
 * Date: 17/3/24
 * Time: 上午11:59
 */

namespace Admin\Controller;

class ToolsController extends MainController
{
    Protected $pagename = '运营工具';

    //会员DIY特色图片工具
    public function DIYImage(){
        $this->actname = 'DIY图片';
        $act = I('get.act');
        switch($act){
            case 'add':
                $this->view();
                break;
            case 'update':
                $diy_id = I('get.diy_id');
                $data = $this->m2('diyimage')->where(['id' => $diy_id])->find();
                $data['bg_path'] = 'http://img.'. WEB_DOMAIN .'/' . $this->m2('pics')->where(['id' => $data['bg_pic_id']])->getField('path');
                $size = getimagesize($data['bg_path']);
                $data['bg_width'] = $size[0];
                $size = explode(',', $data['box_size']);
                $data['box_width'] = $size[0];
                $data['box_height'] = $size[1];
                $datas = json_decode($data['datas'], true);
                $dt = [];
                foreach($datas as $row){
                    $row['path'] = 'http://img.'. WEB_DOMAIN .'/' . $this->m2('pics')->where(['id' => $row['pic_id']])->getField('path');
                    $dt[] = $row;
                }
                $data['datas'] = $dt;
                $this->assign($data);
                $this->view();
                break;
            case 'save':
                $diy_id = I('post.diy_id', null);
                $_POST['datas'] = json_encode($_POST['datas']);
                $_POST['textdatas'] = json_encode($_POST['textdatas']);
                if(empty($diy_id)){
                    $diy_id = $this->m2('diyimage')->add($_POST);
                    $this->success($diy_id);
                }else{
                    unset($_POST['diy_id']);
                    $this->m2('diyimage')->where(['id' => $diy_id])->save($_POST);
                    $this->success('模板修改成功!');
                }
                break;
            case 'del':
                $diy_id = I('post.diy_id', null);
                $this->m2('DiyimageUsers')->where(['diyimage_id' => $diy_id])->delete();
                $this->m2('Diyimage')->where(['id' => $diy_id])->delete();
                $this->success('模板删除成功!');
                break;
            default:
                $data = $this->m2()->query("Select A.*,count(B.id) as count from ym_diyimage A left join ym_diyimage_users B on A.id=B.diyimage_id group by A.id order by A.id desc");
                $this->assign('datalist', $data);
                $this->assign('lang', [
                    'id' => 'ID',
                    'title' => 'DIY模板标题',
                    'count' => '生成数量',
                    'datetime' => '创建时间'
                ]);
                $this->assign('operations', [
                    '修改' => "location.href='DIYImage.html?act=update&diy_id=%id'",
                    '删除' => "del(%id)"
                ]);
                $this->view();
        }
    }

    /*
     * 短网址列表
     * author:cherry
     * date:2017-04-10
     * */
    public function shortUrl_list(){
        $this->actname = '短网址列表';
        $act = I('post.typename');
        $filename=COMMON_PATH."/Conf/shorturl.txt";
        switch($act){
            case 'add':
                $short_url = I('post.shorturl');
                if(empty($short_url))$this->error('添加失败!');
                if(file_exists($filename)){//存在该文本
                    $handle=fopen($filename,"a+");
                    $str=fwrite($handle,$short_url."\t");
                    fclose($handle);

                }else{//不存在该文本
                    $handle=fopen($filename,"w");
                    $str=fwrite($handle,$short_url."\t");
                    fclose($handle);

                }
                $this->success('添加成功!');
                break;
            case 'get_update':
                $id = I('post.id');
                if(empty($id))$this->error('添加失败!');
                if(file_exists($filename)){//存在该文本
                    $content = file_get_contents($filename);
                    $contents= array_filter(explode("\t",$content));
                    foreach($contents as $k =>$v){
                        if($k == ($id-1)){
                            $this->success($v);
                        }
                    }
                }else{//不存在该文本
                    $this->error('不存在!');
                }
                break;
            case 'post_update':
                $id = I('post.id');
                $short_url = I('post.shorturl');
                if(empty($id))$this->error('不存在');
                if(file_exists($filename)){//存在该文本
                    $content = file_get_contents($filename);
                    $contents= array_filter(explode("\t",$content));

                    $handle=fopen($filename,"w");
                    foreach($contents as $k =>$v){
                        if($k == ($id-1)){
                            $v=$short_url;
                        }
                        $str=fwrite($handle,$v."\t");
                    }
                    fclose($handle);
                    $this->success('修改成功');
                }else{//不存在该文本
                    $this->error('不存在!');
                }
                break;
            default:

                if(file_exists($filename)){
                    $content = file_get_contents($filename);
                    $contents= array_filter(explode("\t",$content));
                    foreach($contents as $k =>$v){
                        $datas['datas'][$k]['id'] = $k+1;
                        $datas['datas'][$k]['longurl'] = $v;
                        if(strpos($v, 'm.yami.ren') === false){
                            $datas['datas'][$k]['shorturl'] = 'http://yummy194.cn/?x='.$k;
                        }else{
                            $datas['datas'][$k]['shorturl'] = 'http://m.yami.ren/?x='.$k;
                        }
                    }
                }else{
                    $datas['datas']=[];
                }

                //table页面参数设置
                $datas['operations'] = [
                    '修改' => [
                        'fun' => "update(%id)"
                    ]
                ];
                $datas['lang'] = [
                    'id' => 'ID',
                    'longurl' => '长网址',
                    'shorturl' => '短网址',
                ];
                $this->assign($datas);

                $this->view();
        }
    }
}