<?php
namespace Admin\Controller {
    use Admin\Controller\MainController;

    class TipsController extends MainController{

        Protected $pagename = '活动管理';

        //活动添加
        public function add(){
            $this->actname = '活动添加';
            //获取经营的城市列表
            $citys = C('CITY_CONFIG');
            if(IS_AJAX && IS_POST){
                if(!isset($_POST['submit']) && !empty($_POST['member_id'])) {
                    //选择达人并创建新活动
                    $data = [
                        'member_id' => I('post.member_id'),
                        'title' => null,
                        'category_id' => 1,
                        'price' => 0,
                        'citys_id' => null,
                        'address' => null,
                        'longitude' => null,
                        'latitude' => null,
                        'tel' => null,
                        'is_pass' => 0,
                        'status' => 3
                    ];
                    $id = $this->m2('tips')->add($data);
                    $data['tips_id'] = $id;
                    $this->m2('tips_sub')->add($data);
                    $member_id = I('post.member_id');
                    $spacearr =D('SpaceCitysView')->where(['member_id' => $member_id])->select();
                    $tip_space = array(
                        'id' =>$id,
                        'spacearr' =>$spacearr
                    );

                    //记录活动修改快照信息
                    $this->SaveSnapshotLogs($id,0);
                    $this->ajaxReturn($tip_space);
                    exit;
                }elseif(!isset($_POST['submit']) && !empty($_POST['city_id'])) {
                    $city_id = I('post.city_id');
                    if (!array_key_exists($city_id, $citys)) $this->error('非法操作!');
                    $areas = $this->m2('citys')->field(['id', 'name'])->where(['pid' => $city_id])->select();
                    $this->ajaxReturn($areas);
                }elseif(isset($_POST['submit']) && $_POST['submit'] == 1){
                    //提交审核
                    $this->submit();
                }elseif(isset($_POST['submit']) && $_POST['submit'] == 0){
                    //保存并预览
                    $this->save();
                }elseif(!isset($_POST['submit']) && !empty($_POST['space_id'])) {
                    $space_id = I('post.space_id');
                    $space = D('SpaceCitysView')->where(['spaceId' => $space_id])->find();
                    $environment_pics = $this->m2('pics')->field(['id', 'path'])->where(['group_id'=>$space['pic_group_id']])->select();
                    if(!empty($environment_pics)){
                        foreach($environment_pics as $k => $v){
                            $environment_pics[$k]['path'] = thumb($v['path']);
                        }
                    }
                    $space['environment_pics'] =$environment_pics;

                    $this->ajaxReturn($space);

                }
                $this->error('非法提交');
                exit;
            }


            //获取活动分类
            $categorys = $this->m2('category')->field(['id', 'name'])->where(['type' => 0])->order(['order'])->select();
            //获取活动标签
            $tags = $this->m2('tag')->field(['id', 'name'])->where(['type' => 1, 'official' => 0])->select();
            //获取默认城市下的区
            $areas = $this->m2('citys')->field(['id', 'name'])->where(['pid' => 224])->select();
            //须知列表
            $notices = $this->m2('TipsNotice')->field(['id', 'context', 'status'])->where(['status' => ['IN', '1,2,4']])->select();
            $this->assign([
                'categorys' => $categorys,
                'tags' => $tags,
                'citys' => $citys,
                'areas' => $areas,
                'notices' => $notices
            ]);
            $this->view();
        }

        //活动添加或修改
        public function modify() {
            $this->actname = '活动修改';
            //获取经营的城市列表
            $citys = C('CITY_CONFIG');
            if (IS_AJAX && IS_POST) {
                if (!isset($_POST['submit']) && !empty($_POST['city_id'])) {
                    $city_id = I('post.city_id');
                    if (!array_key_exists($city_id, $citys)) $this->error('非法操作!');
                    $areas = $this->m2('citys')->field(['id', 'name'])->where(['pid' => $city_id])->select();
                    $this->ajaxReturn($areas);
                } elseif (isset($_POST['submit']) && $_POST['submit'] == 1) {
                    //提交审核
                    $this->submit();
                } elseif (isset($_POST['submit']) && $_POST['submit'] == 0) {

                    //保存并预览
                    $this->save();
                } elseif (!isset($_POST['submit']) && !empty($_POST['space_id'])) {
                    $space_id = I('post.space_id');
                    $space = D('SpaceCitysView')->where(['spaceId' => $space_id])->find();
                    $environment_pics = $this->m2('pics')->field(['id', 'path'])->where(['group_id'=>$space['pic_group_id']])->select();
                    if(!empty($environment_pics)){
                        foreach($environment_pics as $k => $v){
                            $environment_pics[$k]['path'] = thumb($v['path']);
                        }
                    }
                    $space['environment_pics'] =$environment_pics;
                    $this->ajaxReturn($space);

                }
                $this->error('非法提交');
                exit;
            }

            $tips_id = I('get.tips_id', null);
            $rs = D('TipsEditView')->where(['id' => $tips_id])->find();
            if (empty($rs)) {
                $this->error('要修改的活动不存在!');
            }
            $rs['stop_buy_time'] = round($rs['stop_buy_time'] / 3600);
            $rs['times'] = $this->m2('TipsTimes')->where(['tips_id' => $tips_id])->select();

            $pics = $this->m2('pics')->field(['id', 'path'])->where(['group_id' => $rs['pics_group_id']])->select();

            foreach($pics as $k => $v){
                $pics[$k]['path'] = thumb($v['path'], 1);
            }
            $rs['pics_group'] = $pics;
            $pics = $this->m2('pics')->field(['id', 'path'])->where(['group_id' => $rs['menu_pics_group_id']])->select();
            foreach($pics as $k => $v){
                $pics[$k]['path'] = thumb($v['path'], 1);
            }
            $rs['menu_pics_group'] = $pics;
            $pics2 = $this->m2('pics')->field(['id', 'path'])->where(['group_id' => $rs['environment_pics_group_id']])->select();
            foreach($pics2 as $k => $v){
                $pics2[$k]['path'] = thumb($v['path']);
            }
            $rs['environment_pics_group'] = $pics2;
            $rs['tags'] = D('TagView')->where(['tips_id' => $tips_id, 'type' => 1, 'official' => 0])->getField('id', true);
            $menus = $this->m2('TipsMenus')->where(['tips_id' => $tips_id])->select();//[转换数据表tips_menu->tips_menus(2016-11-21)]
            $_menus = [];
//            //亮点
            $rs_edges = explode(',',$rs['edges']);
            foreach($rs_edges as $rskey =>$rsval){
                $keyed = $rskey + 1;
                $rs['edge_'.$keyed] = $rsval;
            }
//            if(!empty($menus)){
//                foreach($menus as $k=>$m){
//                    if(empty($m['pid'])){
//                        $menus_a[$m['id']]['name'] = $m['name'];
//                    }else{
//                        $menus_a[$m['pid']]['value'][] = $m['name'];
//                    }
//                }
//                $sortmenu =  array_values($menus_a);
//                $rs['menu'] = $sortmenu;
//            }else{
//                $model =C('MENUS');
//                foreach($model as $key_m =>$md){
//                    if($key_m =='A'){
//                        foreach($md as $me){
//                            $_menuModel[$key_m][]['name'] = 'A@'.$me;
//                        }
//                    }elseif($key_m =='B'){
//                        foreach($md as $me){
//                            $_menuModel[$key_m][]['name'] = 'B@'.$me;
//                        }
//                    }elseif($key_m =='C'){
//                        foreach($md as $me){
//                            $_menuModel[$key_m][]['name'] = 'C@'.$me;
//                        }
//                    }
//                }
//                $rs['menu'] = $_menuModel;
//            }

//            foreach($menus as $m){
//                if(strpos($m['food_type'], 'Tips') === false){
//                    $_val = [];
//                    if(!empty($m['food_name'])){
//                        $_val = explode(',', $m['food_name']);
//                    }
//                }else{
//                    $_val = html_entity_decode($m['food_name']);
//                }
//
//                $_menu[] = [
//                    'name' => $m['food_type'],
//                   // 'value' => html_entity_decode($_val),
//                    'value'=>$_val,
//                ];
//            }
//            $rs['menu'] = $_menu;
            if(!empty($menus)){
                foreach ($menus as $k => $m) {
                    if (empty($m['pid'])) {
                        $menus_a[$m['id']]['name'] = str_replace(['A@', 'B@', 'C@'], '', $m['name']);
                    }else{
                        if(strpos($menus_a[$m['pid']]['name'],'Tips') ===false){
                            if($m['name']!=''){
                                $menus_a[$m['pid']]['value'][] = $m['name'];
                            }else{
                                $menus_a[$m['pid']]['value'] = '';

                            }
                        }else{
                            $menus_a[$m['pid']]['value'] =  $m['name'];
                        }
                    }
                }

                $sortmenu = array_values($menus_a);
                $rs['menu'] = $sortmenu;
            }
            //获取活动分类
            $categorys = $this->m2('category')->field(['id', 'name'])->where(['type' => 0])->order(['order'])->select();
            //获取活动标签
            $tags = $this->m2('tag')->field(['id', 'name'])->where(['type' => 1, 'official' => 0])->select();
            //
            //获取默认城市下的区
            if($rs['area_alt'] == '市'){
                $rs['city_id'] = $rs['citys_id'];
                $rs['area_id'] = '';
                $areas = $this->m2('citys')->field(['id', 'name'])->where(['pid' => $rs['citys_id']])->select();
            }elseif($rs['area_alt'] == '区'){
                $rs['city_id'] = $rs['area_pid'];
                $rs['area_id'] = $rs['citys_id'];
                $areas = $this->m2('citys')->field(['id', 'name'])->where(['pid' => $rs['area_pid']])->select();
            }else{
                $rs['city_id'] = 224;
                $rs['area_id'] = 2095;
                $areas = $this->m2('citys')->field(['id', 'name'])->where(['pid' => 224])->select();
            }
            $rs['context_title'] = '';
            $rs['context_text'] = '';
            if(!empty($rs['content']) && strpos($rs['content'], '%#%$%') !== false){
                $arr = explode('%#%$%', $rs['content']);
                $rs['context_title'] = $arr[0];
                $rs['context_text'] = $arr[1];
            }
            $allselect  = $this->m2('space')->field('id')->where(['member_id'=>$rs['member_id']])->select();
            foreach($allselect as $spaceid){
                $space_id_var[] = $spaceid['id'];
            }
            if (!empty($space_id_var) ) {
                $spaceselect = D('SpaceCitysView')->where(['spaceId' => ['IN', join(',',$space_id_var)]])->select();
            }
            //select地区选项
            if (!empty($rs['space_id']) ) {
                $space = D('SpaceCitysView')->where(['spaceId' =>$rs['space_id']])->find();
                if(!empty($space)) {
                    $rs['alladresss'] = $space['city_name'] . "市&nbsp;&nbsp;" . $space['areaname'] . "区&nbsp;&nbsp;" . $space['address'];
                    $rs['simpleaddress'] = $space['spacename'];
                    $rs['address'] = $space['address'];
                    $rs['longitude'] = $space['longitude'];
                    $rs['latitude'] = $space['latitude'];
                }else{
                    $rs['alladresss'] = '';
                    $rs['simpleaddress'] = '';
                    $rs['address'] = '';
                    $rs['longitude'] = '';
                    $rs['latitude'] = '';
                }
            }else{
                $rs['alladresss'] = '';
                $rs['simpleaddress'] = '';
                $rs['address'] = '';
                $rs['longitude'] = '';
                $rs['latitude'] = '';

            }
            //须知列表
            $notices = $this->m2('TipsNotice')->field(['id', 'context', 'status'])->where(['status' => ['IN', '1,2,3,4']])->select();
//            foreach($notices as $key=>$val){
//                if((in_array($val['id'],explode(',',$rs['notice'])) && $val['status'] == 3) ||  $val['status'] ==1 || $val['status'] ==2 ){
//                    $notices_arr[] =  $val;
//                }
//            }
            $data = [
                'data' => $rs,
                'categorys' => $categorys,
                'tags' => $tags,
                'p' => in_array(76, $rs['tags']) ? 1: 0,
                'citys' => $citys,
                'areas' => $areas,
                'notices' => $notices,
                'spaceselect' => $spaceselect
            ];
            $this->assign($data);
            $this->view();
        }

