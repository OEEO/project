jQuery.extend({
	ajaxUpload : function(setting){
		if(typeof(setting) == 'function'){
			var url = '';
			var dataType = 'text';
			var success = setting;
			var loading = false;
			var mydata = {};
		}else if(typeof(setting) == 'object'){
			var url = setting.url||'';
			var dataType = setting.dataType||'text';
			var success = setting.success;
			var loading = setting.loading||false;
			var mydata = setting.data||{};
		}
		if($(window.uploadWindow).size() == 0){
			var $uploadWindow = $('<iframe width="0" height="0" name="uploadWindow"></iframe>');
			$uploadWindow.css({
				'position':'absolute',
				'width':'0px',
				'height':'0px'
			});
			$uploadWindow.appendTo('body');
		}else{
			var $uploadWindow = $(window.uploadWindow);
		}
		
		if($(window.uploadWindow.document.upload).size() == 0){
			window.uploadWindow.document.write('<form name="upload" action="'+ url +'" method="post" enctype="multipart/form-data"></form>');
		}
		if($(window.uploadWindow.document.upload.file).size() == 0){
			var $file = $('<input type="file" name="file" />').appendTo(window.uploadWindow.document.upload);
			var code = '';
			for(var i in mydata){
				code += '<input type="text" name="'+ i +'" value="'+ mydata[i] +'">';
			}
			$(window.uploadWindow.document.upload).append(code);
			$file.change(function(){
				if(typeof(loading) == 'function')loading();
				$(window.uploadWindow.document.upload).submit();
				$uploadWindow.load(function(){
					var data = $(window.uploadWindow.document).find('pre').html();
					if(dataType == 'json'){
                        try {
                            var _data = eval('('+ data +')');
                        }catch (e){
                            var _data = data;
                        }
                        data = _data;
					}
					success(data);
					this.remove();
				});
			});
		}else{
			$file = $(window.uploadWindow.document.upload.file);
		}
		$file.click();
	}
});
