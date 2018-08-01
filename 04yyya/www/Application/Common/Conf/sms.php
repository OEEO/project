<?php
//短信配置
return [
    'DX_SMS' => [
        //活动结束邀请用户评价
        'SMS_48615069' => [
            'content' => '您参与的『${title}』已经结束啦，一切满意的话记得给全5分好评哟~每周二更多有趣的活动、有料的饭局，一起去探索食物里的风景吧~',
            'params' => ['title']
        ],
        //购买成功通知给达人
        'SMS_36385054' => [
            'content' => '您所发布的活动『${title}』已有用户购买，活动时间${datetime}，昵称：${nickname}，手机号：${telephone}，购买数量${num}份，${leavemessage}请您尽快与她(他)联系。如有疑问请加客服微信：${wx}',
            'params' => ['title','datetime','nickname','telephone','num','leavemessage','wx']
        ],
        //购买商品成功通知给用户
        'SMS_35745136' => [
            'content' => '小主购买的“${title}”已于${datetime}使用${wuliu_name}发货，快递单号为${number}，喜欢请在收货之后点亮五颗星奖励我，评价有福利哦！',
            'params' => ['title','datetime','wuliu_name','number']
        ],
        //成局发送给达人
        'SMS_35955013' => [
            'content' => '您发布的“${title}”已成局，活动时间：${time}，已有${numberofpeople}位报名，请提前准备好迎接食客哦。${platform}客服微信号：${wx_number}',
            'params' => ['title','time','numberofpeople','platform','wx_number']
        ],
        //审核活动失败通知审核活动失败通知
        'SMS_50230072' => [
            'content' => '很抱歉，${customer}认真审核了您的活动${title}，发现个别信息还需要完善，你可在活动管理中修改后再来申请哦！（活动未通过审核原因：${cause}）',
            'params' => ['customer','title','cause']
        ],
        //不成局短信发送给达人
        'SMS_36055220' => [
            'content' => '非常抱歉通知您，您发布的“${title}”由于未达到成局人数，默认不成局，${platform}会在3个工作日内退款给食客。客服微信号：${wx_number}',
            'params' => ['title','wx_number','platform']
        ],
        //活动没有成局
        'SMS_36125026' => [
            'content' => '非常抱歉通知您，您购买的${title}由于未达到成局人数，默认不成局， ${platform}会在3个工作日内为您退款。客服微信号：${wx_number}',
            'params' => ['title','platform','wx_number']
        ],
        //活动成局发送短信通知用户
        'SMS_36320171' => [
            'content' => '您购买的『${title}』已成局，活动时间${datetime}，地址：${address}，${platform_member_1}的电话：${telephone}，如有疑问请与${platform_member}联系。客服微信：${wx_number}',
            'params' => ['title','datetime','address','platform_member_1','telephone','platform_member','wx_number']
        ],
        //登录确认验证码
        'SMS_35915140' => [
            'content' => '欢迎贵客莅临${platform}！您的验证码：${code}。工作人员不会向您索要，请勿向任何人透露。',
            'params' => ['code', 'platform']
        ],
        //验证码短信（直接阅读版）
        'SMS_107810071' => [
            'content' => '验证码：${code}，10分钟内有效。工作人员不会向您索取验证码，请勿向他人泄露。如非本人操作，请忽略此短信。',
            'params' => ['code']
        ],
        //厨房+的询价
        'SMS_35990158' => [
            'content' => '${nickname}提交了 “厨房+” ${space_name}的询价',
            'params' => ['nickname', 'space_name']
        ],
        //申请厨房场地
        'SMS_36035181' => [
            'content' => '尊敬的吖咪厨房主人，您的精美厨房有料理人想要预定啦！预定人：${name}；电话：${tel}。${question}请尽快联系TA，让厨房热闹起来吧！ 吖咪Yummy 更多精彩美食活动尽在yummy194.cn',
            'params' => ['name', 'tel','question']
        ],
        //消费后邀请评价
        '	SMS_36340051' => [
            'content' => '您参与的『${title}』已经结束，现场气氛如何？${platform_member_1}手艺棒不棒？和${platform_member_2}互动愉快吗？快来给${platform_member_3}评分吧！',
            'params' => ['title','platform_member_1','platform_member_2','platform_member_3']
        ],
        //获取消费码
        'SMS_48580145' => [
            'content' => '尊敬的${nickname}：${member_nickname}诚邀您参加${host_nickname}的饭局，时间：${start_dates}，地点：${detail_address}。恭候您的到来。邀请码为：${check_code}',
            'params' => ['nickname','member_nickname','host_nickname','start_dates','detail_address','check_code']
        ],
        //支付错误
        'SMS_35925149' => [
            'content' => '您支付的金额与订单价位不符,我们正在将款项退回给您,请您稍等...',
            'params' => []
        ],
        //支付失败提示已售罄信息
        'SMS_36075312' => [
            'content' => '您购买的活动已售罄或剩余数量不足,我们正在将款项退回给您,请您稍等...',
            'params' => []
        ],
        //支付失败提示已被包场信息
        'SMS_36020221' => [
            'content' => '您购买的包场活动已被别人买进,我们正在将款项退回给您,请您稍等...',
            'params' => []
        ],
        //购买活动成功后，发送信息给用户
        'SMS_36105291' => [
            'content' => '感谢小主购买了“${title}”，小主的入席时间是${sdate} - ${edate}，地址：${address}。${platform_member}会尽快与您联系，如有疑问请添加${platform}客服微信号：${wx}，随时保持联系!',
            'params' => ['title','sdate','edate','address','platform_member','platform','wx'],
            'wxtemplate' => ['template_id' => 'pRvaqNmI5067i7Lz73zPiQsD2cYdhENu14hW6rf2XNQ', 'first' => '感谢小主购买美食活动！', 'keyword1' => 'title', 'keyword2' => 'sdate','keyword3'=>'address']
        ],
        //购买商品支付成功通知用户
        'SMS_36190100' => [
            'content' => '感谢小主购买了“${title}”， 订单号为${sn}，已支付成功。卖家将依照订单顺序陆续发货，请耐心等待，谢谢。',
            'params' => ['title','sn']
        ],
        //奖励到账提醒
        'SMS_127150515' => [
            'content' => '亲爱的用户，你邀请的好友${nickname}刚刚已完成在吖咪上的订单，恭喜你获得了${price}元奖励收益！快去公众号吖咪yummy个人中心的收益专区页面中查看吧！',
            'params' => ['nickname', 'price']
        ],
        //发起项目有人购买发送给达人
        'SMS_55720155' => [
            'content' => '您发起的${project}项目《${title}》已有用户支持，金额：${price} 昵称：${nickname} ，手机号：${telephone} 。请您尽快与她(他)联系。如有疑问请加客服微信：${wx}。',
            'params' => ['project','title','price','nickname','telephone','wx']
        ],
        //项目二次支付成功通知用户(不用了)
        'SMS_36340032' => [
            'content' => '感谢小主支持了${title}，您可以在我的订单中查询到您所支持的${projectname}项目。如有疑问请添加${platform}客服微信号：${wx}，随时保持联系!',
            'params' => ['title','projectname','platform','wx']
        ],
        //有用户购买低金额项目成功发送短信通知达人
        'SMS_36355042' => [
            'content' => '您发起的${project}项目《${title}》已有用户支持幸运抽档位，金额：${price} 昵称：${nickname} ，手机号：${telephone} 。呼朋引伴分享转发，让更多人知道你的项目有多棒。',
            'params' => ['project','title','price','nickname','telephone']
        ],
        //有用户购买幸运抽档位项目成功发送短信通知达人    
        'SMS_107035152' => [
            'content' => '您发起的${project}项目《${title}》已有用户无条件打赏支持，金额：${price} 昵称：${nickname} ，手机号：${telephone} 。呼朋引伴分享转发，让更多人知道你的项目有多棒。',
            'params' => ['project','title','price','nickname','telephone']
        ],
        //购买项目成功发送短信给用户
        'SMS_36610017' => [
            'content' => '感谢您的支持！我是${originator}，已收到您支持我发起的${project}项目《${title}》共${price}元。请进入吖咪公众号或APP查看详情 (风险提示：${project_1}期间请积极关注项目进展，您可以在项目评论区和吖咪发起的微信群组联系到发起人，请勿加入非项目发起人以及非吖咪官方创建的微信群，谨防受骗。)客服微信：${wx}（工作时间：9:00-18:00）有问题随时保持联络！',
            'params' => ['originator','project','title','price','project_1','wx']
        ],
        //项目结束后发送短信给购买的用户
        'SMS_36620003' => [
            'content' => '您支持的${project_name}项目《${title}》认筹金额已达成目标，项目${project_name_1}成功。谢谢您的支持，也希望您能把这个项目告诉更多人。客服微信：${wx}（工作时间：9:00-18:00）有问题随时保持联络！',
            'params' => ['project_name','title','project_name_1','wx']
        ],
        //二次支付成功发送短信给用户
        'SMS_38685093' => [
            'content' => '感谢您的支持！我是${originator}，已收到您支持我发起的${project_name}项目《${title}》二次支付款共${price}元，请进入吖咪公众号或APP查看详情 (风险提示：${project_name_1}期间请积极关注项目进展，您可以在项目评论区和吖咪发起的微信群组联系到发起人，请勿加入非项目发起人以及非吖咪官方创建的微信群，谨防受骗。) 客服微信：${wx}（工作时间：9:00-18:00）有问题随时保持联络！',
            'params' => ['originator','project_name','title','price','project_name_1','wx']
        ],
        //开启购买提醒
        'SMS_48040327' => [
            'content' => '感谢您关注${daren}发起的${project_name}项目《${title}》。项目将于${start_time}正式抢拍，可进入吖咪公众号或APP查看详情。谢谢您的支持！客服微信：${wx}（工作时间：9:00-20:00）有问题随时保持联络！',
            'params' => ['daren','project_name','title','start_time','wx']
        ],
        // 私房菜成功购买
        'SMS_77360070' => [
            'content' => '您已成功购买“${title}”${num}份（订单号：${sn}），有效期至${date}，地址：${address}。如需使用，请提前3天致电Host预约，Host电话：${phone}，客服微信：yami194（工作时间：周一至周五9:00-18:00）',
            'params' => ['title', 'num', 'sn', 'date', 'address', 'phone']
        ],
        // 众筹抽奖成功短信通知
        'SMS_83570001' => [
            'content' => '您支持的${title}！${user}将在7~15个工作日内联系您确认${action}事宜，请您留意。',
            'params' => ['title', 'user', 'action']
        ],
        // 众筹抽奖未中奖短信通知
        'SMS_83610005' => [
            'content' => '您支持的${title}，${action}可进入吖咪公众号或APP查看订单详情。',
            'params' => ['title', 'action']
        ],
        // 众筹支持成功通知
        'SMS_84580051' => [
            'content' => '感谢支持！我是（${name}），您已在${title}中支持“${times}”共${money}。${project_name}期间请积极关注项目进展，可进入吖咪公众号或APP查看详情（风险提示：请勿加入非官方微信群，谨防受骗）。客服微信：yami194（工作时间：9:00-18:00）',
            'params' => ['name', 'title', 'times', 'money', 'project_name']
        ],
        // 众筹尾款支付成功通知
        'SMS_84655050' => [
            'content' => '感谢支持！我是${name}，您已成功支付${title}尾款${money}元，请进入吖咪公众号或APP查看详情 （风险提示：请勿加入非官方微信群，谨防受骗) 客服微信：yami194（工作时间：9:00-18:00）',
            'params' => ['name', 'title', 'money']
        ],
        // 众筹预购入选通知
        'SMS_85170007' => [
            'content' => '恭喜您成功入选${project_name}项目《${title}》的共建人！请进入吖咪公众号或APP查看详情，并于（短信发送日之后第四天0点，如：7月20日发送短信，截至日期为7月24日0点）前支付尾款（过期视为自动放弃，名额将释放给其他候选人），谢谢您的支持！客服微信：yami194（工作时间：9:00-18:00）',
            'params' => ['project_name', 'title']
        ],
        // 众筹支付成功通知
        'SMS_85395035' => [
            'content' => '感谢支持！我是${name}，您已在${title}中支持“${times}”共${money}。${project_name}期间请积极关注项目进展，可进入吖咪公众号或APP查看详情（风险提示：请勿加入非官方微信群，谨防受骗）。客服微信：yami194（工作时间：9:00-18:00）',
            'params' => ['name', 'title', 'times', 'money', 'project_name']
        ],
        // 7日众筹通知
        'SMS_86130029' => [
            'content' => '您支持的${project_name}项目《${title}》还有7天就要结束啦！项目完成进度：${per}%，可进入吖咪公众号或APP查看详情。谢谢您的支持，也希望您将项目分享给好友们，一起共筑梦想。客服微信：yami194（工作时间：9:00-18:00）',
            'params' => [
                'project_name', 'title', 'per'
            ]
        ],
        // 众筹成功通知
        'SMS_85945001' => [
            'content' => '您支持的${project_name}项目《${project_title}》已达目标，项目${project_action}成功，达成度${per}%。请进入吖咪公众号或APP查看详情。谢谢您的支持，也希望您将项目分享给好友们，一起共筑梦想。客服微信：yami194（工作时间：9:00-18:00）',
            'params' => ['project_name', 'project_title', 'project_action', 'per']
        ],
        // 众筹失败通知
        'SMS_85965005' => [
            'content' => '您支持的${project_name}项目《${project_title}》${project_action}期已结束，${project_action}金额未达目标，项目${project_name}失败，请进入吖咪公众号或APP查看详情。您在项目中支付的金额，系统将在7个工作日内原路退回您的支付账户，请留意账户信息。新一期${project_name}项目即将启动，敬请期待。客服微信：yami194（工作时间：9:00-18:00）',
            'params' => ['project_name', 'project_title', 'project_action']
        ],
        // 开团成功（团长）
        'SMS_99240068' => [
            'content' => '恭喜您完成支付，“${title}”开团成功！拼团截止时间：${time}，拼团人数：${num}，快分享给好友一起拼团吧！',
            'params' => ['title', 'time', 'num'],
            'wxtemplate' => [
                'template_id' => '-NiEcn40LIC2mckwGmwTL39Y5YnLotOJ_Rn6cKFYIJY',
                'first' => '恭喜您完成支付，开团成功！',
                'keyword1' => '商品',
                'keyword2' => '拼团价',
                'keyword3' => '团长',
                'keyword4' => '拼团人数',
                'keyword5' => '截至时间',
                'remark' => '快点击进入拼单页面，分享给好友一起拼团吧！>>'
            ]
        ],
        // 拼团成功（团长）
        'SMS_99115059' => [
            'content' => '您的“${title}”拼团已成功！商家将在3天内发货，您可登录吖咪APP或者吖咪yummy公众号查询订单及物流信息。我们期待与您分享更多的生活好物！',
            'params' => ['title'],
            'wxtemplate' => [
                'template_id' => '4UP-wyBf_5VvI2M5eqPYSieomM7z34wB_iRTc75XZpI',
                'first' => '您的{{NAME}}拼团已成功！',
                'keyword1' => '商品名称',
                'keyword2' => '团长',
                'keyword3' => '成团人数',
                'remark' => '您可登录吖咪APP或者吖咪yummy公众号查询订单及物流信息'
            ]
        ],
        // 拼团失败（团长）
        'SMS_99120060' => [
            'content' => '您的“${title}”拼团人数不足，拼团失败！系统将在3个工作日内安排退款，您可登录吖咪APP或者吖咪yummy公众号查询订单信息。我们期待与您分享更多的生活好物！',
            'params' => ['title']
        ],
        // 拼团3小时提醒
        'SMS_105075029' => [
            'content' => '您的${title}拼团还有${time}小时就要结束啦！还差${num}人拼团成功！快点击进入拼单页面，分享给好友一起拼团吧！',
            'params' => ['title','num'],
            'wxtemplate' => [
                'template_id' => 'T5gZe1GEtT_cxUuUwbRjVho5xNvn_NwaX14PsHRtfN8',
                'first' => '您的匹配商品名拼团还有3小时就要结束啦！还差{{NUM}}人拼团成功',
                'keyword1' => '团长姓名',
                'keyword2' => '开团商品',
                'keyword3' => '成团价格',
                'remark' => '快点击进入拼单页面，分享给好友一起拼团吧！>>'
            ]
        ],
        // 参团成功（团员）
        'SMS_99245077' => [
            'content' => '恭喜您完成支付，“${title}”参团成功！拼团截止时间：${time}，拼团人数：还差${num}人，快邀请好友一起拼团，更快成团哦！',
            'params' => ['title', 'time', 'num'],
            'wxtemplate' => [
                'template_id' => 'oArKjQ-isEPbnEkOWsKyK_6za6XMHls34el7T_OrVyw',
                'first' => '恭喜您完成支付，参团成功！',
                'keyword1' => '商品',
                'keyword2' => '拼团价',
                'keyword3' => '团长',
                'keyword4' => '拼团人数',
                'keyword5' => '截至时间',
                'remark' => '点此邀请好友一起拼团，更快成团哦！>>'
            ]
        ],
        // 拼团成功（团员）
        'SMS_99590011' => [
            'content' => '您参与的“${title}”拼团已成功！商家将在3天内发货，您可登录吖咪APP或者吖咪yummy公众号查询订单及物流信息。我们期待与您分享更多的生活好物！',
            'title' => ['title'],
            'wxtemplate' => [
                'template_id' => '4UP-wyBf_5VvI2M5eqPYSieomM7z34wB_iRTc75XZpI',
                'first' => '您的{{NAME}}拼团已成功！',
                'keyword1' => '商品名称',
                'keyword2' => '团长',
                'keyword3' => '成团人数',
                'remark' => '您可登录吖咪APP或者吖咪yummy公众号查询订单及物流信息'
            ]
        ],
        // 拼团失败（团员）
        'SMS_99130060' => [
            'content' => '您参与的“${title}”拼团人数不足，拼团失败！系统将在3个工作日内安排退款，您可登录吖咪APP或者吖咪yummy公众号查询订单信息。我们期待与您分享更多的生活好物！',
            'params' => ['title']
        ],
        //神隐老用户推广
        'SMS_109450138  ' => [
            'content' => '走遍世界的美食摄影师，在广州市中心造了梦想中的居酒屋，有美食美酒和一群有趣的朋友——神隐酒场项目已在吖咪上线，开放四小时突破百万，现在他希望有更多人和他一起造梦！您可进入吖咪公众号或APP查看详情，或点击链接直接参与：http://dwz.cn/6NXIyi，也希望您将项目分享给好友，一起共筑梦想。客服：yami194（工作时间：9:00-18:00），回TD退订',
        //    'params' => ['title']
        ],
        'SMS_35180078' => [
            'content' => '尊敬的${customer}，欢迎您使用阿里云通信服务！',
            'params' => ['customer']
        ],
        // 预付款成功后，发送微信消息
        'WEIXIN_20180731' => [
            'content' => '感谢支持！我是${name}，您已在《${title}》中支持“${time}”共匹配档位金额元。吖咪酱（yami194）将会在三个工作日内联系您，确认下一阶段的安排。众筹期间请积极关注项目进展，可进入吖咪公众号或APP查看详情（风险提示：请勿加入非官方微信群，谨防受骗）。客服微信：yami194（工作时间：工作日9:00-18:00）',
            'params' => [
                'name',
                'title',
                'time'
            ]
        ]
    ]
];