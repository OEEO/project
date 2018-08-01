<?php
namespace Admin\Controller {
    use Admin\Controller\MainController;

    class StatisticsController extends MainController{

        Protected $pagename = '统计管理';

        /*用户统计*/
        public function userManagement(){
            $this->actname = '用户统计';
            $dateUnitstart =time()+( 1 *  24  *  60  *  60 );
            $dateUnitend =time()-( 6 *  24  *  60  *  60 );
            $dateday = date('Y-m-d',$dateUnitstart);
            $totalTime=date("Y-m-d",$dateUnitend);
            $daysArr=array();
            for($i=0;$i<=6;$i++){
                $datas['orderarr'][]['dateList']=$daysarr[]=date("Y-m-d",strtotime('-'.$i.'day'));
            }
            //查询7天内每天的新增用户数
            $totalcont = $this->m2('member')->field("FROM_UNIXTIME(`register_time`, '%Y-%m-%d') as registertime , count(distinct `id`) as count_id" )->where("FROM_UNIXTIME(`register_time`, '%Y-%m-%d')< '{$dateday}' and FROM_UNIXTIME(`register_time`, '%Y-%m-%d')>='{$totalTime}' and status = 1")->order("register_time desc")->group("FROM_UNIXTIME(`register_time`, '%Y-%m-%d')")->select();
            //查询7天内每天的新增吖咪web用户数
            $yamiWebcont = $this->m2('member')->field("FROM_UNIXTIME(`register_time`, '%Y-%m-%d') as registertime , count(distinct `id`) as count_id" )->where("FROM_UNIXTIME(`register_time`, '%Y-%m-%d')< '{$dateday}' and FROM_UNIXTIME(`register_time`, '%Y-%m-%d')>='{$totalTime}' and status = 1 and channel=0")->order("register_time desc")->group("FROM_UNIXTIME(`register_time`, '%Y-%m-%d')")->select();
            //查询7天内每天的新增吖咪APP用户数
            $yamiAppcont = $this->m2('member')->field("FROM_UNIXTIME(`register_time`, '%Y-%m-%d') as registertime , count(distinct `id`) as count_id" )->where("FROM_UNIXTIME(`register_time`, '%Y-%m-%d')< '{$dateday}' and FROM_UNIXTIME(`register_time`, '%Y-%m-%d')>='{$totalTime}' and status = 1 and channel=1")->order("register_time desc")->group("FROM_UNIXTIME(`register_time`, '%Y-%m-%d')")->select();
            //查询7天内每天的新增我有饭web用户数
            $youfanwebcont = $this->m2('member')->field("FROM_UNIXTIME(`register_time`, '%Y-%m-%d') as registertime , count(distinct `id`) as count_id" )->where("FROM_UNIXTIME(`register_time`, '%Y-%m-%d')< '{$dateday}' and FROM_UNIXTIME(`register_time`, '%Y-%m-%d')>='{$totalTime}' and status = 1 and channel=7")->order("register_time desc")->group("FROM_UNIXTIME(`register_time`, '%Y-%m-%d')")->select();
            //查询7天内每天的新增我有饭APP用户数
            $youfanAppcont = $this->m2('member')->field("FROM_UNIXTIME(`register_time`, '%Y-%m-%d') as registertime , count(distinct `id`) as count_id" )->where("FROM_UNIXTIME(`register_time`, '%Y-%m-%d')< '{$dateday}' and FROM_UNIXTIME(`register_time`, '%Y-%m-%d')>='{$totalTime}' and status = 1 and channel=8")->order("register_time desc")->group("FROM_UNIXTIME(`register_time`, '%Y-%m-%d')")->select();
            //查询7天内每天的新增HOST数
            $Hostcont = D('MemberStatisticsView')->where("FROM_UNIXTIME(A.register_time, '%Y-%m-%d')< '{$dateday}' and FROM_UNIXTIME(A.register_time, '%Y-%m-%d')>='{$totalTime}' and A.status = 1 and B.tag_id=18")->order("A.register_time desc")->group("FROM_UNIXTIME(A.register_time, '%Y-%m-%d')")->select();
            //查询7天内每天的其他渠道新增用户数
            $Othercont = $this->m2('member')->field("FROM_UNIXTIME(`register_time`, '%Y-%m-%d') as registertime , count(distinct `id`) as count_id" )->where("FROM_UNIXTIME(`register_time`, '%Y-%m-%d')< '{$dateday}' and FROM_UNIXTIME(`register_time`, '%Y-%m-%d')>='{$totalTime}' and status = 1 and channel in(3,4,5,6,9)")->order("register_time desc")->group("FROM_UNIXTIME(`register_time`, '%Y-%m-%d')")->select();
            //查询7天内每天的新增吖咪web和吖咪app的HOST数
            $yami_Hostcont = D('MemberStatisticsView')->where("FROM_UNIXTIME(A.register_time, '%Y-%m-%d')< '{$dateday}' and FROM_UNIXTIME(A.register_time, '%Y-%m-%d')>='{$totalTime}' and A.status = 1 and B.tag_id=18 and A.channel in(0,1)")->order("A.register_time desc")->group("FROM_UNIXTIME(A.register_time, '%Y-%m-%d')")->select();
            //查询7天内每天的新增我有饭web和我有饭app的HOST数
            $youfan_Hostcont = D('MemberStatisticsView')->where("FROM_UNIXTIME(A.register_time, '%Y-%m-%d')< '{$dateday}' and FROM_UNIXTIME(A.register_time, '%Y-%m-%d')>='{$totalTime}' and A.status = 1 and B.tag_id=18 and A.channel in(7,8)")->order("A.register_time desc")->group("FROM_UNIXTIME(A.register_time, '%Y-%m-%d')")->select();
            //查询7天内每天的其他渠道新增用户数
            $Other_Hostcont = D('MemberStatisticsView')->where("FROM_UNIXTIME(A.register_time, '%Y-%m-%d')< '{$dateday}' and FROM_UNIXTIME(A.register_time, '%Y-%m-%d')>='{$totalTime}' and A.status = 1 and B.tag_id=18 and A.channel in(3,4,5,6,9)")->order("A.register_time desc")->group("FROM_UNIXTIME(A.register_time, '%Y-%m-%d')")->select();


            $arr = [];
            for($i=0; $i<7; $i++){
                $date = date('Y-m-d', time() - $i*24*3600);
                $arr['datehight'][$i] = $date;
                $arr['totalcont'][$date] = 0;
                $arr['yamiWebcont'][$date] = 0;
                $arr['yamiAppcont'][$date] = 0;
                $arr['youfanwebcont'][$date] = 0;
                $arr['youfanAppcont'][$date] = 0;
                $arr['Othercont'][$date] = 0;
                $arr['Hostcont'][$date] = 0;
                $arr['yami_Hostcont'][$date] = 0;
                $arr['youfan_Hostcont'][$date] = 0;
                $arr['Other_Hostcont'][$date] = 0;

            }
            foreach($totalcont as $row){
                $arr['totalcont'][$row['registertime']] = $row['count_id'];
            }
            foreach($yamiWebcont as $row){
                $arr['yamiWebcont'][$row['registertime']] = $row['count_id'];
            }
            foreach($yamiAppcont as $row){
                $arr['yamiAppcont'][$row['registertime']] = $row['count_id'];
            }
            foreach($youfanwebcont as $row){
                $arr['youfanwebcont'][$row['registertime']] = $row['count_id'];
            }
            foreach($youfanAppcont as $row){
                $arr['youfanAppcont'][$row['registertime']] = $row['count_id'];
            }
            foreach($Othercont as $row){
                $arr['Othercont'][$row['registertime']] = $row['count_id'];
            }
            foreach($Hostcont as $row){
                $arr['Hostcont'][$row['registertime']] = $row['count_id'];
            }
            foreach($yami_Hostcont as $row){
                $arr['yami_Hostcont'][$row['registertime']] = $row['count_id'];
            }
            foreach($youfan_Hostcont as $row){
                $arr['youfan_Hostcont'][$row['registertime']] = $row['count_id'];
            }
            foreach($Other_Hostcont as $row){
                $arr['Other_Hostcont'][$row['registertime']] = $row['count_id'];
            }
            krsort($arr['datehight']);
            $totalcont = array_values($arr['totalcont']);
            $yamiWebcont = array_values($arr['yamiWebcont']);
            $yamiAppcont = array_values($arr['yamiAppcont']);
            $youfanwebcont = array_values($arr['youfanwebcont']);
            $youfanAppcont = array_values($arr['youfanAppcont']);
            $Othercont = array_values($arr['Othercont']);
            $Hostcont = array_values($arr['Hostcont']);
            $yami_Hostcont = array_values($arr['yami_Hostcont']);
            $youfan_Hostcont = array_values($arr['youfan_Hostcont']);
            $Other_Hostcont = array_values($arr['Other_Hostcont']);

            krsort($totalcont);
            krsort($yamiWebcont);
            krsort($yamiAppcont);
            krsort($youfanwebcont);
            krsort($youfanAppcont);
            krsort($Othercont);
            krsort($Hostcont);
            krsort($yami_Hostcont);
            krsort($youfan_Hostcont);
            krsort($Other_Hostcont);
            $datas['datehight'] =$arr['datehight'];
            $datas['totalcont'] = $totalcont;
            $datas['yamiWebcont'] = $yamiWebcont;
            $datas['yamiAppcont'] = $yamiAppcont;
            $datas['youfanwebcont'] = $youfanwebcont;
            $datas['youfanAppcont'] = $youfanAppcont;
            $datas['Othercont'] = $Othercont;
            $datas['Hostcont'] = $Hostcont;
            $datas['yami_Hostcont'] = $yami_Hostcont;
            $datas['youfan_Hostcont'] = $youfan_Hostcont;
            $datas['Other_Hostcont'] = $Other_Hostcont;
            $this->assign($datas);
            $this->view();
        }


