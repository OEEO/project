/**
 * Created by toreant on 2017/8/2.
 */
var refundObject = {
    onshow: function() {
        var noHeader = win.get.noHeader;
        if (noHeader == 1) {
            $('.page_refund.header').hide();
            $('.page_refund .headerBlank').hide();
        }
    },
    onload: function() {

    }
};