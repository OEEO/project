var storage = {
    get : function(key){
        var data = false;
        if(key.indexOf('.') > 0){
            var arr = key.split('.');
            if(this.item(arr[0])){
                data = this.item(arr[0]);
                for(var i in arr){
                    if(i == 0)continue;
                    if(data[arr[i]] !== undefined){
                        data = data[arr[i]];
                    }else return false;
                }
            }else{
                return false;
            }
        }else if(this.item(key))data = this.item(key);
        return data;
    },
    set : function(key, value){
        if(value === undefined)return false;
        var data = [];
        var datas = null;
        var _dt = null;
        if(key.indexOf('.') > 0){
            var arr = key.split('.');
            if(this.item(arr[0])){
                datas = this.item(arr[0]);
                data = datas;
                for(var i in arr){
                    if(i == 0)continue;
                    if(data[arr[i]] !== undefined){
                        _dt = data;
                        data = data[arr[i]];
                    }else{
                        if(i == arr.length - 1){
                            data[arr[i]] = '';
                            _dt = data;
                            data = data[arr[i]];
                        }else return false
                    }
                }
            }else{
                return false;
            }
        }else if(this.item(key))data = this.item(key);
        data = value;
        if(datas === null){
            this.item(key, data);
        }else{
            _dt[arr[arr.length - 1]] = data;
            this.item(arr[0], datas);
        }
        return true;
    },
    //入栈
    inset : function(key, value){
        var data = [];
        var datas = null;
        var _dt = null;
        if(key.indexOf('.') > 0){
            var arr = key.split('.');
            if(this.item(arr[0])){
                datas = this.item(arr[0]);
                data = datas;
                for(var i in arr){
                    if(i == 0)continue;
                    if(data[arr[i]] !== undefined){
                        if(i == arr.length - 1)_dt = data;
                        data = data[arr[i]];
                    }else return false;
                }
            }else{
                return false;
            }
        }else if(this.item(key))data = this.item(key);
        if(typeof(data) != 'object')return false;
        data.push(value);
        if(datas === null){
            this.item(key, data);
        }else{
            _dt[arr[arr.length - 1]] = data;
            this.item(arr[0], datas);
            data = datas;
        }
        return data;
    },
    //出栈
    outset : function(key, value){
        var data = [];
        var datas = null;
        var _dt = null;
        if(key.indexOf('.') > 0){
            var arr = key.split('.');
            if(this.item(arr[0])){
                datas = this.item(arr[0]);
                data = datas;
                for(var i in arr){
                    if(i == 0)continue;
                    if(data[arr[i]] !== undefined){
                        if(i == arr.length - 1)_dt = data;
                        data = data[arr[i]];
                    }else return false;
                }
            }else{
                return false;
            }
        }else if(this.item(key))data = this.item(key);
        if(typeof(data) != 'object')return false;
        var _data = [];
        for(var i in data){
            if(data[i] !== value)_data.push(data[i]);
        }
        data = _data;
        if(datas === null){
            this.item(key, data);
        }else{
            _dt[arr[arr.length - 1]] = data;
            this.item(arr[0], datas);
            data = datas;
        }
        return data;
    },
    //尾部弹出
    pop : function(key, way){
        var way = way||1;
        var data = [];
        var datas = null;
        var _dt = null;
        if(key.indexOf('.') > 0){
            var arr = key.split('.');
            if(this.item(arr[0])){
                datas = this.item(arr[0]);
                data = datas;
                for(var i in arr){
                    if(i == 0)continue;
                    if(data[arr[i]] !== undefined){
                        if(i == arr.length - 1)_dt = data;
                        data = data[arr[i]];
                    }else return false;
                }
            }else{
                return false;
            }
        }else if(this.item(key))data = this.item(key);
        if(way == 1)
            var rs = data.pop();
        else
            var rs = data.shift();
        if(datas === null){
            this.item(key, data);
        }else{
            _dt[arr[arr.length - 1]] = data;
            this.item(arr[0], datas);
        }
        return rs;
    },
    //头部弹出
    shift: function(key){
        return this.pop(key, -1);
    },
    //增值
    incr : function(key, value){
        if(typeof(value) != 'number')value = 1;
        var data = [];
        var datas = null;
        var _dt = null;
        if(key.indexOf('.') > 0){
            var arr = key.split('.');
            if(this.item(arr[0])){
                datas = this.item(arr[0]);
                data = datas;
                for(var i in arr){
                    if(i == 0)continue;
                    if(data[arr[i]] !== undefined){
                        if(i == arr.length - 1)_dt = data;
                        data = data[arr[i]];
                    }else return false;
                }
            }else{
                return false;
            }
        }else if(this.item(key))data = this.item(key);
        if(typeof(data) == 'number'){
            data += value;
        }else{
            return false;
        }
        if(datas === null){
            this.item(key, data);
        }else{
            _dt[arr[arr.length - 1]] = data;
            this.item(arr[0], datas);
        }
        return data;
    },
    //减值
    decr : function(key, value){
        if(typeof(value) != 'number')value = 1;
        var data = [];
        var datas = null;
        var _dt = null;
        if(key.indexOf('.') > 0){
            var arr = key.split('.');
            if(this.item(arr[0])){
                datas = this.item(arr[0]);
                data = datas;
                for(var i in arr){
                    if(i == 0)continue;
                    if(data[arr[i]] !== undefined){
                        if(i == arr.length - 1)_dt = data;
                        data = data[arr[i]];
                    }else return false;
                }
            }else{
                return false;
            }
        }else if(this.item(key))data = this.item(key);
        if(typeof(data) == 'number'){
            data -= value;
        }else{
            return false;
        }
        if(datas === null){
            this.item(key, data);
        }else{
            _dt[arr[arr.length - 1]] = data;
            this.item(arr[0], datas);
        }
        return data;
    },
    //删除
    rm : function(key){
        if(key.indexOf('.') > 0){
            var data = [];
            var datas = null;
            var arr = key.split('.');
            if(this.item(arr[0])){
                datas = this.item(arr[0]);
                data = datas;
                for(var i in arr){
                    if(i == 0)continue;
                    if(data[arr[i]] !== undefined){
                        if(i == arr.length - 1){
                            delete data[arr[i]];
                        }else data = data[arr[i]];
                    }else return false;
                }
                this.item(arr[0], datas);
                return datas;
            }else{
                return false;
            }
        }else{
            this.item(key, null);
            return true;
        }
    },
    //遍历
    each : function(key, fn){
        if(typeof(fn) != 'function')return false;
        var data = [];
        var datas = null;
        var _dt = null;
        if(key.indexOf('.') > 0){
            var arr = key.split('.');
            if(this.item(arr[0])){
                datas = this.item(arr[0]);
                data = datas;
                for(var i in arr){
                    if(i == 0)continue;
                    if(data[arr[i]] !== undefined){
                        _dt = data;
                        data = data[arr[i]];
                    }else return false
                }
            }else{
                return false;
            }
        }else if(this.item(key))data = this.item(key);

        if(typeof(data) != 'object')return false;
        for(var i in data){
            var rs = fn(data[i], i);
            if(rs !== undefined){
                data[i] = rs;
            }
        }

        if(datas === null){
            this.item(key, data);
        }else{
            _dt[arr[arr.length - 1]] = data;
            this.item(arr[0], datas);
        }
        return true;
    },
    //核心读写
    item: function(key, value){
        if(window.localStorage){
            if(value === undefined){
                return this.decode(localStorage.getItem(key))||false;
            }else if(value === null)return localStorage.removeItem(key);
            else return localStorage.setItem(key, this.encode(value));
        }else{
            if(value === undefined) {
                var arr,reg=new RegExp("(^| )"+name+"=([^;]*)(;|$)");
                if(arr = document.cookie.match(reg))
                    return this.decode(arr[2]);
                else
                    return false;
            } else if(value === null) {
                var exp = new Date();
                exp.setTime(exp.getTime() - 1);
                document.cookie = name + "="+ this.encode(value) + ";expires=" + exp.toGMTString();
                return true;
            } else {
                var Days = 30;
                var exp = new Date();
                exp.setTime(exp.getTime() + Days*24*60*60*1000);
                document.cookie = name + "="+ this.encode(value) + ";expires=" + exp.toGMTString();
                return true;
            }
        }
    },
    encode: function(obj){
        var str = '';
        try {
            str = JSON.stringify(obj);
        }catch(e){
            str = decodeURI(obj);
        }
        return str;
    },
    decode: function(str){
        var obj = '';
        try {
            obj = JSON.parse(str);
        }catch(e){
            obj = encodeURI(str);
        }
        return obj;
    }
};