        //模糊查找会员
        public function getUser(){
            if(IS_AJAX){
                $search_key = I('post.search_key');

                if(isset($search_key) && $search_key != ''){
                    $condition = 'nickname LIKE '."'%$search_key%'";
                    $member_rs = D('MemberView')->field('id,nickname,telephone,sex,path')->where($condition)->limit(20)->select();
                    $this->ajaxReturn($member_rs);
                }
            }
        }

        //模糊查找达人
        public function getDaren(){
            if(IS_AJAX){
                $search_key = I('post.search_key');

                if(isset($search_key)&&$search_key!=''){
                    $condition = 'nickname LIKE '."'%$search_key%' And C.tag_id =18";
                    $member_rs = D('DarenInfoView')->field('id,nickname,telephone,sex,path')->where($condition)->limit(20)->select();
                    $this->ajaxReturn($member_rs);
                }
            }
        }

        //会员活动场地
        public function addressArea(){
            if(IS_AJAX) {
                $member_id = I('post.member_id');
                if(isset($member_id)&&$member_id !='') {
                    $space =D('SpaceCitysView')->where(['member_id' => $member_id])->select();
                    $this->ajaxReturn($space);
                }
            }
        }

        //提交审核
        private function submit(){
            $tips_id = I('post.tips_id');
            $rs = D('TipsEditView')->where(['id' => $tips_id])->find();
            if(empty($rs)){
                $this->error('非法提交!');
            }
            //获取标签
            $tags = $this->m2('tag')->join('__TIPS_TAG__ on tag_id=__TAG__.id')->where(['tips_id' => $tips_id])->getField('tag_id', true) ?: [];
            //获取上级城市ID
            $citys = D('CityView')->field(['district_id', 'district_name', 'city_id', 'city_name', 'province_id', 'province_name'])->where(['district_id' => $rs['city_id']])->find();

            //获取时间段
            $times = $this->m2('TipsTimes')->field(['id, start_time, end_time, phase'])->where(['tips_id' => $tips_id])->select();

            //获取菜单
            $menu = $this->m2('TipsMenus')->where(['tips_id' => $tips_id])->count();//[转换数据表tips_menu->tips_menus(2016-11-21)]

            if(empty($rs['title']))$this->error('活动标题不能为空！');
            if(empty($rs['price']))$this->error('活动价格不能为空！');
            if(!is_numeric($rs['category_id']))$this->error('活动分类不能为空！');
            if(empty($rs['address']))$this->error('活动详细地址不能为空！');
            if(empty($rs['simpleaddress']))$this->error('活动简写地址不能为空！');
            if(empty($rs['longitude']))$this->error('活动坐标经度不能为空！');
            if(empty($rs['latitude']))$this->error('活动坐标纬度不能为空！');
            if(empty($tags))$this->error('活动标签不能为空！');
            if(empty($citys))$this->error('活动城市区域不能为空！');
            if(empty($times))$this->error('活动时间节点不能为空！');
            if(empty($rs['pic_id']) || empty($rs['pics_group_id']))$this->error('活动主图不能为空！');
            if(empty($rs['city_id']))$this->error('活动城市不能为空！');
            if(empty($rs['environment_pics_group_id']))$this->error('环境图组不能为空！');
            if(empty($rs['menu_pics_group_id']))$this->error('菜单图组不能为空！');
            if(empty($menu))$this->error('菜单不能为空！');

            $this->m2('tips')->save(['id' => $tips_id, 'status' => 1, 'is_pass' => 0]);
            $this->m2('MemberApply')->where(['member_id'=>$rs['member_id'],'type'=>0,'type_id'=>$tips_id,'is_pass'=>0])->delete();
            $this->m2('MemberApply')->data(['member_id'=>$rs['member_id'],'type'=>0,'type_id'=>$tips_id,'is_pass'=>0])->add();
            session('EditingTipsID', null);

            //记录活动修改快照信息
            $this->SaveSnapshotLogs($tips_id,0);
            $this->success('发布成功！等待审核……');
        }

