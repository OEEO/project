/**
 * Created by toreant on 2017/8/4.
 */
var lotteryResultObject = {
    buildResult: function (d) {
        var lucky_code = '';

        for (var i = 0, num1 = d.lucky_num.length; i < num1; i++) {
            lucky_code += '<span>' + d.lucky_num[i] + '</span>';
        }

        if (!lucky_code) {
            lucky_code += '<span>未开奖</span>';
        }

        $('.page_lotteryResult .rule_title').text(d.rule_title);
        $('.page_lotteryResult .lucky_result_code').html(lucky_code);
        $('.page_lotteryResult .raise_nickname span').text(d.nickname);
        $('.page_lotteryResult .raise_left img').attr('src', d.path);
        $('.page_lotteryResult .order_title').text(d.raise_title);
        $('.page_lotteryResult .subot').text(d.times_title);
        $('.page_lotteryResult .total font').text(d.price);
        $('.page_lotteryResult .status').text(d.status_text);
        $('.page_lotteryResult .lottery_desc .title').text(d.times_title);
        $('.page_lotteryResult .lottery_desc .desc').html(d.desc);

        var info = '';
        for (var j = 0, num2 = d.info.length; j < num2; j++) {
            info += '<p class="sub">' + d.info[j] + '</p>';
        }
        $('.page_lotteryResult .lottery_info').append(info);
    },
    onshow: function () {
        var noHeader = win.get.noHeader;
        if (noHeader == 1) {
            $('.page_lotteryResult.header').hide();
            $('.page_lotteryResult .headerBlank').hide();
        }
    },
    onload: function () {
        ajax('Goods/Raise/getLotteryResult', {'raise_id': win.get.raise_id, 'times_id': win.get.times_id}, function (d) {
            if (d.status == "0") {
                location.href = '/?page=choice-lotteryRule';
            } else {
                lotteryResultObject.buildResult(d);
            }
        });

        $('.page_lotteryResult .lottery_footer a').click(function () {
            jump('lotteryRule');
        });
    }
};