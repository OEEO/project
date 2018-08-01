<?php

namespace Home\Controller;
use Home\Common\MainController;
use Common\Util\Cache;

// @className 运营专题接口
class ThemeController extends MainController {

    /**
     * @apiName 获取运营专题id 和图片
     *
     * @apiGetParam {string} token: 通信令牌
     *
     * @apiPostParam {int} type: 是否为精选(默认0)
     *
     * @apiSuccessResponse
     *{
     * "theme":[
     *             {"id":"11","title":"\u5f53\u5b63\u4e4b\u9009","path":"tips\/20151221\/5677e4e90d752.png"},
     *             {"id":"5","title":"\u5f53\u5b63\u4e4b\u9009","path":"tips\/20151221\/5677e3bae857e.png"},
     *             {"id":"9","title":"\u5f53\u5b63\u4e4b\u9009","path":"tips\/20151221\/5677e45eefc37.png"}
     *          ]
     * }
     */
    public function getlist(){
        $type = I('post.type', 0);
        if(in_array($this->channel, [7,8,9])){
            $type += 2;
        }

        $rs = D('ThemeView')->where(['type' => $type, 'sort' => ['EGT', 0], 'citys_id' => [['EXP', 'is null'], session('city_id'), 'or']])->order('`sort` asc  `datetime` desc')->select();
        $data['list'] = [];
        foreach($rs as $row){
            $data['list'][] = [
                'id' => $row['id'],
                'title' => $row['title'],
                'content' => $row['content'],
                'path' => thumb($row['path'],9-$type),
                'url' => !is_numeric($row['url']) ? $row['url'] : '',
                'datetime' => $row['datetime']
            ];
        }

        if($type == 1){
            $tips_view = new \Goods\Model\TipsViewModel();
            $where = [];
            $where[] = 'A.status=1';
            $where[] = 'A.is_pass=1';
            $where[] = 'D.is_public=1';
            $where[] = "F.start_time > ".time();
            $city_id = session('city_id');
            $city_id = empty($city_id)?224:$city_id;
            $citys = M('citys')->where(['pid' => $city_id])->getField('id', true);
            $citys[] = $city_id;

            $where[] = "M.city_id in (". join(',', $citys) .")";
            if(in_array($this->channel, [7,8,9])){
                $where[] = 'C.id=1';
                $where[] = 'A.buy_status<>2';
            }
            $where = join(' and ', $where);
            $tips_row = $tips_view->where($where)->group('A.id')->select();
            $tips_count = count($tips_row);

            $data['theme_count'] = count($data['list']);
            $data['tips_count'] = $tips_count;
            $data['num'] = ceil($data['tips_count']/($data['theme_count']+1));
        }
        $this->ajaxReturn($data);
    }