        //提交编辑内容
        private function save(){
            $tips_id = I('post.tips_id');
            if (session('?copyTips')) {
                $str = session('copyTips');
                session('copyTips', null);
                $arr = explode('to', $str);
                if ($arr[0] == $tips_id) {
                    $rs = $this->m2('TipsSub')->field(['pics_group_id', 'menu_pics_group_id'])->where(['tips_id' => $tips_id])->find();
                    $pics_group_id = $rs['pics_group_id'];
                    $menu_pics_group_id = $rs['menu_pics_group_id'];
                    $tips_id = $arr[1];
                    $copy = true;
                }
            }
            $member_id = I('post.member_id');
            $rs = $this->m2('Tips')->where(['id' => $tips_id, 'member_id' => $member_id])->find();
            $member = $this->m2('Member')->join('__MEMBER_INFO__ AS B ON B.member_id = __MEMBER__.id')->where(['id' => $member_id])->find();
            if (empty($rs)) {
                $this->error('非法提交!');
            }
            $this->m2()->startTrans();
            $data = ['member_id' => $member_id];
            $data['category_id'] = I('post.category_id');
            $data['title'] = $_POST['title'];
            $data['intro'] =  $_POST['intro'];
            //$data['edge_1'] = I('post.edge_1','');
            //$data['edge_2'] = I('post.edge_2','');
            // $data['edge_3'] = I('post.edge_3','');
            $data['edges'] = $_POST['edges'];
            $data['tips_content'] =$_POST['tips_content'];
            $data['pic_id'] = I('post.pic_id');//活动主图
            //上线的活动不能修改 价格/包场折扣
            if ($rs['status'] != 1 || $rs['is_pass'] != 1) {
                $data['price'] = I('post.price');
                if (I('post.discount') < 0 || I('post.discount') > 100) {
                    $this->error('包场折扣必须在0~100之间取值!');
                }
            }
            $data['buy_status'] = I('post.buy_status');
            $data['discount'] = I('post.discount');
            $data['space_id'] = I('post.space_id');
            $data['area_id'] = I('post.area_id');
            $data['citys_id'] = I('post.citys_id');
            $data['longitude'] = I('post.longitude');
            $data['latitude'] = I('post.latitude');
            $data['simpleaddress'] = $_POST['simpleaddress'];
            $data['address'] = $_POST['address'];
            if (isset($_POST['context_text']) && !empty($_POST['context_text'])) {
                $data['content'] = I('post.context_title', '特色') . '%#%$%' . $_POST['context_text'];
            } else {
                $data['content'] = ['exp', 'null'];
            }
            $data['notice'] = I('post.notice');
            $data['is_public'] = I('post.is_public', 1);
            if (I('post.tags_id')) {
                //活动标签
                $tag_ids = explode(',', I('post.tags_id'));
                //删除旧标签
                $sql = $this->m2('Tag')->field(['id'])->where(['official' => 0, 'type' => 1])->buildSql();
                $this->m2('TipsTag')->where(['tips_id' => $tips_id, 'tag_id' => ['EXP', "in {$sql}"]])->delete();
                //添加新标签
                $tags = [];
                foreach ($tag_ids as $id) {
                    $tags[] = ['tips_id' => $tips_id, 'tag_id' => $id];
                }
                $this->m2('TipsTag')->addAll($tags);
            }


            if (!empty($pics_group_id)) {
                $data['pics_group_id'] = $this->m2('PicsGroup')->add(['type' => 0]);
                $pics = $this->m2('pics')->where(['group_id' => $pics_group_id])->select();
                $_data = [];
                foreach ($pics as $pic) {
                    $_data[] = [
                        'group_id' => $data['pics_group_id'],
                        'type' => 0,
                        'path' => $pic['path'],
                        'size' => $pic['size'],
                        'is_used' => 1
                    ];
                }
                $this->m2('pics')->addAll($_data);
            } elseif (I('post.group_pic_ids')) {
                //活动图组
                $pic_ids = I('post.group_pic_ids');
                if (!empty($rs['pics_group_id'])) {
                    //删除旧图组
                    $this->m2('pics')->where(['group_id' => $rs['pics_group_id']])->delete();
                    $group_id = $rs['pics_group_id'];
                } else {
                    $group_id = $this->m2('PicsGroup')->add(['type' => 0]);
                }
                //修改上传来的图片
                $this->m2('pics')->where(['id' => ['IN', $pic_ids]])->save(['group_id' => $group_id]);
                $data['pics_group_id'] = $group_id;
            }

            if (!empty($menu_pics_group_id)) {
                $data['menu_pics_group_id'] = $this->m2('PicsGroup')->add(['type' => 0]);
                $pics = $this->m2('pics')->where(['group_id' => $menu_pics_group_id])->select();
                $_data = [];
                foreach ($pics as $pic) {
                    $_data[] = [
                        'group_id' => $data['menu_pics_group_id'],
                        'type' => 0,
                        'path' => $pic['path'],
                        'size' => $pic['size'],
                        'is_used' => 1
                    ];
                }
                $this->m2('pics')->addAll($_data);
            } elseif (I('post.menu_group_pic_ids')) {
                //菜单图组
                $pic_ids = I('post.menu_group_pic_ids');
                if (!empty($rs['menu_pics_group_id'])) {
                    //删除旧图组
                    $this->m2('pics')->where(['group_id' => $rs['menu_pics_group_id']])->delete();
                    $group_id = $rs['menu_pics_group_id'];
                } else {
                    $group_id = $this->m2('PicsGroup')->add(['type' => 0]);
                }
                //修改上传来的图片
                $this->m2('pics')->where(['id' => ['IN', $pic_ids]])->save(['group_id' => $group_id]);
                $data['menu_pics_group_id'] = $group_id;
            }

            if (!empty($environment_pics_group_id)) {
                $data['environment_pics_group_id'] = $this->m2('PicsGroup')->add(['type' => 0]);
                $pics = $this->m2('pics')->where(['group_id' => $environment_pics_group_id])->select();
                $_data = [];
                foreach ($pics as $pic) {
                    $_data[] = [
                        'group_id' => $data['environment_pics_group_id'],
                        'type' => 0,
                        'path' => $pic['path'],
                        'size' => $pic['size'],
                        'is_used' => 1
                    ];
                }

                $this->m2('pics')->addAll($_data);

            } elseif (I('post.environment_group_pic_ids')) {
                //环境图组
                $pic_ids = I('post.environment_group_pic_ids');
                if (!empty($rs['environment_pics_group_id'])) {
                    //删除旧图组
                    $this->m2('pics')->where(['group_id' => $rs['environment_pics_group_id']])->delete();
                    $group_id = $rs['environment_pics_group_id'];
                } else {
                    $group_id = $this->m2('PicsGroup')->add(['type' => 0]);
                }
                //修改上传来的图片

                $pic_oneid = explode(',', $pic_ids);
                $data['space_pic_id'] = $pic_oneid[0];
                $this->m2('pics')->where(['id' => ['IN', $pic_ids]])->save(['group_id' => $group_id]);
                $data['environment_pics_group_id'] = $group_id;
                $data['pic_group_id'] = $group_id;
            } else {
                $this->error('场地环境图不能为空!');
            }

            if (!$copy) {
                $times = I('post.times');
                if (count($times) == 0) {
                    $this->error('不能没有时间段!');
                }
                $times_ids = [];
                $num = 1;
                foreach ($times as $row) {
                    $row['tips_id'] = $tips_id;
                    if (empty($row['start_time'])) $this->error('活动开始时间不能为空!');
                    $row['start_time'] = strtotime($row['start_time'] . ':00');
                    if (empty($row['end_time'])) $this->error('活动结束时间不能为空!');
                    $row['end_time'] = strtotime($row['end_time'] . ':00');
                    if ($row['start_time'] >= $row['end_time']) $this->error('开始时间必须小于结束时间!');
                    if (empty($row['start_buy_time']))
                        $row['start_buy_time'] = 0;
                    else
                        $row['start_buy_time'] = strtotime($row['start_buy_time'] . ':00');


                    $timeArr = $row['start_time'] - (1 * 24 * 3600);
                    if (empty($row['stop_buy_time'])) $row['stop_buy_time'] = $timeArr;
                    else
                        $row['stop_buy_time'] = strtotime($row['stop_buy_time'] . ':00');

                    if ($row['min_num'] > $row['max_num']) $this->error('成局人数不能小于接待人数!');

                    if (($rs['status'] == 1 && $rs['is_pass'] == 1 && empty($row['id'])) || ($rs['status'] == 3 && $rs['is_pass'] !=1 && empty($row['id'])) ) $row['stock'] = $row['max_num'];
//                    $row['stock'] = $row['max_num'];

                    if ($rs['status'] == 1 && $rs['is_pass'] == 1 )$row['release_time']=time();
                    $row['phase'] = $num++;
                    if (empty($row['id'])) {
                        unset($row['id']);
                        $times_ids[] = $this->m2('TipsTimes')->add($row);
                    } else {
                        $times_ids[] = $row['id'];
                        $this->m2('TipsTimes')->save($row);
                    }
                }
                $rs = $this->m2('OrderWares')->join('__ORDER__ a on a.id=order_id')->where(['type' => 0, 'ware_id' => $tips_id, 'tips_times_id' => ['NOT IN', join(',', $times_ids)], 'status' => 1, 'act_status' => ['IN', '1,2,3,4,5']])->find();
                if (!empty($rs)) $this->error('已产生订单的时间段不能删除!');
                $this->m2('TipsTimes')->where(['tips_id' => $tips_id, 'id' => ['NOT IN', join(',', $times_ids)]])->delete();
            }
            if (!empty($data['space_id'])) {
                $spacearr = $this->m2('space')->where(['id' => $data['space_id']])->find();
                $cityarr = D('CityView')->where(['district_id' => $spacearr['city_id']])->find();
                $data['district_id'] = $cityarr['district_id'];
                $data['citys_id'] = $cityarr['city_id'];
            }
            //添加菜单
            $menu = $_POST['menu'];
            if (!empty($menu)) {
                $this->m2('tips_menus')->where(['tips_id' => $tips_id])->delete();//[转换数据表tips_menu->tips_menus(2016-11-21)]
                $menus = explode('|', $menu);
                foreach($menus as $mn){
                    $mns = explode(':', $mn);
//                    $menu_c = explode('@',$mns[0]);
                    $pid = $this->m2('tips_menus')->add([
                        'id' => ['exp', 'null'],
                        'tips_id' => $tips_id,
                        'name' => trim($mns[0]),
                        'pid' => ['exp', 'null']

                    ]);
                    $vals = explode(',', trim(str_replace([',,', '[_maohao_]', '[_aite_]'], [',', ':', '@'], $mns[1]), ','));
                    foreach($vals as $val){
                        $this->m2('TipsMenus')->add([
                            'id' => ['exp', 'null'],
                            'tips_id' => $tips_id,
                            'name' => $val,
                            'pid' => $pid
                        ]);
                    }
                }
//                foreach ($menus as $menu) {
//                    $menu = explode(':', $menu);
//                    $menu_c = explode('@',$menu[1]);
//                    $_menu[] = [
//                        'tips_id' => $tips_id,
//                        'food_type' => $menu[0],
//                        'food_name' => trim(str_replace([',,', '[_maohao_]', '[_aite_]'], [',', ':', '@'], $menu[1]), ',')
//                    ];
//                }
//                if (!empty($data)) {
//                    $this->m2('TipsMenu')->addAll($_menu);
//                }
            }

            //添加须知
            $addnotice = $_POST['addnotice'];
            if(!empty($addnotice)){
                $nn = [];
                foreach($addnotice as $val){
                    if(!empty($val)){
                        $m['context'] = $val['notice'];
                        $m['status'] = 3;
                        $notice_id = $this->m2('TipsNotice')->add($m);
                        $nn[]=$notice_id;
                    }
                }
            }

            $datavar = [
                'member_id' => $data['member_id'],
                'nickname' => $member['nickname'],
                'telephone' => $member['telephone'],
                'weixincode' => $member['weixincode'],
                'name' => $data['simpleaddress'],
                'category_id' => $data['category_id'],
                'city_id' => $data['area_id'],
                'address' => $data['address'],
                'latitude' => $data['latitude'],
                'longitude' => $data['longitude'],
                'pic_id' => $data['pic_id'],
                'pic_group_id' => $data['environment_pics_group_id']

            ];
            if (empty($data['space_id'])) {
                $space_id = $this->m2('space')->add($datavar);
                $data['space_id'] = $space_id;
            }
            //统一修改
            $_data = [];
            $pace_data = [];
            foreach ($data as $key => $row) {
                if ($row === 0 || $row === '0' || !empty($row) || strpos($key, 'edge') !== false) {
                    $_data[$key] = $row;
                    $pace_data[$key] = $row;
                }
            }
            $notice_str = $this->m2('TipsSub')->where(['tips_id'=>$tips_id])->getField('notice');
            if(!empty($notice_str)){

                $diff_notice = array_diff(explode(',',$notice_str),explode(',',$data['notice']));
                if(!empty($diff_notice)){
                    $this->m2('TipsNotice')->where(['id'=>['in',join(',',$diff_notice)],'status'=>3])->save(['status'=>0]);
                }
            }
            $no = $data['notice'] || '';
            if(!empty($nn) && !empty($no)){
                $notice = explode(',',$no);
                $_data['notice'] = implode(',',array_merge($notice, $nn));
            } elseif(!empty($nn)) {
                $_data['notice'] = implode(',',$nn);
            }else{
                $_data['notice'] = $no;
            }
            $_data['content'] = $data['tips_content'];
            //环境图
            $pace_data['pic_id'] = $data['space_pic_id'];
            $pace_data['telephone'] = $data['telephone'];
            $pace_data['nickname'] = $data['nickname'];
            $pace_data['weixincode'] = $member['weixincode'];
            if (empty($data['space_id'])) {
                $pace_data['city_id'] = $data['area_id'];
            }
            if(!empty($_data)) {

                $this->m2('space')->where(['id' => $data['space_id']])->save($pace_data);
                $this->m2('TipsSub')->where(['tips_id' => $tips_id])->save($_data);
                $this->m2('tips')->where(['id' => $tips_id])->save($_data);
                $_data['last_update_time'] = time();
                $this->m2()->commit();

                //记录活动修改快照信息
                $this->SaveSnapshotLogs($tips_id,0);
                $this->success($tips_id);
            }
        }

