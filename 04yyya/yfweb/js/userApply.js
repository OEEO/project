/**
 * Created by fyt on 2016/10/18.
 */
var userApplyObject = {
    apply:function(){
        var data = {};
        data.catid = 34;
        for(var i =0;i<document.applyform.elements.length;i++){
            if(document.applyform.elements[i].value == ''){
                $.alert('请完善资料','error');
                return;
            }else{
                data[document.applyform.elements[i].name] = document.applyform.elements[i].value;
            }
        }
        console.log(data);
        ajax('Home/Apply/submit', data, function(d){
            if(d.status == 1){
                $.alert(d.info, function () {
                    for(var i =0;i<document.applyform.elements.length;i++){
                        document.applyform.elements[i].value = '';
                    }
                    $('.page_userApply select').css('color','#ccc');
                    return;
                });
            }else{
                $.alert(d.info, 'error');
                // page.reload();
                return;
            }
        }, 2);
    },
    bian:function(em){
        $(em).css('color','#888');
    },
    loadtitle:function(){
        ajax('Home/Research/GetDetail', {get:{token:win.token},post:{research_id:34}}, function(d){
            var code = '';
            for(var i in d){
                if(d[i].type == 0){
                    code += '<input type="text" name="'+d[i].value+'"  placeholder="'+d[i].content+'"/>';
                }else{
                    code += '<select name="'+d[i].value+'" onchange="userApplyObject.bian(this)">';
                    code += '<option value="" style="display:none;" disabled selected>'+d[i].content+'</option>';
                    for (var j in d[i].answer){
                        code += '<option value="'+d[i].answer[j].value+'">'+d[i].answer[j].content+'</option>';
                    }
                    code += '</select>';
                }
            }
            $('.page_userApply form[name="applyform"]').html(code);
        }, 2);
    },
    onload:function(){
        userApplyObject.loadtitle();
        // $('.page_userApply select').change(function(){
        //     $(this).css('color','#888');
        // });
        // $('.page_userApply .demand').change(function(){
        //     $('.page_userApply .demand').css('color','#888');
        // });
        $('.page_userApply .submit').on('click',function(){
            userApplyObject.apply();
        });
    }
};