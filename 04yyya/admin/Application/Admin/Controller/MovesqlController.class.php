<?php
namespace Admin\Controller;
use Admin\Controller\MainController;

class MovesqlController extends MainController {
	
	Public function _initialize(){
		set_time_limit(0);
	}
	

	Public function index(){
		echo 123;

	}


    /**
     * 0
     * 转移category表 (一般不会操作)
     */
    Public function category(){
        // $sum = $this->m3('catalog')->join()->count();
        // $page = 1;
        // $count = 30;
        // while(($page-1) * $count < $sum){
        //     echo "开始第 {$page} 页数据录入……\n";
        $rs = $this->m3('catalog')->field('id,catalog_name,catalog_name_alias,sort_order,create_time')->select();
        $data = array();
        foreach($rs as $key => $row){
            $data[] = array(
                'id' => $row['id'],
                'name' => $row['catalog_name'],
                'sign' => $row['catalog_name_alias'],
                'order' => $row['sort_order'],
                'datetime' => date('Y-m-d H:i:s', $row['create_time'])
            );
        }

        $sql = createSqlInsertAll('__CATEGORY__', $data);
        echo $sql . "\n";
        //批量插入category表
        if(!$this->m2('category')->execute($sql)){
            echo $sql;
            exit;
        }

        // echo "第 {$page} 页数据录入完成！还剩 ". ($sum - $page * $count) ." 条数据没有录入！\n";
        // $page ++;

//            ob_flush();
//            flush();
//            sleep(1);
        //}
        echo "全部完成！";
    }

	/**
     * 1
	 * 转移member表/member_info表/pics表部分记录
     * 2748-452     12018-12030     10485-10615    485,1869,10144-2021   2887-2593  130-1726   1664-3349   1565,1570-672  2681-12090  2602-1472 894-13711    1147-13786      12019-12027    1228-3881

     */
	Public function member(){

            $sum = $this->m3('member')->count();
            $id = $this->m2('member')->order('id desc')->limit(1)->getField('id');
            $pic_id = $this->m2('pics')->order('id desc')->limit(1)->getField('id');

            $id = $id ? $id+1 : 1;
            $pic_id = $pic_id ? $pic_id : 1;

            $page = 1;
            $count = 100;
            while(($page-1) * $count < $sum){
                echo "开始第 {$page} 页数据录入……<br/>";
                $rs = $this->m3('member')->page($page, $count)->select();
                $data = array();
                foreach($rs as $key => $row){
                     /*$telphone = $row['telphone'];
                       $email = $row['email'];
                        $openid = $row['openid']
                                /*if(preg_match('/^\d{11}$/', $row['username'])){
                                $telphone = $row['username'];
                                }elseif(preg_match('/^.+@.+$/', $row['username'])){
                                $email = $row['username'];
                                }elseif(preg_match('/^[A-Za-z0-9_\-]{20,}$/', $row['username'])){
                                    $openid = $row['username'];
                                }else{
                                    file_put_contents("{$page}_{$key}.txt", json_encode($row));
                                    continue;
                                }

                                if(strlen($row['password']) != 32){
                                    file_put_contents("{$page}_{$key}.txt", json_encode($row));
                                    continue;
                                }*/

//                    'type' => $row['is_vip']=='Y' ? 1 : 0,                      //'is_vip',
                                $data['member'][] = array(
                                        'id' => $id,                                                  // 'id',
                                        'portrait' => $pic_id,
                                        'username' => $row['username'],                            //'username',
                                        'password' => $row['password'],                            //'password',
                                        'telephone' => $row['telephone'],                            //'telephone',
                                        'email' => $row['email'],                                   //'email',
                                        'nickname' => $row['realname'],                            //'realname',
                                        'openid' =>$row['openid'],                                 //'openid' ,
                                        'unionid' => $row['unionid'],                             //'unionid' ,
                                        'status' => $row['status_is'] == 'Y' ? 1:0,              //'status_is' ,
                                        'oldid'=>$row['id'],                                       //'id'
                                        'datetime' => date('Y-m-d H:i:s', $row['create_time'])//'create_time'
                                );
                                if(!empty($row['portrait'])){

                                    $data['pics'][] = array(
                                            'id' => $pic_id,
                                            'member_id' => $id,
                                            'type' => 2,
                                            'path' => $row['portrait'],                            //'portrait'
                                            'is_thumb' => !empty($row['portrait_thumb']) ? 1 : 0,//,'portrait_thumb' ,
                                            'is_used' => 1
                                    );
                                }

                   // 'contact' ,'company' ,'address'  ,'interest' ,'signature'  ,'last_login_ip' ,'last_login_time' ,'last_update_time' ,'login_count' ,'register_type' ,'is_bind' ,'money' ,'commend' ,'invitation_code' ,'address_type' ,'admid' ,'vip_intro','_table'=>'__MEMBER__'),

                                $data['member_info'][] = array(
                                        'member_id' => $id,
                                        'surname' => $row['truename'],
                                        'sex' => $row['gender'] == 1 ? 1 : ($row['gender'] == 0 ? 2 : 0),
                                        'qq' => $row['qq'],
                                    'vip_intro'=>$row['vip_intro'],
                                    'invitation_code'=>$row['invitation_code'],
                                    'login_count'=>$row['login_count'],
                                    'last_update_time'=>$row['last_update_time'],
                                    'signature'=>$row['signature'],
                                    'interest'=>$row['interest'],
                                    'company'=>$row['company'],
                                    'contact'=>$row['contact'],
                                    'address'=>$row['address'],
                                    'citys_id'=>224,
                                    'birth'=>$row['birth'],

                                );
                                $id ++;
                                $pic_id ++;
                }

                $sql = createSqlInsertAll('__MEMBER__', $data['member']);
                echo $sql . "<br/>";
                //批量插入会员核心表
                if(!$this->m2('Member')->execute($sql)){
                    echo 'MEMBER:'.$sql;
                    exit;
                }//die($sql);

                $sql = createSqlInsertAll('__PICS__', $data['pics']);
                echo $sql . "\n";
                //批量插入图片附件表
                if(!$this->m2('Pics')->execute($sql)) {
                    echo 'PICS:'.$sql;
                    exit;
                }//die($sql);

                $sql = createSqlInsertAll('__MEMBER_INFO__', $data['member_info']);
                echo $sql . "\n";
                //批量插入会员信息表
                if(!$this->m2('MemberInfo')->execute($sql)) {
                    echo 'MemberInfo'.$sql;
                    exit;
                }//die($sql);

                echo "第 {$page} 页数据录入完成！还剩 ". ($sum - $page * $count) ." 条数据没有录入！\n";
                $page ++;

                ob_flush();
                flush();
                sleep(1);
            }
            echo "全部完成！";
        }

