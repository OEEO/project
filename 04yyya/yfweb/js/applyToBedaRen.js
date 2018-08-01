var applyToBedaRenObject = {
	realname: null,
	goodat: null,
	telephone: null,
	instruct: null,
	userInftitle: null,
	pic_num: 0,
	changeUserimg:function(pic_id){
		var data = {};
		data.pic_id = pic_id;
		data.nickname = member.nickname;
		ajax('Member/Index/modifyInfo', data, function(d){
			if(d.status == 1){
				member = d.info;
				$.alert('修改成功');
			}
		}, 2);
	},
	selectImg:function(path,pic_id){
		$('.page_applyToBedaRen .contes').html('<div class="nashou_pic"><img src="'+path+'" pic_id ="'+ pic_id +'" /></div>');
	},
	uploadpic : function(em){
		var fileInput = $('<input>').attr('type', 'file').attr('multiple', "true");
		fileInput.change(function(){
			win.loading();
			var file = this.files[0];
			var cvs = $('<canvas>').width(640).attr('width', 640);
			var url = window.URL.createObjectURL(file);
			var img = $('<img/>').attr('src', url);
			img.load(function(){
				var height = 640 / this.width * this.height;
				cvs.height(height).attr('height', height);
				var context = cvs[0].getContext('2d');
				context.drawImage(this, 0, 0, 640, height);
				var data = cvs[0].toDataURL('image/jpeg');
				data = data.replace('data:image/jpeg;base64,', '');

				ajax('Member/Mypic/upload', {file:[data]}, function(d){
					win.close_loading();
					if(d.status == 1){
						var info = d.info;
						$(em).attr('src', info.path).attr('pic_id', info.pic_id).removeClass('empty');
					}else{
						$.alert(d.info, 'error');
					}
				}, false);
			});
		});
		fileInput.click();
	},
	apply : function() {
		var arr = $(document.myform).serializeArray();
		var data = {};
		for(var i in arr){
			data[arr[i].name] = arr[i].value;
			if(!arr[i].value){
				$.alert('请将信息填写完整', 'error');
				return;
			}
		}
		if($('.page_applyToBedaRen').find('[name="pic_group_id"]').attr('pic_id')){
			data['pic_group_id'] = $('.page_applyToBedaRen').find('[name="pic_group_id"]').attr('pic_id');
		}

		ajax('Member/Daren/apply', data, function (d) {
			if (d.status == 1) {
				$.alert(d.info, function () {
					member.dr_status = 0;
					page.reload();
				});
			} else {
				$.alert(d.info, 'error');
			}
		});
	},
	onload : function(){
		//判断是否登录
		if(!member){
			// sessionStorage.setItem('jumpUrl', 'page=choice-ucenter-applyToBedaRen');
			win.login();
			return;
		}
		ajax('member/daren/getApplyInfo', {'category_id':18}, function(d){
			if(d.info){
				$.alert(d.info,'error');
			}else{
				if(d.is_pass==0){
					$('.header.page_applyToBedaRen .applytobe').text('修改');
					$('.page_applyToBedaRen .contenting').show();
				}else if(d.is_pass==1){
					$.alert('您已经是达人了', function(){
						page.back();
					},'error');
					return;
				}else if(d.is_pass==2){
					$('.page_applyToBedaRen .contenting').show();
					$('.page_applyToBedaRen .contenting .title_top').html('申请被<font color="#FF8744">拒绝</font>');
					if(d.refusal_reason != '')
						$('.page_applyToBedaRen .contenting .title_bottom').html('<b>拒绝原因:</b>' + d.refusal_reason);
					else
						$('.page_applyToBedaRen .contenting .title_bottom').text('请完善资料后重新提交');
				}

				var code = '<div class="item">';
				code += '<div class="left">头像</div>';
				code += '<div class="right headpic"><img src="'+ member.path + '"></div>';
				code += '</div>';
				code += '<div class="item">';
				code += '<div class="left">姓名</div>';
				code += '<div class="right">'+ member.nickname +'</div>';
				code += '</div>';
				code += '<div class="item">';
				code += '<div class="left">手机号码</div>';
				code += '<div class="right">'+ member.telephone +'</div>';
				code += '</div>';
				for(var i in d.items){
					code += '<div class="item">';
					code += '<div class="left">'+ d.items[i].question.text +'</div>';
					code += '<div class="right">';
					if(d.items[i].type == 0){
						if(d.items[i].answer)
							code += '<input type="text" name="'+ d.items[i].question.name +'" placeholder="待输入.." value="'+ d.items[i].answer +'">';
						else
							code += '<input type="text" name="'+ d.items[i].question.name +'" placeholder="待输入..">';
					}else if(d.items[i].type == 1){
						code += '<select name="'+ d.items[i].question.name +'">';
						code += '<option>请选择..</option>';
						for(var j in d.items[i].option){
							if(d.items[i].answer && d.items[i].answer == d.items[i].option[j].id)
								code += '<option value="'+ d.items[i].option[j].id +'" selected>'+ d.items[i].option[j].text +'</option>';
							else
								code += '<option value="'+ d.items[i].option[j].id +'">'+ d.items[i].option[j].text +'</option>';
						}
						code += '</select>';
					}else if(d.items[i].type == 2){
						if(d.items[i].answer && d.items[i].answer.pic_id)
							code += '<img name="'+ d.items[i].question.name +'" class="img" src="'+ d.items[i].answer.path +'" pic_id="'+ d.items[i].answer.pic_id +'" onclick="applyToBedaRenObject.uploadpic()">';
						else
							code += '<img name="'+ d.items[i].question.name +'" class="img empty" src="images/add160x160@2x.png" onclick="applyToBedaRenObject.uploadpic()">';
					}

					code += '</div>';
					code += '</div>';
				}
				$('.page_applyToBedaRen .contents').html(code);
			}
		}, 2);

	},
	onshow:function(){
		//图片上传
		applyToBedaRenObject.fileInput.on('change',function(){
			win.loading();
			var file = this.files[0];
			var cvs = $('<canvas>').width(640).attr('width', 640);
			var url = window.URL.createObjectURL(file);
			var img = $('<img/>').attr('src', url);
			img.load(function(){
				var height = 640 / this.width * this.height;
				cvs.height(height).attr('height', height);
				var context = cvs[0].getContext('2d');
				context.drawImage(this, 0, 0, 640, height);
				var data = cvs[0].toDataURL('image/jpeg');
				data = data.replace('data:image/jpeg;base64,', '');

				ajax('Member/Mypic/upload', {file:[data]}, function(d){
					win.close_loading();
					if(d.status == 1){
						var info = d.info;
						$("img[name='pic_group_id']").attr('src', info.path).attr('pic_id', info.pic_id).removeClass('empty');
					}else{
						$.alert(d.info, 'error');
					}
				}, 2);
			});
		});
	}
};

