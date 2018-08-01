
/************获取地址栏参数*************/
function GetHrefParameter(manageHref){
	var arr=new Array();
	if(manageHref==null||manageHref==''||manageHref=='undefined'){manageHref = window.location.href;}
	
	if(manageHref.indexOf('?') == -1||manageHref.indexOf('=') == -1||manageHref.split('?')[1]==''||manageHref.split('?')[1]=='undefined'){
		arr['error']=1;//console.log("没有 参数");
		}else{
			var hrefParameter = manageHref.split('?')[1];
			//console.log("获取地址参数\t"+hrefParameter);
			for(var i in hrefParameter.split('&')){
				arr[i] = hrefParameter.split('&')[i].split('=');
				}
			}
	//if(arr['error']){alert("错误!");console.log("Error!");}
	return arr;
	}
/***************************收藏、关注 算法*******************************/
function makeConditions(t_URL,ClassName){//根据 t_URL 获取 收藏相关的 type 和 type_id
	var postType,postId;//关注类型 0-关注活动 1-关注商品 2-关注达人
	if(t_URL==null||t_URL==undefined||t_URL.length=='') t_URL = window.location.href;
	//console.log("当前URL\t"+t_URL);
	if(t_URL.indexOf('tips')>=0){/*alert("活动页面 ！"); */postType=0;}
	if(t_URL.indexOf('goods')>=0){/*alert("商品页面 ！");*/postType=1;}
	if(t_URL.indexOf('daRen')>=0){/*alert("达人页面 ！");*/postType=2;}
	//console.log("postType="+postType);
/*	var t_id=t_URL.split('#');
	if(t_id <= 1){
		$('body').html('非法访问！');
		return;
	}
	postId = t_id[1];*/
	//console.log("postId="+postId);
	
	var XX=GetHrefParameter(t_URL);
	if(XX['error']){$('body').html('<center>非法访问！</center>');}
	for(var i in XX){
		if(XX[i][0]=='tips_id'){postId = XX[i][1];}
		if(XX[i][0]=='goods_id'){postId = XX[i][1];}
		if(XX[i][0]=='daRen_id'){postId = XX[i][1];}
		}
		

	if(parseInt(postType)*parseInt(postId)>=0)	
	handleDb(postType,postId,ClassName);//调用插入数据库函数
}
//	if(form1.box.checked)
//  {
//    document.cookie("usn_"+str,form1.loginName.value);
//    document.cookie("pwd_"+str,form1.password.value);
//    document.cookie("box_"+str,"yes");
//  }else{
//   delCookie("box_"+str);
//  }


function handleDb(type,typeId,ClassName){//添加、取消 关注
	//console.log("进行数据库插入操作 ！");
	var member_id;
	//console.log("type="+type+"\t\t"+"typeId="+typeId);
	if(parent.member == null){
		/******************先清除掉原有的缓存记录*******************/
		 document.cookie='type'+'='; //删除 缓存
		 document.cookie='typeId'+'=';//删除 缓存
		 //console.log("删除 缓存 后获取的cookies\t"+document.cookie);
		 /******************再添加新的对应的的缓存记录*******************/
		 document.cookie='type'+'='+type; //添加 缓存
		 document.cookie='typeId'+'='+typeId;//添加 缓存
		 //console.log("获取的cookies\t"+document.cookie);//读取缓存
		if(confirm("尚未登陆!"))window.location.href("jump('ucenter.html')");
	}else{
		member_id=parent.member.id;
		}
		
	console.log(parseInt(member_id)+"\t\t"+parseInt(type)+"\t\t"+parseInt(typeId));
	if(parseInt(member_id)*parseInt(type)*parseInt(typeId)>=0){//判断传递过来的参数是否正确
		ajax('Member/Follow/changeFollow', {'member_id':member_id,'type':type,'type_id':typeId}, function(d){
			console.log("d.info \t"+d.info);
			if(d.info =='关注成功！'){
				//$('.header .values').addClass('valued');
				$(ClassName).addClass('valued');
				//alert('关注成功！');
			}else if(d.info =='取消关注成功！'){
				//$('.header .values').removeClass('valued');
				$(ClassName).removeClass('valued');
				//alert('取消关注成功！');
			}else{
				alert(' 操作失败!');
			}
		
		});
	}else{}
}	
/**************************收藏、关注 算法 结束***************************/

function getFollowInfFromCookies(){//从缓存中 获取 原本想收藏的 type 和 type_id ,获取后，继续收藏操作，并删除对缓存应记录
	var mcookies = document.cookie;
	//console.log("获取到的缓存\t"+mcookies);
	if(mcookies==''||mcookies=='unfined'||mcookies=='type=; typeId='){
	}else{
		var  type=mcookies.split(';')[0].split('=')[1];
		var type_id = mcookies.split(';')[1].split('=')[1];
		//console.log("type="+type+"\t\t type_id="+type_id);
		document.cookie='type'+'='; //删除 缓存
		document.cookie='typeId'+'=';//删除 缓存
		//console.log("从缓存中提取的信息\t type="+type+"\t type_id="+type_id);
		handleDb(type,type_id);
		
		}
	}