    public function asymemberid()
    {
        $newlist = $this->m2('member')->field('id, oldid')->select();
        $memberlist = $this->m3('member');
        foreach($newlist as $key => $row){
            $memberlist->where(array('id' => $row['oldid']))->save(array(
                'yamiid' => $row['id']
            ));
            if($key % 500 == 499){
                echo "已同步 ". ($key + 1) ." 条数据！\n";
                ob_flush();
                flush();
                sleep(1);
            }
        }
        echo "同步完成！";
    }


    //update `ym_member` a,`yamitest`.`member` b set a.`telephone`=b.`telephone` where a.`id`=b.`yamiid`;
    public function asymembertel()
    {
        $newlist = $this->m2('member');
        $oldlist = $this->m3('member');
        foreach($oldlist as $key => $row){
            $newlist->where(array('id' => $row['yamiid']))->save(array(
                'telephone' => $row['telephone']
            ));
            if($key % 500 == 499){
                echo "已同步 ". ($key + 1) ." 条数据！\n";
                ob_flush();
                flush();
                sleep(1);
            }
        }
        echo "同步完成！";
    }
//update `ym_member` set `password`=MD5(CONCAT(`password`,'WI4NTFjOGNmODYxMDI1Y'));
    Public function feedback(){
        $sum = $this->m3('feedback')->count();
        $page = 1;
        $count =100;
        while(($page-1) * $count < $sum){
            echo "开始第 {$page} 页数据录入……\n";
            $rs = $this->m3('feedback')->field('content, yamiid, feedback.create_time')->join('member on feedback.user_id=member.id')->page($page, $count)->select();
            $data = array();
            foreach($rs as $key => $row){
                $data[] = array(
                    'member_id' => $row['yamiid'],
                    'content' => $row['content'],
                    'datetime' => date('Y-m-d H:i:s', $row['create_time'])
                );
            }

            $sql = createSqlInsertAll('__FEEDBACK__', $data);
            echo $sql . "\n";
            //批量插入会员核心表
            if(!$this->m2('feedback')->execute($sql)){
                echo $sql;
                exit;
            }

            echo "第 {$page} 页数据录入完成！还剩 ". ($sum - $page * $count) ." 条数据没有录入！\n";
            $page ++;

            ob_flush();
            flush();
            sleep(1);
        }
        echo "全部完成！";
    }






