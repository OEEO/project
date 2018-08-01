var app = getApp()
var that;
Page({
    data:{
        signtext:null,
        member:{},
        nick:'',
        sex:['未选择','男','女'],
        sexindex:0,
        pindex:0,
        province:[],
        cindex:0,
        city:['选择城市'],
        city_id:'',
        ids:[],
    },
    onShow:function(){
        console.log(app.member);
        if(app.member.sex !=null && app.member.sex !=''){
            this.setData({
                sexindex:app.member.sex
            })
        }
        if(app.member.province_id !=null && app.member.province_id !=''){
                this.setData({
                    pindex:app.member.province_id
                })
            }
    },
    onReady:function(){
        that = this;
        that.setData({
          member:app.member
        })
        wx.setNavigationBarTitle({
            title: '编辑个人资料'
        });
        that.loadprovince();
        if(that.data.pindex !=0){
            that.loadcity();
        }
    },
    tishi:function(){
        app.alert('该功能正在开发中');
    },
    loadprovince:function(){
        app.ajax('Home/Index/getCityList', function(d){
            var p = ['选择省份'];
            for(var i in d){
                p.push(d[i].name);
		    }
            that.setData({
                province: p
            })
            
        },false)
    },
    loadcity:function(){
        app.ajax('Home/Index/getCityList',{pid:that.data.pindex},function(d){
            var c = ['选择城市'];
            var idd = [];
			for(var i in d){
                if(d[i].id == that.data.member.city_id){
                    var n = parseInt(i)+1;
                    that.setData({
                        cindex: n
                     })
                }
                c.push(d[i].name);
                idd.push(d[i].id);
		    }
            that.setData({
                city: c,
                ids : idd
            })
        })
        
    },
    provinceChange:function(e){
        that.setData({
            pindex: e.detail.value,
            cindex:0
        })
        app.ajax('Home/Index/getCityList',{pid:e.detail.value},function(d){
            var c = ['选择城市'];
            var idd = [];
            for(var i in d){
                c.push(d[i].name);
                idd.push(d[i].id);
		    }
            that.setData({
                city: c,
                ids : idd
            })
        })
    },
    cityChange:function(e){
        var num = parseInt(e.detail.value - 1);
        that.setData({
            cindex: e.detail.value,
            city_id:that.data.ids[num]
        })
    },
    sexChange:function(e){
        that.setData({
            sexindex: e.detail.value
        })
    },
    nickname:function(e){
        that.setData({
            nick: e.detail.value
        })
    },
    submit:function(){
        var datas = {};
        if(that.data.city_id == ''){datas.city_id = that.data.member.city_id;}
        else{datas.city_id = that.data.city_id;}
        if(that.data.nick == ''){datas.nickname = that.data.member.nickname;}
        else{datas.nickname = that.data.nick;}
        datas.sex = that.data.sexindex;
        app.ajax('Member/Index/modifyInfo', datas, function(d){
            if(d.status == 1){
                app.member = d.info;
                app.alert('提交成功',function(){
                    wx.switchTab({
                        url: '../ucenter/index'
                    })
                });
				
			}else{
				app.alert(d.info, 'error');
			}
        },2)
    }
})