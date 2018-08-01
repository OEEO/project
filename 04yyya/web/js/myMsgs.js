var myMsgsObject = {
	onload : function(){
		if(!member){
			win.login();
			return;
		}
		$('#message').hide();
		ajax('Member/Message/getList', {get:{page:1},post:{is_all:1}}, function(d){
			var code = '';
console.log(d);
			//即时通信
			code +='<div class="item customer" onclick="javascript:jump(\'MsgIM\',{type:1});">';
			code +='	<div class="User_Img">';
			code +='    	<img class="imgPortrait" src="images/mymsgs_x2.png" />';
			code +='	</div>';
			code +='	<div class="User_Conten">';
			code +='		<div class="Contens">';
			code +='        	<div class="qUserName">在线客服</div>';
			code +='        	<div class="msgDetail"><span class="one_title">服务时间: 工作日9:30-12:00 14:00-18:00</span></div>';
			code +='    	</div>';
			code +='    	<div class="User_right">';
			code +='   			<img class="dayu" src="images/mymsgs_xl.png" />';
			code +='    	</div>';
			code +='	</div>';
			code +='	<div class="clearfix"></div>';
			code +='</div>';

			/*吖咪小助理--系统消息*/
			if(d.system.message.content){
				console.log(d.system.message.content);
				code +='<div class="item" onclick="javascript:jump(\'sysMsgsDetail\',{type:0});">';
				code +='	<div class="User_Img">';
				code +='    	<img class="imgPortrait" src="images/mymsgs_x1.png" />';
				if(d.system.message.count != 0){
					code +='    	<span class="num_img">'+ d.system.message.count +'</span>';
				}
				code +='	</div>';
				code +='	<div class="User_Conten">';
				code +='		<div class="Contens">';
				code +='        	<div class="qUserName">吖咪小助理</div>';
				code +='        	<div class="msgDetail"><span class="one_title">'+ d.system.message.content +'</span></div>';
				code +='    	</div>';
				code +='    	<div class="User_right">';
				code +='   			<img class="dayu" src="images/mymsgs_xl.png" />';
				code +='    	</div>';
				code +='	</div>';
				code +='	<div class="clearfix"></div>';
				code +='</div>';
			}

			if(d.more.length > 0){
				for(var i in d.more){
					code +='<div class="item" onclick="javascript:jump(\'MsgsDetail\',{origin_id:'+ d.more[i].id +'});">';
					code +='	<div class="User_Img">';
					if(d.more[i].headpic)
						code +='    	<img class="imgPortrait" src="'+ d.more[i].headpic.pathFormat() +'" />';
					else
						code +='    	<img class="imgPortrait" src="images/portrait.jpg" />';
					if(d.more[i].count)code +='    	<span class="num_img">'+ d.more[i].count +'</span>';
					code +='	</div>';
					code +='	<div class="User_Conten">';
					code +='		<div class="Contens">';
					code +='        	<div class="qUserName">'+ d.more[i].nickname +'</div>';
					code +='        	<div class="msgDetail">'+ d.more[i].content +'</div>';
					code +='    	</div>';
					var dateArr = d.more[i].datetime.split(' ')[0].split('-');
					code +='    	<span class="commitTime">'+ dateArr[1] + '-' + dateArr[2] +'</span>';
					code +='	</div>';
					code +='	<div class="clearfix"></div>';
					code +='</div>';
				}
			}
			if(d.more.length == 0 && d.system.comment == '' && d.system.message.count == '0'){
				code +='<div class="no_msgs"><img src="images/myInfo_blank.png" /><span>您暂时还没有消息</span></div>';
			}
			$('.page_myMsgs .msgList').html(code);

			if(win.ws.power == 1){
				$('.page_myMsgs .msgList .customer').css('display', 'flex');
			}
		});
	}
};