    /**
     * 3
     * 转移tips表  pics id 6981
     */
    Public function pics(){

        //排除 tips_=0 和user_id=0的tips——album
        $where['tips_id']=array('GT','0');
        $where['user_id']=array('GT','0');
        $sum = $this->m3('tips_album')->where($where)->count();

         $page = 1 ;
         $count =500;
       while(($page-1) * $count < $sum){
             echo "开始第 {$page} 页数据录入……\n";
          /**/ $where['tips_id']=array('GT','0');
           $where['user_id']=array('GT','0');
        $rs = $this->m3('tips_album')->where($where)->field('tips_album.id,file_name,tips_album.create_time,yamiid')->join('member on tips_album.user_id=member.id')->page($page,$count)->select();
        $data = array();
        foreach($rs as $key => $row){
            $data[] = array(
                'member_id'=>$row['yamiid'],
                'tips_albumorgoods_id' => $row['id'],
                'type' => 0,
                'path' => $row['file_name'],
                'is_thumb' => 0,
                'is_used'=>1,
                'datetime' => date('Y-m-d H:i:s', $row['create_time'])
            );
        }

        $sql = createSqlInsertAll('__PICS__', $data);
           //dump($data);
           //exit;
        echo $sql . "\n";
        //批量插入pics表
        if(!$this->m2('pics')->execute($sql)){
            echo $sql;
            exit;
        }
         echo "tips_album 第 {$page} 页数据录入完成！还剩 ". ($sum - $page * $count) ." 条数据没有录入！\n";
         $page ++;

            ob_flush();
            flush();
            sleep(1);
        }
        echo "全部完成！";
    }


    /**
     * 4
     * 同步ID
     */
    public function asytips_albumid(){
        ini_set('memory_limit', '256M');//扩大php内存
        set_time_limit(0);//不设置响应超时

        $picslist = $this->m2('pics')->where(array( 'type'=>0))->field('id, tips_albumorgoods_id')->select();
        $tips_albums = $this->m3('tips_album');
        foreach($picslist as $key => $row){
            $tips_albums->where(array('id' => $row['tips_albumorgoods_id']))->save(array(
                'ympicsid' => $row['id']
            ));
            if($key % 100 == 99){
                echo "已同步 ". ($key + 1) ." 条数据！\n";
                ob_flush();
                flush();
                sleep(1);
            }
        }
        echo "同步完成！";
    }