        /*活动统计*/
        public function tipsManagement(){
            $this->actname = '活动统计';
            $dateUnitstart =time()+( 1 *  24  *  60  *  60 );
            $dateUnitend =time()-( 6 *  24  *  60  *  60 );
            $dateday = date('Y-m-d',$dateUnitstart);
            $totalTime=date("Y-m-d",$dateUnitend);
            $daysArr=array();
            for($i=0;$i<=6;$i++){
                $datas['orderarr'][]['dateList']=$daysarr[]=date("Y-m-d",strtotime('-'.$i.'day'));
            }
            //查询7天内每天的新增活动总数(以分期发布时间统计)
            $tipsCount = D('TipsStatisticsView')->where("FROM_UNIXTIME(B.release_time, '%Y-%m-%d')< '{$dateday}' and FROM_UNIXTIME(B.release_time, '%Y-%m-%d')>='{$totalTime}'and A.status =1 and A.is_pass = 1 and A.title not like '%测试%'")->order("B.release_time desc")->group("FROM_UNIXTIME(B.release_time, '%Y-%m-%d')")->select();
            //查询7天内每天成局数(以活动开始时间统计)
            $gameTipscount = $this->m2('tips')->join('__TIPS_TIMES__ AS B ON B.tips_id = __TIPS__.id')->field('FROM_UNIXTIME(B.start_time, "%Y-%m-%d") as start_time,count(distinct B.id) as count_id ')->where("FROM_UNIXTIME(B.start_time, '%Y-%m-%d')< '{$dateday}' and FROM_UNIXTIME(B.start_time, '%Y-%m-%d')>='{$totalTime}' and ym_tips.status =1 and ym_tips.is_pass = 1 and B.is_finish = 1 and ym_tips.title not like '%测试%'")->order("B.release_time desc")->group("FROM_UNIXTIME(B.release_time, '%Y-%m-%d')")->select();
            //查询7天内每天未成局数(以活动开始时间统计)
            $notgameTipscount = $this->m2('tips')->join('__TIPS_TIMES__ AS B ON B.tips_id = __TIPS__.id')->field('FROM_UNIXTIME(B.start_time, "%Y-%m-%d") as start_time,count(distinct B.id) as count_id ')->where("FROM_UNIXTIME(B.start_time, '%Y-%m-%d')< '{$dateday}' and FROM_UNIXTIME(B.start_time, '%Y-%m-%d')>='{$totalTime}' and ym_tips.status =1 and ym_tips.is_pass = 1 and B.is_finish = 2 and ym_tips.title not like '%测试%'")->order("B.release_time desc")->group("FROM_UNIXTIME(B.release_time, '%Y-%m-%d')")->select();
            //查询7天内每天未公开活动数(以分期发布时间统计)
            $nopublictipsCount = D('TipsStatisticsView')->where("FROM_UNIXTIME(B.release_time, '%Y-%m-%d')< '{$dateday}' and FROM_UNIXTIME(B.release_time, '%Y-%m-%d')>='{$totalTime}'and A.status =1 and A.is_pass = 1 and C.is_public = 0 and A.title not like '%测试%'")->order("B.release_time desc")->group("FROM_UNIXTIME(B.release_time, '%Y-%m-%d')")->select();
            //查询7天内每天公开活动数(以分期发布时间统计)
            $publictipsCount = D('TipsStatisticsView')->where("FROM_UNIXTIME(B.release_time, '%Y-%m-%d')< '{$dateday}' and FROM_UNIXTIME(B.release_time, '%Y-%m-%d')>='{$totalTime}'and A.status =1 and A.is_pass = 1 and C.is_public = 1 and A.title not like '%测试%'")->order("B.release_time desc")->group("FROM_UNIXTIME(B.release_time, '%Y-%m-%d')")->select();


            $arr = [];
            for($i=0; $i<7; $i++){
                $date = date('Y-m-d', time() - $i*24*3600);
                $arr['datehight'][$i] = $date;
                $arr['tipsCount'][$date] = 0;
                $arr['gameTipscount'][$date] = 0;
                $arr['notgameTipscount'][$date] = 0;
                $arr['nopublictipsCount'][$date] = 0;
                $arr['publictipsCount'][$date] = 0;

            }
            foreach($tipsCount as $row){
                $arr['tipsCount'][$row['release_time']] = $row['count_id'];
            }
            foreach($gameTipscount as $row){
                $arr['gameTipscount'][$row['start_time']] = $row['count_id'];
            }
            foreach($notgameTipscount as $row){
                $arr['notgameTipscount'][$row['start_time']] = $row['count_id'];
            }
            foreach($nopublictipsCount as $row){
                $arr['nopublictipsCount'][$row['release_time']] = $row['count_id'];
            }
            foreach($publictipsCount as $row){
                $arr['publictipsCount'][$row['release_time']] = $row['count_id'];
            }
            krsort($arr['datehight']);
            $tipsCount = array_values($arr['tipsCount']);
            $gameTipscount = array_values($arr['gameTipscount']);
            $notgameTipscount = array_values($arr['notgameTipscount']);
            $nopublictipsCount = array_values($arr['nopublictipsCount']);
            $publictipsCount = array_values($arr['publictipsCount']);

            krsort($tipsCount);
            krsort($gameTipscount);
            krsort($notgameTipscount);
            krsort($nopublictipsCount);
            krsort($publictipsCount);
            $datas['datehight'] =$arr['datehight'];
            $datas['tipsCount'] = $tipsCount;
            $datas['gameTipscount'] = $gameTipscount;
            $datas['notgameTipscount'] = $notgameTipscount;
            $datas['nopublictipsCount'] = $nopublictipsCount;
            $datas['publictipsCount'] = $publictipsCount;
            $this->assign($datas);
            $this->view();
        }

