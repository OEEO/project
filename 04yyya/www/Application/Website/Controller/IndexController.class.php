<?php

namespace Website\Controller;
use Website\Common\MainController;

// @className 常规工具
class IndexController extends MainController {

    /**
     * @apiName 获取众筹列表
     *
     * @apiGetParam {string} token: 通信令牌
     * @apiGetParam {int} page: 分页编号，默认1
     * @apiGetParam {int} num: 每页的个数，默认5
     *
     * @apiSuccessResponse
     * [
     *     {
     *         "id": "69",
     *         "title": "年轻人的力量生活服务创新",
     *         "content": "我的自述1、生活服务创新； 2、年轻人的力量！1、生活服务创新；2、年轻人的力量！我的项目1、",
     *         "total": "30",
     *         "status": "1",
     *         "category_id": "29",
     *         "datetime": "2017-03-16 11:33:29",
     *         "start_time": "1489554000",
     *         "end_time": "1491666900",
     *         "video_url": "",
     *         "introduction": "1、生活服务创新；\n2、年轻人的力量！",
     *         "path": "http://img.m.yami.ren/20170314/929030dd73acc1995f04bd1af11cd4b974380272.jpg",
     *         "catname": "众筹分类1",
     *         "nickname": "Ada",
     *         "headpath": "20161208/M5YTBlOTlmMGVmYThhOTkzMWE0MDAw.jpg"
     *     },
     *     {
     *         "id": "68",
     *         "title": "全明星投资人、创始人，跟你一起聊聊衣食住行，吃喝玩乐睡 | 中国生活创新峰会",
     *         "content": "我的自述全明星投资人、创始人，跟你一起聊聊衣食住行，吃喝玩乐睡 | 中国生活创新峰会全明星投资人",
     *         "total": "30",
     *         "status": "1",
     *         "category_id": "29",
     *         "datetime": "2017-03-14 15:52:34",
     *         "start_time": "1490425200",
     *         "end_time": "1490857200",
     *         "video_url": "",
     *         "introduction": "全明星投资人、创始人，跟你一起聊聊衣食住行，吃喝玩乐睡 | 中国生活创新峰会",
     *         "path": "http://img.m.yami.ren/20170314/c92985ff41356d6007c3b2d6facb3486fdba256f.jpg",
     *         "catname": "众筹分类1",
     *         "nickname": "Ada",
     *         "headpath": "20161208/M5YTBlOTlmMGVmYThhOTkzMWE0MDAw.jpg"
     *     },
     * ]
     */
    public function raiseList(){
        $page = I('get.page', 1);
        $num = I('get.num', 5);

        $where = ['status' => 1,'is_public'=>1];

        $rs = D('RaiseView')->where($where)->order('id desc')->page($page, $num)->group('id')->select();

        $data = [];
        foreach($rs as $row){
            $row['path'] = thumb($row['path'], 1);
            $row['content'] = utf8_substr(preg_replace(['/\&\w+?;/', '/\[img.+?\]/'], '', strip_tags($row['content'])), 0, 100);
            if($rs['end_time']>= time()){
                $rs_arr = D('RaiseOrderWaresView')->where(['A.type' => 2, 'A.ware_id' => $row['id'], 'B.status' => 1, 'B.act_status' => ['in', '1,2,3,4'],'order_pid'=>['EXP', 'IS NULL']])->group('A.id')->select();
                foreach($rs_arr as $row_a){
                    $row['totaled'] += $row_a['raise_times_price'];
                    $row['sum'] ++;
                }
            }else{
                $row['sum'] =$row['buyer_num'];
            }
            $data[] = $row;
        }
        $this->put($data);
    }