    /**5
     * 转移tips  tips_sub表  这样是没错的 tips表中可能会有ymid=0 那是因为 tip_album表中没有他们的记录或他们的user_id=0
     */
    Public function tips(){
        ini_set('memory_limit', '256M');//扩大php内存
        set_time_limit(0);//不设置响应超时

        $tips_id = 0;
        $sum = D('TestTipsView')->count();
        $page = 1;
        $count = 50;
        while(($page-1) * $count < $sum){
            echo "开始第 {$page} 页数据录入……\n";
            $rs=D('TestTipsView')->page($page, $count)->select();

            $data = array();
            foreach($rs as $key => $row){
                $tips_id ++;



                $data['tips'][] = array(
                    'id' =>$tips_id,
                    'category_id' => !empty($row['catalog_id'])?$row['catalog_id']:7,
                    'member_id' => $row['yamiid'],
                    'price' => $row['price1'],
                    'title' => $row['title'],
                    'pic_id'=>$row['ympicsid'],
                    'start_buy_time'=>$row['start_time1'],
                    'stop_buy_time'=>$row['end_time1'],
                    'is_pass' =>1,
                    'status' => $row['status_is'] == 'Y' ? 1:0,
                    'oldid'=>$row['id'],
                    'is_top'=>$row['is_top'],
                    'min_num'=>$row['min_num'],
                    'restrict_num'=>$row['restrict_num1'],

                    'datetime' => date('Y-m-d H:i:s', $row['create_time'] )
                );

                // 'citys_id' => $row['address_type1']==1?224:234,
                $data['tips_sub'][] = array(
                    'tips_id' =>$tips_id,
                    'content' =>$row['content'],
                    'citys_id' => 224,
                    'address'=>$row['address'],
                    'checkpasstime'=>$row['checkpasstime'],
                    'simpleaddress'=>$row['simpleaddress'],
                    'couseorcanstarttime'=>$row['couseorcanstarttime'],
                    'tel'=>$row['tel'],
                    'principal'=>$row['principal'],
                    'targetid'=>$row['targetid'],
                    'last_edit_admin_id'=>$row['last_edit_admin_id'],
                    'tips_flag'=>$row['tips_flag'],
                    'service_status'=>$row['service_status'],
                    'course_identity'=>$row['course_identity'],
                    'issue'=>$row['issue'],
                    'intro'=>$row['intro'],
                    'notice'=>$row['notice'],
                    'is_featured'=>$row['is_featured'],
                    'checkbyadm'=>$row['checkbyadm'],
                    'fcbl'=>$row['fcbl'],
                    'fromvip'=>$row['fromvip'],
                    'author'=>$row['author'],
                    'keyword'=>$row['keyword'],
                    'last_update_time'=>$row['last_update_time'],
                    'on_sell'=>$row['on_sell']
                );
            }

            $sql = createSqlInsertAll('__TIPS__', $data['tips']);
            //echo $sql . "\n";
            //批量插入tips核心表
            if(!$this->m2('tips')->execute($sql)){
                //echo $sql;
               // exit;
            }


            $sql = createSqlInsertAll('__TIPS_SUB__', $data['tips_sub']);
            //echo $sql . "\n";
            //批量插入Tips_sub表
            if(!$this->m2('tips_sub')->execute($sql)){
                echo $sql;
                exit;
            }

            $oldlist = $this->m2('tips')->field('id, oldid')->page($page, $count)->select();
            $tipslist = $this->m3('tips');
            foreach($oldlist as $key => $row){
                $tipslist->where(array('id' => $row['oldid']))->save(array(
                    'ymid' => $row['id']
                ));
            }


            echo "第 {$page} 页数据录入完成！还剩 ". ($sum - $page * $count) ." 条数据没有录入！\n";
            $page ++;

            ob_flush();
            flush();
            sleep(1);
        }
        echo "全部完成！";
    }

    /**
     * 6
     * 同步ID
     */
    public function asytipsoldid(){
        $oldlist = $this->m2('tips')->field('id, oldid')->select();
        $tips = $this->m3('tips');
        foreach($oldlist as $key => $row){
            $tips->where(array('id' => $row['oldid']))->save(array(
                'ymid' => $row['id']
            ));
            if($key % 100 == 99){
                echo "已同步 ". ($key + 1) ." 条数据！\n";
                ob_flush();
                flush();
                sleep(1);
            }
        }
        echo "同步完成！";
    }



    /**
     *7
     *转移address表  pics id 6981
     */
    public function address(){
        $sum = $this->m3('address')->count();
        $page = 1;
        $count =100;
        while(($page-1) * $count < $sum){
            echo "开始第 {$page} 页数据录入……\n";
            $rs = $this->m3('address')->field('yamiid,consignee,address.address,zipcode,phone_tel')->join('member on address.user_id=member.id')->page($page, $count)->select();
            $data = array();
            foreach($rs as $key => $row){
                $data[] = array(
                    'member_id' => $row['yamiid'],
                    'citys_id'=>224,
                    'address'=>$row['address'],
                    'zipcode'=>$row['zipcode'],
                    'linkman' => $row['consignee'],
                    'telephone'=>$row['phone_tel'],
                    'is_default'=>1,
                    'datetime' => date('Y-m-d H:i:s',time())
                );
            }

            $sql = createSqlInsertAll('__MEMBER_ADDRESS__', $data);
            echo $sql . "\n";
            //批量插入会员核心表
            if(!$this->m2('member_address')->execute($sql)){
                echo $sql;
                exit;
            }

            echo "第 {$page} 页数据录入完成！还剩 ". ($sum - $page * $count) ." 条数据没有录入！\n";
            $page ++;

            ob_flush();
            flush();
            sleep(1);
        }
        echo "全部完成！";
    }