        //活动列表
        public function index(){
            $this->actname = '活动列表';
            if(isset($_GET['status']) && is_numeric($_GET['status'])){
                $condition = "A.status = " . $_GET['status'];
            }else{
                $condition = "A.status = 1 ";
            }
            //$condition = " (I.type=0 OR I.type is null) ";
            if(IS_GET && $_GET!=null){                      //条件查询

                $tips_id = I('get.id');
                $tips_title = I('get.title');
                $member_nickname = I('get.member');
                $tips_start_time = strtotime(I('get.start_time'));
                $tips_end_time = strtotime(I('get.end_time'));
                $tips_category = I('get.category');
                $tips_citys = I('get.citys');
                $tips_theme = I('get.theme');
                $tips_tag = I('get.tag');
                $tips_is_pass = I('get.is_pass');
                $buy_status = I('get.buy_status', null);

                //$condition = array(); //新版 12/21
                $tips_id &&  $condition .= " AND A.id='$tips_id' ";
                $tips_title && $condition .= " AND A.title LIKE '%$tips_title%' ";
                //开始-结束时间

                if(!empty($tips_start_time) && !empty( $tips_end_time)){
                    $time_tips_id = $this->m2('tips_times')->where(['start_time'=>['GT',$tips_start_time],'end_time'=>['LT',$tips_end_time]])->getField('tips_id',true);
                    $time_tips_id = join(',',$time_tips_id);
                }elseif(!empty($tips_start_time) && empty($tips_end_time)){
                    $time_tips_id = $this->m2('tips_times')->where(['start_time'=>['GT',$tips_start_time]])->getField('tips_id',true);
                    $time_tips_id = join(',',$time_tips_id);
                }elseif(!empty($tips_end_time) && empty($tips_start_time)){
                    $time_tips_id = $this->m2('tips_times')->where(['end_time'=>['LT',$tips_end_time]])->getField('tips_id',true);
                    $time_tips_id = join(',',$time_tips_id);
                }
//                print_r($this->m2('tips_times')->getLastSql());
                if($time_tips_id) {
                    $condition .= " AND A.id IN ($time_tips_id) ";
                }
//                if($time_tips_id){
//                    $start_end_time_ids = $start_time_tips_id.','.$end_time_tips_id;
//                    $start_end_time_ids = trim($start_end_time_ids,',');
//                    $start_end_time_ids && $condition .= " AND A.id IN ($start_end_time_ids) ";
//                }



                /*$tips_start_time && $condition .= " AND K.start_time > $tips_start_time ";
                $tips_end_time && $condition .= " AND K.end_time < $tips_end_time ";*/
                $member_nickname && $condition .= " AND D.nickname LIKE '%$member_nickname%' ";
                $tips_category && $condition .= " AND A.category_id = '$tips_category' ";
                //城市子区域查询
                $area = $this->m2('Citys')->where(['pid'=>$tips_citys])->getField('id',true);
                $area_ids = array();
                $area_ids = $area;
                $area_ids[] = $tips_citys;
                $tips_citys && $condition .= " AND F.citys_id in (" .join(',',$area_ids). ") ";
                //$tips_theme && $condition .= " AND J.id='$tips_theme' ";
                //标签筛选
                if(!empty($tips_tag)){
                    $tag_tips_id = $this->m2('TipsTag')->where(['tag_id' => $tips_tag])->getField('tips_id',true);
                    $tag_tips_id = join(',',$tag_tips_id);
                    if(!empty($tag_tips_id)){
                        $condition .= " AND A.id IN ($tag_tips_id) ";
                    }
                }
                //$tips_tag && $condition .= " AND H.id='$tips_tag' ";
                $tips_is_pass!=='' && $condition .= " AND A.is_pass='$tips_is_pass' ";
                $buy_status!==null && $condition .= " AND A.buy_status='$buy_status' ";
                //专题筛选
                if(!empty($tips_theme)){
                    $tipsId = $this->m2('theme_element')->where(['theme_id'=>$tips_theme,'type'=>0])->getField('type_id',true);
                    $tipsId = join(',',$tipsId);
                    if(!empty($tipsId)){
                        $condition .= " AND A.id IN ($tipsId)";
                    }
                }

                $this->assign('search_id',$tips_id);
                $this->assign('search_title',$tips_title);
                $this->assign('search_member',$member_nickname);
                $this->assign('search_category',$tips_category);
                $this->assign('search_citys',$tips_citys);
                $this->assign('search_theme',$tips_theme);
                $this->assign('search_tag',$tips_tag);
                $this->assign('search_is_pass',$tips_is_pass);
                $this->assign('search_status',$_GET['status']);
                $this->assign('search_buy_status',$buy_status);
                $tips_start_time && $this->assign('search_start_time',date('Y-m-d H:i',$tips_start_time));
                $tips_end_time && $this->assign('search_end_time',date('Y-m-d H:i',$tips_end_time));

            }
            //$condition['I.type'] = array('NEQ',1);
            $datas['datas'] = D('TipsView')->where($condition)->page(I('get.page'), 20)->order('is_top desc,datetime desc')->group('A.id')->select();
            if(!empty($datas['datas'])){
                foreach($datas['datas'] as $re){
                    $ids[] = $re['id'];
                }
                $ids = join(',',$ids);
                //数据处理
                $tag_rs = $this->m2('tips_tag')->join('__TAG__ ON __TIPS_TAG__.tag_id = __TAG__.id')->where(['ym_tips_tag.tips_id'=>['IN',$ids]])->select();
                //$marketing_rs = $this->m2('marketing')->field('type_id,end_time')->where(['type'=>0,'type_id'=>['IN',$ids]])->order('id desc')->select();
                $theme_rs = $this->m2('theme_element')->join('__THEME__ ON __THEME__.id=__THEME_ELEMENT__.theme_id')->where(['ym_theme_element.type'=>0,'ym_theme_element.type_id'=>['IN',$ids]])->select();

                foreach ($datas['datas'] as $key=>$row) {
                    $datas['datas'][$key]['tips_id'] = $row['id'];

                    $tags = '';
                    $theme = '';
                    foreach ($tag_rs as $key2 => $row2) {
                        if ($row2['tips_id'] == $row['id']) {
                            $tags .= $row2['name'] . ',';
                        }
                    }

                    foreach($theme_rs as $rs){
                        if($rs['type_id'] == $row['id']){
                            $theme .= $rs['title'] . ',';
                        }
                    }

                    $datas['datas'][$key]['tag_name'] = $tags;
                    $datas['datas'][$key]['theme_title'] = $theme;


                    //判断是否在促销期间
                    /*$set_marketing = false;
                    if (!empty($marketing_rs)) {
                        foreach ($marketing_rs as $row4) {
                            if ($row4['type_id'] == $row['id']) {
                                //echo $row4['end_time'];
                                if ($row4['end_time'] > time()) {
                                    $set_marketing = true;
                                }
                            }
                        }
                    }
                    $datas['datas'][$key]['marketing_status'] = $set_marketing?1:0;*/

                    //获取城市
                    //$citys = D('CityView')->where(['A.id|B.id' => $row['tips_sub_citys_id']])->find();
                    $citys = $this->m2('citys')->where(['id' => $row['tips_sub_citys_id']])->find();
                    $datas['datas'][$key]['citys_name'] = $citys['name'];

                    switch($row['status']){
                        case '1':
                            $datas['datas'][$key]['status'] = ['审核中', '正常', '未通过'][$row['is_pass']];
                            break;
                        case '2':
                            $datas['datas'][$key]['status'] = '下架';
                            break;
                        case '3':
                            $datas['datas'][$key]['status'] = '草稿';
                            break;
                    }

                    if($row['is_public']==0)$datas['datas'][$key]['title'] = '[非公开]'.$datas['datas'][$key]['title'];
                    if($row['is_top']==1)$datas['datas'][$key]['title'] = '[置顶]'.$datas['datas'][$key]['title'];
                    if($row['buy_status']==2)$datas['datas'][$key]['title'] = '[定制]'.$datas['datas'][$key]['title'];
                    if($row['buy_status']==1)$datas['datas'][$key]['title'] = '[包场]'.$datas['datas'][$key]['title'];
                    $datas['datas'][$key]['member_nickname'] = '昵称：'.$row['member_nickname']."<br/>".'Host ID：'.$row['member_id']."<br/>";
                }
            }

            //table页面参数设置
            $datas['operations'] = [
                '查看分期' => "showTimes(%id,%category_id)",
                '修改标签' => "tips_tags(%id)",
                //'添加促销' => "addMarketing(%id)",
                '历史促销' => "marketingHistory(%id)",
                '通过' => [
                    'style' => 'success',
                    'fun' => 'checkout(%id, 1)',
                    'condition' => "%status=='审核中'"
                ],
                '下架' => [
                    'style' => 'warning',
                    'fun' => 'checkout(%id, 2)',
                    'condition' => "%status=='正常'"
                ],
                '上架' => [
                    'style' => 'success',
                    'fun' => 'checkout(%id, 3)',
                    'condition' => "%status=='下架'"
                ],
                '提交审核' => [
                    'style' => 'success',
                    'fun' => 'checkout(%id, 4)',
                    'condition' => "%status=='草稿'"
                ],
                '拒绝' => [
                    'style' => 'danger',
                    'fun' => 'checkout(%id,0)',
                    'condition' => "%status=='审核中'"
                ],
                '设置为首页推荐' => "setToHome(%id, 0)",
                /*'设为推荐'=> array(
                    'style' => 'success',
                    'fun' => 'setTop(%id,1)',
                    'condition' => '%is_top==0'
                ),
                '取消推荐'=> array(
                    'style' => 'danger',
                    'fun' => 'setTop(%id,0)',
                    'condition' => '%is_top==1'
                ),*/
                //'添加促销'=> array(
                //    'style' => 'success',
                //    'fun' => 'addMarketing(%id)',
                //    'condition' => '%marketing_status==0'
                //),
                //'取消促销'=> array(
                //    'style' => 'danger',
                //    'fun' => 'cancelMarketing(%id)',
                //    'condition' => '%marketing_status==1'
                //)
                '修改' => "location.href='modify.html?tips_id=%id'",
                '开团记录' => "location.href='piecelist.html?tips_id=%id'",
                '修改记录'=> 'showLogs(%id)',
                '复制活动'=> 'copyTips(%id)',
                '购买模式'=> 'buysModel(%id)',
                '设置私房菜'=> 'set_appointment(%id, 76)',
                '删除' => [
                    'condition' => "%status!='审核中'",
                    'style' => 'danger',
                    'fun' => "dataDelete(%id)"
                ]
            ];
            $datas['pages'] = [
                'sum' => D('TipsView')->where($condition)->count('DISTINCT A.id'),
                'count' => 20,
            ];
//            print_r(D('TipsView')->where($condition)->distinct('A.id')->count());
//            print_r(D('TipsView')->getLastSql());
            $datas['batch'] = [
                '批量推荐' => "setAllTop()",
                '批量撤销推荐' => "setAllNotTop()",
                '加入专题' => "joinTheme()",
                '批量移除专题' => "outTheme()"
                //'导出为EXCEL' => "exportExcel()"
            ];
            $datas['lang'] = [
                'id' => 'ID',
                'tips_id' => ['预览', '<a><i class="am-icon-eye" onclick="preview(%*%)"></i></a>'],
                'title' => '活动',
                'member_nickname' => '发布者',
                'price' => '价格',
                'category_name' => '分类',
                'citys_name' => '城市',
                'tag_name' => '标签',
                'status' => '状态'
            ];

            //读取专题列表
            $datas['themes'] = $this->m2('Theme')->order('sort desc,id desc')->select();
            //读取活动标签列表
            $tags = $this->m2('tag')->where('type=1')->select();
            foreach($tags as $key=>$result){
                if($result['official']==0)$tags[$key]['name'] = '(普)'.$tags[$key]['name'];
                if($result['official']==1)$tags[$key]['name'] = '(官)'.$tags[$key]['name'];
            }
            $this->assign('tags',$tags);
            //读取分类列表
            $category = $this->m2('category')->where(['type'=>0])->select();
            //读取城市筛选列表
            $citys = C('CITY_CONFIG');
            $this->assign('citys',$citys);

            $this->assign('category_list',$category);
            $this->assign($datas);

            $this->view();
        }

        //复制活动
        public function copyTips(){
            $tips_id = I('post.tips_id');
            $rs = $this->m2('tips')->where(['id' => $tips_id])->find();
            if(empty($rs))$this->error('活动不存在!');
            //选择达人并创建新活动
            $data = [
                'member_id' => $rs['member_id'],
                'title' => null,
                'category_id' => 1,
                'price' => 0,
                'citys_id' => null,
                'address' => null,
                'longitude' => null,
                'latitude' => null,
                'tel' => null,
                'is_pass' => 0,
                'status' => 3
            ];
            $id = $this->m2('tips')->add($data);
            $data['tips_id'] = $id;
            $this->m2('tips_sub')->add($data);
            session('copyTips', $tips_id . 'to' . $id);
            //记录活动修改快照信息
            $this->SaveSnapshotLogs($id,0);
            $this->success($id);
        }

        //分期查看
        public function showTimes(){
            $data = [];
            $tips_id = I('post.tips_id');
            $data['times'] = $this->m2('tips_times')->where(['tips_id' => $tips_id])->order('phase')->select();
            foreach($data['times'] as $key=>$row){
                $data['times'][$key]['start_time'] = date('Y-m-d H:i',$row['start_time']);
                $data['times'][$key]['end_time'] = date('Y-m-d H:i',$row['end_time']);
                $data['times'][$key]['start_buy_time'] = date('Y-m-d H:i',$row['start_buy_time']);
                $data['times'][$key]['stop_buy_time'] = date('Y-m-d H:i',$row['stop_buy_time']);
                //查看分期的订单
                $data['times'][$key]['buy_num'] =0;
                $rs = $this->m2('OrderWares')->join('__ORDER__ a on a.id=order_id')->where(['type' => 0, 'ware_id' => $tips_id, 'tips_times_id' => $row['id'], 'status' => 1, 'act_status' => ['IN', '1,2,3,4,5']])->find();
                if(!empty($rs)){
                    $data['times'][$key]['buy_num'] =1;
                }
                if($row['is_finish'] != 2){
                    $data['times'][$key]['buttonGame'] ='style="display:inline;"';
                }else{
                    $data['times'][$key]['buttonGame'] ='style="display:none;"';;
                }
            }
            $this->ajaxReturn($data);
        }

        //分期保存
        public function saveTimes(){
            $times = I('post.times');
            if(count($times) == 0){
                $this->error('不能没有时间段!');
            }
            $this->m2()->startTrans();
            $times_ids = [];
            if(!empty($times[0]['tips_id'])){
                $num = $this->m2('TipsTag')->where(['tips_id'=>$times[0]['tips_id'],'tag_id'=>76])->count();
                if($num>0 && count($times)>1){
                    $this->error('该活动已贴上私房菜标签，不能有多个分期!');
                }
            }
            foreach($times as $row){
                $tips_id = $row['tips_id'];
                $tips = $this->m2('tips')->where(['id'=>$tips_id])->find();
                if(empty($row['start_time']))$this->error('活动开始时间不能为空!');
                $row['start_time'] = strtotime($row['start_time'] . ':00');
                if(empty($row['end_time']))$this->error('活动结束时间不能为空!');
                $row['end_time'] = strtotime($row['end_time'] . ':00');
                if($row['start_time'] >= $row['end_time'])$this->error('开始时间必须小于结束时间!');
                if(empty($row['start_buy_time']))
                    $row['start_buy_time'] = 0;
                else
                    $row['start_buy_time'] = strtotime($row['start_buy_time'] . ':00');

                //$addstart_time= $this->m2('tips_times')->field('start_time')->where(['tips_id' => $tips_id])->find();
                //$timeArr = (int)$addstart_time['start_time'] +(1*24*3600);

                $timeArr = $row['start_time']-(1*24*3600);
                if(empty($row['stop_buy_time']))$row['stop_buy_time'] =$timeArr;
                else $row['stop_buy_time'] = strtotime($row['stop_buy_time'] . ':00');

                if($row['min_num'] > $row['max_num'])$this->error('成局人数不能小于接待人数!');
                if($row['lowest_num'] > $row['limit_num'] && $row['limit_num']>0)$this->error('最低购买数不能大于限制购买数!');
                if(empty($row['id'])){
                    unset($row['id']);
                    if($tips['status'] == 1 && $tips['is_pass']==1)$row['release_time']=time();
                    $times_ids[] = $this->m2('TipsTimes')->add($row);
                }else{
                    $times_ids[] = $row['id'];
                    $this->m2('TipsTimes')->save($row);
                }
            }

            $rs = $this->m2('OrderWares')->join('__ORDER__ a on a.id=order_id')->where(['type' => 0, 'ware_id' => $tips_id, 'tips_times_id' => ['NOT IN', join(',', $times_ids)], 'status' => 1])->find();
            if(!empty($rs))$this->error('某时间段已产生订单,不能删除!');
            $this->m2('TipsTimes')->where(['tips_id' => $tips_id, 'id' => ['NOT IN', join(',', $times_ids)]])->delete();
            $this->m2()->commit();
            //记录活动修改快照信息
            $this->SaveSnapshotLogs($tips_id,0);
            $this->success('保存成功!');
        }

