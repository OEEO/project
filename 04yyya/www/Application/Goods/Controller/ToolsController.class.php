<?php
/**
 * Created by PhpStorm.
 * User: Dean
 * Date: 17/3/29
 * Time: 下午4:10
 */

namespace Goods\Controller;
use Goods\Common\MainController;

class ToolsController extends MainController
{
    /**
     * @apiName 获取DIY模板数据
     *
     * @apiGetParam {string} token: 通信令牌
     *
     * @apiPostParam {int} diy_id: 模板ID
     *
     * @apiSuccessResponse
     * {
     *     "id": "1",
     *     "title": "早餐日志！~",
     *     "bg_pic_id": "1000669600",
     *     "bg_color": "#FF0000",
     *     "box_size": "500,500",
     *     "box_pos": "119,288",
     *     "box_depth": "3",
     *     "datas": [
     *         {
     *             "pic_id": "1000669609",
     *             "pos": ["296","665"],
     *             "depth": "2",
     *             "path": "http://img.m.yami.ren/20170329/e72d7efe14958e366564a3fe7051d74713180de8.png"
     *         },
     *         {
     *             "pic_id": "1000669610",
     *             "pos": ["125","96"],
     *             "depth": "4",
     *             "path": "http://img.m.yami.ren/20170329/b1272e188ec81bcef132687e11e3ed5a36e35791.png"
     *         },
     *         {
     *             "pic_id": "1000669611",
     *             "pos": ["271","985"],
     *             "depth": "5",
     *             "path": "http://img.m.yami.ren/20170329/c3b9aff4440e5d583d2a6e6a7222c6cd26e60dc3.png"
     *         }
     *     ],
     *     "datetime": "2017-03-29 11:26:26",
     *     "bg_path": "http://img.m.yami.ren/20170329/6e79647a1e3fb6662a161788b9823b622877f136.jpg",
     *     "box_width": "500",
     *     "box_height": "500",
     *     "box_left": "119",
     *     "box_top": "288"
     * }
     */
    public function DIYImage(){
        $diy_id = I('post.diy_id', 0);
        $rs = M('diyimage')->where(['id' => $diy_id])->find();
        $rs['bg_path'] = getBase64(M('pics')->where(['id' => $rs['bg_pic_id']])->getField('path'));
        $arr = explode(',', $rs['box_size']);
        $rs['box_width'] = $arr[0];
        $rs['box_height'] = $arr[1];
        $arr = explode(',', $rs['box_pos']);
        $rs['box_left'] = $arr[0];
        $rs['box_top'] = $arr[1];
        $rs['textdatas'] = json_decode($rs['textdatas'], true);
        $datas = json_decode($rs['datas'], true);
        $dt = [];
        foreach($datas as $row){
            $row['path'] = getBase64(M('pics')->where(['id' => $row['pic_id']])->getField('path'));
            $dt[] = $row;
        }
        $rs['datas'] = $dt;

        $this->put($rs);
    }

    /**
     * @apiName 保存用户DIY后的图片
     *
     * @apiGetParam {string} token: 通信令牌
     *
     * @apiPostParam {int} diy_id: 模板ID
     * @apiPostParam {int} pic_id: 用于DIY的图片ID
     * @apiPostParam {int} code: DIY出来的图片base64编码
     * @apiPostParam {int} datas: DIY数据 JSON格式 {w:<宽度>, h:<高度>, x:<x坐标>, y:<y坐标>, r:<旋转角度90的倍数>}
     *
     * @apiSuccessResponse
     * {
     *   "status" : 1,
     *   "info" : {
     *     "pic_id" : "合成的图片ID",
     *     "diyimage_id" : "diyimage_users表的id",
     *     "path" : "合成的图片路径"
     *   }
     * }
     *
     * @apiErrorResponse
     * {
     *   "status" : 0,
     *   "info" : "错误详情"
     * }
     */
    public function DIYImageSave(){
        $diy_id = I('post.diy_id');
        $pic_id = I('post.pic_id');
        $file = I('post.code');
        $datas = I('post.datas');

        $conf = C('UPLOAD_CONFIG');
        try {
            $ossClient = new \OSS\OssClient($conf['accessKeyId'], $conf['accessKeySecret'], $conf['endpoint']);
        } catch (\OSS\OssException $e) {
            \Think\Log::write($e->getMessage());
            return false;
        }

        $date = date($conf['subName']);
        $bucket = substr(DOMAIN, 0, 1) == 't' ? "yamiimg" : "yummyimg";

        if(strlen($file) > $conf['maxSize']){
            $this->error('超过上传大小限制!');
        }
        //将base64解码
        $file = base64_decode($file);

        $myDir = $date . '/';
        $filename = fileCrypt();
        $object = $myDir . $filename . $conf['ext'];
        try {
            $ossClient->putObject($bucket, $object, $file);
        } catch (\OSS\OssException $e) {
            $this->error($e->getMessage());
        }

        $data = [
            'path' => $myDir . $filename . $conf['ext']
        ];
        if(session('?member'))$data['member_id'] = session('member.id');
        $diy_pic_id = M('pics')->add($data);

        $data = [
            'diyimage_id' => $diy_id,
            'pic_id' => $pic_id,
            'datas' => json_encode($datas),
            'diy_pic_id' => $diy_pic_id
        ];
        if(session('?member'))$data['member_id'] = session('member.id');
        if(session('?wxUser'))$data['open_id'] = session('wxUser.id');
        $id = M('diyimage_users')->add($data);

        $this->success([
            'pic_id' => $pic_id,
            'diyimage_id' => $id,
            'path' => thumb($myDir . $filename . $conf['ext'])
        ]);
    }

    /**
     * 下载图片(非API)
     */
    public function DIYImageDownload(){
        $diyimage_id = I('get.id');
        $rs = M('DiyimageUsers')->where(['id' => $diyimage_id])->find();
        if(empty($rs))$this->error('未找到图片!');
        $title = M('diyimage')->where(['id' => $rs['diyimage_id']])->getField('title') . '_' . date('YmdHis');
        $path = thumb(M('pics')->where(['id' => $rs['diy_pic_id']])->getField('path'));
        header("Content-type: application/octet-stream");
        header("Content-disposition:attachment;filename=".$title.".jpg;");
        header("Content-Length:".filesize($path));
        readfile($path);
    }
}