    /**
     * @apiName 该专题的id详情信息
     *
     * @apiGetParam {string} token: 通信令牌
     * @apiPostParam {string} theme_id: 专题ID
     *
     * @apiSuccessResponse
     * {
     *     "theme": {
     *         "id": "34",
     *         "title": "测试3",
     *         "content": "啊啊快打开",
     *         "path": "http://img.m.yami.ren/tips/20160421/5718c93d0fd07_640x420.jpg",
     *         "url": "1",
     *         "datetime": "2016-04-25 21:12:44"
     *     },
     *     "tips": [
     *         {
     *             "id": "3038",
     *             "title": "吖咪饭局| 一场浪漫的的分子料理梦幻饭局",
     *             "price": "168.00",
     *             "datetime": "2016-03-09 18:38:29",
     *             "status": "1",
     *             "is_pass": "1",
     *             "path": "uploads/20160307/56dd4f3f0679b.jpg",
     *             "simpleaddress": "",
     *             "theme_theme_id": "32",
     *             "theme_type": "0",
     *             "catname": "饭局",
     *             "theme_id": "34",
     *             "start_time": "1457863200"
     *         },
     *         {
     *             "id": "3036",
     *             "title": "吖咪饭局| 与艺术老师易峰一起学油画享饭局",
     *             "price": "150.00",
     *             "datetime": "2016-03-09 18:38:28",
     *             "status": "1",
     *             "is_pass": "1",
     *             "path": "uploads/20160307/56dd257e6d401.jpg",
     *             "simpleaddress": "",
     *             "theme_theme_id": "28",
     *             "theme_type": "0",
     *             "catname": "饭局",
     *             "theme_id": "34",
     *             "start_time": "1457769600"
     *         },
     *         {
     *             "id": "3036",
     *             "title": "吖咪饭局| 与艺术老师易峰一起学油画享饭局",
     *             "price": "150.00",
     *             "datetime": "2016-03-09 18:38:28",
     *             "status": "1",
     *             "is_pass": "1",
     *             "path": "uploads/20160307/56dd257e6d401.jpg",
     *             "simpleaddress": "",
     *             "theme_theme_id": "29",
     *             "theme_type": "0",
     *             "catname": "饭局",
     *             "theme_id": "34",
     *             "start_time": "1457769600"
     *         },
     *         {
     *             "id": "3033",
     *             "title": "吖咪饭局│ Dr.Wang ，九型人格心理学导师解",
     *             "price": "418.00",
     *             "datetime": "2016-03-09 18:38:28",
     *             "status": "1",
     *             "is_pass": "1",
     *             "path": "uploads/20160303/56d7bcee9df9e.jpg",
     *             "simpleaddress": "",
     *             "theme_theme_id": "29",
     *             "theme_type": "0",
     *             "catname": "饭局",
     *             "theme_id": "34",
     *             "start_time": "1458037800"
     *         },
     *         {
     *             "id": "3033",
     *             "title": "吖咪饭局│ Dr.Wang ，九型人格心理学导师解",
     *             "price": "418.00",
     *             "datetime": "2016-03-09 18:38:28",
     *             "status": "1",
     *             "is_pass": "1",
     *             "path": "uploads/20160303/56d7bcee9df9e.jpg",
     *             "simpleaddress": "",
     *             "theme_theme_id": "28",
     *             "theme_type": "0",
     *             "catname": "饭局",
     *             "theme_id": "34",
     *             "start_time": "1458037800"
     *         }
     *     ]
     * }
     */
    public function getDetail(){
        //$page = I('get.page',1);
        $theme_id = I('post.theme_id',null);

        //获取专题信息
        $rs = D('ThemeView')->where(['id' => $theme_id])->find();
        if(empty($rs))$this->ajaxReturn('专题不存在');
        $group_path = M('Pics')->where(['group_id'=>$rs['pic_group_id']])->getField('path',true);
        foreach($group_path as $key => $row){
            $group_path[$key] = thumb($row, 9);
        }
        $theme_data = [
            'id' => $rs['id'],
            'title' => $rs['title'],
            'content' => strip_tags($rs['content']),
            'html_content' => $rs['content'],
            'path' => thumb($rs['path'], 9 - $rs['type']),
            'groupPath' => $group_path,
            'url' =>  preg_match("/^[1-9]\d*$/",$rs['url']) ? '' : $rs['url'],
            'datetime' => $rs['datetime']
        ];

        //$tips = D('ThemetipsView')->where("(D.theme_id={$theme_id} or F.id={$theme_id}) and (D.type=0 and A.status=1 and is_pass=1)")->page($page,5)->order('A.id desc')->select();
        if(preg_match("/^[1-9]\d*$/",$rs['url'])){
            $tips = D('ThemetipsView')->where(['D.theme_id|F.id' => $theme_id, 'theme_type' => 0, 'status' => 1, 'is_pass' => 1,'A.category_id'=>(int)$rs['url']])->group('A.id')->select();
        }else{
            $tips = D('ThemetipsView')->where(['D.theme_id|F.id' => $theme_id, 'theme_type' => 0, 'status' => 1, 'is_pass' => 1])->group('A.id')->select();
        }
        $data = [];
        if(!empty($tips)){

            $ids = [];
            $member_ids= [];
            foreach($tips as $row){
                $ids[] = $row['id'];
                $member_ids[]=$row['member_id'];
            }

            $times = M('TipsTimes')->field(['tips_id', 'start_time'])->where(['tips_id' => ['IN', join(',', $ids)]])->group('tips_id')->order('phase asc')->select();
            $_times = M('TipsTimes')->field(['tips_id', 'start_time'])->where(['tips_id' => ['IN', join(',', $ids)]])->group('tips_id')->order('phase desc')->select();

            $datetime = [];

            foreach($tips as $row){
                $row['path'] = thumb($row['path'], 1);

                //活动时间,以及剩余份数
                foreach($times as $time){
                    if($time['tips_id']==$row['id']){
                        $row['start_time'] = $time['start_time'];
                        $datetime[$row['id']] = $time['start_time'];
                    }
                }

                if(empty($row['start_time'])){
                    foreach($_times as $time){
                        if($time['tips_id']==$row['id']){
                            $row['start_time'] = $time['start_time'];
                        }
                    }
                }

                $data[] = $row;
            }

            asort($datetime);
            $_data1 = [];
            $_data2 = [];
            foreach($datetime as $i => $t){
                foreach($data as $row){
                    if($i == $row['id']){
                        if($t > time())
                            $_data1[] = $row;
                        else
                            $_data2[] = $row;
                    }
                }
            }
        }

        $arr_data['theme'] = $theme_data;
        $arr_data['tips'] = $_data1;
        $arr_data['tipsPass'] = $_data2;

        $this->ajaxReturn($arr_data);
    }

}