        //购买模式
        public function buyModel(){
            $tips_id = I('post.tips_id');
            $rs = $this->m2('tips')->where(['id' => $tips_id])->find();
            if(empty($rs))$this->error('活动不存在!');
            $or = $this->m2('order_wares')->join('__ORDER__ a on order_id=a.id')->where(['type' => 0, 'ware_id' => $tips_id, 'act_status' => 0, 'status' => 1])->find();
            if(!empty($or))$this->error('有未支付订单,不能修改购买模式!');
            if(isset($_POST['limit_time'])){
                $limit_time = I('post.limit_time');
                if($limit_time < 0 || $limit_time > 604800){
                    $this->error('购买时间限制不能超过7天');
                }
                $this->m2('tips')->save(['id' => $tips_id, 'limit_time' => $limit_time]);

                //记录活动修改快照信息
                $this->SaveSnapshotLogs($tips_id,0);
                $this->success('购买模式设置成功!');
            }
            $this->ajaxReturn(['limit_time' => $rs['limit_time']]);
        }

        //活动标签查改
        function getTipsTags(){
            //查询活动标签
            if(IS_AJAX && I('post.tips_id')==''){
                $id = I('post.id');
                $tips_tags = $this->m2('tag')->where('type=1 and official=0')->select();    //普通活动标签
                $official_tips_tags = $this->m2('tag')->where('type=1 and official=1')->select();       //官方活动标签
                $my_tags = $this->m2('tips_tag')->join('__TAG__ ON ym_tips_tag.tag_id = ym_tag.id')->where('tips_id='.$id)->select();
                $label = array();
                $official_label = array();
                foreach($my_tags as $row){
                    if($row['official']==0){
                        $label[] = $row['tag_id'];
                    }else{
                        $official_label[] = $row['tag_id'];
                    }
                }
                $data['tips_tags'] = $tips_tags;
                $data['official_tips_tags'] = $official_tips_tags;
                $data['my_label'] = $label;
                $data['my_official'] = $official_label;

                $this->ajaxReturn($data);
                exit;
            }
            //修改活动标签
            if(IS_AJAX){
                $id = I('post.tips_id');
                $official_tag_ids = I('post.official_tag_ids');
                $tag_ids = I('post.tag_ids');

                $this->m2('tips_tag')->where('tips_id='.$id)->delete();

                foreach($official_tag_ids as $row){
                    $data = [];
                    $data['tips_id'] = $id;
                    $data['tag_id'] = $row;
                    $this->m2('tips_tag')->data($data)->add();
                }

                foreach($tag_ids as $row){
                    $data = [];
                    $data['tips_id'] = $id;
                    $data['tag_id'] = $row;
                    $this->m2('tips_tag')->data($data)->add();
                }

                $this->success('修改成功');
            }
        }

        //批量将活动加入专题
        public function joinTheme() {
            $tips_ids = I('post.tips_ids');

            foreach($tips_ids as $row)
            {
                $data['theme_id'] = I('post.id');
                $data['type'] = 0;
                $data['type_id'] =$row;
                $data['sort'] = 0;

                $theme_element = $this->m2('theme_element');
                $theme_element->data($data)->add();

            }
            $this->success('设置成功');
        }

        //批量置顶
        public function setAllTop(){
            $tips_ids = I('post.tips_ids');
            $type = I('post.type', 1);
            $ids = join(',',$tips_ids);
            $find_data['id'] = ['IN',$ids];
            $save_data['is_top'] = $type?:0;
            $this->m2('tips')->where($find_data)->save($save_data);
            //记录活动修改快照信息
            foreach($ids as $val){
                $this->SaveSnapshotLogs($val,0);
            }

            $this->success(($type?'':'取消') . '推荐成功');
        }

        //单项置顶or取消置顶
        public function setTop(){
            $tips_id = I('post.tips_id');
            $oper = I('post.oper');
            $save_data['id'] = $tips_id;

            if($oper == 0){
                $save_data['is_top'] = 0;
            }elseif($oper == 1){
                $save_data['is_top'] = 1;
            }

            $this->m2('tips')->save($save_data);
            //记录活动修改快照信息
            $this->SaveSnapshotLogs($tips_id,0);

            $this->success('更改成功');
        }

        //tips_index->删除
        Public function delete(){
            if(IS_AJAX){
                $data['id'] = I('post.id');
                $data['status'] = 0;
                $this->m2('tips')->save($data);
                $this->m2('member_collect')->where(['type'=>0 ,'type_id'=>$data['id']])->delete();
                //记录活动修改快照信息
                $this->SaveSnapshotLogs(I('post.id'),0);
                $this->success('删除成功！');
                exit;
            }
            $this->error('非法访问！');
        }

        //tips_index->审核
        public function checkout(){
            $this->actname = '活动审核';
            if(IS_AJAX){
                $data['id'] = I('post.id');
                $oper = I('post.oper');
                $reason = I('post.reason');
                $select_reason = I('post.select_reason');
                $select_array = array(0=>'活动分类不正确',1=>'活动标签不正确',2=>'图片有第三方水印',3=>'活动标题或小标题有极限词');

                if($oper == '1'){//上架&通过
                    $tips_mod = $this->m2('tips');
                    $rs = $tips_mod->where($data)->find();

                    if($rs){
                        $data['is_pass'] = 1;
                        $data['status'] = 1;
                        //$data['start_buy_time'] = time();
                        $tips_mod->save($data);
//                        $this->m2('tips_sub')->where('tips_id='.I('post.id'))->data(array('checkpasstime'=>time()))->save();//有疑问
                        $this->m2('MemberApply')->where(['type'=>0,'type_id'=>$data['id'],'is_pass'=>0])->data(['is_pass'=>1,'update_time'=>time()])->save();
                        //$this->pushMessage($rs['member_id'],'您的活动『'.$rs['title'].'』审核已通过，请留意下单情况，做好准备哦~','sms',4,I('post.id'));
                        $tips_sub = $this->m2('tips_sub')->where(['tips_id'=>I('post.id')])->find();
                        $this->m2('tips_sub')->where('tips_id='.I('post.id'))->data(array('checkpasstime'=>time()))->save();

                        //活动时间段更新下架时间
                        $rstimes =   $this->m2('TipsTimes')->where(['tips_id' => I('post.id')])->select();
                        foreach($rstimes as $v){
                            if($v['stop_buy_time']>time() ){
                                $this->m2('TipsTimes')->where(['id' => $v['id']])->save(['release_time' => time()]);
                            }
                        }

                        $is_public = $this->m2('tips_sub')->where('tips_id='.I('post.id'))->getField('is_public');
                        //推送消息给粉丝
                        if($is_public){
                            $fans_id = $this->m2('MemberFollow')->where(['follow_id'=>$rs['member_id']])->getField('member_id',true);
                            $message_id = $this->m2('Message')->data(['member_id'=>$rs['member_id'],'type'=>4,'type_id'=>$data['id'],'content'=>'我在本平台发布了新活动，一起来耍吧'])->add();
                            foreach($fans_id as $f_id){
                                $this->m2('MemberMessage')->data(['member_id'=>$f_id,'message_id'=>$message_id])->add();
                            }
                        }

                        //记录活动修改快照信息
                        $this->SaveSnapshotLogs(I('post.id'),0);
                        $this->success('活动已通过');
                    }else{
                        $this->error('找不到该活动');
                    }
                }elseif($oper == '0'){//拒绝
                    $order_num = $this->m2('order_wares')->where('type=0 and ware_id='.I('post.id'))->count();
                    if($order_num > 0)$this->success('该活动已产生订单，无法拒绝');
                    $tips_mod = $this->m2('tips');
                    $rs = $tips_mod->where($data)->find();

                    if($rs){
                        $data['is_pass'] = 0;
                        //$data['start_buy_time'] = null;
                        $tips_mod->save($data);
                        $this->m2('tips_sub')->where('tips_id='.I('post.id'))->data(array('checkpasstime'=>null))->save();//有疑问
                        $this->m2('Tips')->data(['id'=>$data['id'],'is_pass'=>0,'status'=>3])->save(); //更改审核状态,（未审核，草稿）

                        //拼接拒绝理由
                        $select_rs = '';
                        if(!empty($select_reason)){
                            foreach($select_reason as $key=>$row){
                                $select_rs .= ($key+1).':'.$select_array[$row].',';
                            }
                        }
                        //更新提交审核记录
                        $this->m2('MemberApply')->where(['type'=>0,'type_id'=>$data['id'],'is_pass'=>0])->data(['is_pass'=>2,'refusal_reason'=>$select_rs.'-'.$reason,'update_time'=>time()])->save();
                        //发送消息
//                        $this->pushMessage($rs['member_id'],'很抱歉，吖咪酱认真审核了您的活动『'.$rs['title'].'』，发现个别信息还需完善，你可在活动管理中修改后再来申请哦！(审核未通过原因：'.$select_rs.'  '.$reason.')',null,4,I('post.id'));

                        $params = array(
                            'title' => $rs['title'],
                            'select_reason' => $select_rs.$reason,
                        );
                        $this->push_Message($rs['member_id'],$params,'SMS_37025113','sms',null,4,$rs['id']);
                        //记录活动修改快照信息
                        $this->SaveSnapshotLogs(I('post.id'),0);
                        $this->success('活动已拒绝');
                    }else{
                        $this->error('找不到该活动');
                    }
                }
            }
        }

