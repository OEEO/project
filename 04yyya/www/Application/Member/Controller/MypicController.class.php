<?php

namespace Member\Controller;
use Member\Common\MainController;

// @className 我的图库
class MypicController extends MainController {

    /**
     * @apiName 获取我的图库列表
     *
     * @apiGetParam {string} token: 通信令牌
     * @apiGetParam {int} page: 分页页码
     *
     * @apiSuccessResponse
     * [
     * 	[
     * 		{
     * 			"id": "34228",
     * 			"path": "http://img.m.yami.ren/member/2016-01-09/DNjZGM1YzkyNDBkNTJkNTZjMWRkMjM_640x420.jpg",
     * 			"is_used": "0",
     *          "size": "[[640,624],["640","420"]]",
     * 			"date": "2016-01-07"
     * 		}
     * 	],
     * 	[
     * 		{
     * 			"id": "34226",
     * 			"path": "http://img.m.yami.ren/member/2016-01-06/ODY0M2QzMzBjZmZlZjg5NjI2MjNmMG_640x420.jpg",
     * 			"is_used": "0",
     *          "size": "[[320,320]]",
     * 			"date": "2016-01-06"
     * 		},
     * 		{
     * 			"id": "34225",
     * 			"path": "http://img.m.yami.ren/member/2016-01-06/hNzljNDY5MzhhMGY4MGE5YWNjNzVlZ_640x420.jpg",
     * 			"is_used": "0",
     *          "size": "[[640,624],["640","420"]]",
     * 			"date": "2016-01-06"
     * 		},
     * 		{
     * 			"id": "34224",
     * 			"path": "http://img.m.yami.ren/member/2016-01-06/5MDNmODEzNzY5N2M1M2ZkNmE1YjRjY_640x420.jpg",
     * 			"is_used": "0",
     *          "size": "[[640,624],["640","420"]]",
     * 			"date": "2016-01-06"
     * 		}
     * 	]
     * ]
     */
    Public function getList(){
        $member_id = session('member.id');
        $page = I('get.page', 1);
        $rs = M('pics')->where(['member_id' => $member_id])->order('id desc')->page($page, 50)->select();
        $data = [];
        foreach($rs as $row){
            $date = substr($row['datetime'], 0, strpos($row['datetime'], ' '));
            $size = json_decode($row['size'], true);
            if(empty($size))$size = [];
            else $size = $size[0];
            $data[$date][] = [
                'id' => $row['id'],
                'path' => thumb($row['path'], $size),
                'is_used' => $row['is_used'],
                'size' => $row['size'],
                'date' => $date
            ];
        }
        $data = array_values($data);
        $this->ajaxReturn($data);
    }

    /**
     * @apiName 上传图片到我的图库
     *
     * @apiGetParam {string} token: 通信令牌
     *
     * @apiPostParam {int} file[]: 图片编码数组
     * @apiPostParam {int} pic_id: 图片ID
     * @apiPostParam {string} size: 压缩尺寸(例如:640x420)
     *
     * @apiSuccessResponse
     * {
     *     "info": {
     * 			"pic_id" : 111,
     * 			"path" : "http://img.m.yami.ren/member/2016-01-06/5MDNmODEzNzY5N2M1M2ZkNmE1YjRjY_640x420.jpg"
     * 	   },
     *     "status": 1,
     *     "url": ""
     * }
     */
    Public function upload(){
        $files = $_POST['file'];
        $pic_id = I('post.pic_id', null);
        $size = I('post.size', null);
        if(empty($files))$this->error('检测不到要上传的图片!');
        if(!empty($pic_id) && !empty($size)){
            $rs = M('pics')->where(['id' => $pic_id])->find();
            if(empty($rs))$this->error('pic_id所对应的图片不存在!');
            $size = explode('x', $size);
            $size = [(int)$size[0], (int)$size[1]];
            $_size = json_decode($rs['size'], true);
            if(in_array($size, $_size))$this->error('缩略图已存在!');
        }else{
            $size = [];
        }

        $conf = C('UPLOAD_CONFIG');

        try {
            $ossClient = new \OSS\OssClient($conf['accessKeyId'], $conf['accessKeySecret'], $conf['endpoint']);
        } catch (\OSS\OssException $e) {
            \Think\Log::write($e->getMessage());
            return false;
        }

        $return = [];
        $date = date($conf['subName']);
//        $bucket = substr(DOMAIN, 0, 1) == 'm' ? "yamiimg" : "yummyimg";
        $bucket = substr(DOMAIN, 0, 1) == 't' ? "yamiimg" : "yummyimg";
        foreach($files as $file){
            if(strlen($file) > $conf['maxSize']){
                $return[] = [
                    'status' => 0,
                    'info' => '超过上传大小限制!'
                ];
                continue;
            }
            //将base64解码
            $file = base64_decode($file);
            if(empty($pic_id)){
                $myDir = $date . '/';
                $filename = fileCrypt();
                $object = $myDir . $filename . $conf['ext'];
                try {
                    $ossClient->putObject($bucket, $object, $file);
                } catch (\OSS\OssException $e) {
                    $return[] = [
                        'status' => 0,
                        'info' => $e->getMessage()
                    ];
                    continue;
                }
            }elseif(!empty($size)){
                $len = strrpos($rs['path'], '/') + 1;
                $myDir = substr($rs['path'], 0, $len);
                $filename = substr($rs['path'], $len, strrpos($rs['path'], '.') - $len);
                $object = $myDir . $filename . "_{$size[0]}x{$size[1]}" . $conf['ext'];
                try {
                    $ossClient->putObject($bucket, $object, $file);
                } catch (\OSS\OssException $e) {
                    $return[] = [
                        'status' => 0,
                        'info' => $e->getMessage()
                    ];
                    continue;
                }
            }
            if(empty($pic_id)){
                $data = [
                    'member_id' => session('member.id'),
                    'path' => $myDir . $filename . $conf['ext'],
                    'size' => json_encode([$size])
                ];
                $id = M('pics')->add($data);
            }else{
                $_size[] = $size;
                M('pics')->where(['id' => $pic_id])->save(['size' => json_encode($_size)]);
            }
            $return[] = [
                'status' => 1,
                'info' => [
                    'pic_id' => $pic_id?:$id,
                    'path' => thumb($myDir . $filename . '.jpg', $size)
                ]
            ];
        }
        if(count($return) == 1){
            $return = $return[0];
        }
        $this->ajaxReturn($return);
    }