    /**
     * @apiName 获取筛选活动列表
     *
     * @apiGetParam {string} token: 通信令牌
     * @apiGetParam {int} page: 分页编号，默认1
     * @apiPostParam {int} tag_id: 标签ID（ID=>76：预约制饭局）
     * @apiPostParam {int} week: 星期编号（0-6）
     * @apiPostParam {int} date: 日期（2015-12-01）
     * @apiPostParam {int} city_id: 城市ID
     * @apiPostParam {string} price: 价格区间，必须含有“-”符号，例如：100-200、600-
     * @apiPostParam {int} theme_id: 专题ID
     * @apiPostParam {int} member_id: 活动达人的id
     * @apiPostParam {int} category: 分类 ，（1:饭局，2:课程，3:沙龙, -1:定制 ，-4：社交饭局 -5：大咖饭局）
     * @apiPostParam {int} is_overdue: 是否包含过期的（默认0-否）
     *
     * @apiSuccessResponse
     *[
     *    {
     *        "id": "7713",
     *        "nickname": "紫嫣",
     *        "customers": "27",
     *        "follow_num": "4",
     *        "member_id": "278518",
     *        "headpic": "http://img.m.yami.ren/20170313/NhYTUwODczZGQzOTcwMTM2NzEyYWIw_320x320.jpg",
     *        "mainpic": "http://img.m.yami.ren/20161101/13b27a00e681ab665f6888ea9560ff1f712311f2.jpg",
     *        "catname": "课程",
     *        "tagname": [
     *            "日料"
     *        ],
     *        "title": "彻底 测试",
     *        "price": "0.01",
     *        "start_time": "1494201600",
     *        "end_time": "1494295200",
     *        "sellout": "0",
     *        "buy_status": "0",
     *        "date": "05月08号 周一 08:00 - 05月09号 周二 10:00",
     *        "simpleaddress": "华翠街",
     *        "address": "华翠街建中路16号",
     *        "is_follow": "0",
     *        "is_collect": "0",
     *        "min_num": "1",
     *        "restrict_num": "2"
     *    },
     *    {
     *        "id": "7747",
     *        "nickname": "小江",
     *        "customers": "10",
     *        "follow_num": "1",
     *        "member_id": "34583",
     *        "headpic": "http://img.m.yami.ren/20161021/feae9f978388a858e5db77f6b4548f08730ccb31_320x320.jpg",
     *        "mainpic": "http://img.m.yami.ren/20161129/OTQxY2MzNTdhNzljNzYzMjYzNmYwZT.jpg",
     *        "catname": "饭局",
     *        "tagname": [
     *            "中餐",
     *            "预约制"
     *        ],
     *        "title": "测试结果",
     *        "price": "0.1",
     *        "start_time": "1493970600",
     *        "end_time": "1498823700",
     *        "sellout": "0",
     *        "buy_status": "0",
     *        "date": "05月05号 周五 15:50 - 06月30号 周五 19:55",
     *        "simpleaddress": "北京市  北京区",
     *        "address": "2楼呀咪",
     *        "is_follow": "0",
     *        "is_collect": "0",
     *        "min_num": "5",
     *        "restrict_num": "6"
     *    }
     *]
     * */
    public function tipsList($sort = []){
        $page = I('get.page', 1);
        $tag_id = I('post.tag_id', null);
        $week = I('post.week', null);
        $date = I('post.date', null);
        $city_id = I('post.city_id', null);
        $price = I('post.price', null);
        $count =  I('get.num', 5); //每页显示5条
        $theme_id = I('post.theme_id',null);
        $member_id = I('post.member_id',null);
        $category = I('post.category',null);
        $is_overdue = I('post.is_overdue', 1);

        $where = [];
        $where[] = 'A.status=1';
        $where[] = 'A.is_pass=1';
        $where[] = 'is_public=0';
        if(!empty($tag_id)){
            $tags = M('TipsTag')->field('tips_id')->where(['tag_id' => $tag_id])->buildSql();
            $where[] = "A.id in " . $tags;
        }
        if($week !== null){
            $where[] = "FROM_UNIXTIME(C.`start_time`,'%w') = '{$week}'";
        }
        if(!empty($date)){
            $datetime1 = strtotime($date . ' 00:00:00');
            $datetime2 = strtotime($date . ' 23:59:59');
            $where[] = "F.`start_time` <= '{$datetime2}' and F.`end_time` >= '{$datetime1}'";
        }
        if(!empty($city_id)){
            //如果有子区域则添加进条件
            $sub_area = M('citys')->where(['pid'=>$city_id])->getField('id',true);

            $area = $sub_area;
            $area[] = $city_id;
            $where[] = "M.city_id in (". join(',',$area) .")";

        }elseif(empty($member_id)){
            $citys = M('citys')->where(['pid' => session('city_id')])->getField('id', true);
            $citys[] = session('city_id');
            $where[] = "M.city_id in (". join(',', $citys) .")";
        }
        if(!empty($sort)){
            $ids = [];
            foreach($sort as $row){
                $ids[] = $row['id'];
            }
            $where[] = "A.id in (" . join(',', $ids) . ")";
        }
        if(!empty($price) && strpos($price, '-') !== false){
            $arr = explode('-', $price);
            if(empty($arr[0]))$arr[0] = 0;
            if(empty($arr[1]))$arr[1] = 9999999;
            $where[] = "A.price > {$arr[0]} and A.price < {$arr[1]}";
        }
        if(!empty($theme_id)){
            $theme = M('theme_element')->field('type_id')->where(['theme_id' => $theme_id , 'type' => '0'])->buildSql();
            $where[] = "A.id in " . $theme;
        }
        if(!empty($member_id)){
            $where[] = "A.member_id=" . $member_id;
        }
        if(!empty($category)){
            if($category > 0) {
                $where[] = 'C.id=' . $category;
            }elseif($category == -1){
                $where[] = 'A.buy_status=2';
            }elseif($category == -2){
                $where[] = 'A.buy_status<>2';
            }elseif($category == -4){
                $tags = M('TipsTag')->field('tips_id')->where(['tag_id' => ['NEQ', 65]])->buildSql();
                $where[] = "A.id in " . $tags;
            }elseif($category == -5){
                $tags = M('TipsTag')->field('tips_id')->where(['tag_id' => 65])->buildSql();
                $where[] = "A.id in " . $tags;
            }
        }

        //筛选活动开始前的活动列表
        $having = '';
        if(!$is_overdue || empty($member_id)) {
            $order[] = 'A.is_top desc';
            if($category != -5){
                $having = "max(F.end_time) > ".time();;
                //$where[] = "F.end_time > ".time();
                $order[] = 'F.start_time asc';
            }else{
                $order[] = 'F.start_time desc';
            }
        }else{
            $having = "max(F.end_time) <= ".time();
            $order[] = 'F.start_time desc';
        }

        if(in_array($this->channel, [7,8,9])){
            $where[] = 'C.id=1';
            $where[] = 'A.buy_status<>2';
        }

        $where = join(' and ', $where);

        $data = D('TipsView')->where($where)->group('A.id')->having($having)->page($page, $count)->order(join(',', $order))->select();
        if(!empty($data)){
            //获取所有tips的id
            $ids = $member_ids = [];
            foreach($data as $row){
                $ids[] = $row['id'];
                $member_ids[] = $row['member_id'];
            }
            /*查询出所有tips的标签*/
            $tipstag=D('TipstagView')->where('A.tips_id in (' . join(',', $ids) . ')')->select();
            //查询出所有的订单活动
            $rs = D('OrderWaresView')->field(['ware_id', 'count(ware_id) as num'])->where(array('type' => 0, 'ware_id' => array('IN', join(',', $ids)), 'act_status' => array('LT', 6)))->group('ware_id')->select();

            //查询出所有关注
            if(session('?member'))$follows = M('MemberFollow')->where(['member_id' => session('member.id'), 'follow_id' => ['IN', join(',', $member_ids)]])->getField('follow_id', true);
            if(empty($follows))$follows = [];

            //找出分期库存
            $times = M('TipsTimes')->where(['tips_id'=>['IN',join(',', $ids)],'end_time'=>['GT', time()]])->Field(['tips_id','stock','start_time','end_time','stop_buy_time'])->order('start_time')->select();
            //$this->ajaxReturn($stock);

            //找出已收藏的活动
            if(session('?member')){
                $collect_tips = M('MemberCollect')->where(['member_id'=>session('member.id') , 'type'=>0])->getField('type_id',true);
            }
            if(empty($collect_tips))$collect_tips = [];

            foreach($data as $k => $r){
                //判断库存是否售罄
                $sellout = 1;
                $useful_phase = 0;
                foreach($times as $ke=>$re){
                    if($re['tips_id'] == $r['id']){
                        //$data[$k]['sellout'] = ($re['stock']==0) ?  1 : 0 ;
                        if($re['stock']>0)$sellout = 0;
                        //可用的最近一期时间也顺便赋值下
                        if($re['stock'] > 0 && $re['stop_buy_time'] > time() && !$useful_phase){
                            $data[$k]['start_time'] = $re['start_time'];
                            $data[$k]['end_time'] = $re['end_time'];
                            $useful_phase = 1;
                        }
                        if(!$useful_phase){
                            $data[$k]['start_time'] = $re['start_time'];
                            $data[$k]['end_time'] = $re['end_time'];
                        }
                    }
                }

                //把对应的标签加入到相应的tips中
                $tagnames= [];
                foreach($tipstag as $tt){
                    if($tt['tips_id']==$r['id']) {
                        $tagnames[] = $tt['name'];
                        if($tt['tag_id'] == 76){
                            $sellout = 0;
                        }
                    }
                }
                $data[$k]['sellout'] = $sellout;
                $data[$k]['tags']=$tagnames;

                //运用缩略图
                $data[$k]['path'] = thumb($r['path'], 1);
                $data[$k]['headpic'] = thumb($r['headpic'], 2);

                //把剩余份数加入到相应的tips中
                $amount = $r['max_num'];
                foreach($rs as $row){
                    if($row['ware_id']==$r['id'])$amount = $r['max_num'] - $row['num'];
                }
                $data[$k]['amount'] = $amount < 0 ? 0 : $amount;

                //获取粉丝数量
                $data[$k]['follow_num'] = M('MemberFollow')->where(['follow_id'=>$r['member_id']])->count();
                //赏味
                $data[$k]['customers'] = D('ShangweiView')->where(['member_id' => $r['member_id'], 'act_status' => ['IN', '1,2,3,4'], 'status' => 1])->getField('count');
                if(empty($data[$k]['customers']))$data[$k]['customers'] = '0';
            }
        }

        $_data = [];
        $date_array = [1=>'一',2=>'二',3=>'三',4=>'四',5=>'五',6=>'六',7=>'日'];
        foreach($data as $row){

            if(date('md',$row['start_time'])==date('md',$row['end_time'])){
                //开始和结束时间在同一天
                $row['date'] = date('m月d号',$row['start_time']) . ' 周' . $date_array[date('N',$row['start_time'])] . ' ' .date('H:i',$row['start_time']).' - '.date('H:i',$row['end_time']);
            }else{
                //开始和结束时间不在同一天
                $row['date'] = date('m月d号',$row['start_time']) . ' 周' . $date_array[date('N',$row['start_time'])] . ' ' .date('H:i',$row['start_time']).' - '.date('m月d号',$row['end_time']) . ' 周' . $date_array[date('N',$row['end_time'])] . ' ' .date('H:i',$row['end_time']);
            }

            $_data[] = [
                'id' => $row['id'],
                'nickname' => $row['nickname'],
                'customers' => $row['customers'],
                'follow_num' => $row['follow_num']?:0,
                'member_id' => $row['member_id'],
                'headpic' => $row['headpic'],
                'mainpic' => $row['path'],
                'catname' => $row['catname'],
                'tagname' => $row['tags'],
                'title' => $row['title'],
                'price' => (string)(float)$row['price'],
                'start_time' => $row['start_time'],
                'end_time' => $row['end_time'],
                'sellout' => $row['sellout'],
                'buy_status' => $row['buy_status'],
                'date' => $row['date'],
                'simpleaddress' => $row['simpleaddress']?:'',
                'address' => $row['address']?:'',
                'is_follow' => (in_array($row['member_id'], $follows) ? 1 : 0),
                'is_collect' => (in_array($row['id'],$collect_tips) ? 1 : 0),
                'min_num' => $row['min_num'],
                'restrict_num' => $row['max_num'],
            ];
        };
        if(!empty($sort)) {
            return $_data;
        }

        $this->put($_data);
    }