        //操作该活动是否成局
        public function GameFinish(){
            $id = I('post.id');
            if(!empty($id)){
                $rs = $this->m2('TipsTimes')->where('id='.$id)->find();
                if($rs['is_finish'] == 2){
                    $this->success('该分期已经设置为未成局状态！');
                }else{
                    $order_arr =  D('OrderTipsView')->where(['G.id'=>$id, 'act_status' => ['in', '1,2,3,4'], 'status' => 1])->order('id desc')->group('A.id')->select();
                    if(!empty($order_arr)) {
                        foreach ($order_arr as $odkey => $odvar) {
                            $datavar['order_id'] = $odvar['id'];
                            $datavar['money'] = $odvar['price'];
                            $datavar['cause'] = '因该分期未成局【系统】';
                            $this->m2('OrderRefund')->add($datavar);
                            $orderid[] = $odvar['id'];
                            $piece_id = $this->m2('OrderPiece')->where(['order_id'=>$odvar['id']])->getField('piece_originator_id');
                            $piece_ids[]=$piece_id;
                        }
                        $orderids = join(',',$orderid);
                        //删除操作未成局的会员订单信息
                        $message_arr = $this->m2('message')->field('id')->where(['type_id'=>['IN',$orderids],'type = 3'])->group('id')->select();
                        foreach($message_arr as $messkey => $messvalue){
                            $message_arrs[] = $messvalue['id'];
                        }
                        $message_string = join(',',$message_arrs);
                        $this->m2('member_message')->where(['message_id'=>['IN',$message_string]])->delete();
                        $this->m2('message')->where(['type_id'=>['IN',$orderids],'type = 3'])->delete();

                        //保存操作未成局的会员订单的订单状态和服务状态
                        $this->m2('order')->where(['id'=>['IN', $orderids]])->save(['act_status'=>8]);
                        $this->m2('order_wares')->where(['order_id'=>['IN', $orderids]])->save(['server_status'=>0]);
                        $this->m2('order_refund')->where(['order_id'=>['IN', $orderids]])->save(['type'=>3]);
                        //将目标期数的库存清空
//                        $this->m2('TipsTimes')->where(['id' => $id])->save(['stock' => 0]);
                        $this->m2('TipsTimes')->where(['id' => $id])->save(['is_finish' => 2]);
                        //将改分期的所有拼团设置为删除状态
                        $this->m2('piece')->where(['type_times_id' => $id])->save(['status' => 0]);

                        //如有拼团订单，修改状态
                        if(!empty($piece_ids)){
                            $this->m2('member_piece')->where(['id'=>['IN',join(',',$piece_ids)]])->save(['act_status'=>10,'status'=>1]);
                        }
                        //推送未成局退款信息通知给客户
                        foreach($order_arr as $row){
//                            $context = "非常抱歉通知你，你购买的“{$row['tips_title']}”，由于未达到成局人数，默认不成局，会在3个工作日内给您退款！";
//                            $this->pushMessage($row['member_id'], $context, 'sms', 3, $row['id'], 0, in_array($row['channel'], [7,8,9]?1:0));

                            //2016-12-29
                            $params = array(
                                'tips_title' =>$row['tips_title'],
                            );
                            $this->push_Message($row['member_id'], $params,'SMS_36185354', 'sms',null, 3, $row['id'], 0, in_array($row['channel'], [7,8,9]?1:0));
                        }
                    }
                    $data['is_finish']=2;
                    $savers = $this->m2('TipsTimes')->where('id='.$id)->save($data);
                    if($savers){
                        //记录活动修改快照信息
                        $this->SaveSnapshotLogs($rs['tips_id'],0);
                        $this->success('该分期设置为未成局状态成功！');
                    }else{
                        $this->error('该分期设置为未成局状态失败！');
                    }

                }
            }
        }

        //活动促销
        public function Marketing(){

            $id = I('post.id');
            $oper = I('post.status');

            //终止抢购
            if($oper == 0){
                $data = array();
                //修改marketing终止时间
                $rs = $this->m2('marketing')->where('type=0 and type_id='.$id)->order('id desc')->find();
                $data['id'] =  $rs['id'];
                $data['end_time'] = time();
                $this->m2('marketing')->data($data)->save();
                //删除抢购专题
                $this->m2('theme_element')->where('type=0 and type_id='.$id)->delete();
                $this->success('已取消');
            }
            //添加抢购
            if($oper == 1){
                $type = 0;
                $price = I('post.price');
                $start_time = strtotime(I('post.start_time'));
                $end_time = strtotime(I('post.end_time'));
                $title = I('post.title');
                $allow_coupon = I('post.allow_coupon');
                $limit = I('post.limit');
                //添加marketing表
                $data['type'] = $type;
                $data['type_id'] = $id;
                $data['price'] = $price;
                $data['start_time'] = $start_time;
                $data['end_time'] = $end_time;
                $data['title'] = $title;
                $data['allow_coupon'] = $allow_coupon;
                $data['num'] = $limit;
                $this->m2('marketing')->data($data)->add();
                //添加theme_tipsorgoods表
                $data = array();
                $data['theme_id'] = 1;
                $data['type'] = 0;
                $data['type_id'] = $id;
                $this->m2('theme_element')->data($data)->add();

                $this->success('添加成功');
            }
            //查询商品原价格
            if($oper == 2){
                $tips_rs = $this->m2('tips')->where('id='.$id)->find();
                $price = $tips_rs['price'];
                $this->ajaxReturn($price);
            }

            //查询历史促销
            if($oper == 3){
                $rs = $this->m2('marketing')->field('title,price,start_time,end_time,num,allow_coupon')->where('type=0 and type_id='.$id)->order('id desc')->select();
                foreach($rs as $key=>$row){
                    $rs[$key]['start_time'] = date('Y-m-d H:i',$row['start_time']);
                    $rs[$key]['end_time'] = date('Y-m-d H:i',$row['end_time']);
                    $rs[$key]['allow_coupon'] = $row['allow_coupon']?'允许':'拒绝';
                }
                $this->ajaxReturn($rs);
            }
        }

        /*
         * 设置私房菜标签
         * author:cherry
         * date:2017-05-03
         * */
        public function SetAppointment(){
            $tips_id = I('post.id');
            if(empty($tips_id))$this->error('非法访问');
            $oper = I('post.oper');
            $rs = $this->m2('TipsTag')->where(['tips_id'=>$tips_id,'tag_id'=>76])->find();
            $times = $this->m2('TipsTimes')->where(['tips_id'=>$tips_id])->order('id desc')->select();
            if($oper == 0){
                if(!empty($rs)){
                    $allow = 1;
                }else{
                    $allow = 0;
                }
                $this->ajaxReturn($allow);
            }elseif($oper == 1){
                $allow_Appointment = I('post.allow_Appointment');

                if($allow_Appointment == 0){//删除
                    $this->m2('TipsTag')->where(['tips_id'=>$tips_id])->delete();
                    $this->success('设置成功');
                }else{//设置添加
                    if(!empty($times)){
                        if(count($times)==1){
                            if(empty($rs)){
                                $data['tips_id']= $tips_id;
                                $data['tag_id']= 76;
                                // 设置成私房菜后，其他标签删除
                                $this->m2('TipsTag')->where(['tips_id' => $tips_id])->delete();
                                $id = $this->m2('TipsTag')->add($data);
                                if($id>0){
                                    if($times){
                                        $this->m2('TipsTimes')->where(['id'=>$times[0]['id']])->data(['stock'=>-1])->save();
                                    }
                                    $this->success('设置成功');
                                }else{
                                    $this->error('设置失败');
                                }
                            }else{
                                $this->success('设置成功');
                            }
                        }else{
                            $this->error('分期数量不能多于1个，或者不能没有分期添加');

                        }
                    }else{
                        $this->error('先设置分期，再贴私房菜标签');
                    }
                }
            }
        }

        //批量移除专题
        public function outTheme(){
            if(IS_POST){
                $tips_ids = I('post.tips_ids');
                //var_dump($tips_ids);exit;
                $count = 0;
                foreach($tips_ids as $row)
                {
                    $this->m2('theme_element')->where('type=0 and type_id='.$row)->delete();
                    $count++;
                }

                $this->success("成功移除{$count}个专题");
            }
        }

        //下架
        public function online(){
            if(IS_AJAX && isset($_POST['id']) && isset($_POST['status'])){
                $id = I('post.id');
                $status = I('post.status');
                if($status == 1){
                    $this->m2('tips')->where(['id' => $id])->save(['status' => 1]);
                    $this->success('上架成功!');
                }else{
                    //活动时间段更新下架时间
                    $rstimes =  $this->m2('tips_times')->where(['tips_id' => $id])->select();
                    foreach($rstimes as $v){
                        if($v['stop_buy_time']>time()){
                            $this->m2('tips_times')->where(['id' => $v['id']])->save(['under_time' => time()]);
                        }
                    }
                    $this->m2('tips')->where(['id' => $id])->save(['status' => 2]);

                    //记录活动修改快照信息
                    $this->SaveSnapshotLogs($id,0);
                    $this->success('下架成功!');
                }
            }
            $this->error('非法操作!');
        }

        //查看修改活动日志
        public function showLogs(){
            if(IS_AJAX && IS_POST){
                $id = I('post.id');
                $starttime = I('post.starttime', null);
                $endtime = I('post.endtime', null);

                $map = ['framework_id' => 154, 'gt' => ['LIKE', '%\"'. $id .'\"%'], 'pt' => ['LIKE', '%\"title\"%']];
                if(!empty($starttime))$map['datetime'] = ['EGT', $starttime];
                if(!empty($endtime))$map['datetime'] = ['ELT', $endtime];

                $rs = D('ActMemberView')->where($map)->limit(1000)->order('datetime desc')->select();
                $this->success($rs);
            }
        }

