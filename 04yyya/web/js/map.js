var mapObject = {
	init : function(){
		var map = new qq.maps.Map(document.getElementById("container"),{
			zoom: 13
		});
		var marker;
		geocoder = new qq.maps.Geocoder({
			complete : function(result){
				map.setCenter(result.detail.location);
				marker = new qq.maps.Marker({
					position:result.detail.location,
					title:win.get.name,
					map:map
				});
			}
		});
		var latLng = new qq.maps.LatLng(win.get.latitude, win.get.longitude);
		//调用获取位置方法
		geocoder.getAddress(latLng);
	},
	onload : function(){
		var script = document.createElement("script");
		script.type = "text/javascript";
		script.src = "http://map.qq.com/api/js?v=2.exp&key=B6JBZ-JLVK4-QFCUC-DFNRG-PBIP7-OTFAJ&callback=mapObject.init";
		document.body.appendChild(script);
	}
};
