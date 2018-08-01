var fansObject = {
	winScrollSock:false,
	loadfans:function(p){
		$('.page_fans center').show();
		ajax('Member/Follow/getlist', {get:{page:p},post:{type:0}}, function(d){
			$('.page_fans center').hide();
			var code = '';
			if(d.length > 0){
				for(var i in d){				
					code +='<div class="item">';
					code +='	<div class="User_Img">';
					code +='    	<img class="imgPortrait" src="'+ d[i].path +'" onerror="javascript:this.src=\'images/personaldata_eadportrait_icon@2x.png\'" />';
					code +='	</div>';
					code +='	<div class="User_Conten">';
					code +='		<div class="Contens">';
					code +='        	<div class="qUserName">'+ d[i].fans_nickname +'</div>';
					if(d[i].tips)
					code +='        	<div class="msgDetail"><font>发布了</font>'+ d[i].tips +'</div>';
					else
					code +='        	<div class="msgDetail">尚未发布活动</div>';
					
					code +='    	</div>';
					code +='	</div>';   
					code +='	<div class="clearfix"></div>';	
					code +='</div>';
				}
				fansObject.winScrollSock = false;
			}else{
				if(p ==1)
				code +='<div class="no_msgs"><img src="images/fansover.png" /><span>您还没有粉丝！</span></div>';
				else
				code +='<div class="no_more"></div>';
			}
			if(p == 1){
				$('.page_fans .msgList').html(code);
			}else{
				$('.page_fans .msgList').append(code);
			}
			
		});
	},
	onload : function(){
		if(!member){
			win.login();
			return;
		}
		$('#message').hide();

		//屏幕滚动事件
		$('.page_fans.wrapper').scroll(function(){
			//滚动加载内容
			if($(this).scrollTop() + $(this).height() > $(this).get(0).scrollHeight - 10 && !fansObject.winScrollSock){
				fansObject.winScrollSock = true;
				fansObject.loadfans(Math.ceil($('.page_fans .msgList .item').size() / 10 + 1));
			}
		});

		// 加载默认筛选列表
		fansObject.loadfans(1);
	}
};