        /*导出excel表*/
        public function courseExport(){
            $gz = array(2091,2092,2093,2094,2095,2096,2097,2098,2099,2100,2101,2102);
            $sz = array(2158,2159,2160,2161,2162,2163);
            $sh = array(459,460,461,462,463,464,465,466,467,468,469,470,471,472,473,474,475,476,477);
            $bj = array(423,424,425,426,427,428,429,430,431,432,433,434,435,436,437,438,439,440);
            $hz = array(1206,1207,1208,1209,1210,1211,1212,1213,1214,1215,1216,1217,1218,3898,3899,3900,3901,3902,3903,3904,3905,3906,3907,3908);
            $xm = array(1403,1404,1405,1406,1407,1408);

            $tips_id = I('get.id');
            $tips_title = I('get.title');
            $member_nickname = I('get.member');
            $tips_start_time = strtotime(I('get.start_buy_time'));
            $tips_end_time = strtotime(I('get.stop_buy_time'));
            $tips_category = I('get.category');
            $tips_citys = I('get.citys');
            $tips_theme = I('get.theme');
            $tips_tag = I('get.tag');
            $buy_status = I('get.buy_status', null);
            $tips_is_pass = I('get.is_pass', null);
            $tips_status = I('get.status');

            $condition ='';
            $tips_is_pass !==1? $condition .= " A.status='$tips_status' ":"  $condition = 'A.status =1 '";
            $tips_id && $condition .= " AND A.id=$tips_id ";
            $tips_title && $condition .= " AND A.title LIKE '%$tips_title%'";
            $tips_start_time && $condition .= " AND C.start_time > $tips_start_time ";
            $tips_end_time && $condition .= " AND C.end_time < $tips_end_time ";
            $member_nickname && $condition .= " AND B.nickname LIKE '%$member_nickname%' ";
            $tips_category && $condition .= " AND A.category_id='$tips_category' ";
            $buy_status!==null && $condition .= " AND A.buy_status='$buy_status' ";
            $tips_is_pass && $condition .= " AND A.is_pass='$tips_is_pass' ";
            if(!empty($tips_citys)){
                $area = $this->m2('Citys')->where(['pid'=>$tips_citys])->select();
                foreach($area as $re){
                    $area_id[] = $re['id'];
                }
                if(!empty($area_id)){
                    $city_id = join(',',$area_id);
                    $city_id .= ','.$tips_citys;
                }else{
                    $city_id = $tips_citys;
                }

            }
            $tips_citys && $condition .= " AND F.citys_id in($city_id) ";
//             print_r($condition);
            //标签筛选
            if(!empty($tips_tag)){
                $tag_tips_id = $this->m2('TipsTag')->where(['tag_id' => $tips_tag])->getField('tips_id',true);
                $tag_tips_id = join(',',$tag_tips_id);
                if(!empty($tag_tips_id)){
                    $condition .= " AND A.id IN ($tag_tips_id) ";
                }
            }

            //专题筛选
            if(!empty($tips_theme)){
                $tipsId = $this->m2('theme_element')->where(['theme_id'=>$tips_theme,'type'=>0])->getField('type_id',true);
                $tipsId = join(',',$tipsId);
                if(!empty($tipsId)){
                    $condition .= " AND A.id IN ($tipsId)";
                }
            }

            $tips_mod = D('TipsExportView');
            $datas = $tips_mod->where($condition)->order('id desc')->order('id')->group('tips_times_id')->select();
//            print_r($datas);
//            print_r($tips_mod->getLastSql());
            foreach($datas as $value){
                $datas_times_id[] =$value['tips_times_id'] ;
            }
            $tips_times_ids = join(',',$datas_times_id);
//           print_r($datas);
//            print_r($tips_mod->getLastSql());
            //数据处理
            $tag_rs = $this->m2('tips_tag')->join('__TAG__ ON __TIPS_TAG__.tag_id = __TAG__.id')->select();
            //$tt_ts = $this->m2('tips_times')->select();
            $theme_rs = $this->m2('theme_element')->join('__THEME__ ON __THEME_ELEMENT__.theme_id=__THEME__.id')->where(['ym_theme_element.type'=>0])->select();

            //$buy_times = D('TipsBuyTimesView')->where(['act_status'=>['IN','1,2,3,4'],'status'=>1,'tips_times_id'=>['IN',$tips_times_ids]])->group('tips_times_id')->select();
           // print_r($buy_times);
            foreach($datas as $key=>$row){
                $tags = '';
                $theme = '';
                foreach($tag_rs as $key2 =>$row2){
                    if($row2['tips_id'] == $row['id']){
                        $tags .= $row2['name'].',';
                    }
                }

                foreach($theme_rs as $key4=>$row4){
                    if($row4['type_id'] == $row['id']){
                        $theme .= $row4['title'].',';
                    }
                }

                $buy_times = D('TipsBuyTimesView')->where(['act_status'=>['IN','1,2,3,4,5,6,8'],'status'=>1,'tips_times_id'=>$row['tips_times_id']])->group('B.id')->select();
//                 print_r($buy_times);
//                print_r(D('TipsBuyTimesView')->getLastSql());
                $buy_times_count = count($buy_times);
                if(!empty($buy_times_count)){
                    $row['buy_num']=$buy_times_count;
                }else{
                    $row['buy_num']=0;
                }

                $tips_times_rs = $this->m2('tips_times')->where("tips_id={$row['id']}")->select();


                $datas01[$key]['id'] = $row['id'];
                $datas01[$key]['member_nickname'] = $row['member_nickname'];
                $datas01[$key]['title'] = $row['title'];
                $datas01[$key]['category_name'] = $row['category_name'];
                $datas01[$key]['tips_times_start_time'] = date('Y/m/d H:i',$row['tips_times_start_time']);
                $datas01[$key]['tips_times_end_time'] =date('Y/m/d H:i',$row['tips_times_end_time']);
                $datas01[$key]['price'] = $row['price'];
                $datas01[$key]['min_num'] = $row['min_num'];
                $datas01[$key]['max_num'] = $row['max_num'];
                $datas01[$key]['buy_num'] = $row['buy_num'];
                $datas01[$key]['citys_name'] = $row['citys_name'];
                if(in_array($row['citys_id'],$gz))$datas01[$key]['citys_name'] = '广州';
                if(in_array($row['citys_id'],$sz))$datas01[$key]['citys_name'] = '深圳';
                if(in_array($row['citys_id'],$bj))$datas01[$key]['citys_name'] = '北京';
                if(in_array($row['citys_id'],$sh))$datas01[$key]['citys_name'] = '上海';
                if(in_array($row['citys_id'],$hz))$datas01[$key]['citys_name'] = '杭州';
                if(in_array($row['citys_id'],$xm))$datas01[$key]['citys_name'] = '厦门';
                if($datas[$key]['status']==0)$datas01[$key]['status'] = '删除';
                if($datas[$key]['status']==1)$datas01[$key]['status'] = '正常';
                if($datas[$key]['status']==2)$datas01[$key]['status'] = '下架';
                $datas01[$key]['tag_name'] = $tags;
                $datas01[$key]['theme'] = $theme;
                $datas01[$key]['phase'] = $row['phase'];
                $datas01[$key]['times_count'] =  count($tips_times_rs);;

                unset($datas[$key]['category_id']);
                unset($datas[$key]['tips_sub_citys_id']);
                unset($datas[$key]['theme_tipsorgoods_type']);
                unset($datas[$key]['theme_id']);
                //unset($datas[$key]['status']);
                unset($datas[$key]['tips_times_id']);
                unset($datas[$key]['citys_id']);
            }
            $title = ['活动ID','用户昵称','活动名称','活动分类','开始时间','结束时间','价格','最小人数','最大人数','购买数','城市名称','活动状态','标签','专题','期数','总期数'];
            toXls($title,$datas01,'活动列表');
        }

        /*申请活动非公开是否通过*/
        public function checkapply(){
            if(IS_AJAX){
                $data['id'] = I('post.id');
                $oper = I('post.oper');
                $reason = I('post.reason');
                $select_reason = I('post.select_reason');
                $select_array = [0=>'活动分类不正确',1=>'活动标签不正确',2=>'图片有第三方水印',3=>'活动标题或小标题有极限词'];

                if($oper == '1'){
                    $rs = D('TipsView')->where('H.is_pass = 0 and H.type = 4 and A.status = 1 and A.is_pass = 1')->where($data)->find();
                    if($rs){
                        $this->m2('tips_sub')->where('tips_id='.I('post.id'))->data(['is_public'=>1,'last_update_time'=>time()])->save();
                        $this->m2('member_apply')->where(['id'=>$rs['apply_id'],'type'=>4])->data(['is_pass'=>1,'update_time'=>time()])->save();
                        //推送消息给达人
//                        $this->pushMessage($rs['member_id'],'您的活动公开申请已通过，请留意下单情况，做好准备哦~',null,4,$rs['id']);
//                        $this->pushMessage($rs['member_id'],'您的活动公开申请已通过，请留意下单情况，做好准备哦~','sms',4,$rs['id']);

                        //2016-12-29
                        $this->push_Message($rs['member_id'],array(),'SMS_36360302','sms',null,4,$rs['id']);

                        //记录活动修改快照信息
                        $this->SaveSnapshotLogs($data['id'],0);
                        $this->success('活动申请公开已通过');
                    }else{
                        $this->error('找不到该活动申请');
                    }
                }elseif($oper == '0'){
                    $rs = D('TipsView')->where('H.is_pass = 0  and H.type = 4  and A.status = 1  and A.is_pass = 1')->where($data)->find();
                    if($rs){
                        $this->m2('member_apply')->where(['id'=>$rs['apply_id'],'type'=>4])->data(['is_pass'=>2,'last_update_time'=>time()])->save();

                        //拼接拒绝理由
                        $select_rs = '';
                        if(!empty($select_reason)){
                            foreach($select_reason as $key=>$row){
                                $select_rs .= ($key+1).':'.$select_array[$row].',';
                            }
                        }
                        //更新提交审核记录
                        $this->m2('member_apply')->where(['type'=>0,'type_id'=>$rs['id'],'is_pass'=>0])->data(['is_pass'=>2,'refusal_reason'=>$select_rs.'-'.$reason,'update_time'=>time()])->save();
                        //发送消息
//                        $this->pushMessage($rs['member_id'],array(),null,null,'很抱歉，吖咪酱认真审核了您的活动，发现个别信息还需完善，你可在活动管理中修改后再来申请哦！(审核未通过原因：'.$select_rs.'  '.$reason.')',null,4,$rs['id']);
//                        $this->pushMessage($rs['member_id'],array(),null,null,'很抱歉，吖咪酱认真审核了您的活动，发现个别信息还需完善，你可在活动管理中修改后再来申请哦！(审核未通过原因：'.$select_rs.'  '.$reason.')','sms',4,$rs['id']);

                        //2016-12-29
                        $params = array(
                            'select_rs' => $select_rs,
                            'reason' => $reason,
                        );
                        $this->push_Message($rs['member_id'],$params,'SMS_36240319','sms',null,4,$rs['id']);


                        //记录活动修改快照信息
                        $this->SaveSnapshotLogs($data['id'],0);
                        $this->success('活动申请已拒绝');
                    }else{
                        $this->error('找不到该活动');
                    }
                }
            }

        }

        /*申请活动非公开列表*/
        public function applyaction(){
            $this->actname = '申请公开的活动列表';
            if(IS_AJAX) {
                $data['member_id'] = I('post.member_id');
                $memberinfo = D('MemberInformationView')->where(['id' => $data['member_id']])->find();
                if (!empty($memberinfo)){
                    $memberinfo['birth'] = date('Y-m-d', $memberinfo['birth']);
                    $memberinfo['register_time'] = date('Y-m-d H:i:s', $memberinfo['register_time']);
                    $memberinfo['path'] = thumb($memberinfo['path'], '1');
                    if ($memberinfo['sex'] == 0) $memberinfo['member_info_sex'] = '未设置';
                    if ($memberinfo['sex'] == 1) $memberinfo['member_info_sex'] = '男';
                    if ($memberinfo['sex'] == 2) $memberinfo['member_info_sex'] = '女';
                    $this->ajaxReturn($memberinfo);
                }else{
                    $this->error('找不到该发布者的信息');
                }
            }

            $where = 'H.is_pass = 0  and H.type = 4 and A.status = 1 and A.is_pass = 1';
            $datas['datas'] = D('TipsView')->where($where)->page(I('get.page'), 20)->order(' apply_time desc A.id desc')->group('A.id')->select();
            $channel_type = ['webapp','ios','android','k11','sport','wechat','alipay','youfan_webapp','youfan_ios','youfan_android'];
            foreach($datas['datas'] as $datakey => $dataval){
                $datas['datas'][$datakey]['apply_channel'] = $channel_type[$dataval['apply_channel']];
                $datas['datas'][$datakey]['member_nickname'] = '<a onclick="memberMessage('.$dataval['member_id'].')">'.$dataval['member_nickname'].'</a>';
            }


            //table页面参数设置
            $datas['operations'] = [
                '通过' => [
                    'style' => 'success',
                    'fun' => 'checkApply(%id, 1)'
                ],
                '拒绝' => [
                    'style' => 'danger',
                    'fun' => 'checkApply(%id, 0)'
                ]
            ];
            $datas['pages'] = [
                'sum' => D('TipsView')->where($where)->count('DISTINCT A.id'),
                'count' => 20,
            ];
            $datas['lang'] = [
                'id' => ['预览', '<a><i class="am-icon-eye" onclick="preview(%*%)"></i></a>'],
                'title' => '活动',
                'member_nickname' => '发布者',
                'member_telephone' => '手机号',
                'apply_channel' => '申请渠道',
                'apply_time' => '申请时间',
            ];

            $this->assign($datas);

            $this->view();
        }