    /**
     *8
     *coupon
     */
    public function coupon(){
/*      $coupon_id = $this->m2('coupon')->order('id desc')->limit(1)->getField('id');
        $coupon_id = $coupon_id ? $coupon_id+1 : 1;
*/
        $sum = $this->m3('coupon')->count();
        $page = 1;
        $count =100;
        while(($page-1) * $count < $sum){
            echo "开始第 {$page} 页数据录入……\n";
            $rs = $this->m3('coupon')->page($page, $count)->select();
            $data = array();
            foreach($rs as $key => $row){
                $data[] = array(
                    'category'=>0,
                    'name'=>$row['coupon_name'],
                    'type'=>0,
                    'value'=>$row['coupon_value'],
                    'content' => $row['remark'],
                    'count'=>$row['issue_count'],
                    'start_time' => date('Y-m-d H:i:s',$row['start_time']),
                    'end_time'=> date('Y-m-d H:i:s',$row['end_time']),
                    'min_amount'=>$row['min_amount'],
                    'status'=>1,
                    'member_tags'=>'*',
                    'tips_tags'=>'*',
                    'goods_tags'=>'*',
                    'oldid'=>$row['id'],
                    'datetime'=> date('Y-m-d H:i:s',$row['create_time'])
                );
            }

            $sql = createSqlInsertAll('__COUPON__', $data);
            echo $sql . "\n";
            //批量插入coupon表
            if(!$this->m2('coupon')->execute($sql)){
            }

            $oldidlist = $this->m2('coupon')->field('id, oldid')->select();
            $coupons = $this->m3('coupon');
            foreach($oldidlist as $key => $row){
                $coupons->where(array('id' => $row['oldid']))->save(array(
                    'ymid' => $row['id']
                ));
            }



            echo "第 {$page} 页数据录入完成！还剩 ". ($sum - $page * $count) ." 条数据没有录入！\n";
            $page ++;

            ob_flush();
            flush();
            sleep(1);
        }
        echo "全部完成！";

    }

    /**
     *9
     *member_coupon
     */
    public function member_coupon(){
        $sum = $this->m3('member_couponsn')->count();
        $page = 1;
        $count =100;
        while(($page-1) * $count < $sum){
            echo "开始第 {$page} 页数据录入……\n";
            $rs = $this->m3('member_couponsn')->page($page, $count)->select();
            $data = array();
            foreach($rs as $key => $row){
                $data[] = array(
                    'member_id' => $row['yamiid'],
                    'coupon_id' => $row['ymid'],
                    'sn'=>$row['coupon_sn'],
                    'datetime' => date('Y-m-d H:i:s', time()),
                    'used_time' => 0
                );
            }

            $sql = createSqlInsertAll('__MEMBER_COUPON__', $data);
            echo $sql . "\n";
            //批量插入member_coupon表
            if(!$this->m2('member_coupon')->execute($sql)){
                echo $sql;
                exit;
            }

            echo "第 {$page} 页数据录入完成！还剩 ". ($sum - $page * $count) ." 条数据没有录入！\n";
            $page ++;

            ob_flush();
            flush();
            sleep(1);
        }
        echo "全部完成！";
    }


    /**
     *10
     *goods 表的图片先插入到ym_pics 以便goods插入时的外键可以关联
     */
    public function goodspic(){

        $sum = $this->m3('goods')->count();
        $page = 1;
        $count =100;
        while(($page-1) * $count < $sum){
            echo "开始第 {$page} 页数据录入……\n";
            $rs = $this->m3('goods')->field('yamiid,goods.id,goods_name,goods_shortname,attach_file,short_attach_file,description,price,default_spec,shipping,free_shipping,keyword,start_time,duration,goods.create_time,can_buy,goods.status_is,remark,quota,goods_identity,goods.issue,user_id')->join('member on goods.user_id=member.id')->page($page, $count)->select();
            $data = array();
            foreach($rs as $key => $row){

                $data['pics'][] = array(
                    'member_id' => $row['yamiid'],
                    'tips_albumorgoods_id' => $row['id'],
                    'type' => 1,
                    'path' => $row['attach_file'],
                    'is_thumb' => 0,
                    'is_used'=>1,
                    'datetime' => date('Y-m-d H:i:s', $row['create_time'])
                );
            }

            $sql = createSqlInsertAll('__PICS__', $data['pics']);
            echo $sql . "\n";
            //批量插入pics表
            if(!$this->m2('Pics')->execute($sql))die($sql);

            echo "第 {$page} 页数据录入完成！还剩 ". ($sum - $page * $count) ." 条数据没有录入！\n";
            $page ++;

            ob_flush();
            flush();
            sleep(1);
        }
        echo "全部完成！";
    }



