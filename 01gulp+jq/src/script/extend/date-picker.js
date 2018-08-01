define(['jquery', 'utils'], function ($, utils) {
    function DatePicker(option) {
        var defaultOption = {
            target: '',
            curDate: new Date(),
            activeDate: [],
            onchange: function () {},
            onchangeMonth: function () {}
        };

        this.options = $.extend(defaultOption, option);
        this.active = false;
        this.dataDate = this.indexDate = this.options.curDate;
        this.onchange = this.options.onchange;
        this.onchangeMonth = this.options.onchangeMonth;
         
        this.target = document.getElementById(this.options.target.split('#')[1]);

        if (!this.target || this.target.length === 0) {
            console.error('nofind target');
            return false;
        }

        this.init();
    }

    function getDays(curDate, indexDate) {
        var days = [];

        var curDateStr = utils.timeFormat(indexDate, 'yyyyMMdd');

        var dateReg = curDateStr.match(/(\d{4})(\d{2})(\d{2})/);

        var curDay = +dateReg[3];
        var curMon = +dateReg[2];
        var curYear = +dateReg[1];

        // 查询本月第一天时间
        var firstDate = new Date(indexDate.getTime() - 86400000 * (curDay - 1));
        // 本月第一天的星期几
        var firstDay = +utils.timeFormat(firstDate, 'D');

        // 下个月
        var nextMon = curMon + 1;
        var nextYear = curYear;
        nextMon = nextMon > 12
            ? (nextYear += 1, 1) // 如果nextMon大于12了，代表进入下一年
            : nextMon;
        var nextDate = utils.parseISO8601(nextYear + '-' + utils.format(nextMon, 2) + '-01');

        // 下个月和本月相距多少天
        var spDate = (nextDate.getTime() - firstDate.getTime()) / 86400000;

        for (var i = 0; i < firstDay; i++) {
            days.push({
                label: ' ',
                time: 0
            });
        }

        for (var j = 0; j < spDate; j++) {
            days.push({
                label: j + 1,
                time: curYear + utils.format(curMon, 2) + utils.format(j + 1, 2) //`${curYear}${format(curMon, 2)}${format(i + 1, 2)}`
            });
        }

        return days;
    }

    function appendDays(curDate, indexDate, activeDate) {
        var days = getDays(curDate, indexDate);
        var fragment = document.createDocumentFragment();

        var curDateFot = utils.timeFormat(curDate, 'yyyyMMdd');

        for (var i = 0, num = days.length; i < num; i++) {
            var item = days[i];
            var dateItem = document.createElement('span');
            dateItem.className = 'date-pick-date_item';
            dateItem.innerText = item.label;
            dateItem.setAttribute('data-pick-date', item.time);

            if (item.time === curDateFot) {
                dateItem.className += ' active';
            }

            if ($.inArray(+item.label, activeDate) !== -1) {
                dateItem.className += ' able';
            }

            fragment.appendChild(dateItem);
        }

        return fragment;
    }

    DatePicker.prototype.init = function () {
        var fragment = document.createDocumentFragment();

        var selector = document.createElement('div');
        selector.setAttribute('id', 'date-selector');
        selector.innerHTML = '<span>查看日期</span><i class="caret"></i>';
        fragment.appendChild(selector);

        var datePickerContainer = document.createElement('div');
        datePickerContainer.className = 'date-picker-container';

        // date header
        var dateHeader = document.createElement('div');
        dateHeader.className = 'date-picker-container_header';
        var dateLeftPick = document.createElement('span');
        dateLeftPick.className = 'left-caret';
        dateLeftPick.setAttribute('data-month-pick', 'left');
        var dateRightPick = document.createElement('div');
        dateRightPick.className = 'right-caret';
        dateRightPick.setAttribute('data-month-pick', 'right');
        var curDate = document.createElement('span');
        curDate.setAttribute('data-date-current', '1');
        curDate.innerText = utils.timeFormat(this.options.curDate, 'yyyy年MM月');
        dateHeader.appendChild(dateLeftPick);
        dateHeader.appendChild(curDate);
        dateHeader.appendChild(dateRightPick);
        datePickerContainer.appendChild(dateHeader);

        // date body
        var dateBody = document.createElement('div');
        dateBody.className = 'date-picker-container_body';
        var datePickDay = document.createElement('div');
        datePickDay.className = 'date-pick-day';
        datePickDay.innerHTML = '<span>日</span><span>一</span><span>二</span><span>三</span><span>四</span><span>五</span><span>六</span>';
        dateBody.appendChild(datePickDay);

        var datePickDate = document.createElement('div');
        datePickDate.className = 'date-pick-day date-pick-date';
        datePickDate.setAttribute('id', 'date-pick-date');

        var days = appendDays(this.options.curDate, this.indexDate, this.options.activeDate);
        datePickDate.appendChild(days);
        dateBody.appendChild(datePickDate);


        datePickerContainer.appendChild(dateBody);
        fragment.appendChild(datePickerContainer);

        this.target.appendChild(fragment);

        this.initEvent();
    };

    DatePicker.prototype.initEvent = function () {
        var self = this;

        var $target = $(this.options.target);

        $target.on('click', '#date-selector', function (e) {
            e.stopPropagation();
            self.active = !self.active;
            var $this = $(this);

            if (self.active) {
                $this.parent().addClass('active');
            } else {
                $this.parent().removeClass('active');
            }

            var tag = $this.siblings('.date-picker-container');

            $(document).on('click', function (e) {
                e.stopPropagation();
                if ($(e.target).closest(tag).length === 0 && self.active) {
                    self.active = false;
                    $this.parent().removeClass('active');
                }
            });
            
        });

        $target.on('click', '[data-month-pick]', function () {
            var flag = $(this).data('month-pick');
            var dateReg = utils.timeFormat(self.dataDate, 'yyyyMMdd').match(/(\d{4})(\d{2})(\d{2})/);
            var y = +dateReg[1];
            var m = +dateReg[2];
            var d = +dateReg[3];

            if (flag === 'left') {
                // minus
                m -= 1;
                m = m < 1
                    ? (y -= 1, 12)
                    : m;
            } else {
                m += 1;
                m = m > 12
                    ? (y += 1, 1)
                    : m;
            }

            self.indexDate = self.dataDate = utils.parseISO8601(y + '-' + m + '-01');

            $('[data-date-current]').text(utils.timeFormat(self.dataDate, 'yyyy年MM月'));

            var days = appendDays(self.options.curDate, self.indexDate, []);
            $('#date-pick-date').empty();
            $('#date-pick-date')[0].appendChild(days);

            self.onchangeMonth(utils.timeFormat(self.dataDate, 'yyyyMM'));
        });

        $target.on('click', '.date-pick-date_item', function () {

            var date = $(this).data('pick-date') + '';

            if (!self.dataDate || date !== utils.timeFormat(self.dataDate, 'yyyyMMdd')) {
                var dateReg = date.match(/(\d{4})(\d{2})(\d{2})/);
                var y = dateReg[1];
                var m = dateReg[2];
                var d = dateReg[3];

                self.options.curDate = self.dataDate = utils.parseISO8601(y + '-' + m + '-' + d);//new Date(`${y}-${m}-${d} 00:00:00`);
                self.active = false;

                $(this).siblings('span.active').removeClass('active');
                $(this).addClass('active');
                
                self.onchange(self.dataDate);

                self.active = false;
                $target.removeClass('active');
            }
        });
    };

    DatePicker.prototype.changeActiveDate = function (newArr) {
        if (Object.prototype.toString.call(newArr) !== '[object Array]') {
            console.error('activeDate should be an Array');
            return false;
        }
        
        this.options.activeDate = newArr;

        $('#date-pick-date').children('.date-pick-date_item').each(function () {
            if ($.inArray(+$(this).text(), newArr) !== -1) {
                $(this).addClass('able');
            }
        });
    };

    return DatePicker;
});