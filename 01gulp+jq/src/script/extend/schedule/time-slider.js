define(['jquery', 'utils'], function ($, utils) {
    var TimeSlider = function (options) {
        var defaultOptions = {
            target: '',
            onWeekChange: function () { },
            onchange: function () { },
            curDate: new Date()
        };

        this.options = $.extend(defaultOptions, options);
        console.log(this.options);
        var curDateFot = utils.timeFormat(new Date(), 'MM-dd');
        var indexDateFot = utils.timeFormat(this.options.curDate, 'MM-dd');
        var $this = $(this.options.target);

        this.$target = $this;
        this.indexDateFot = indexDateFot;
        this.curDateFot = curDateFot;
        this.dates = [];

        this.init();
        this.initEvent();
    };

    TimeSlider.prototype.init = function () {
        var fragment = document.createDocumentFragment();

        var leftCaret = document.createElement('div');
        leftCaret.className = 'prev-caret';
        leftCaret.setAttribute('data-time-side', 'prev');
        var rightCaret = document.createElement('div');
        rightCaret.className = 'next-caret';
        rightCaret.setAttribute('data-time-side', 'next');

        var timeSliderList = document.createElement('ul');
        timeSliderList.className = 'time-slider-list';
        var timeItemList = this.appendItemList(this.options.curDate);
        timeSliderList.appendChild(timeItemList);

        fragment.appendChild(leftCaret);
        fragment.appendChild(timeSliderList);
        fragment.appendChild(rightCaret);

        this.$target[0].appendChild(fragment);
    };

    TimeSlider.prototype.initEvent = function () {

        var self = this;

        this.$target.on('click', '.time-slider-item', function () {
            var time = $(this).data('date-time');
            self.options.curDate = new Date(time);
            self.options.onchange(new Date(time));

            $(this).siblings('li.active').removeClass('active');
            $(this).addClass('active');
        });

        this.$target.on('click', '[data-time-side]', function () {
            var side = $(this).data('time-side');
            var date;

            if (side === 'prev') {
                date = self.options.curDate.getTime() - 86400000 * 7;
            } else {
                date = self.options.curDate.getTime() + 86400000 * 7;
            }

            date = self.options.curDate = new Date(date);
            self.indexDateFot = utils.timeFormat(date, 'MM-dd');

            var dateItemList = self.appendItemList(date);

            self.$target.find('.time-slider-list').empty();
            self.$target.find('.time-slider-list')[0].appendChild(dateItemList);

            self.options.onWeekChange(self.options.curDate);
        });
    };

    TimeSlider.prototype.getDates = function (curDate) {
        var dateReg = utils.timeFormat(this.options.curDate, 'yyyyMMddD').match(/(\d{4})(\d{2})(\d{2})(\d)/);
        var day = +dateReg[4] + 1;
        var m = dateReg[2];
        var y = dateReg[1];
        var d = dateReg[3];

        var options = this.options;

        var dates = [];

        for (var i = day - 1; i > 0; i--) {
            var date = new Date(options.curDate.getTime() - 86400000 * i);
            dates.push({
                date: utils.timeFormat(date, 'MM-dd'),
                time: date.getTime()
            });
        }

        dates.push({
            date: m + '-' + d,
            time: this.options.curDate.getTime()
        });

        for (var j = 1, num = 8 - day; j < num; j++) {
            (function (k) {
                var date = new Date(options.curDate.getTime() + 86400000 * k);
                dates.push({
                    date: utils.timeFormat(date, 'MM-dd'),
                    time: date.getTime()
                });
            })(j);
        }

        return dates;
    };

    TimeSlider.prototype.appendItemList = function (curDate) {
        var fragment = document.createDocumentFragment();
        this.dates = this.getDates(curDate);

        for (var i = 0, num = this.dates.length; i < num; i++) {
            var item = document.createElement('li');
            item.className = 'time-slider-item';
            item.innerText = this.dates[i].date;
            item.setAttribute('data-date-time', this.dates[i].time);

            if (this.dates[i].date === this.curDateFot && this.dates[i].date !== this.indexDateFot) {
                item.className += ' current';
            } else if (this.dates[i].date === this.indexDateFot) {
                item.className += ' active';
            }

            fragment.appendChild(item);
        }

        return fragment;
    };

    TimeSlider.prototype.changeDate = function (date) {
        date = this.options.curDate = new Date(date);
        this.indexDateFot = utils.timeFormat(date, 'MM-dd');

        var dateItemList = this.appendItemList(date);

        this.$target.find('.time-slider-list').empty();
        this.$target.find('.time-slider-list')[0].appendChild(dateItemList);
    };

    return TimeSlider;
});
