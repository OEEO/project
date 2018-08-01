module.exports = {
    get : function(key){
        try {
            var value = wx.getStorageSync(key);
            if (value) {
                return value;
            }else{
                return false;
            }
        } catch (e) {
            return false;
        }
    },
    set : function(key, value){
        try {
            wx.setStorageSync(key, value);
            return true;
        } catch (e) {
            return false; 
        }
    },
    rm : function(key){
        try {
            wx.removeStorageSync(key);
            return true;
        } catch (e) {
            return false;
        }
    }
};