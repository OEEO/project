Page({
    data: {
        url: ''
    },

    onLoad: function (option) {
        var url = option.url;
        url = decodeURIComponent(url);
        this.setData({
            url: url
        })
    }
})