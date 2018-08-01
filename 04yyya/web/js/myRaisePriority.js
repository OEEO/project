/**
 * Created by fyt on 2017/3/10.
 */
var myRaisePriorityObject = {
    tourl:function(em) {
        var data = {};
        data.raise_id = $(em).attr('raise_id');
        data.times_id = $(em).attr('times_id');
        data.id = $(em).attr('p_id');
        jump('raiseDetail', data);
    },
    onload : function(){
        ajax('Member/Index/privilege',{get:{token:win.token}},function(d){
            var code = '';
            var raiseIdset = {}; //用于去重
            for(var k in d){
                raiseIdset[d[k].type_id+""]=false;
                //raiseIdset[d[k].type_id]=false;
            }
            if(d.length > 0){
                for(var i in d){
                    if(raiseIdset[d[i].type_id+""]){
                        continue;
                    }else{
                        raiseIdset[d[i].type_id+""]=true;
                    }
                    if(d[i].order_id == ''){
                        code += '<div class="viplist list1">';
                        code += '<div class="list_rtitle"></div>';
                        code += '<div class="list_title">'+d[i].title+'</div>';
                        
                        if ((new Date().getTime()/1000) > d[i].end_time ) {
                            code += '<div class="list_time">目前众筹已经开始</div>';
                            code += '<a href="javascript:void(0)" onclick="myRaisePriorityObject.tourl(this)" class="usebtn" raise_id="'+d[i].type_id+'" times_id="'+d[i].tips_times_id+'" p_id="'+d[i].id+'">查看项目</a>';
                        }else {
                            code += '<div class="list_time">请在 <font>'+d[i].end_time.timeFormat('Y-m-d H:i')+'</font> 前使用</div>';
                            code += '<a href="javascript:void(0)" onclick="myRaisePriorityObject.tourl(this)" class="usebtn" raise_id="'+d[i].type_id+'" times_id="'+d[i].tips_times_id+'" p_id="'+d[i].id+'">优先认筹</a>';                        
                        }
                    }else{
                        code += '<div class="viplist list2">';
                        code += '<div class="list_rtitle"></div>';
                        code += '<div class="list_title">'+d[i].title+'</div>';
                        code += '<div class="list_time">到期日 | <font>'+d[i].end_time.timeFormat('Y-m-d H:i')+'</font></div>';
                        code += '<a href="javascript:void(0)" class="usebtn">已使用</a>';
                    }
                    code += '</div>';
                }
                $('.page_myRaisePriority .raisevip').html(code);
            }else{
                $('.page_myRaisePriority .raisevip').html('<div class="no_msgs"><img src="images/category_over.png" /><span>暂时没有优先认筹权哦</span></div>');
            }
        });
    }
};
