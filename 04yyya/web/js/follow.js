var followObject = {
	del_follow:function(em,id){
		ajax('Member/Follow/changeFollow', {member_id:id, type:0}, function(d){
			if(d.status == 1){
				$.alert('已经取消关注', function(){
					$(em).parent().parent().remove();
				});
			}else{
				$.alert('操作失败', 'error');
			}
		});
	},
	loadfans:function(p){
		$('.page_follow center').show();
		ajax('Member/Follow/getlist', {get:{page:p},post:{type:1}}, function(d){
			var code = '';
			if(d.length > 0){
				for(var i in d){				
					code +='<div class="item">';
					code +='	<div class="User_Img">';
					code +='    	<img class="imgPortrait" src="'+ d[i].path +'"/>';
					code +='	</div>';
					code +='	<div class="User_Conten">';
					code +='		<div class="Contens">';
					code +='        	<div class="qUserName">'+ d[i].fans_nickname +'</div>';
					if(d[i].tips)
					code +='        	<div class="msgDetail"><font>发布了</font>'+ d[i].tips +'</div>';
					else
					code +='        	<div class="msgDetail">尚未发布活动</div>';
					
					code +='    	</div>';
					code +='    	<div class="User_right" onclick="followObject.del_follow(this,'+ d[i].member_id +')">取消关注</div>';
					code +='	</div>';   
					code +='	<div class="clearfix"></div>';	
					code +='</div>';
				}
			}else{
				//code +='<div class="no_msgs"><!--<img src="images/myInfo_blank.png" />--><span>暂时没有关注</span></div>';
				if(p == 1)
				code ='<div class="no_msgs"><img src="images/followover.png" /><span>您还没有任何关注！</span></div>';
				else
				code ='<div class="no_more"></div>';
				$('.page_follow').off('scroll');
			}
			if(p == 1)
				$('.page_follow .msgList').html(code);
			else
				$('.page_follow .msgList').append(code);
			$('.page_follow center').hide();
			
		});
	},
	onload : function(){
		if(!member){
			win.login();
			return;
		}
		$('#message').hide();
	},
	onshow:function(){
		$('.page_follow .msgList').empty();
		//屏幕滚动事件
		$('.page_follow.wrapper').on('scroll', function(){
			//滚动加载内容
			var pagenum = Math.ceil($('.page_follow .msgList .item').length / 10) + 1;
			if($(this).scrollTop() + $(this).height() > $(this).get(0).scrollHeight - 10
				&& $('.page_follow center:visible').length == 0){
				followObject.loadfans(pagenum);
			}
		});

		// 加载默认筛选列表
		followObject.loadfans(1);
	}
};