    /**
     *11
     *goods
     */
    public function goods(){

        $sum = $this->m3('goods')->count();
        $page = 1;
        $count =50;
        while(($page-1) * $count < $sum){
            echo "开始第 {$page} 页数据录入……\n";
            $rs = $this->m3('goods')->field('yamiid,goods.id,goods_name,goods_shortname,attach_file,short_attach_file,description,price,default_spec,shipping,free_shipping,keyword,start_time,duration,goods.create_time,can_buy,goods.status_is,remark,quota,goods_identity,goods.issue,user_id')->join('member on goods.user_id=member.id')->page($page, $count)->select();
            $data = array();
            foreach($rs as $key => $row){
                $pic_id=$this->m2('pics')->where(array('tips_albumorgoods_id'=>$row['id']))->limit(1)->getField('id');

                $data['goods'][] = array(
                    'member_id' => $row['yamiid'],
                    'category_id' =>7,
                    'pic_id'=>$pic_id,
                    'price'=>$row['price'],
                    'title'=>$row['goods_name'],
                    'is_pass'=>1,
                    'status'=>1,
                    'oldid'=>$row['id'],
                    'datetime' => date('Y-m-d H:i:s', $row['create_time']),
                );

            }

            $sql = createSqlInsertAll('__GOODS__', $data['goods']);
            echo $sql . "\n";
            //批量插入goods表
            if(!$this->m2('goods')->execute($sql)){
            }

            $oldidlist = $this->m2('goods')->field('id, oldid')->select();
            $goodslist = $this->m3('goods');
            foreach($oldidlist as $key => $row){
                $goodslist->where(array('id' => $row['oldid']))->save(array(
                    'ymid' => $row['id']
                ));
            }

            echo "第 {$page} 页数据录入完成！还剩 ". ($sum - $page * $count) ." 条数据没有录入！\n";
            $page ++;

            ob_flush();
            flush();
            sleep(1);
        }
        echo "全部完成！";
    }


    /**
     *12
     *goods
     */
    public function goods_sub_market(){
        $sum = $this->m3('goods')->count();
        $page = 1;
        $count =50;
        while(($page-1) * $count < $sum){
            echo "开始第 {$page} 页数据录入……\n";
            //
            $rs = $this->m3('goods')->field('yamiid,goods.id,goods_name,goods_shortname,attach_file,short_attach_file,description,price,default_spec,shipping,free_shipping,keyword,start_time,duration,goods.create_time,can_buy,goods.status_is,remark,quota,goods_identity,goods.issue,user_id')->join('member on goods.user_id=member.id')->page($page, $count)->select();
            $data = array();
            foreach($rs as $key => $row){
                $pic_id=$this->m2('pics')->where(array('tips_albumorgoods_id'=>$row['id']))->limit(1)->getField('id');
                $goods_id = $this->m2('goods')->where(array('pic_id'=>$pic_id))->limit(1)->getField('id');

                $data['marketing'][] = array(
                    'type_id'=>$goods_id,
                    'type'=>1,
                    'discount' => 0,
                    'price'=>$row['price'],
                    'start_time'=>$row['start_time'],
                    'end_time'=>time(),
                    'title'=>$row['goods_name'],
                    'allow_coupon'=>1,
                    'datetime' => date('Y-m-d H:i:s', $row['create_time']),
                );

                $data['goods_sub'][] = array(
                    'goods_id'=>$goods_id,
                    'content' =>$row['description']
                );

            }

            $sql = createSqlInsertAll('__GOODS_MARKETING__', $data['marketing']);
            echo $sql . "\n";
            //批量插入goods表
            if(!$this->m2('marketing')->execute($sql))die($sql);


            $sql = createSqlInsertAll('__GOODS_SUB__',$data['goods_sub']);
            echo $sql . "\n";
            //批量插入goods_sub表
            if(!$this->m2('goods_sub')->execute($sql))die($sql);





             echo "第 {$page} 页数据录入完成！还剩 ". ($sum - $page * $count) ." 条数据没有录入！\n";
            $page ++;

            ob_flush();
            flush();
            sleep(1);
        }
        echo "全部完成！";
    }