        /*订单统计*/
        public function orderManagement(){
            $this->actname = '订单统计';
            $dateUnitstart =time()+( 1 *  24  *  60  *  60 );
            $dateUnitend =time()-( 6 *  24  *  60  *  60 );
            $dateday = date('Y-m-d',$dateUnitstart);
            $totalTime=date("Y-m-d",$dateUnitend);
            $daysArr=array();
            for($i=0;$i<=6;$i++){
                $datas['orderarr'][]['dateList']=$daysarr[]=date("Y-m-d",strtotime('-'.$i.'day'));
            }
            //查询7天内每天的下单订单总数
            $OrdersCount = D('OrdersStatisticsView')->where("FROM_UNIXTIME(A.create_time, '%Y-%m-%d')< '{$dateday}' and FROM_UNIXTIME(A.create_time, '%Y-%m-%d')>='{$totalTime}'and A.act_status in (1,2,3,4) and (C.title not like '%测试%' or D.title not like '%测试%' or E.title not like '%测试%')")->order("A.create_time desc")->group("FROM_UNIXTIME(A.create_time, '%Y-%m-%d')")->select();
//            print_r(D('OrdersStatisticsView')->getLastSql());
//            exit;
            //查询7天内每天活动订单数
            $TipsOrderCount = D('OrdersStatisticsView')->where("FROM_UNIXTIME(A.create_time, '%Y-%m-%d')< '{$dateday}' and FROM_UNIXTIME(A.create_time, '%Y-%m-%d')>='{$totalTime}'and A.act_status in (1,2,3,4) and B.type = 0 and C.title not like '%测试%'")->order("A.create_time desc")->group("FROM_UNIXTIME(A.create_time, '%Y-%m-%d')")->select();
            //查询7天内每天商品订单数
            $GoodsOrderCount = D('OrdersStatisticsView')->where("FROM_UNIXTIME(A.create_time, '%Y-%m-%d')< '{$dateday}' and FROM_UNIXTIME(A.create_time, '%Y-%m-%d')>='{$totalTime}'and A.act_status in (1,2,3,4) and B.type = 1 and D.title not like '%测试%'")->order("A.create_time desc")->group("FROM_UNIXTIME(A.create_time, '%Y-%m-%d')")->select();
            //查询7天内每天众筹订单数
            $RaisesOrderCount = D('OrdersStatisticsView')->where("FROM_UNIXTIME(A.create_time, '%Y-%m-%d')< '{$dateday}' and FROM_UNIXTIME(A.create_time, '%Y-%m-%d')>='{$totalTime}'and A.act_status in (1,2,3,4) and B.type = 2 and E.title not like '%测试%'")->order("A.create_time desc")->group("FROM_UNIXTIME(A.create_time, '%Y-%m-%d')")->select();
            //查询7天内每天吖咪web活动下单订单数
            $yamiweb_tipsOrder = D('OrdersStatisticsView')->where("FROM_UNIXTIME(A.create_time, '%Y-%m-%d')< '{$dateday}' and FROM_UNIXTIME(A.create_time, '%Y-%m-%d')>='{$totalTime}'and A.act_status in (1,2,3,4) and B.type = 0 and A.channel = 0 and C.title not like '%测试%'")->order("A.create_time desc")->group("FROM_UNIXTIME(A.create_time, '%Y-%m-%d')")->select();
            //查询7天内每天吖咪ios活动下单订单数
            $yamiIOS_tipsOrder = D('OrdersStatisticsView')->where("FROM_UNIXTIME(A.create_time, '%Y-%m-%d')< '{$dateday}' and FROM_UNIXTIME(A.create_time, '%Y-%m-%d')>='{$totalTime}'and A.act_status in (1,2,3,4) and B.type = 0 and A.channel = 1 and C.title not like '%测试%'")->order("A.create_time desc")->group("FROM_UNIXTIME(A.create_time, '%Y-%m-%d')")->select();
            //查询7天内每天有饭web活动下单订单数
            $youfanweb_tipsOrder = D('OrdersStatisticsView')->where("FROM_UNIXTIME(A.create_time, '%Y-%m-%d')< '{$dateday}' and FROM_UNIXTIME(A.create_time, '%Y-%m-%d')>='{$totalTime}'and A.act_status in (1,2,3,4) and B.type = 0 and A.channel = 7 and C.title not like '%测试%'")->order("A.create_time desc")->group("FROM_UNIXTIME(A.create_time, '%Y-%m-%d')")->select();
            //查询7天内每天有饭ios活动下单订单数
            $youfanIOS_tipsOrder = D('OrdersStatisticsView')->where("FROM_UNIXTIME(A.create_time, '%Y-%m-%d')< '{$dateday}' and FROM_UNIXTIME(A.create_time, '%Y-%m-%d')>='{$totalTime}'and A.act_status in (1,2,3,4) and B.type = 0 and A.channel = 8 and C.title not like '%测试%'")->order("A.create_time desc")->group("FROM_UNIXTIME(A.create_time, '%Y-%m-%d')")->select();
            //查询7天内每天从其他渠道活动下单订单数
            $Other_tipsOrder = D('OrdersStatisticsView')->where("FROM_UNIXTIME(A.create_time, '%Y-%m-%d')< '{$dateday}' and FROM_UNIXTIME(A.create_time, '%Y-%m-%d')>='{$totalTime}'and A.act_status in (1,2,3,4) and B.type = 0 and A.channel in (2,3,4,5,6,9) and C.title not like '%测试%'")->order("A.create_time desc")->group("FROM_UNIXTIME(A.create_time, '%Y-%m-%d')")->select();
            //查询7天内每天吖咪web商品下单订单数
            $yamiweb_goodsOrder = D('OrdersStatisticsView')->where("FROM_UNIXTIME(A.create_time, '%Y-%m-%d')< '{$dateday}' and FROM_UNIXTIME(A.create_time, '%Y-%m-%d')>='{$totalTime}'and A.act_status in (1,2,3,4) and B.type = 1 and A.channel = 0 and D.title not like '%测试%'")->order("A.create_time desc")->group("FROM_UNIXTIME(A.create_time, '%Y-%m-%d')")->select();
            //查询7天内每天吖咪ios商品下单订单数
            $yamiIOS_goodsOrder = D('OrdersStatisticsView')->where("FROM_UNIXTIME(A.create_time, '%Y-%m-%d')< '{$dateday}' and FROM_UNIXTIME(A.create_time, '%Y-%m-%d')>='{$totalTime}'and A.act_status in (1,2,3,4) and B.type = 1 and A.channel = 1 and D.title not like '%测试%'")->order("A.create_time desc")->group("FROM_UNIXTIME(A.create_time, '%Y-%m-%d')")->select();
            //查询7天内每天有饭web商品下单订单数
            $youfanweb_goodsOrder = D('OrdersStatisticsView')->where("FROM_UNIXTIME(A.create_time, '%Y-%m-%d')< '{$dateday}' and FROM_UNIXTIME(A.create_time, '%Y-%m-%d')>='{$totalTime}'and A.act_status in (1,2,3,4) and B.type = 1 and A.channel = 7 and D.title not like '%测试%'")->order("A.create_time desc")->group("FROM_UNIXTIME(A.create_time, '%Y-%m-%d')")->select();
            //查询7天内每天有饭ios商品下单订单数
            $youfanIOS_goodsOrder = D('OrdersStatisticsView')->where("FROM_UNIXTIME(A.create_time, '%Y-%m-%d')< '{$dateday}' and FROM_UNIXTIME(A.create_time, '%Y-%m-%d')>='{$totalTime}'and A.act_status in (1,2,3,4) and B.type = 1 and A.channel = 8 and D.title not like '%测试%'")->order("A.create_time desc")->group("FROM_UNIXTIME(A.create_time, '%Y-%m-%d')")->select();
            //查询7天内每天从其他渠道商品下单订单数
            $Other_goodsOrder = D('OrdersStatisticsView')->where("FROM_UNIXTIME(A.create_time, '%Y-%m-%d')< '{$dateday}' and FROM_UNIXTIME(A.create_time, '%Y-%m-%d')>='{$totalTime}'and A.act_status in (1,2,3,4) and B.type = 1 and A.channel in (2,3,4,5,6,9) and D.title not like '%测试%'")->order("A.create_time desc")->group("FROM_UNIXTIME(A.create_time, '%Y-%m-%d')")->select();
            //查询7天内每天吖咪web众筹下单订单数
            $yamiweb_raisesOrder = D('OrdersStatisticsView')->where("FROM_UNIXTIME(A.create_time, '%Y-%m-%d')< '{$dateday}' and FROM_UNIXTIME(A.create_time, '%Y-%m-%d')>='{$totalTime}'and A.act_status in (1,2,3,4) and B.type = 2 and A.channel = 0 and E.title not like '%测试%'")->order("A.create_time desc")->group("FROM_UNIXTIME(A.create_time, '%Y-%m-%d')")->select();
            //查询7天内每天吖咪ios众筹下单订单数
            $yamiIOS_raisesOrder = D('OrdersStatisticsView')->where("FROM_UNIXTIME(A.create_time, '%Y-%m-%d')< '{$dateday}' and FROM_UNIXTIME(A.create_time, '%Y-%m-%d')>='{$totalTime}'and A.act_status in (1,2,3,4) and B.type = 2 and A.channel = 1 and E.title not like '%测试%'")->order("A.create_time desc")->group("FROM_UNIXTIME(A.create_time, '%Y-%m-%d')")->select();
            //查询7天内每天有饭web众筹下单订单数
            $youfanweb_raisesOrder = D('OrdersStatisticsView')->where("FROM_UNIXTIME(A.create_time, '%Y-%m-%d')< '{$dateday}' and FROM_UNIXTIME(A.create_time, '%Y-%m-%d')>='{$totalTime}'and A.act_status in (1,2,3,4) and B.type = 2 and A.channel = 7 and E.title not like '%测试%'")->order("A.create_time desc")->group("FROM_UNIXTIME(A.create_time, '%Y-%m-%d')")->select();
            //查询7天内每天有饭ios众筹下单订单数
            $youfanIOS_raisesOrder = D('OrdersStatisticsView')->where("FROM_UNIXTIME(A.create_time, '%Y-%m-%d')< '{$dateday}' and FROM_UNIXTIME(A.create_time, '%Y-%m-%d')>='{$totalTime}'and A.act_status in (1,2,3,4) and B.type = 2 and A.channel = 8 and E.title not like '%测试%'")->order("A.create_time desc")->group("FROM_UNIXTIME(A.create_time, '%Y-%m-%d')")->select();
            //查询7天内每天从其他渠道商品下单订单数
            $Other_raisesOrder = D('OrdersStatisticsView')->where("FROM_UNIXTIME(A.create_time, '%Y-%m-%d')< '{$dateday}' and FROM_UNIXTIME(A.create_time, '%Y-%m-%d')>='{$totalTime}'and A.act_status in (1,2,3,4) and B.type = 2 and A.channel in (2,3,4,5,6,9) and E.title not like '%测试%'")->order("A.create_time desc")->group("FROM_UNIXTIME(A.create_time, '%Y-%m-%d')")->select();


            $arr = [];
            for($i=0; $i<7; $i++){
                $date = date('Y-m-d', time() - $i*24*3600);
                $arr['datehight'][$i] = $date;
                $arr['OrdersCount'][$date] = 0;
                $arr['TipsOrderCount'][$date] = 0;
                $arr['GoodsOrderCount'][$date] = 0;
                $arr['RaisesOrderCount'][$date] = 0;
                $arr['yamiweb_tipsOrder'][$date] = 0;
                $arr['yamiIOS_tipsOrder'][$date] = 0;
                $arr['youfanweb_tipsOrder'][$date] = 0;
                $arr['youfanIOS_tipsOrder'][$date] = 0;
                $arr['Other_tipsOrder'][$date] = 0;
                $arr['yamiweb_goodsOrder'][$date] = 0;
                $arr['yamiIOS_goodsOrder'][$date] = 0;
                $arr['youfanweb_goodsOrder'][$date] = 0;
                $arr['youfanIOS_goodsOrder'][$date] = 0;
                $arr['Other_goodsOrder'][$date] = 0;
                $arr['yamiweb_raisesOrder'][$date] = 0;
                $arr['yamiIOS_raisesOrder'][$date] = 0;
                $arr['youfanweb_raisesOrder'][$date] = 0;
                $arr['youfanIOS_raisesOrder'][$date] = 0;
                $arr['Other_raisesOrder'][$date] = 0;

            }
            foreach($OrdersCount as $row){
                $arr['OrdersCount'][$row['create_time']] = $row['count_id'];
            }
            foreach($TipsOrderCount as $row){
                $arr['TipsOrderCount'][$row['create_time']] = $row['count_id'];
            }
            foreach($GoodsOrderCount as $row){
                $arr['GoodsOrderCount'][$row['create_time']] = $row['count_id'];
            }
            foreach($RaisesOrderCount as $row){
                $arr['RaisesOrderCount'][$row['create_time']] = $row['count_id'];
            }
            foreach($yamiweb_tipsOrder as $row){
                $arr['yamiweb_tipsOrder'][$row['create_time']] = $row['count_id'];
            }
            foreach($yamiIOS_tipsOrder as $row){
                $arr['yamiIOS_tipsOrder'][$row['create_time']] = $row['count_id'];
            }
            foreach($youfanweb_tipsOrder as $row){
                $arr['youfanweb_tipsOrder'][$row['create_time']] = $row['count_id'];
            }
            foreach($youfanIOS_tipsOrder as $row){
                $arr['youfanIOS_tipsOrder'][$row['create_time']] = $row['count_id'];
            }
            foreach($Other_tipsOrder as $row){
                $arr['Other_tipsOrder'][$row['create_time']] = $row['count_id'];
            }
            foreach($yamiweb_goodsOrder as $row){
                $arr['yamiweb_goodsOrder'][$row['create_time']] = $row['count_id'];
            }
            foreach($yamiIOS_goodsOrder as $row){
                $arr['yamiIOS_goodsOrder'][$row['create_time']] = $row['count_id'];
            }
            foreach($youfanweb_goodsOrder as $row){
                $arr['youfanweb_goodsOrder'][$row['create_time']] = $row['count_id'];
            }
            foreach($youfanIOS_goodsOrder as $row){
                $arr['youfanIOS_goodsOrder'][$row['create_time']] = $row['count_id'];
            }
            foreach($Other_goodsOrder as $row){
                $arr['Other_goodsOrder'][$row['create_time']] = $row['count_id'];
            }
            foreach($yamiweb_raisesOrder as $row){
                $arr['yamiweb_raisesOrder'][$row['create_time']] = $row['count_id'];
            }
            foreach($yamiIOS_raisesOrder as $row){
                $arr['yamiIOS_raisesOrder'][$row['create_time']] = $row['count_id'];
            }
            foreach($youfanweb_raisesOrder as $row){
                $arr['youfanweb_raisesOrder'][$row['create_time']] = $row['count_id'];
            }
            foreach($youfanIOS_raisesOrder as $row){
                $arr['youfanIOS_raisesOrder'][$row['create_time']] = $row['count_id'];
            }
            foreach($Other_raisesOrder as $row){
                $arr['Other_raisesOrder'][$row['create_time']] = $row['count_id'];
            }

            krsort($arr['datehight']);
            $OrdersCount = array_values($arr['OrdersCount']);
            $TipsOrderCount = array_values($arr['TipsOrderCount']);
            $GoodsOrderCount = array_values($arr['GoodsOrderCount']);
            $RaisesOrderCount = array_values($arr['RaisesOrderCount']);
            $yamiweb_tipsOrder = array_values($arr['yamiweb_tipsOrder']);
            $yamiIOS_tipsOrder = array_values($arr['yamiIOS_tipsOrder']);
            $youfanweb_tipsOrder = array_values($arr['youfanweb_tipsOrder']);
            $youfanIOS_tipsOrder = array_values($arr['youfanIOS_tipsOrder']);
            $Other_tipsOrder = array_values($arr['Other_tipsOrder']);
            $yamiweb_goodsOrder = array_values($arr['yamiweb_goodsOrder']);
            $yamiIOS_goodsOrder = array_values($arr['yamiIOS_goodsOrder']);
            $youfanweb_goodsOrder = array_values($arr['youfanweb_goodsOrder']);
            $youfanIOS_goodsOrder = array_values($arr['youfanIOS_goodsOrder']);
            $Other_goodsOrder = array_values($arr['Other_goodsOrder']);
            $yamiweb_raisesOrder = array_values($arr['yamiweb_raisesOrder']);
            $yamiIOS_raisesOrder = array_values($arr['yamiIOS_raisesOrder']);
            $youfanweb_raisesOrder = array_values($arr['youfanweb_raisesOrder']);
            $youfanIOS_raisesOrder = array_values($arr['youfanIOS_raisesOrder']);
            $Other_raisesOrder = array_values($arr['Other_raisesOrder']);

            krsort($OrdersCount);
            krsort($TipsOrderCount);
            krsort($GoodsOrderCount);
            krsort($RaisesOrderCount);
            krsort($yamiweb_tipsOrder);
            krsort($yamiIOS_tipsOrder);
            krsort($youfanweb_tipsOrder);
            krsort($youfanIOS_tipsOrder);
            krsort($Other_tipsOrder);
            krsort($yamiweb_goodsOrder);
            krsort($yamiIOS_goodsOrder);
            krsort($youfanweb_goodsOrder);
            krsort($youfanIOS_goodsOrder);
            krsort($Other_goodsOrder);
            krsort($yamiweb_raisesOrder);
            krsort($yamiIOS_raisesOrder);
            krsort($youfanweb_raisesOrder);
            krsort($youfanIOS_raisesOrder);
            krsort($Other_raisesOrder);
            $datas['datehight'] =$arr['datehight'];
            $datas['OrdersCount'] = $OrdersCount;
            $datas['TipsOrderCount'] = $TipsOrderCount;
            $datas['GoodsOrderCount'] = $GoodsOrderCount;
            $datas['RaisesOrderCount'] = $RaisesOrderCount;
            $datas['yamiweb_tipsOrder'] = $yamiweb_tipsOrder;
            $datas['yamiIOS_tipsOrder'] = $yamiIOS_tipsOrder;
            $datas['youfanweb_tipsOrder'] = $youfanweb_tipsOrder;
            $datas['youfanIOS_tipsOrder'] = $youfanIOS_tipsOrder;
            $datas['Other_tipsOrder'] = $Other_tipsOrder;
            $datas['yamiweb_goodsOrder'] = $yamiweb_goodsOrder;
            $datas['yamiIOS_goodsOrder'] = $yamiIOS_goodsOrder;
            $datas['youfanweb_goodsOrder'] = $youfanweb_goodsOrder;
            $datas['youfanIOS_goodsOrder'] = $youfanIOS_goodsOrder;
            $datas['Other_goodsOrder'] = $Other_goodsOrder;
            $datas['yamiweb_raisesOrder'] = $yamiweb_raisesOrder;
            $datas['yamiIOS_raisesOrder'] = $yamiIOS_raisesOrder;
            $datas['youfanweb_raisesOrder'] = $youfanweb_raisesOrder;
            $datas['youfanIOS_raisesOrder'] = $youfanIOS_raisesOrder;
            $datas['Other_raisesOrder'] = $Other_raisesOrder;
            $this->assign($datas);
            $this->view();
        }