        /*活动发布列表*/
        public function releaseTips(){
            $this->actname = '活动发布时间列表';
            if(isset($_GET['status']) && is_numeric($_GET['status'])){
                $condition = "A.status = " . $_GET['status'];
            }else{
                $condition = "A.status = 1 ";
            }
            //$condition = " (I.type=0 OR I.type is null) ";
            if(IS_GET && $_GET!=null){                      //条件查询

                $tips_title = I('get.title');
                $status = I('get.status');
                $citys_id = I('get.citys');

                $tips_title && $condition .= " AND A.title LIKE '%$tips_title%' ";
                $citys_id && $condition .= " AND C.citys_id = $citys_id";
                $this->assign('search_title',$tips_title);
                $this->assign('search_status',$status);
                $this->assign('search_citys',$citys_id);

            }
            $datas['datas'] = D('TipsTimesView')->where($condition)->page(I('get.page'), 20)->order('release_time desc,B.id desc')->group('B.id')->select();
//            print_r(D('TipsTimesView')->getLastSql());
//            print_r($datas['datas']);
            foreach($datas['datas'] as $key =>$val){
                $datas['datas'][$key]['start_time']=date('Y-m-d H:i:s',$val['start_time']);
                $datas['datas'][$key]['end_time']=date('Y-m-d H:i:s',$val['end_time']);
                $datas['datas'][$key]['release_time']=$val['release_time']?date('Y-m-d H:i:s',$val['release_time']):'';
                $datas['datas'][$key]['stop_buy_time']=date('Y-m-d H:i:s',$val['stop_buy_time']);
                $datas['datas'][$key]['HostInfo']='ID:'.$val['member_id'].'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;昵称：'.$val['nickname'].'<br/>手机号：'.$val['telephone'];
            }
            $datas['pages'] = [
                'sum' => D('TipsTimesView')->where($condition)->count('DISTINCT B.id'),
                'count' => 20,
            ];
            $datas['lang'] = [
                'id' => '活动ID',
                'time_id' => '分期ID',
                'title' => '活动标题',
                'HostInfo' => '达人信息',
                'city_name' => '城市',
                'start_time' => '开始时间',
                'end_time' => '结束时间',
                'stop_buy_time' => '结束购买时间',
                'release_time' => '发布时间',
            ];


            //读取城市筛选列表
            $citys = C('CITY_CONFIG');
            $this->assign('citys',$citys);
            $this->assign($datas);

            $this->view();
        }

        /*
         * 获取活动分期的拼团
         * author:cherry
         * date:2017-03-29
         * */
        public function GetPrice(){
            if(IS_AJAX && IS_POST){
                $type_times_id = I('post.type_times_id');
                if(empty($type_times_id)) $this->error('非法访问！');
                $data = $this->m2('piece')->where(['type_times_id'=>$type_times_id,'type'=>0])->select();
                $this->ajaxReturn($data);

            }
        }

        /*
         * 提交活动分期的拼团
         * author:cherry
         * date:2017-03-29
         * */
        public function PostPrice(){
            if(IS_AJAX && IS_POST){
                $type_times_id = I('post.type_times_id');
                $type_id = I('post.type_id');
                $type = I('post.type',0);
                $times = I('post.times');
                if(empty($type_times_id)) $this->error('非法访问！');
                $num = 1;
                $rs = $this->m2('tips_times')->where('id = '.$type_times_id.' and tips_id = '.$type_id)->find();
                if($rs['start_buy_time']>=0 && $rs['start_buy_time']<time()) $this->error('拼团要在活动未开始售卖之前添加！');
                foreach($times as $val){
                    $data = [
                        'type' => $type,
                        'type_id' => $type_id,
                        'type_times_id' => $type_times_id,
                        'phase' => $num++,
                        'price' => $val['price'],
                        'limit_num' => $val['limit_num'],
                        'is_cap' => $val['is_cap'],
                        'count' => ($val['count']>$rs['max_num'])?floor($rs['max_num']/2): $val['count'],
                        'limit_time' => $val['limit_time'],
                    ];
                    if(!empty($val['id'])){
                        $price_id[] = $val['id'];
                        $this->m2('piece')->where(['id'=>$val['id']])->save($data);
                    }else{
                        unset($val['id']);
                        $price_id[]=$this->m2('piece')->add($data);
                    }
                }
                $piece_originator_id = $this->m2('member_piece')->where(['piece_id'=>['IN', join(',', $price_id)]])->getField('id',true);
                if(!empty($piece_ids)){
                    $rs = $this->m2('Order')->join('__ORDER_PIECE__ AS A ON A.order_wares_id = __ORDER__.id')->where(['piece_originator_id' => ['IN', join(',', $piece_originator_id)], 'status' => 1, 'act_status' => ['IN', '1,2,3,4,5']])->find();
                    if (!empty($rs)) $this->error('已产生订单的拼团不能删除!');
                }
                $this->m2('piece')->where(['id'=>['NOT IN', join(',', $price_id)],'type_id'=>$type_id,'type_times_id'=>$type_times_id])->save(['status'=>0]);
                $this->success('提交成功！');

            }
        }

        /*
         * 活动组团列表
         * author:cherry
         * date:2017-03-29
         * */
        public function piecelist(){
            $this->actname = '活动组团列表';
            $tips_id = I('get.tips_id');
            $page = I('get.page',0);
            $datas['datas'] = D('MemberPieceView')->where(['type_id'=>$tips_id])->page($page,20)->select();
            foreach($datas['datas'] as $key =>$val){
                switch($val['act_status']){
                    case 0:
                        $datas['datas'][$key]['act_status'] = '未开团';
                        break;
                    case 1:
                        $datas['datas'][$key]['act_status'] = '进行中';
                        break;
                    case 2:
                        $datas['datas'][$key]['act_status'] = '已成团(还可购买)';
                        break;
                    case 3:
                        $datas['datas'][$key]['act_status'] = '已成团(已结束)';
                        break;
                    case 8:
                        $datas['datas'][$key]['act_status'] = '已过期';
                        break;
                    case 9:
                        $datas['datas'][$key]['act_status'] = '已取消';
                        break;
                    case 10:
                        $datas['datas'][$key]['act_status'] = '已退款';
                        break;
                }
                $datas['datas'][$key]['end_time'] = date('Y-m-d H:i:s',$val['end_time']);
                $orders = D('OrderPieceView')->where(['piece_originator_id'=>$val['id'],'act_status'=>['IN',[1,2,3,4]],'status'=>1])->count();
                if($orders>0){
                    $datas['datas'][$key]['check_status'] = 1;
                }else{
                    $datas['datas'][$key]['check_status'] = 0;
                }
            }


            //table页面参数设置
            $datas['operations'] = [
                '查看参团' => [
                    'condition' => "%check_status==1",
                    'style' => 'success',
                    'fun' => "look_piece(%id)"
                ]
            ];
            $datas['pages'] = [
                'sum' => D('MemberPieceView')->where(['type_id'=>$tips_id])->count('DISTINCT A.id'),
                'count' => 20,
            ];
            $datas['lang'] = [
                'id' => '开团ID',
                'nickname' => '团长昵称',
                'end_time' => '开团的结束时间',
                'act_status' => '状态',
            ];
            $this->assign($datas);

            $this->view();
        }

        /*
         * 查看拼团订单
         * author:cherry
         * date:2017-04-17
         * */
        public function pieceOrder(){
            $this->actname = '拼图订单';
            //条件筛选
            if(IS_GET && $_GET !=null){
                $piece_originator_id = I('get.piece_originator_id');
                if(empty($piece_originator_id)) $this->error('不存在该团');
                $condition['O.piece_originator_id'] = ['EQ',$piece_originator_id];

            }

            $condition['F.type'] = ['EQ',0];
            $condition['O.order_id'] = ['EXP','is not null'];
            $datas['datas'] = D('OrderTipsView')->where($condition)->page(I('get.page', 1), 30)->order('id desc')->group('A.id')->select();

            //计算优惠券的实际优惠金额和实际支付金额
            foreach($datas['datas'] as $key=>$row){
                if($row['coupon_type'] == 0 && $row['coupon_type'] != ''){                                           //抵价券
                    $datas['datas'][$key]['discount'] = $row['coupon_value'];
                }elseif($row['coupon_type'] == 1){                                      //折扣券
                    $datas['datas'][$key]['discount'] = ($row['price']/($row['coupon_value']*0.01)-$row['price']);
                }elseif($row['coupon_type'] == 2){                                      //礼品券
                    $datas['datas'][$key]['discount'] = 0;
                }else{
                    $datas['datas'][$key]['discount'] = 0;                              //不使用优惠券
                }
            }

            //数据处理
            foreach($datas['datas'] as $key=>$row){
                if($row['act_status'] == 0){
                    $datas['datas'][$key]['status'] = '未支付';
                }elseif($row['act_status'] == 1){
                    $datas['datas'][$key]['status'] = '已支付';
                }elseif($row['act_status'] == 2){
                    $datas['datas'][$key]['status'] = '待评价';
                }elseif($row['act_status'] == 3){
                    $datas['datas'][$key]['status'] = '待评价';
                }elseif($row['act_status'] == 4){
                    $datas['datas'][$key]['status'] = '已完成';
                }elseif($row['act_status'] == 5){
                    $datas['datas'][$key]['status'] = '申请退款';
                }elseif($row['act_status'] == 6){
                    $datas['datas'][$key]['status'] = '退款申请已处理';
                }elseif($row['act_status'] == 7){
                    $datas['datas'][$key]['status'] = '已取消';
                }elseif($row['act_status'] == 8){
                    $datas['datas'][$key]['status'] = '系统自动操作，退款中';
                }

                $datas['datas'][$key]['tips_times_start_time'] = date('Y-m-d H:i',$datas['datas'][$key]['tips_times_start_time']);
                $datas['datas'][$key]['tips_times_end_time'] = date('Y-m-d H:i',$datas['datas'][$key]['tips_times_end_time']);
                $datas['datas'][$key]['create_time'] = date('Y-m-d H:i',$datas['datas'][$key]['create_time']);
                $datas['datas'][$key]['customer'] = '昵称：'.$row['member_nickname']."<br/>".'手机：'.$row['member_telephone']."<br/>".'留言：'.$row['context'];
                $datas['datas'][$key]['order_wares_server_status'] = $datas['datas'][$key]['order_wares_server_status'] ==0?'未验票':'已验票';

                if($row['act_status'] !=0){
                    $pay_arr = $this->m2('order_pay')->field('success_pay_time,type')->where(['order_id'=>$row['id'],'success_pay_time'=>['EXP','IS NOT NULL']])->find();
                    if(!empty($pay_arr['success_pay_time'])){
                        $datas['datas'][$key]['success_pay_time'] =  date('Y-m-d H:i',$pay_arr['success_pay_time']);
                        if($pay_arr['type'] == 0){
                            $datas['datas'][$key]['pay_type'] =  '支付宝客户端';
                        }elseif($pay_arr['type'] == 1){
                            $datas['datas'][$key]['pay_type'] =  '微信APP';
                        }elseif($pay_arr['type'] == 2){
                            $datas['datas'][$key]['pay_type'] =  '微信公众号';
                        }elseif($pay_arr['type'] == 3){
                            $datas['datas'][$key]['pay_type'] =  '支付宝网页支付';
                        }elseif($pay_arr['type'] == 4){
                            $datas['datas'][$key]['pay_type'] =  '小程序支付';
                        }

                    }else{
                        $datas['datas'][$key]['pay_type'] =  '';
                        $datas['datas'][$key]['success_pay_time'] ='';
                    }
                }else{
                    $datas['datas'][$key]['pay_type'] =  '';
                    $datas['datas'][$key]['success_pay_time'] ='';
                }
            }


            $datas['pages'] = [
                'sum' => D('OrderTipsView')->where($condition)->count( 'DISTINCT A.id'),
                'count' => 30,
            ];
            $datas['lang'] = [
                'id' => 'ID',
                'sn' => '订单号',
                'tips_title' =>'活动名称',
                'tips_times_start_time' => '开始时间',
                'tips_times_end_time' => '结束时间',
                'tips_times_phase' => '期数',
                'category_name' => '分类',
                'customer' => '购买者信息',
                'inviter_nickname' => '邀请人昵称',
                'order_wares_price' => '单价',
                'buy_num' => '购买数量',
                'price' => '实付金额',
                'discount' => '优惠金额',
                //'act_pay' => '实际支付',
                'create_time' => '下单时间',
                'success_pay_time' => '支付时间',
                'pay_type' => '支付方式',
                'order_wares_server_status' => '消费码状态',
                'status' => '订单状态'
            ];

            $this->assign($datas);
            $this->view();
        }
    }
}