    /**
     * @apiName 获取筛选咨询列表
     *
     * @apiGetParam {string} token: 通信令牌
     * @apiGetParam {int} page: 分页编号，默认1
     * @apiGetParam {int} num: 分页个数，默认5
     *
     * @apiSuccessResponse
     *[
     *    {
     *        "id": "7",
     *        "member_id": "278514",
     *        "title": "国家卫计委：中国注册护士超350万 医护比提升",
     *        "nickname": "小不点",
     *        "path": "http://img.m.yami.ren/20170510/49ae9bee4b88f46addcef68c5ba5c5b9afb51911.jpg"
     *    }
     *]
     * */
    public function newsList(){
        $page = I('get.page', 1);
        $count =  I('get.num', 5); //每页显示5条
        $data = D('NewsView')->where('A.status=2')->page($page, $count)->order('A.id desc')->group('A.id')->select();
        if(!empty($data)){
           foreach($data as $key=>$val){
               $data[$key]['path'] = thumb($val['path']);
               $data[$key]['content'] = preg_replace('/\[img(.*?)\/\]/', '<img$1>', $val['content']);
           }
        }
        $this->put($data);
    }

    /**
     * @apiName 获取咨询详情页
     *
     * @apiGetParam {string} token: 通信令牌
     * @apiGetParam {int} news_id: 咨询ID
     *
     * @apiSuccessResponse
     *{
     *    "id": "7",
     *    "member_id": "278514",
     *    "title": "国家卫计委：中国注册护士超350万 医护比提升",
     *    "nickname": "小不点",
     *    "path": "http://img.m.yami.ren/20170510/49ae9bee4b88f46addcef68c5ba5c5b9afb51911.jpg",
     *    "content": ""
     *}
     * */
     public function newsDetail(){
        $news_id =  I('post.news_id');
        if(empty($news_id)) $this->error('非法访问');

        $data = D('NewsView')->where('A.id = '.$news_id)->find();
        if(!empty($data)){
            $data['path'] = thumb($data['path']);
            $data['datetime'] = strtotime($data['datetime']);
            $data['content'] = preg_replace('/\[img(.*?)\/\]/', '<img$1>', $data['content']);

        }
        $this->put($data);
    }
}