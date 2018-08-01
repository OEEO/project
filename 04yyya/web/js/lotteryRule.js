/**
 * Created by toreant on 2017/8/2.
 */
var lotteryRuleObject = {
    onshow: function() {
        var noHeader = win.get.noHeader;
        if (noHeader == 1) {
            $('.page_lotteryRule.header').hide();
            $('.page_lotteryRule .headerBlank').hide();
        }
    },
    onload: function() {

    }
};