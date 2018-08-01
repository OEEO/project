var wechat = {};
var my_err = [];
var myWxF = false;
function loadWechat(appid, timestamp, noncestr, signature, callback){
	wx.config({
		debug: false,
		appId: appid, // 必填，公众号的唯一标识
		timestamp: timestamp, // 必填，生成签名的时间戳，切记时间戳是整数型，别加引号
		nonceStr: noncestr, // 必填，生成签名的随机串
		signature: signature, // 必填，签名，见附录1
		jsApiList: [
			'checkJsApi',
			'onMenuShareTimeline',
			'onMenuShareAppMessage',
			'onMenuShareQQ',
			'onMenuShareWeibo',
			'onMenuShareQZone',
			//'hideMenuItems',
			//'showMenuItems',
			//'hideAllNonBaseMenuItem',
			//'showAllNonBaseMenuItem',
			//'translateVoice',
			//'startRecord',
			//'stopRecord',
			//'onRecordEnd',
			//'playVoice',
			//'pauseVoice',
			//'stopVoice',
			//'uploadVoice',
			//'downloadVoice',
			'chooseImage',
			'previewImage',
			'uploadImage',
			'downloadImage',
			'getNetworkType',
			'openLocation',
			'getLocation',
			//'hideOptionMenu',
			//'showOptionMenu',
			'closeWindow',
			'scanQRCode',
			'chooseWXPay',
			//'openProductSpecificView',
			'addCard',
			'chooseCard',
			'openCard'
		]
	});
	
	wx.ready(function () {
		// 1 判断当前版本是否支持指定 JS 接口，支持批量判断
		wechat.checkJsApi = function () {
			wx.checkJsApi({
				jsApiList: [
					'checkJsApi',
					'onMenuShareTimeline',
					'onMenuShareAppMessage',
					'onMenuShareQQ',
					'onMenuShareWeibo',
					'onMenuShareQZone',
					//'hideMenuItems',
					//'showMenuItems',
					//'hideAllNonBaseMenuItem',
					//'showAllNonBaseMenuItem',
					//'translateVoice',
					//'startRecord',
					//'stopRecord',
					//'onRecordEnd',
					//'playVoice',
					//'pauseVoice',
					//'stopVoice',
					//'uploadVoice',
					//'downloadVoice',
					'chooseImage',
					'previewImage',
					'uploadImage',
					'downloadImage',
					'getNetworkType',
					'openLocation',
					'getLocation',
					//'hideOptionMenu',
					//'showOptionMenu',
					'closeWindow',
					'scanQRCode',
					'chooseWXPay',
					//'openProductSpecificView',
					'addCard',
					'chooseCard',
					'openCard'
				],
				success: function (res) {
					alert("检测通过："	+JSON.stringify(res));
				},
				fail: function(res) {
					alert("检测失败："	+JSON.stringify(res));
				},
				complete: function(res) {
					alert("检测结束");
				}
			});
		};
	
		// 2. 分享接口
		// 2.1 监听“分享给朋友”，按钮点击、自定义分享内容及分享结果接口
		wechat.onMenuShareAppMessage = function (opt) {
			wx.onMenuShareAppMessage({
				title: opt.title,
				desc: opt.desc,
				link: opt.link,
				imgUrl: opt.imgUrl,
				trigger: function (res) {
					if(typeof(opt.trigger) == 'function')opt.trigger(res);
					// 用户确认分享后执行的回调函数
				},
				success: function (res) {
					if(typeof(opt.success) == 'function')opt.success(res);
					// 用户确认分享后执行的回调函数
				},
				cancel: function (res) {
					if(typeof(opt.cancel) == 'function')opt.cancel(res);
					// 用户取消分享后执行的回调函数
				},
				fail:function (res) {
					if(typeof(opt.fail) == 'function')opt.fail(res);
				}
			});
		};
	
		// 2.2 监听“分享到朋友圈”按钮点击、自定义分享内容及分享结果接口
		wechat.onMenuShareTimeline = function (opt) {
			wx.onMenuShareTimeline({
				title: opt.title,
				desc: opt.desc,
				link: opt.link,
				imgUrl: opt.imgUrl,
				trigger: function (res) {
					if(typeof(opt.trigger) == 'function')opt.trigger(res);
					// 用户确认分享后执行的回调函数
				},
				success: function (res) {
					if(typeof(opt.success) == 'function')opt.success(res);
					// 用户确认分享后执行的回调函数
				},
				cancel: function (res) {
					if(typeof(opt.cancel) == 'function')opt.cancel(res);
					// 用户取消分享后执行的回调函数
				},
				fail:function (res) {
					if(typeof(opt.fail) == 'function')opt.fail(res);
				}
			});
		};
	
		// 2.3 监听“分享到QQ”按钮点击、自定义分享内容及分享结果接口
		wechat.onMenuShareQQ = function (opt) {
			wx.onMenuShareQQ({
				title: opt.title,
				desc: opt.desc,
				link: opt.link,
				imgUrl: opt.imgUrl,
				trigger: function (res) {
					if(typeof(opt.trigger) == 'function')opt.trigger(res);
					// 用户确认分享后执行的回调函数
				},
				success: function (res) {
					if(typeof(opt.success) == 'function')opt.success(res);
					// 用户确认分享后执行的回调函数
				},
				cancel: function (res) {
					if(typeof(opt.cancel) == 'function')opt.cancel(res);
					// 用户取消分享后执行的回调函数
				},
				fail:function (res) {
					if(typeof(opt.fail) == 'function')opt.fail(res);
				}
			});
		};
				
		// 2.4 监听“分享到微博”按钮点击、自定义分享内容及分享结果接口
		wechat.onMenuShareWeibo = function (opt) {
			wx.onMenuShareWeibo({
				title: opt.title,
				desc: opt.desc,
				link: opt.link,
				imgUrl: opt.imgUrl,
				trigger: function (res) {
					if(typeof(opt.trigger) == 'function')opt.trigger(res);
					// 用户确认分享后执行的回调函数
				},
				success: function (res) {
					if(typeof(opt.success) == 'function')opt.success(res);
					// 用户确认分享后执行的回调函数
				},
				cancel: function (res) {
					if(typeof(opt.cancel) == 'function')opt.cancel(res);
					// 用户取消分享后执行的回调函数
				},
				fail:function (res) {
					if(typeof(opt.fail) == 'function')opt.fail(res);
				}
			});
		};

		// 2.5 监听“分享到QQ空间”按钮点击、自定义分享内容及分享结果接口
		wechat.onMenuShareQZone = function (opt) {
			if(wx.onMenuShareQZone){
				wx.onMenuShareQZone({
					title: opt.title,
					desc: opt.desc,
					link: opt.link,
					imgUrl: opt.imgUrl,
					trigger: function (res) {
						if(typeof(opt.trigger) == 'function')opt.trigger(res);
						// 用户确认分享后执行的回调函数
					},
					success: function (res) {
						if(typeof(opt.success) == 'function')opt.success(res);
						// 用户确认分享后执行的回调函数
					},
					cancel: function (res) {
						if(typeof(opt.cancel) == 'function')opt.cancel(res);
						// 用户取消分享后执行的回调函数
					},
					fail:function (res) {
						if(typeof(opt.fail) == 'function')opt.fail(res);
					}
				});
			}
		};

		// 3 智能接口
		/*var voice = {
			localId: '',
			serverId: ''
		};
		// 3.1 识别音频并返回识别结果
		wechat.translateVoice = function () {
		if (voice.localId == '') {
			console.log('请先使用 startRecord 接口录制一段声音');
			return;
		}
		wx.translateVoice({
			localId: voice.localId,
			complete: function (res) {
			if (res.hasOwnProperty('translateResult')) {
				console.log('识别结果：' + res.translateResult);
			} else {
				console.log('无法识别');
			}
			}
		});
		};
	
		// 4 音频接口
		// 4.2 开始录音
		wechat.startRecord = function () {
		wx.startRecord({
			cancel: function () {
			console.log('用户拒绝授权录音');
			}
		});
		};
	
		// 4.3 停止录音
		wechat.stopRecord = function () {
		wx.stopRecord({
			success: function (res) {
			voice.localId = res.localId;
			},
			fail: function (res) {
			console.log(JSON.stringify(res));
			}
		});
		};
	
		// 4.4 监听录音自动停止
		wx.onVoiceRecordEnd({
		complete: function (res) {
			voice.localId = res.localId;
			console.log('录音时间已超过一分钟');
		}
		});
	
		// 4.5 播放音频
		wechat.playVoice = function () {
		if (voice.localId == '') {
			console.log('请先使用 startRecord 接口录制一段声音');
			return;
		}
		wx.playVoice({
			localId: voice.localId
		});
		};
	
		// 4.6 暂停播放音频
		wechat.pauseVoice = function () {
		wx.pauseVoice({
			localId: voice.localId
		});
		};
	
		// 4.7 停止播放音频
		wechat.stopVoice = function () {
		wx.stopVoice({
			localId: voice.localId
		});
		};
	
		// 4.8 监听录音播放停止
		wx.onVoicePlayEnd({
		complete: function (res) {
			console.log('录音（' + res.localId + '）播放结束');
		}
		});
	
		// 4.8 上传语音
		wechat.uploadVoice = function () {
		if (voice.localId == '') {
			console.log('请先使用 startRecord 接口录制一段声音');
			return;
		}
		wx.uploadVoice({
			localId: voice.localId,
			success: function (res) {
			console.log('上传语音成功，serverId 为' + res.serverId);
			voice.serverId = res.serverId;
			console.log("上传语音信息：" + JSON.stringify(res));
			}
		});
		};
	
		// 4.9 下载语音
		wechat.downloadVoice = function () {
		if (voice.serverId == '') {
			console.log('请先使用 uploadVoice 上传声音');
			return;
		}
		wx.downloadVoice({
			serverId: voice.serverId,
			success: function (res) {
			console.log('下载语音成功，localId 为' + res.localId);
			voice.localId = res.localId;
			console.log("下载语音信息：" + JSON.stringify(res));
			}
		});
		};*/
	
		// 5 图片接口
		// 5.1 拍照、本地选图
		//wechat.images = {
		//	localId: [],
		//	serverId: []
		//};
		//wechat.chooseImage = function (count) {
		//	wx.chooseImage({
		//		count : count||9,
		//		success: function (res) {
		//			wechat.images.localId = res.localIds;
		//			console.log('已选择 ' + res.localIds.length + ' 张图片');
		//			wechat.uploadImage();
		//		}
		//	});
		//};
        //
		//// 5.2 图片预览
		//wechat.previewImage = function (current, urls) {
		//	wx.previewImage({
		//		current: current,
		//		urls: urls||[]
		//	});
		//};
        //
		//// 5.3 上传图片
		//wechat.uploadImage = function () {
		//	if (wechat.images.localId.length == 0) {
		//		console.log('请先使用 chooseImage 接口选择图片');
		//		return;
		//	}
		//	var i = 0, length = wechat.images.localId.length;
		//	wechat.images.serverId = [];
		//	(function upload() {
		//		wx.uploadImage({
		//			localId: wechat.images.localId[i],
		//			success: function (res) {
		//				i++;
		//				console.log('已上传：' + i + '/' + length);
		//				console.log("上传图片信息：" + JSON.stringify(res));
		//				wechat.images.serverId.push(res.serverId);
		//				if (i < length) {
		//					upload();
		//				}
		//			},
		//			fail: function (res) {
		//				console.log(JSON.stringify(res));
		//			}
		//		});
		//	})();
		//};
        //
		//// 5.4 下载图片
		//wechat.downloadImage = function () {
		//	if (wechat.images.serverId.length === 0) {
		//		console.log('请先使用 uploadImage 上传图片');
		//		return;
		//	}
		//	var i = 0, length = wechat.images.serverId.length;
		//	wechat.images.localId = [];
		//	function download() {
		//		wx.downloadImage({
		//		serverId: wechat.images.serverId[i],
		//		success: function (res) {
		//			i++;
		//			console.log('已下载：' + i + '/' + length);
		//			console.log("下载图片信息：" + JSON.stringify(res));
		//			wechat.images.localId.push(res.localId);
		//			if (i < length) {
		//				download();
		//			}
		//		}
		//		});
		//	}
		//	download();
		//};
        //
		//// 6 设备信息接口
		//// 6.1 获取当前网络状态
		//wechat.getNetworkType = function () {
		//	wx.getNetworkType({
		//		success: function (res) {
		//			console.log(res.networkType);
		//		},
		//		fail: function (res) {
		//			console.log(JSON.stringify(res));
		//		}
		//	});
		//};
	
		// 7 地理位置接口
		// 7.1 查看地理位置
		/*{
				latitude: 23.099994,
				longitude: 113.324520,
				name: 'TIT 创意园',
				address: '广州市海珠区新港中路 397 号',
				scale: 14,
				infoUrl: 'http://weixin.qq.com'
			}*/
		wechat.openLocation = function (opt) {
			wx.openLocation(opt);
		};
	
		// 7.2 获取当前地理位置
		wechat.getLocation = function (fn) {
			wx.getLocation({
				success: function (res) {
					if(typeof(fn) == 'function'){
						myWxF = true;
						fn(res);
					}
				},
				cancel: function () {
					if(typeof(fn) == 'function'){
						myWxF = true;
						fn();
					}
				}
			});
			/*****调试代码（待删）****/
			window.setTimeout(function(){
				if(!myWxF){
					fn();
				}
			}, 2000);
			/*********************/
		};
		
		// 8 界面操作接口
		// 8.1 隐藏右上角菜单
		/*wechat.hideOptionMenu = function () {
			wx.hideOptionMenu();
		};
	
		// 8.2 显示右上角菜单
		wechat.showOptionMenu = function () {
			wx.showOptionMenu();
		};
	
		// 8.3 批量隐藏菜单项
		wechat.hideMenuItems = function () {
			wx.hideMenuItems({
				menuList: [
					'menuItem:readMode', // 阅读模式
					'menuItem:share:timeline', // 分享到朋友圈
					'menuItem:copyUrl' // 复制链接
				],
				success: function (res) {
					console.log('已隐藏“阅读模式”，“分享到朋友圈”，“复制链接”等按钮');
				},
				fail: function (res) {
					console.log(JSON.stringify(res));
				}
			});
		};
	
		// 8.4 批量显示菜单项
		wechat.showMenuItems = function () {
			wx.showMenuItems({
				menuList: [
					'menuItem:readMode', // 阅读模式
					'menuItem:share:timeline', // 分享到朋友圈
					'menuItem:copyUrl' // 复制链接
				],
				success: function (res) {
					console.log('已显示“阅读模式”，“分享到朋友圈”，“复制链接”等按钮');
				},
				fail: function (res) {
					console.log(JSON.stringify(res));
				}
			});
		};
	
		// 8.5 隐藏所有非基本菜单项
		wechat.hideAllNonBaseMenuItem = function () {
			wx.hideAllNonBaseMenuItem({
				success: function () {
					console.log('已隐藏所有非基本菜单项');
				}
			});
		};
	
		// 8.6 显示所有被隐藏的非基本菜单项
		wechat.showAllNonBaseMenuItem = function () {
			wx.showAllNonBaseMenuItem({
				success: function () {
				console.log('已显示所有非基本菜单项');
				}
			});
		};*/
	
		// 8.7 关闭当前窗口
		wechat.closeWindow = function () {
			wx.closeWindow();
		};
	
		// 9 微信原生接口
		// 9.1.1 扫描二维码并返回结果
		/*wechat.scanQRCode0 = function () {
			wx.scanQRCode({
				desc: 'scanQRCode desc'
			});
		};*/
		// 9.1.2 扫描二维码并返回结果
		wechat.scanQRCode = function (fn, desc) {
			wx.scanQRCode({
				needResult: 1,
				desc: desc||'我有饭扫描',
				success: function (res) {
					if(typeof(fn) == 'function')fn(res);
				}
			});
		};
	
		// 10 微信支付接口
		// 10.1 发起一个支付请求
		/* {
				timestamp: 1414723227,
				nonceStr: 'noncestr',
				package: 'addition=action_id%3dgaby1234%26limit_pay%3d&bank_type=WX&body=innertest&fee_type=1&input_charset=GBK&notify_url=http%3A%2F%2F120.204.206.246%2Fcgi-bin%2Fmmsupport-bin%2Fnotifypay&out_trade_no=1414723227818375338&partner=1900000109&spbill_create_ip=127.0.0.1&total_fee=1&sign=432B647FE95C7BF73BCD177CEECBEF8D',
				paySign: 'bd5b1933cda6e9548862944836a9b52e8c9a2b69'
			} */
		wechat.chooseWXPay = function (opt) {
			wx.chooseWXPay(opt);
		};
	
		// 11.3	跳转微信商品页
		/*wechat.openProductSpecificView = function () {
			wx.openProductSpecificView({
				productId: 'pDF3iY0ptap-mIIPYnsM5n8VtCR0'
			});
		};*/
	
		// 12 微信卡券接口
		// 12.1 批量添加卡券
		/* {
						cardId: 'pDF3iY9tv9zCGCj4jTXFOo1DxHdo',
						cardExt: '{"code": "", "openid": "", "timestamp": "1418301401", "signature":"64e6a7cc85c6e84b726f2d1cbef1b36e9b0f9750"}'
					},
					{
						cardId: 'pDF3iY9tv9zCGCj4jTXFOo1DxHdo',
						cardExt: '{"code": "", "openid": "", "timestamp": "1418301401", "signature":"64e6a7cc85c6e84b726f2d1cbef1b36e9b0f9750"}'
					} */
		wechat.addCard = function (cardList, fn) {
			wx.addCard({
				cardList: cardList||[],
				success: function (res) {
					if(typeof(fn) == 'function')fn(res);
				}
			});
		};
	
		// 12.2 选择卡券
		wechat.chooseCard = function (cardSign, timestamp, nonceStr, fn) {
			wx.chooseCard({
				cardSign: cardSign,
				timestamp: timestamp,
				nonceStr: nonceStr,
				success: function (res) {
					if(typeof(fn) == 'function')fn(res);
				}
			});
		};
	
		// 12.3 查看卡券
		wechat.openCard = function (cardList) {
			wx.openCard({
				cardList: cardList
			});
		};
		
		if(typeof(callback) == 'function'){
			callback();
		}
	});
	
	wx.error(function (res) {
		my_err.push(res.errMsg);
		$.alert(my_err.join("\n"), 'error');
	});
};
