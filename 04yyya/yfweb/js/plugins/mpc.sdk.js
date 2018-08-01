function Mpc(host, get){
	this.ws = null;
	this.host = host||'';
	this.get = get||'';
	this.open = null;
	this.fn = {};
	this.close = null;
	//申请建立IM服务器连接
	this.connect = function(){
		var t = this;
		if(t.get == '')
			this.ws = new WebSocket('ws://'+ t.host);
		else{
			var p = [];
			for(var i in this.get){
				p.push(i + '=' + this.get[i]);
			}
			this.ws = new WebSocket('ws://'+ t.host +'/?' + p.join('&'));
		}
		this.ws.addEventListener('open', function(d){
			if(typeof(this.open) == 'function'){
				this.open(d);
			}else{
				console.info('Websocket连接成功！');
			}
		});
		this.ws.addEventListener('message', function(d){
			try {
				var d = eval('('+ d.data +')');
				var act = d.act;
				t.fn[act](d.data);
			}catch(ev){
				if(this.fn['auto'])
					this.fn['auto'](d);
				else
					console.warn(d);
			}
		});
		this.ws.addEventListener('close', function(d){
			t.ws = null;
			console.log('Websocket连接断开！');
		});
	};
	//发送websocket数据包
	this.send = function(data, act){
		if(this.ws == null){
			console.warn('Websocket没有连接，无法进行操作！');
		}else{
			if(act){
				var d = {};
				d['data'] = data||'';
				d['act'] = act;
				d = JSON.stringify(d);
				this.ws.send(d);
			}else{
				this.ws.send(data);
			}
		}
	};
	//接收websocket数据包
	this.on = function(fn, act){
		if(act)
			this.fn[act] = fn;
		else
			this.fn['auto'] = fn;
	};
	//绑定消息提示（可按需求重载）
	this.on('callback', function(d){
		if(d.status && d.status == 1){
			console.info(d.info);
		}else{
			if(d.status)
				console.warn(d.info);
			else
				console.warn(d);
		}
	});
};
