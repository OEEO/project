/**
 * Created by fyt on 2016/10/8.
 */
var myRaiseOrderObject = {
    page : 1,
    act_status : null,
    locked : false,
    cancelOrder : function(order_id){
        $.dialog('是否确定要取消该订单?', function(){
            ajax('Member/Order/cancel', {order_id : order_id}, function(d){
                if(d.status == 1){
                    $.alert('成功取消订单', function(){
                        page.reload();
                    });
                }else{
                    $.alert(d.info, 'error');
                }
            });
        });
    },
    loadOrder : function(page){
        var data = {
            get:{page:page||myRaiseOrderObject.page}
        };
        data.post = {type:2};
        if(myRaiseOrderObject.act_status){
            //all, none, 5
            data.post.act_status = myRaiseOrderObject.act_status;
        }
        $('.page_myRaiseOrder center').show();
        ajax('Member/Order/index', data, function(d){
            // $('.page_myRaiseOrder center').hide();
            // win.close_loading();
            console.log(d);
            if(d.length > 0){
                var code = '';
                var statu = '众筹';
                var status = ['待付款', '已付款', '已付款', '已付款', '众筹成功', '退款中', '退款成功', '已取消', '退款中'];
                var statusDiv = '';
                for(var i in d){
                    code += '<li>';
                    if(d[i].is_free === '0') {
                        var buyDate = d[i].create_time.toString().timeFormat('Y-m-d H:i');
                        var left = '<div class="buy-date">'+ buyDate +'</div>';
                        var right = '<div class="status">'+ (typeof(status[d[i].act_status])=='string'?status[d[i].act_status]:status[d[i].act_status][d[i].type]) +'</div>';
                        statusDiv = '<div class="status-wrap">'+ left + right +'</div>';
                    } else {
                        statusDiv = '<div class="status-wrap"><div class="status">抽奖福利</div></div>';
                    }
                    code += '	<a class="top" href="javascript:jump(\'orderRaiseDetail\',{order_id:'+ d[i].id +'})">';
                    code += statusDiv;
                    code += '       <div class="middle">';
                    code += '		    <div class="left">';
                    code += '			    <img src="'+ d[i].path.pathFormat() +'">';
                    code += '		    </div>';
                    code += '		    <div class="right">';
                    code += '               <div class="t">';
                    code += '			        <div class="l orderTitle">'+ d[i].title +'</div>';
                    code += '                   <div class="r"><span><font class="caodan">￥</font><font>'+ d[i].price +'</font></span></div>';
                    code += '               </div>';
                    code += '			    <div class="b">';
                    code += '                   <div class="l">'+ d[i].raise_times_title +'</div>';
                    code += '				    <div class="r">X'+ d[i].count +'</div>';
                    code += '			    </div>';
                    code += '           </div>';
                    code += '		</div>';
                    code += '       <div class="footer">'+'<div>' + '项目结束时间：' + d[i].end_time.toString().timeFormat('Y-m-d H:i') + '</div>'+'</div>';
                    code += '	</a>';
                    if(d[i].act_status == 0){
                        code += '   <div class="bottom">';
                        if(win.get.android){
                            var url = 'http://' + DOMAIN + '/order/pay/submitAlipay.do?token='+ win.token +'&order_id=' + d[i].id;
                            code += '		<a href="'+url+'" class="btn">立即付款</a>';
                        }else{
                            code += '		<a href="javascript:wxpay('+ d[i].id +','+d[i].price+','+d[i].limit_time+',2)" class="btn">立即付款</a>';
                        }
                        code += '   </div>';
                    }
                    code += '</li>';
                }
            }else{
                if(page==1)
                    code = '<div class="no_msgs"><img src="images/order_over.png" /><span>抱歉！您还没有相关的订单！</span></div>';
                else
                    code = '<div class="no_more"></div>';
                $('.page_myRaiseOrder').off('scroll');
            }
            if(page == 1)
                $('.page_myRaiseOrder .content').html(code);
            else
                $('.page_myRaiseOrder .content').append(code);
            $('.page_myRaiseOrder center').hide();
        });
    },
    onload : function(){
        $('.page_myRaiseOrder .statu').click(function(){
            $(this).addClass('add_hei').siblings().removeClass('add_hei');
            if($(this).attr('act_status')){
                myRaiseOrderObject.act_status = $(this).attr('act_status');
            }else{
                myRaiseOrderObject.act_status = null;
            }
            $('.page_myRaiseOrder .content').empty();
            myRaiseOrderObject.loadOrder(1);
        });
    },
    onshow:function () {
        $('.page_myRaiseOrder .content').empty();
        $('.page_myRaiseOrder').on('scroll', function(){
            var pagenum = Math.ceil($('.page_myRaiseOrder .content > li').length / 5) + 1;
            if($(this).scrollTop() + $(this).height() > $(this).get(0).scrollHeight - 10
                && $('.page_myRaiseOrder center:visible').length == 0){
                myRaiseOrderObject.loadOrder(pagenum);
            }
        });
        myRaiseOrderObject.loadOrder(1);
    }
};