    /**
     *11
     *goods
     */
    public function order(){

        $sum = $this->m3('order')->count();
        $page = 1;
        $count =100;
        while(($page-1) * $count < $sum){
            echo "开始第 {$page} 页数据录入……\n";
            $rs = $this->m3('order')->page($page, $count)->select();
            $data = array();
            foreach($rs as $key => $row){
                $member_id = $this->m2('member')->where(array('oldid'=>$row['user_id']))->limit(1)->getField('id');
                $member_coupon_id=0;
                if(isset($row['coupon_sn']))
                {
                    $member_coupon_id = $this->m2('member_coupon')->where(array('sn'=>$row['coupon_sn']))->limit(1)->getField('id');
                }
                //$member_coupon_id = $this->m2('member_coupon')->where(array('sn'=>$row['coupon_sn']))->limit(1)->getField('id');
                $data['order'][] = array(
                    'sn' => $row['order_no'],
                    'member_id' =>$member_id,
                    'price'=>$row['goods_amount'],
                    'act_status'=>$row['status'],
                    'member_coupon_id'=>$member_coupon_id,
                    'create_time'=>$row['create_time'],
                    'paytime'=>$row['create_time'],
                    'finishtime'=>$row['create_time'],
                    'status'=>1,
                    'datetime' => date('Y-m-d H:i:s', $row['create_time']),
                    'oldid'=>$row['id']
                );

            }

            $sql = createSqlInsertAll('__ORDER__', $data['order']);
            echo $sql . "\n";
            //批量插入goods表
            if(!$this->m2('order')->execute($sql)){
                echo $sql;
                exit;
            }

            $oldidlist = $this->m2('order')->field('id, oldid')->page($page ,$count)->select();
            $goodslist = $this->m3('order');
            foreach($oldidlist as $key => $row){
                $goodslist->where(array('id' => $row['oldid']))->save(array(
                    'ymid' => $row['id']
                ));
            }

            echo "第 {$page} 页数据录入完成！还剩 ". ($sum - $page * $count) ." 条数据没有录入！\n";
            $page ++;

            ob_flush();
            flush();
        }
        echo "全部完成！";
    }


    /**
     *11
     *order_wares
     */
    public function order_wares(){

        $sum = $this->m3('order_goods')->count();
        $page = 1;
        $count =100;
        while(($page-1) * $count < $sum){
            echo "开始第 {$page} 页数据录入……\n";
            $rs = $this->m3('order_goods')->field('`order`.ymid,item_type,item_id,item_price,item_quantity,`order`.create_time')->join('`order` on `order`.id=order_goods.order_id')->page($page, $count)->select();
            $data = array();
            foreach($rs as $key => $row){

                for( $i=0;$i<$row['item_quantity'];$i++ )
                {
                    $data['order_goods'][] = array(
                        'order_id' => $row['ymid'],
                        'type' =>$row['item_type']==1?0:1,
                        'ware_id'=>$row['item_id'],
                        'price'=>$row['item_price'],
                        'datetime' => date('Y-m-d H:i:s', $row['create_time']),
                    );
                }

            }

            $sql = createSqlInsertAll('__ORDER_WARES__', $data['order_goods']);
            echo $sql . "\n";
            //批量插入goods表
            if(!$this->m2('order_wares')->execute($sql)){
                echo $sql;
                exit;
            }

            echo "第 {$page} 页数据录入完成！还剩 ". ($sum - $page * $count) ." 条数据没有录入！\n";
            $page ++;

            ob_flush();
            flush();
        }
        echo "全部完成！";
    }


//    执行完全部方法后就要调用数据库的查询

}


