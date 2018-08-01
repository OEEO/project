//全选
function clickAll(e){
	$(e).parents('form').find(':checkbox').prop('checked',e.checked);
}

// 删除/批量删除
function datasDelete(){
	var $that = $('.am-form');
	var actionLink = $that.attr('action');
	var datas = 'method=delete&'+$that.serialize();
	$.ajax({
		type: "POST",
		url: actionLink,
		data: datas,
		async: false,
		dataType: "json",
		success: function(data) {
			if(data.status == 1){
				$that.find(':checkbox').prop('checked',false);
				updateAlert(data.info,'am-alert-success');
			}else{
				updateAlert(data.info,'am-alert-danger');
			}
			setTimeout(function(){
				$('#top-alert').find('button').click(); // 隐藏提示框
				if(data.status == 1){ // 操作数据库成功
					if (data.url) {
						location.href=data.url;
					}else{
						location.reload();
					}
				}
			},1500);
		}
	});
}

/*
* 消息提示js
* string text 	：提示文本
* string c 		：提示样式
*	成功：绿色	am-alert-success
*	警告：橙色	am-alert-warning
*	危险：红色	am-alert-danger
*	次要：白色	am-alert-secondary
*/
window.updateAlert = function (text,c) {
	/**顶部警告栏*/
	var content = $('#main');
	var top_alert = $('#top-alert');
	top_alert.find('.close').on('click', function () {
		top_alert.removeClass('block').slideUp(200);
		// content.animate({paddingTop:'-=55'},200);
	});
	text = text||'default';
	c = c||false;
	if ( text!='default' ) {
		top_alert.find('.alert-content').text(text);
		if (top_alert.hasClass('block')) {
		} else {
			top_alert.addClass('block').slideDown(200);
			// content.animate({paddingTop:'+=55'},200);
		}
	} else {
		if (top_alert.hasClass('block')) {
			top_alert.removeClass('block').slideUp(200);
			// content.animate({paddingTop:'-=55'},200);
		}
	}
	if ( c!=false ) {
		top_alert.removeClass('am-alert-success am-alert-warning am-alert-danger am-alert-secondary').addClass(c);
	}
};

/*
* 提示错误
* string text 	：提示文本
*/
function alertMsg(text,c){
	c = c||'am-alert-danger';
	updateAlert(text , c);
	setTimeout(function(){
		$('#top-alert').find('button').click(); // 隐藏提示框
	},1500);
}


/**
 * 去掉文件路径，返回文件名
 * @param {String} str1
 */
function basename(str1)
{
   str2="/"
   
   var s = str1.lastIndexOf(str2);
   if (s==-1) {
   str2="\\"
   var s = str1.lastIndexOf(str2);
   }
   if (s==-1) return str1;
   else{
   	return(str1.substring(s+1,str1.length));
   }
   return ""
}