        /*订单金额统计*/
        public function orderPriceManagement(){
            $this->actname = '订单金额统计';
            $dateUnitstart = time() + (1 * 24 * 60 * 60);
            $dateUnitend = time() - (6 * 24 * 60 * 60);
            $dateday = date('Y-m-d', $dateUnitstart);
            $totalTime = date("Y-m-d", $dateUnitend);
            $daysArr = array();
            for ($i = 0; $i <= 6; $i++) {
                $datas['orderarr'][]['dateList'] = $daysarr[] = date("Y-m-d", strtotime('-' . $i . 'day'));
            }
            $order_total = D('OrdersStatisticsPriceView')->where("FROM_UNIXTIME(A.create_time, '%Y-%m-%d')< '{$dateday}' and FROM_UNIXTIME(A.create_time, '%Y-%m-%d')>='{$totalTime}' and act_status in(1,2,3,4,5,6,8) and (E.title not like '%测试%' OR F.title not like '%测试%' OR G.title not like '%测试%')")->order("A.create_time desc")->group("A.create_time")->select();
//            print_r(D('OrdersStatisticsPriceView')->getLastSql());
            //计算优惠券的实际优惠金额和实际支付金额
            $arr = [];
            for($i=0; $i<7; $i++){
                $date = date('Y-m-d', time() - $i*24*3600);
                $arr['datehight'][$i] = $date;
                $arr['coupon_price'][$date] = 0;
                $arr['act_pay'][$date] = 0;
                $arr['order_total'][$date] = 0;
                $arr['yami_coupon_price'][$date] = 0;
                $arr['yami_act_pay'][$date] = 0;
                $arr['yami_order_total'][$date] = 0;
                $arr['youfan_coupon_price'][$date] = 0;
                $arr['youfan_act_pay'][$date] = 0;
                $arr['youfan_order_total'][$date] = 0;
                $arr['tips_coupon_price'][$date] = 0;
                $arr['tips_act_pay'][$date] = 0;
                $arr['tips_order_total'][$date] = 0;
                $arr['Other_coupon_price'][$date] = 0;
                $arr['Other_act_pay'][$date] = 0;
                $arr['Other_order_total'][$date] = 0;
                $arr['tipsYami_coupon_price'][$date] = 0;
                $arr['tipsYami_act_pay'][$date] = 0;
                $arr['tipsYami_order_total'][$date] = 0;
                $arr['tipsYoufan_coupon_price'][$date] = 0;
                $arr['tipsYoufan_act_pay'][$date] = 0;
                $arr['tipsYoufan_order_total'][$date] = 0;
                $arr['tipsOther_coupon_price'][$date] = 0;
                $arr['tipsOther_act_pay'][$date] = 0;
                $arr['tipsOther_order_total'][$date] = 0;
                $arr['goods_coupon_price'][$date] = 0;
                $arr['goods_act_pay'][$date] = 0;
                $arr['goods_order_total'][$date] = 0;
                $arr['goodsYami_coupon_price'][$date] = 0;
                $arr['goodsYami_act_pay'][$date] = 0;
                $arr['goodsYami_order_total'][$date] = 0;
                $arr['goodsYoufan_coupon_price'][$date] = 0;
                $arr['goodsYoufan_act_pay'][$date] = 0;
                $arr['goodsYoufan_order_total'][$date] = 0;
                $arr['goodsOther_coupon_price'][$date] = 0;
                $arr['goodsOther_act_pay'][$date] = 0;
                $arr['goodsOther_order_total'][$date] = 0;
                $arr['raise_coupon_price'][$date] = 0;
                $arr['raise_act_pay'][$date] = 0;
                $arr['raise_order_total'][$date] = 0;
                $arr['raiseYami_coupon_price'][$date] = 0;
                $arr['raiseYami_act_pay'][$date] = 0;
                $arr['raiseYami_order_total'][$date] = 0;
                $arr['raiseYoufan_coupon_price'][$date] = 0;
                $arr['raiseYoufan_act_pay'][$date] = 0;
                $arr['raiseYoufan_order_total'][$date] = 0;
                $arr['raiseOther_coupon_price'][$date] = 0;
                $arr['raiseOther_act_pay'][$date] = 0;
                $arr['raiseOther_order_total'][$date] = 0;

            }
            foreach($order_total as $row){
                if($row['coupon_type'] == 0 && $row['coupon_type'] != ''){                                           //抵价券
                    $arr['coupon_price'][$row['create_time']] += $row['coupon_value'];
                    $arr['order_total'][$row['create_time']] += $row['price']+$row['coupon_value'];
                    $arr['act_pay'][$row['create_time']] += $row['price'];
                }elseif ($row['coupon_type'] == 2 || $row['coupon_type'] == 1){                                      //礼品券&[折扣券-不再需要]
                    $arr['coupon_price'][$row['create_time']] += 0;
                    $arr['act_pay'][$row['create_time']] +=$row['price'];
                    $arr['order_total'][$row['create_time']] +=$row['price'];
                }else{
                    $arr['coupon_price'][$row['create_time']] += 0;                              //不使用优惠券
                    $arr['act_pay'][$row['create_time']] +=$row['price'];
                    $arr['order_total'][$row['create_time']] +=$row['price'];
                }

                if($row['channel']==0 ||$row['channel']==2 ){
                    if($row['coupon_type'] == 0 && $row['coupon_type'] != ''){                                           //抵价券
                        $arr['yami_coupon_price'][$row['create_time']] += $row['coupon_value'];
                        $arr['yami_order_total'][$row['create_time']] +=$row['price']+$row['coupon_value'];
                        $arr['yami_act_pay'][$row['create_time']] += $row['price'];
                    }elseif ($row['coupon_type'] == 2 || $row['coupon_type'] == 1){                                      //礼品券&[折扣券-不再需要]
                        $arr['yami_coupon_price'][$row['create_time']] += 0;
                        $arr['yami_act_pay'][$row['create_time']] +=$row['price'];
                        $arr['yami_order_total'][$row['create_time']] +=$row['price'];
                    }else{
                        $arr['yami_coupon_price'][$row['create_time']] += 0;                              //不使用优惠券
                        $arr['yami_act_pay'][$row['create_time']] +=$row['price'];
                        $arr['yami_order_total'][$row['create_time']] +=$row['price'];
                    }
                }elseif($row['channel']==7 ||$row['channel']==8){
                    if($row['coupon_type'] == 0 && $row['coupon_type'] != ''){                                           //抵价券
                        $arr['youfan_coupon_price'][$row['create_time']] += $row['coupon_value'];
                        $arr['youfan_order_total'][$row['create_time']] += $row['price']+$row['coupon_value'];
                        $arr['youfan_act_pay'][$row['create_time']] = $row['price'];
                    }elseif ($row['coupon_type'] == 2 || $row['coupon_type'] == 1){                                      //礼品券&[折扣券-不再需要]
                        $arr['youfan_coupon_price'][$row['create_time']] += 0;
                        $arr['youfan_act_pay'][$row['create_time']] +=$row['price'];
                        $arr['youfan_order_total'][$row['create_time']] +=$row['price'];
                    }else{
                        $arr['youfan_coupon_price'][$row['create_time']] += 0;                              //不使用优惠券
                        $arr['youfan_act_pay'][$row['create_time']] +=$row['price'];
                        $arr['youfan_order_total'][$row['create_time']] +=$row['price'];
                    }
                }elseif(in_array($row['channel'],[3,4,5,6,9])){
                    if($row['coupon_type'] == 0 && $row['coupon_type'] != ''){                                           //抵价券
                        $arr['Other_coupon_price'][$row['create_time']] += $row['coupon_value'];
                        $arr['Other_order_total'][$row['create_time']] += $row['price']+$row['coupon_value'];
                        $arr['Other_act_pay'][$row['create_time']] = $row['price'];
                    }elseif ($row['coupon_type'] == 2 || $row['coupon_type'] == 1){                                      //礼品券&[折扣券-不再需要]
                        $arr['Other_coupon_price'][$row['create_time']] += 0;
                        $arr['Other_act_pay'][$row['create_time']] +=$row['price'];
                        $arr['Other_order_total'][$row['create_time']] +=$row['price'];
                    }else{
                        $arr['Other_coupon_price'][$row['create_time']] += 0;                              //不使用优惠券
                        $arr['Other_act_pay'][$row['create_time']] +=$row['price'];
                        $arr['Other_order_total'][$row['create_time']] +=$row['price'];
                    }
                }
                if($row['order_type']==0){//活动
                    if($row['coupon_type'] == 0 && $row['coupon_type'] != ''){                                           //抵价券
                        $arr['tips_coupon_price'][$row['create_time']] += $row['coupon_value'];
                        $arr['tips_order_total'][$row['create_time']] += $row['price']+$row['coupon_value'];
                        $arr['tips_act_pay'][$row['create_time']] += $row['price'];
                    }elseif ($row['coupon_type'] == 2 || $row['coupon_type'] == 1){                                      //礼品券&[折扣券-不再需要]
                        $arr['tips_coupon_price'][$row['create_time']] += 0;
                        $arr['tips_act_pay'][$row['create_time']] +=$row['price'];
                        $arr['tips_order_total'][$row['create_time']] +=$row['price'];
                    }else{
                        $arr['tips_coupon_price'][$row['create_time']] += 0;                              //不使用优惠券
                        $arr['tips_act_pay'][$row['create_time']] +=$row['price'];
                        $arr['tips_order_total'][$row['create_time']] +=$row['price'];
                    }
                    if($row['channel']==0 ||$row['channel']==2 ){
                        if ($row['coupon_type'] == 0 && $row['coupon_type'] != '') {                                           //抵价券
                            $arr['tipsYami_coupon_price'][$row['create_time']] += $row['coupon_value'];
                            $arr['tipsYami_order_total'][$row['create_time']] += $row['price']+$row['coupon_value'];
                            $arr['tipsYami_act_pay'][$row['create_time']] += $row['price'];

                        } elseif ($row['coupon_type'] == 2 || $row['coupon_type'] == 1) {                                      //礼品券&[折扣券-不再需要]
                            $arr['tipsYami_coupon_price'][$row['create_time']] += 0;
                            $arr['tipsYami_act_pay'][$row['create_time']] +=$row['price'];
                            $arr['tipsYami_order_total'][$row['create_time']] +=$row['price'];
                        } else {
                            $arr['tipsYami_coupon_price'][$row['create_time']] += 0;                              //不使用优惠券
                            $arr['tipsYami_act_pay'][$row['create_time']] +=$row['price'];
                            $arr['tipsYami_order_total'][$row['create_time']] +=$row['price'];
                        }
                    }elseif($row['channel']==7 ||$row['channel']==8){
                        if ($row['coupon_type'] == 0 && $row['coupon_type'] != '') {                                           //抵价券
                            $arr['tipsYoufan_coupon_price'][$row['create_time']] += $row['coupon_value'];
                            $arr['tipsYoufan_order_total'][$row['create_time']] +=  $row['price']+$row['coupon_value'];
                            $arr['tipsYoufan_act_pay'][$row['create_time']]  += $row['price'];
                        } elseif ($row['coupon_type'] == 2 || $row['coupon_type'] == 1) {                                      //礼品券&[折扣券-不再需要]
                            $arr['tipsYoufan_coupon_price'][$row['create_time']] += 0;
                            $arr['tipsYoufan_act_pay'][$row['create_time']] +=$row['price'];
                            $arr['tipsYoufan_order_total'][$row['create_time']] +=$row['price'];
                        } else {
                            $arr['tipsYoufan_coupon_price'][$row['create_time']] += 0;                              //不使用优惠券
                            $arr['tipsYoufan_act_pay'][$row['create_time']] +=$row['price'];
                            $arr['tipsYoufan_order_total'][$row['create_time']] +=$row['price'];
                        }

                    }elseif(in_array($row['channel'],[3,4,5,6,9])){
                        if ($row['coupon_type'] == 0 && $row['coupon_type'] != '') {                                           //抵价券
                            $arr['tipsOther_coupon_price'][$row['create_time']] += $row['coupon_value'];
                            $arr['tipsOther_order_total'][$row['create_time']] +=  $row['price']+$row['coupon_value'];
                            $arr['tipsOther_act_pay'][$row['create_time']]  += $row['price'];
                        } elseif ($row['coupon_type'] == 2 || $row['coupon_type'] == 1) {                                      //礼品券&[折扣券-不再需要]
                            $arr['tipsOther_coupon_price'][$row['create_time']] += 0;
                            $arr['tipsOther_act_pay'][$row['create_time']] +=$row['price'];
                            $arr['tipsOther_order_total'][$row['create_time']] +=$row['price'];
                        } else {
                            $arr['tipsOther_coupon_price'][$row['create_time']] += 0;                              //不使用优惠券
                            $arr['tipsOther_act_pay'][$row['create_time']] +=$row['price'];
                            $arr['tipsOther_order_total'][$row['create_time']] +=$row['price'];
                        }

                    }
                }elseif($row['order_type']==1){//商品
                    if($row['coupon_type'] == 0 && $row['coupon_type'] != ''){                                           //抵价券
                        $arr['goods_coupon_price'][$row['create_time']] += $row['coupon_value'];
                        $arr['goods_order_total'][$row['create_time']] += $row['price']+$row['coupon_value'];
                        $arr['goods_act_pay'][$row['create_time']] = $row['price'];
                    }elseif($row['coupon_type'] == 2 || $row['coupon_type'] == 1){                                      //礼品券&[折扣券-不再需要]
                        $arr['goods_coupon_price'][$row['create_time']] += 0;
                        $arr['goods_act_pay'][$row['create_time']] +=$row['price'];
                        $arr['goods_order_total'][$row['create_time']] +=$row['price'];
                    }else{
                        $arr['goods_coupon_price'][$row['create_time']] += 0;                              //不使用优惠券
                        $arr['goods_act_pay'][$row['create_time']] +=$row['price'];
                        $arr['goods_order_total'][$row['create_time']] +=$row['price'];
                    }
                    if($row['channel']==0 ||$row['channel']==2 ){
                        if ($row['coupon_type'] == 0 && $row['coupon_type'] != '') {                                           //抵价券
                            $arr['goodsYami_coupon_price'][$row['create_time']] += $row['coupon_value'];
                            $arr['goodsYami_order_total'][$row['create_time']] += $row['price']+$row['coupon_value'];
                            $arr['goodsYami_act_pay'][$row['create_time']] += $row['price'];
                        } elseif ($row['coupon_type'] == 2 || $row['coupon_type'] == 1){                                      //礼品券&[折扣券-不再需要]
                            $arr['goodsYami_coupon_price'][$row['create_time']] += 0;
                            $arr['goodsYami_act_pay'][$row['create_time']] +=$row['price'];
                            $arr['goodsYami_order_total'][$row['create_time']] +=$row['price'];
                        } else {
                            $arr['goodsYami_coupon_price'][$row['create_time']] += 0;                              //不使用优惠券
                            $arr['goodsYami_act_pay'][$row['create_time']] +=$row['price'];
                            $arr['goodsYami_order_total'][$row['create_time']] +=$row['price'];
                        }
                    }elseif($row['channel']==7 ||$row['channel']==8 ){
                        if($row['coupon_type'] == 0 && $row['coupon_type'] != ''){                                           //抵价券
                            $arr['goodsYoufan_coupon_price'][$row['create_time']] += $row['coupon_value'];
                            $arr['goodsYoufan_order_total'][$row['create_time']] += $row['price']+$row['coupon_value'];
                            $arr['goodsYoufan_act_pay'][$row['create_time']] += $row['price'];
                        }elseif ($row['coupon_type'] == 2 || $row['coupon_type'] == 1){                                      //礼品券&[折扣券-不再需要]
                            $arr['goodsYoufan_coupon_price'][$row['create_time']] += 0;
                            $arr['goodsYoufan_act_pay'][$row['create_time']] +=$row['price'];
                            $arr['goodsYoufan_order_total'][$row['create_time']] +=$row['price'];
                        }else{
                            $arr['goodsYoufan_coupon_price'][$row['create_time']] += 0;                              //不使用优惠券
                            $arr['goodsYoufan_act_pay'][$row['create_time']] +=$row['price'];
                            $arr['goodsYoufan_order_total'][$row['create_time']] +=$row['price'];
                        }
                    }elseif(in_array($row['channel'],[3,4,5,6,9]) ){
                        if($row['coupon_type'] == 0 && $row['coupon_type'] != ''){                                           //抵价券
                            $arr['goodsOther_coupon_price'][$row['create_time']] += $row['coupon_value'];
                            $arr['goodsOther_order_total'][$row['create_time']] += $row['price']+$row['coupon_value'];
                            $arr['goodsOther_act_pay'][$row['create_time']] += $row['price'];
                        }elseif ($row['coupon_type'] == 2 || $row['coupon_type'] == 1){                                      //礼品券&[折扣券-不再需要]
                            $arr['goodsOther_coupon_price'][$row['create_time']] += 0;
                            $arr['goodsOther_act_pay'][$row['create_time']] +=$row['price'];
                            $arr['goodsOther_order_total'][$row['create_time']] +=$row['price'];
                        }else{
                            $arr['goodsOther_coupon_price'][$row['create_time']] += 0;                              //不使用优惠券
                            $arr['goodsOther_act_pay'][$row['create_time']] +=$row['price'];
                            $arr['goodsOther_order_total'][$row['create_time']] +=$row['price'];
                        }
                    }

                }elseif($row['order_type']==2){//众筹
                    if($row['coupon_type'] == 0 && $row['coupon_type'] != ''){                                           //抵价券
                        $arr['raise_coupon_price'][$row['create_time']] += $row['coupon_value'];
                        $arr['raise_order_total'][$row['create_time']] += $row['price']+$row['coupon_value'];
                        $arr['raise_act_pay'][$row['create_time']] += $row['price'];
                    }elseif ($row['coupon_type'] == 2 || $row['coupon_type'] == 1){                                      //礼品券&[折扣券-不再需要]
                        $arr['raise_coupon_price'][$row['create_time']] += 0;
                        $arr['raise_act_pay'][$row['create_time']] +=$row['price'];
                        $arr['raise_order_total'][$row['create_time']] +=$row['price'];
                    }else {
                        $arr['raise_coupon_price'][$row['create_time']] += 0;                              //不使用优惠券
                        $arr['raise_act_pay'][$row['create_time']] +=$row['price'];
                        $arr['raise_order_total'][$row['create_time']] +=$row['price'];
                    }
                    if($row['channel']==0 ||$row['channel']==2 ){
                        if($row['coupon_type'] == 0 && $row['coupon_type'] != ''){                                           //抵价券
                            $arr['raiseYami_coupon_price'][$row['create_time']] += $row['coupon_value'];
                            $arr['raiseYami_order_total'][$row['create_time']] += $row['price']+$row['coupon_value'];
                            $arr['raiseYami_act_pay'][$row['create_time']] += $row['price'];
                        }elseif ($row['coupon_type'] == 2 || $row['coupon_type'] == 1){                                      //礼品券&[折扣券-不再需要]
                            $arr['raiseYami_coupon_price'][$row['create_time']] += 0;
                            $arr['raiseYami_act_pay'][$row['create_time']] +=$row['price'];
                            $arr['raiseYami_order_total'][$row['create_time']] +=$row['price'];
                        }else {
                            $arr['raiseYami_coupon_price'][$row['create_time']] += 0;                              //不使用优惠券
                            $arr['raiseYami_act_pay'][$row['create_time']] +=$row['price'];
                            $arr['raiseYami_order_total'][$row['create_time']] +=$row['price'];
                        }
                    }elseif ($row['coupon_type'] == 7 || $row['coupon_type'] == 8){
                        if($row['coupon_type'] == 0 && $row['coupon_type'] != ''){                                           //抵价券
                            $arr['raiseYoufan_coupon_price'][$row['create_time']] += $row['coupon_value'];
                            $arr['raiseYoufan_order_total'][$row['create_time']] +=$row['price']+$row['coupon_value'];
                            $arr['raiseYoufan_act_pay'][$row['create_time']] += $row['price'];
                        }elseif ($row['coupon_type'] == 2 || $row['coupon_type'] == 1){                                      //礼品券&[折扣券-不再需要]
                            $arr['raiseYoufan_coupon_price'][$row['create_time']] += 0;
                            $arr['raiseYoufan_act_pay'][$row['create_time']] +=$row['price'];
                            $arr['raiseYoufan_order_total'][$row['create_time']] +=$row['price'];
                        }else {
                            $arr['raiseYoufan_coupon_price'][$row['create_time']] += 0;                              //不使用优惠券
                            $arr['raiseYoufan_act_pay'][$row['create_time']] +=$row['price'];
                            $arr['raiseYoufan_order_total'][$row['create_time']] +=$row['price'];
                        }
                    }elseif (in_array($row['coupon_type'],[3,4,5,6,9])){
                        if($row['coupon_type'] == 0 && $row['coupon_type'] != ''){                                           //抵价券
                            $arr['raiseOther_coupon_price'][$row['create_time']] += $row['coupon_value'];
                            $arr['raiseOther_order_total'][$row['create_time']] += $row['price']+$row['coupon_value'];
                            $arr['raiseOther_act_pay'][$row['create_time']] += $row['price'];
                        }elseif ($row['coupon_type'] == 2 || $row['coupon_type'] == 1){                                      //礼品券&[折扣券-不再需要]
                            $arr['raiseOther_coupon_price'][$row['create_time']] += 0;
                            $arr['raiseOther_act_pay'][$row['create_time']] +=$row['price'];
                            $arr['raiseOther_order_total'][$row['create_time']] +=$row['price'];
                        }else {
                            $arr['raiseOther_coupon_price'][$row['create_time']] += 0;                              //不使用优惠券
                            $arr['raiseOther_act_pay'][$row['create_time']] +=$row['price'];
                            $arr['raiseOther_order_total'][$row['create_time']] +=$row['price'];
                        }
                    }

                }
            }

            krsort($arr['datehight']);
            $coupon_price = array_values($arr['coupon_price']);
            $act_pay = array_values($arr['act_pay']);
            $order_total = array_values($arr['order_total']);
            $yami_coupon_price = array_values($arr['yami_coupon_price']);
            $yami_act_pay = array_values($arr['yami_act_pay']);
            $yami_order_total = array_values($arr['yami_order_total']);
            $youfan_coupon_price = array_values($arr['youfan_coupon_price']);
            $youfan_act_pay = array_values($arr['youfan_act_pay']);
            $youfan_order_total = array_values($arr['youfan_order_total']);
            $Other_coupon_price = array_values($arr['Other_coupon_price']);
            $Other_act_pay = array_values($arr['Other_act_pay']);
            $Other_order_total = array_values($arr['Other_order_total']);
            $tips_coupon_price = array_values($arr['tips_coupon_price']);
            $tips_act_pay = array_values($arr['tips_act_pay']);
            $tips_order_total = array_values($arr['tips_order_total']);
            $tipsYami_order_total = array_values($arr['tipsYami_order_total']);
            $tipsYami_coupon_price = array_values($arr['tipsYami_coupon_price']);
            $tipsYami_act_pay = array_values($arr['tipsYami_act_pay']);
            $tipsYoufan_order_total = array_values($arr['tipsYoufan_order_total']);
            $tipsYoufan_coupon_price = array_values($arr['tipsYoufan_coupon_price']);
            $tipsYoufan_act_pay = array_values($arr['tipsYoufan_act_pay']);
            $tipsOther_order_total = array_values($arr['tipsOther_order_total']);
            $tipsOther_coupon_price = array_values($arr['tipsOther_coupon_price']);
            $tipsOther_act_pay = array_values($arr['tipsOther_act_pay']);
            $goods_coupon_price = array_values($arr['goods_coupon_price']);
            $goods_act_pay = array_values($arr['goods_act_pay']);
            $goods_order_total = array_values($arr['goods_order_total']);
            $goodsYami_coupon_price = array_values($arr['goodsYami_coupon_price']);
            $goodsYami_act_pay = array_values($arr['goodsYami_act_pay']);
            $goodsYami_order_total = array_values($arr['goodsYami_order_total']);
            $goodsYoufan_coupon_price = array_values($arr['goodsYoufan_coupon_price']);
            $goodsYoufan_act_pay = array_values($arr['goodsYoufan_act_pay']);
            $goodsYoufan_order_total = array_values($arr['goodsYoufan_order_total']);
            $goodsOther_coupon_price = array_values($arr['goodsOther_coupon_price']);
            $goodsOther_act_pay = array_values($arr['goodsOther_act_pay']);
            $goodsOther_order_total = array_values($arr['goodsOther_order_total']);
            $raise_coupon_price = array_values($arr['raise_coupon_price']);
            $raise_act_pay = array_values($arr['raise_act_pay']);
            $raise_order_total = array_values($arr['raise_order_total']);
            $raiseYami_coupon_price = array_values($arr['raiseYami_coupon_price']);
            $raiseYami_act_pay = array_values($arr['raiseYami_act_pay']);
            $raiseYami_order_total = array_values($arr['raiseYami_order_total']);
            $raiseYoufan_coupon_price = array_values($arr['raiseYoufan_coupon_price']);
            $raiseYoufan_act_pay = array_values($arr['raiseYoufan_act_pay']);
            $raiseYoufan_order_total = array_values($arr['raiseYoufan_order_total']);
            $raiseOther_coupon_price = array_values($arr['raiseOther_coupon_price']);
            $raiseOther_act_pay = array_values($arr['raiseOther_act_pay']);
            $raiseOther_order_total = array_values($arr['raiseOther_order_total']);
            krsort($coupon_price);
            krsort($act_pay);
            krsort($order_total);
            krsort($yami_coupon_price);
            krsort($yami_act_pay);
            krsort($yami_order_total);
            krsort($youfan_coupon_price);
            krsort($youfan_act_pay);
            krsort($youfan_order_total);
            krsort($Other_coupon_price);
            krsort($Other_act_pay);
            krsort($Other_order_total);
            krsort($tips_coupon_price);
            krsort($tips_act_pay);
            krsort($tips_order_total);
            krsort($tipsYami_coupon_price);
            krsort($tipsYami_act_pay);
            krsort($tipsYami_order_total);
            krsort($tipsYoufan_coupon_price);
            krsort($tipsYoufan_act_pay);
            krsort($tipsYoufan_order_total);
            krsort($tipsOther_coupon_price);
            krsort($tipsOther_act_pay);
            krsort($tipsOther_order_total);
            krsort($goods_coupon_price);
            krsort($goods_act_pay);
            krsort($goods_order_total);
            krsort($goodsYami_coupon_price);
            krsort($goodsYami_act_pay);
            krsort($goodsYami_order_total);
            krsort($goodsYoufan_coupon_price);
            krsort($goodsYoufan_act_pay);
            krsort($goodsYoufan_order_total);
            krsort($goodsOther_coupon_price);
            krsort($goodsOther_act_pay);
            krsort($goodsOther_order_total);
            krsort($raise_coupon_price);
            krsort($raise_act_pay);
            krsort($raise_order_total);
            krsort($raiseYami_coupon_price);
            krsort($raiseYami_act_pay);
            krsort($raiseYami_order_total);
            krsort($raiseYoufan_coupon_price);
            krsort($raiseYoufan_act_pay);
            krsort($raiseYoufan_order_total);
            krsort($raiseOther_coupon_price);
            krsort($raiseOther_act_pay);
            krsort($raiseOther_order_total);
            $datas['datehight'] =$arr['datehight'];
            $datas['coupon_price'] = $coupon_price;
            $datas['act_pay'] = $act_pay;
            $datas['order_total'] = $order_total;
            $datas['yami_coupon_price'] = $yami_coupon_price;
            $datas['yami_act_pay'] = $yami_act_pay;
            $datas['yami_order_total'] = $yami_order_total;
            $datas['youfan_coupon_price'] = $youfan_coupon_price;
            $datas['youfan_act_pay'] = $youfan_act_pay;
            $datas['youfan_order_total'] = $youfan_order_total;
            $datas['Other_coupon_price'] = $Other_coupon_price;
            $datas['Other_act_pay'] = $Other_act_pay;
            $datas['Other_order_total'] = $Other_order_total;
            $datas['tips_coupon_price'] = $tips_coupon_price;
            $datas['tips_act_pay'] = $tips_act_pay;
            $datas['tips_order_total'] = $tips_order_total;
            $datas['tipsYami_coupon_price'] = $tipsYami_coupon_price;
            $datas['tipsYami_act_pay'] = $tipsYami_act_pay;
            $datas['tipsYami_order_total'] = $tipsYami_order_total;
            $datas['tipsYoufan_coupon_price'] = $tipsYoufan_coupon_price;
            $datas['tipsYoufan_act_pay'] = $tipsYoufan_act_pay;
            $datas['tipsYoufan_order_total'] = $tipsYoufan_order_total;
            $datas['tipsOther_coupon_price'] = $tipsOther_coupon_price;
            $datas['tipsOther_act_pay'] = $tipsOther_act_pay;
            $datas['tipsOther_order_total'] = $tipsOther_order_total;
            $datas['goods_coupon_price'] = $goods_coupon_price;
            $datas['goods_act_pay'] = $goods_act_pay;
            $datas['goods_order_total'] = $goods_order_total;
            $datas['goodsYami_coupon_price'] = $goodsYami_coupon_price;
            $datas['goodsYami_act_pay'] = $goodsYami_act_pay;
            $datas['goodsYami_order_total'] = $goodsYami_order_total;
            $datas['goodsYoufan_coupon_price'] = $goodsYoufan_coupon_price;
            $datas['goodsYoufan_act_pay'] = $goodsYoufan_act_pay;
            $datas['goodsYoufan_order_total'] = $goodsYoufan_order_total;
            $datas['goodsOther_coupon_price'] = $goodsOther_coupon_price;
            $datas['goodsOther_act_pay'] = $goodsOther_act_pay;
            $datas['goodsOther_order_total'] = $goodsOther_order_total;
            $datas['raise_coupon_price'] = $raise_coupon_price;
            $datas['raise_act_pay'] = $raise_act_pay;
            $datas['raise_order_total'] = $raise_order_total;
            $datas['raiseYami_coupon_price'] = $raiseYami_coupon_price;
            $datas['raiseYami_act_pay'] = $raiseYami_act_pay;
            $datas['raiseYami_order_total'] = $raiseYami_order_total;
            $datas['raiseYoufan_coupon_price'] = $raiseYoufan_coupon_price;
            $datas['raiseYoufan_act_pay'] = $raiseYoufan_act_pay;
            $datas['raiseYoufan_order_total'] = $raiseYoufan_order_total;
            $datas['raiseOther_coupon_price'] = $raiseOther_coupon_price;
            $datas['raiseOther_act_pay'] = $raiseOther_act_pay;
            $datas['raiseOther_order_total'] = $raiseOther_order_total;
            $this->assign($datas);
            $this->view();
        }