    /**
     * @apiName 从我的图库中删除图片
     *
     * @apiGetParam {string} token: 通信令牌
     *
     * @apiPostParam {string} ids: 要删除的图片ID(多个ID用 | 隔开,如 321|4321|12553)
     *
     * @apiSuccessResponse
     * {
     *     "info": '删除成功!',
     *     "status": 1,
     *     "url": ""
     * }
     * @apiErrorResponse
     * {
     *     "info": '失败原因',
     *     "status": 0,
     *     "url": ""
     * }
     */
    Public function delete(){
        $ids = I('post.ids');
        $member_id = session('member.id');
        if(empty($ids))$this->error('请提供要删除的图片ID');
        $ids = str_replace('|', ',', $ids);
        $rs = M('pics')->where(['member_id' => $member_id, 'id' => ['IN', $ids]])->select(false);
        foreach($rs as $row){
            if(is_file(C('UPLOAD_CONFIG.rootPath') . $row['path'])){
                unlink(C('UPLOAD_CONFIG.rootPath') . $row['path']);
            }
            $arr = json_decode($row['size'], true);
            if(is_array($arr)){
                $_path = explode('.', $row['path']);
                foreach($arr as $ar){
                    $path = "{$_path[0]}_{$ar[0]}x{$ar[1]}.{$_path[1]}";
                    if(is_file(C('UPLOAD_CONFIG.rootPath') . $path)){
                        unlink(C('UPLOAD_CONFIG.rootPath') . $path);
                    }
                }
            }
        }
        M('pics')->where(['member_id' => $member_id, 'id' => ['IN', $ids]])->delete();
        $this->success('删除成功!');
    }

    /**
     * @apiName 返回base64的图片编码
     *
     * @apiGetParam {string} token: 通信令牌
     *
     * @apiPostParam {string} pic_id: 图片ID
     *
     * @apiSuccessResponse
     * {
     *     "info": '编码后的图片',
     *     "status": 1,
     *     "url": ""
     * }
     * @apiErrorResponse
     * {
     *     "info": '失败原因',
     *     "status": 0,
     *     "url": ""
     * }
     */
    Public function toBase64(){
        $pic_id = I('post.pic_id');
        if(is_numeric($pic_id))
            $data = [$pic_id];
        else
            $data = $pic_id;
        $codes = [];
        foreach($data as $pic_id){
            $path = M('pics')->where(['id' => $pic_id])->getField('path');
            if(empty($path))$this->error('找不到指定图片!');
            $path = thumb($path);

            $filetype = getimagesize($path);
            $filetype = $filetype['mime'];
            if(in_array($filetype, ['image/png', 'image/gif', 'image/x-icon', 'image/jpeg'])){
                while(empty($code = file_get_contents($path))){
                    usleep(10000);
                }
                $codes[] = 'data:'. $filetype .';base64,' . str_replace(' ', '', base64_encode($code));
            }
        }
        if(count($data) == 1)$this->success($codes[0]);
        else $this->success($codes);
    }

}