/******************↓↓ 根据屏幕分辨率，自动调整页面大小 ↓↓*********************/
$('html').attr('style', 'font-size:' + 10 * ($(window).width() / 360) +'px !important');
$(window).resize(function(){
	$('html').attr('style', 'font-size:' + 10 * ($(window).width() / 360) +'px !important');
});

$(function(){
	$('a').each(function(){
		if(this.href.indexOf('javascript:') < 0)
			this.href = 'javascript:parent.page.jump("'+ this.href +'")';
	});
});

/*********************** ↓↓ 自定义单选框 ↓↓ **********************/
/***************当前页面 只有一个选择框的时候****************/

var ifSame=0;	
function  showOrHide(em){
	if(ifSame%2==0) $(em).addClass(' checked');
	else $(em).removeClass('checked');
	ifSame++;
	console.log(ifSame);
	}

/***************当前页面 有多个选择框的时候*************/
function radioStyle(em,name){
   	  $(name).removeClass('checked');
	  $(em).addClass('checked');
	}
/******************自定义单选框**结束***********************/


var ajax = parent.win.ajax;
var $$ = parent.page.ready;
var jump = parent.page.jump;

//时间戳格式化
String.prototype.timeFormat = function(format){
	var time = this.toString();
	var myDate = new Date(time * 1000);
	var _date = {};
	_date.Y = myDate.getFullYear();
	_date.m = (myDate.getMonth() + 1).toString();
	if(_date.m.length == 1)_date.m = '0' + _date.m;
	_date.d = myDate.getDate();
	_date.H = myDate.getHours();
	_date.i = myDate.getMinutes().toString();
	if(_date.i.length == 1)_date.i = '0' + _date.i;
	_date.s = myDate.getSeconds().toString();
	if(_date.s.length == 1)_date.s = '0' + _date.s;
	_date.w = myDate.getDay().toString();
	weekday = ['周日','周一','周二','周三','周四','周五','周六'];
	_date.W = weekday[myDate.getDay()];
	for(var i in _date){
		format = format.replace(i, _date[i]);
	}
	return format;
}
//var date = time.timeFormat('Y-m-d H:i:s w');

//将url参数转为json
String.prototype.decodeURL = function(){
	var url = this.toString();
	if(url.indexOf('?') > 0){
		url = url.split('?')[1];
	}
	var arr = url.split('&');
	var params = {};
	for(var i in arr){
		var a = arr[i].split('=');
		if(a.length == 2){
			params[a[0]] = a[1];
		}
	}
	return params;
}

//图片路径格式化
String.prototype.pathFormat = function(){
	var path = this.toString();
	if(path.indexOf('http://') == 0)return path;
	if(path.substr(0,1) == '/')path = path.substr(1);
	if(path.indexOf('uploads/') >= 0){
		path = 'http://yummy194.cn/' + path;
	}else{
		path = 'http://img.m.yami.ren/' + path;
	}
	return path;
}

var btnSubmit = {
	btnLoadTimer : null,
	btnLoadem : null,
	beforeLoadWords : null,
	isLoading : function(){
		if(this.btnLoadTimer != null)return true;
		return false;
	},
	loading : function($em, msg){
		var msg = msg||'提交中';
		this.btnLoadem = $em;
		$em.addClass('disabled');
		this.beforeLoadWords = $em.val();
		var s = 0;
		this.btnLoadTimer = window.setInterval(function (){
			s ++;
			if(s > 3)s = 0;
			var m = msg;
			for(var i=0; i<s; i++){
				m += '.';
			}
			$em.val(m);
		}, 300);
	},
	//关闭loading层
	close : function(){
		window.clearInterval(this.btnLoadTimer);
		this.btnLoadem.removeClass('disabled');
		this.btnLoadem.val(this.beforeLoadWords);
		this.btnLoadTimer = null;
		this.btnLoadem = null;
		this.beforeLoadWords = null;			
	}
};
	
function selectthis(em, num){
	var tmp;
	console.log("原来的值"+tmp);
	if(tmp==num){alert();}
	tmp = num;
	console.log("现在的值"+tmp);
	if(num==1){$(en).css('background-position','top left');}
	else{$(this).css('background-position','bottom left');}
	//$(em).css('background','red');
	//$(this).css('background-position','bottom left');
	//console.log($(this).pre.attr('class'));
	//$('.selectedEffect').css('background-position','bottom left');
}
