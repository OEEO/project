<?php

namespace Admin\Controller;
use Admin\Controller\MainController;


Class WechatController extends MainController {

    Protected $pagename = '公众号管理';
    Private $access_token = '';

    Public function __construct()
    {
        parent::__construct();

        //判断是否已选择了公众号
        if(!session('?current_channel')){
            //判断是否有cookie储存公众号
            if(isset($_COOKIE['current_channel'])){
                session('current_channel', $_COOKIE['current_channel']==1 ? 1 : 0);
            }else{
                setcookie('current_channel', 0, time() + 30 * 24 * 3600);
                session('current_channel', 0);
            }
        }

        if(session('current_channel') == 1)
            C('WX_CONF', C('YF_WX_CONF'));
        $this->access_token = getAccessToken();

        $this->assign('channel', session('current_channel'));

    }

    //同步图文素材
    Public function material(){
        set_time_limit(0);
        $pre = session('current_channel')==1 ? 'yf-' : 'ym-';
        //$this->m2('article')->where(['media_id' => ['like', $pre . '%']])->delete();

        $rs = file_get_contents('https://api.weixin.qq.com/cgi-bin/material/get_materialcount?access_token=' . $this->access_token);
        $json = json_decode($rs, true);
        if (isset($json['errcode'])) {
            $this->error($json['errmsg']);
        }
        $sum = $json['news_count'];
        $count = 0;
        for($i=0; $i<$sum; $i+=20){
            $data = [
                'type' => 'news',
                'offset' => $i,
                'count' => 20,
            ];
            $result = $this->curl_post('https://api.weixin.qq.com/cgi-bin/material/batchget_material?access_token=' . $this->access_token, json_encode($data));
            if ($result)
            {
                $json = json_decode($result,true);
                if (isset($json['errcode'])) {
                    $this->error($json);
                }
                foreach($json['item'] as $row){
                    foreach($row['content']['news_item'] as $r){
                        $_rs = $this->m2('article')->where(['media_id' => $pre . $row['media_id'], 'title' => $r['title']])->find();
                        if(!empty($_rs))continue;
                        $data = [
                            'media_id' => $pre . $row['media_id'],
                            'title' => $r['title'],
                            'author' => $r['author'],
                            'content' => $r['content'],
                            'datetime' => date('Y-m-d H:i:s', $row['update_time'])
                        ];
                        $this->m2('article')->add($data);
                        $count ++;
                    }
                }
            }
        }

        $this->success(['sum' => $sum, 'count' => $count]);
    }

    Public function change(){
        //0=吖咪 1=有饭
        $channel = I('get.channel', 0);
        if($channel != 0)$channel = 1;

        setcookie('current_channel', $channel, time() + 30 * 24 * 3600);
        session('current_channel', $channel);

        $this->success('切换成功!');
    }

    Public function menu(){
        $this->actname = '自定义菜单';

        $act = I('post.act');

        if(!empty($act)){
            switch($act){
                case 'list':
                    $rs = file_get_contents("https://api.weixin.qq.com/cgi-bin/menu/get?access_token=" . $this->access_token);
                    $rs = json_decode($rs, true);
                    $data = [];
                    if(isset($rs['menu']) && isset($rs['menu']['button'])){
                        $data = $rs['menu']['button'];
                    }
                    $this->ajaxReturn($data);
                    break;
                case 'save':
                    $data = $_POST['data'];
                    $result = $this->curl_post('https://api.weixin.qq.com/cgi-bin/menu/create?access_token=' . $this->access_token, $data);
                    $result = json_decode($result, true);
                    if($result['errmsg'] == 'ok'){
                        $this->success('保存成功!');
                    }else{
                        $this->error($result['errmsg']);
                    }
                    break;
            }
        }
        $this->view();
    }

    //二维码场景管理
    public function qrcode(){
        $this->actname = '带参二维码';

        $qrcode_id = I('post.qrcode_id', 0);
        $act = I('post.act');

        if(!empty($act)){
            switch($act){
                //获取某个二维码的扫码用户列表
                case 'list':
                    $rs = $this->m2('QrcodeUsers')->field(['nickname', 'openid', 'event'])->join('__MEMBER__ a on a.id=member_id', 'left')->where(['qrcode_id' => $qrcode_id])->select();
                    $this->ajaxReturn($rs);
                    break;
                //获取某个二维码的详情,用于修改
                case 'info':
                    $rs = D('QrcodeView')->where(['id' => $qrcode_id])->find();
                    $rs['path'] = thumb($rs['path']);
                    $this->ajaxReturn($rs);
                    break;
                //获取公众号的图文素材列表
                case 'getForeverList':
                    $pre = session('current_channel')==1 ? 'yf-' : 'ym-';
                    $rs = $this->m2('article')->field(['media_id', 'title'])->where(['media_id' => ['like', $pre . '%']])->select();
                    $data = [];
                    foreach($rs as $row){
                        $row['media_id'] = substr($row['media_id'], '3');
                        $data[] = $row;
                    }
                    $this->ajaxReturn($data);
                    break;
                //添加或修改二维码
                case 'save':
                    $title = I('post.title');
                    $status = I('post.status', 1);
                    $media_id = I('post.media_id', '');
                    $context = I('post.context', '');

                    if(empty($qrcode_id)){
                        $scene_id = $this->m2('qrcode')->add([
                            'title' => $title,
                            'channel' => session('current_channel')
                        ]);
                        if($scene_id > 100000){
                            $this->m2('qrcode')->where(['id' => $scene_id])->delete();
                            $this->error('数量不能超过100000!');
                        }
                        //获取ticket
                        $data = [
                            'action_name' => 'QR_LIMIT_SCENE',
                            'action_info' => [
                                'scene' => [
                                    'scene_id' => $scene_id
                                ]
                            ]
                        ];
                        $result = $this->curl_post('https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=' . $this->access_token, json_encode($data));
                        if ($result)
                        {
                            $json = json_decode($result,true);
                            if (isset($json['errcode'])) {
                                $this->error($json);
                            }

                            //下载二维码图片
                            $path = '../upload/' . date('Ymd');
                            if(!is_dir($path))mkdir($path);
                            $name = substr(base64_encode(sha1(time().rand(1000,9999))), rand(10,20), 30);
                            $file = $path . '/' . $name . '.jpg';
                            $object = date('Ymd') . '/' . $name . '.jpg';
                            \Org\Net\Http::curlDownload('https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=' . urlencode($json['ticket']), $file);
                            try {
                                $ossClient = new \OSS\OssClient(C('UPLOAD_CONFIG.accessKeyId'), C('UPLOAD_CONFIG.accessKeySecret'), C('UPLOAD_CONFIG.endpoint'));
                                $bucket = substr(WEB_DOMAIN, 0, 1) == 'm' ? "yamiimg" : "yummyimg";
                                $ossClient->uploadFile($bucket, $object, $file);
                            } catch (OssException $e) {
                                $this->error($e->getMessage());
                            }
                            $pic_id = $this->m2('pics')->add(['path' => $object]);

                            //保存数据
                            $this->m2('qrcode')->where(['id' => $scene_id])->save([
                                'ticket' => $json['ticket'],
                                'url' => $json['url'],
                                'pic_id' => $pic_id,
                                'media_id' => $media_id,
                                'context' => $context,
                                'status' => $status
                            ]);
                        }
                        $this->success('添加成功!');
                    }else{
                        $scene_id = $qrcode_id;
                        $this->m2('qrcode')->where(['id' => $scene_id])->save([
                            'media_id' => $media_id,
                            'context' => $context,
                            'title' => $title
                        ]);
                        $this->success('修改成功!');
                    }
                    break;
                case 'status':
                    $status = I('post.status', 1);
                    $this->m2('qrcode')->where(['id' => $qrcode_id])->save([
                        'status' => $status
                    ]);
                    $this->success('启用/禁用成功!');
                    break;
                case 'delete':
                    $this->m2('QrcodeUsers')->where(['qrcode_id' => $qrcode_id])->delete();
                    $this->m2('qrcode')->where(['id' => $qrcode_id])->delete();
                    $this->success('删除成功!');
                    break;
            }
        }else{
            $pageSize = 30;
            $rs = D('QrcodeView')->where(['channel' => session('current_channel')])->page($_GET['page'], $pageSize)->order('id desc')->select();
            $datas['datas'] = [];
            //获取图表数据
            foreach($rs as $row){
                $row['path'] = thumb($row['path']);
                $datas['datas'][] = $row;
                if($row['status'] == 1){
                    $datas['xAxis'][] = $row['title'];
                    $datas['data1'][] = $this->m2('QrcodeUsers')->where(['qrcode_id' => $row['id'], 'event' => 0])->count();
                    $datas['data2'][] = $this->m2('QrcodeUsers')->where(['qrcode_id' => $row['id'], 'event' => 1])->count();
                    $datas['data3'][] = $this->m2('QrcodeUsers')->where(['qrcode_id' => $row['id'], 'member_id' => ['EXP', 'IS NOT NULL']])->count();
                }
            }
            $datas['xAxis'] = array_reverse($datas['xAxis']);
            $datas['data1'] = array_reverse($datas['data1']);
            $datas['data2'] = array_reverse($datas['data2']);
            $datas['data3'] = array_reverse($datas['data3']);

            $datas['operations'] = [
                '启用' => [
                    'style' => 'success',
                    'fun' => 'updateStatus(%id, 1)',
                    'condition' => '%status == 0'
                ],
                '禁用' => [
                    'style' => 'warning',
                    'fun' => 'updateStatus(%id, 0)',
                    'condition' => '%status == 1'
                ],
                '修改' => "update(%id)",
                '查看用户' => "showUsers(%id)",
                '删除' => "remove(%id)"
            ];
            $datas['pages'] = [
                'sum' => D('QrcodeView')->count(),
                'count' => $pageSize,
            ];

            $datas['lang'] = [
                'id' => 'ID',
                'title'=>'场景标题',
                'path' => ['二维码', '<img src="%*%" height="100px" width="100px" onclick="imgEnlarge(this)"/>'],
                'url' => '链接地址',
                'media_id' => '素材ID',
                'datetime' => '创建时间'
            ];
            $this->assign($datas);

            $this->view();
        }
    }

    //自动回复
    Public function autoReply(){
        $this->actname = '自定义回复';

        if(IS_AJAX && IS_POST){
            $act = I('post.act');
            switch($act){
                case 'getdata':
                    $rs = $this->m2('WechatReply')->where(['channel' => session('current_channel')])->order('id desc')->select();
                    $data = [];
                    foreach($rs as $row){
                        $row['keys'] = json_decode($row['keys'], true);
                        $row['contents'] = json_decode($row['contents'], true);
                        $data[] = $row;
                    }
                    $this->ajaxReturn($data);
                    break;
                case 'save':
                    $data = I('post.data');
                    $datas = [];
                    foreach($data as $d){
                        $datas[] = [
                            'channel' => session('current_channel'),
                            'name' => $d['name'],
                            'contents' => $d['contents']?json_encode($d['contents']):'[]',
                            'keys' => $d['keys']?json_encode($d['keys']):'[]',
                            'send_type' => $d['send_type']?:0,
                            'status' => $d['status']?:0
                        ];
                    }
                    $this->m2()->startTrans();
                    $this->m2('WechatReply')->where(['channel' => session('current_channel')])->delete();
                    $this->m2('WechatReply')->addAll($datas);
                    $error = $this->m2()->getError();
                    if(empty($error)){
                        $this->m2()->commit();
                        $this->success('保存成功!');
                    }else{
                        $this->m2()->rollback();
                        $this->error('保存失败!');
                    }
                    break;
            }
            exit;
        }

        $this->view();
    }
}