        /*浏览统计*/
        public function browseManagement(){
            $this->actname = '众筹浏览统计';
            $raise_id = 32;
            //条件筛选
            if(IS_GET && $_GET !=null) {
                $search_start_time = I('get.start_time');
                $search_end_time = I('get.end_time');
                $raise_id = I('get.raise_id')?I('get.raise_id'):32;

            }
            $search_start_time && $this->assign('search_start_time',$search_start_time);
            $search_end_time && $this->assign('search_end_time',$search_end_time);
            $raise_id && $this->assign('raise_id',$raise_id);
            $datas['datas'] = $this->m2('Raise')->field('id,title')->where(['id'=>$raise_id])->find();
            $str ='post LIKE \'%{"raise_id":"'.$raise_id.'"}%\'';
            if(!empty($search_start_time)) $str .= ' AND datetime >= "'.$search_start_time.'" ';
            if(!empty($search_end_time)) $str .=' AND datetime < "'.$search_end_time.'"';
            //查询7天内每天的浏览众筹的总数
            $datas['datas']['raiseLookCount'] = $this->m2('member_act_log')->where($str)->count('distinct `id`');
            //查询7天内每天注册用户的浏览众筹的总数
            $datas['datas']['User_Count'] = $this->m2('member_act_log')->where($str.' AND member_id IS NOT NULL')->count('distinct `id`');
            //查询7天内每天注册用户通过吖咪web渠道浏览众筹页面的总数
            $datas['datas']['User_YamiWebCount'] = $this->m2('member_act_log')->where($str.' AND member_id IS NOT NULL AND channel =0')->count('distinct `id`');
            //查询7天内每天注册用户通过吖咪APP渠道浏览众筹页面的总数
            $datas['datas']['User_YamiAppCount'] = $this->m2('member_act_log')->where($str.' AND member_id IS NOT NULL AND channel =1')->count('distinct `id`');
            //查询7天内每天注册用户通过我有饭web渠道浏览众筹页面的总数
            $datas['datas']['User_YoufanWebCount'] = $this->m2('member_act_log')->where($str.' AND member_id IS NOT NULL AND channel =7')->count('distinct `id`');
            //查询7天内每天注册用户通过我有饭APP渠道浏览众筹页面的总数
            $datas['datas']['User_YoufanAppCount'] = $this->m2('member_act_log')->where($str.' AND member_id IS NOT NULL AND channel =8')->count('distinct `id`');
            //查询7天内每天非注册用户的浏览众筹的总数
            $datas['datas']['NotUser_Count'] = $this->m2('member_act_log')->where($str.' AND member_id IS NULL')->count('distinct `id`');
            //查询7天内每天非注册用户通过吖咪web渠道浏览众筹页面的总数
            $datas['datas']['NotUser_YamiWebCount'] = $this->m2('member_act_log')->where($str.' AND member_id IS NULL AND channel =0')->count('distinct `id`');
            //查询7天内每天非注册用户通过吖咪APP渠道浏览众筹页面的总数
            $datas['datas']['NotUser_YamiAppCount'] = $this->m2('member_act_log')->where($str.' AND member_id IS NULL AND channel =1')->count('distinct `id`');
            //查询7天内每天非注册用户通过我有饭web渠道浏览众筹页面的总数
            $datas['datas']['NotUser_YoufanWebCount'] = $this->m2('member_act_log')->where($str.' AND member_id IS NULL AND channel =7')->count('distinct `id`');
            //查询7天内每天非注册用户通过我有饭APP渠道浏览众筹页面的总数
            $datas['datas']['NotUser_YoufanAppCount'] = $this->m2('member_act_log')->where($str.' AND member_id IS NULL AND channel =8')->count('distinct `id`');
            //查询7天内每天注册用户浏览众筹页面的人数
            $datas['datas']['NumCount'] = $this->m2('member_act_log')->where($str.' AND member_id IS NOT NULL')->count('distinct `member_id`');
            //查询7天内每天非注册用户浏览众筹页面的人数
            $datas['datas']['NotNumCount'] = $this->m2('member_act_log')->where($str.' AND member_id IS NULL')->count('distinct `id`');

            $this->assign('data',$datas['datas']);
            $this->view();
        }

//        public $cookie_pa,$content;
//        public function post($post_url,$param) {
//            $ch = curl_init();
//            curl_setopt($ch, CURLOPT_URL,$post_url); //设定远程抓取网址
//            curl_setopt($ch, CURLOPT_POST, 1); //设置为POST提交模式
//            curl_setopt($ch, CURLOPT_POSTFIELDS, $param); //提交参数
//            curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookie_pa);
//            //把返回的cookie保存到$this->cookie_abcd9_com文件中
//            curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookie_pa);
//            //读取cookie
//            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//            //返回获取的输出文本流，而不自动显示
//            $this->content = curl_exec($ch);
//            curl_close($ch);
//        }
        /*统计百度数据*/
//        public function baiduDate(){
//            $this->cookie_pa=tempnam("","cookie"); //设置cookie临时文件
//            $this->post('http://tongji.baidu.com/web/welcome/ico?s=783716691e606db0c3dd11a238224da2','passwd=yami123');
//            //模拟登陆。其中淡蓝色字符串为目标网站的查看地址，红色字符串为查看密码
//            $this->post('http://tongji.baidu.com/web/11239810/ajax/post','indicators=ip_count&method=visit/district/f&siteId=7516755');
//            //获取数据。其中淡蓝色字符串为ajax处理url，三个红色字符串为传递参数
//            $data=json_decode($this->content,true); //获取到的数据为json格式，转换为数组
//            print_r($data); //输出，或进行其他操作




//            $passwd=[
//                'passwd'=>'yami123',
//            ];
//            $this->httpUrl('http://tongji.baidu.com/web/welcome/ico?s=783716691e606db0c3dd11a238224da2',$passwd);
//            $pa = [
//                'indicators'=>'ip_count',
//                'method'=>'visit/topdomain/a',
//                'siteId'=>'7516755',
//            ];
//            $data = $this->httpUrl('http://tongji.baidu.com/web/11239810/ajax/post',$pa);
//            //获取数据。其中淡蓝色字符串为ajax处理url，三个红色字符串为传递参数
//            $data_1=json_decode($data,true); //获取到的数据为json格式，转换为数组
//            print_r($data_1); //输出，或进行其他操作
//            print_r(444);
//            exit;

//        }
    }
}