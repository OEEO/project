/**
 * 这是入口文件
 */
require(['../common'], function (common) {
    require(['jquery', 'utils', 'url', 'datePicker', 'collapse', 'extend/schedule/time-slider'], function ($, utils, URL, DatePicker, Collapse, TimeSlider) {
        $(document).ready(function () {

            var $scheduleList = $('#schedule-list');

            var liveStatus = ['未开始', '进行中', '已结束'];

            var activeLeagueIndex = 0;

            var gameName = {
                '2': 'lol',
                '6': 'kog'
            };

            var gameId = GAME_TYPE;
            var game = gameName[gameId];
            var curDate = new Date();


            var datePicker;

            getScheduleDate(game, activeLeagueIndex, utils.timeFormat(curDate, 'yyyyMM'), function (err, dates) {
                var activeDate = [];

                if (err) {
                    console.error(err);
                } else {
                    activeDate = dates;
                }

                datePicker = new DatePicker({
                    target: '#date-picker',
                    activeDate: activeDate,
                    curDate: curDate,
                    onchangeMonth: function (date) {
                        var url = URL.GET_ACTIVE_DATE;
                        url = url.replace('{game}', game);
                        url = url.replace('{e_index}', activeLeagueIndex);
                        url = url.replace('{/type_id}', '');
                        url = url.replace('{date}', date);

                        utils.getScript(url, 'date', function (err, data) {
                            if (err || !window.date) {
                                console.error(err);
                            } else {
                                datePicker.changeActiveDate(data);
                            }
                        });
                    },

                    onchange: function (date) {
                        changeDate(date);
                        timeSlider.changeDate(date); // 修改timeSlider中的curDate
                    }
                });
            });

            var collapse;

            getLeagueList(game, function (err, list) {
                collapse = new Collapse({
                    target: '#collapse',
                    data: list,
                    defaultTitle: '选择季赛',
                    onchange: function (val) {
                        activeLeagueIndex = +val;
                    }
                });
            });

            var timeSlider = new TimeSlider({
                target: '#time-slider',
                onchange: function (date) {
                    changeDate(date);
                },
                onWeekChange: function (date) {
                    changeDate(date);
                },
                curDate: curDate
            });

            function _setLiveStatus(status) {
                return liveStatus[+status];
            }

            function _setDate(date) {
                return date.replace(/(\d{4})(\d{2})(\d{2})/, function (a, b, c, d) {
                    return b + '-' + (+c) + '-' + d;
                });
            }

            function _parseTeam(data, side) {
                var fragment = document.createDocumentFragment();
                var logo = document.createElement('img');
                logo.className = 'schedule-item_logo';
                logo.setAttribute('onerror', 'nofind(1)');
                logo.setAttribute('src', data[side + '_logo']);
                logo.setAttribute('alt', data[side + '_name']);
                var name = document.createElement('a');
                name.setAttribute('target', '_blank');
                name.setAttribute('href', './team.html?id=' + data[side + '_id'] + '&gameType=' + gameId);
                name.innerText = data[side + '_name'];
                if (side === 'left') {
                    fragment.appendChild(logo);
                    fragment.appendChild(name);
                } else {
                    fragment.appendChild(name);
                    fragment.appendChild(logo);
                }
                return fragment;
            }

            function appendScheduleList(data) {
                var fragment = document.createDocumentFragment();
                var date = [];
                for (var i = 0, num = data.length; i < num; i++) {
                    (function (k) {
                        var item = data[k];

                        var scheduleItem = document.createElement('div');
                        scheduleItem.className = 'schedule-item';

                        if (item.live_status === 0) {
                            scheduleItem.className += ' not-start';
                        }

                        // column1
                        var column1 = document.createElement('div');
                        column1.className = 'schedule-item_column_1';
                        var liveTag = document.createElement('span');
                        liveTag.className = 'schedule-live-tag';
                        liveTag.innerText = _setLiveStatus(item.live_status);
                        if (item.live_status === 1) {
                            liveTag.className += ' active';
                        }
                        column1.appendChild(liveTag);

                        // column2
                        var column2 = document.createElement('div');
                        column2.className = 'schedule-time-info schedule-item_column_2';
                        var startTime = document.createElement('span');
                        startTime.className = 'schedule-item-info_time';
                        startTime.innerText = item.start_time;
                        var startDate = document.createElement('span');
                        startDate.innerText = _setDate(item.start_date);
                        column2.appendChild(startTime);
                        column2.appendChild(startDate);

                        // column3
                        var column3 = document.createElement('div');
                        column3.className = 'schedule-item_column_3';
                        column3.innerText = item.league_name + '—' + item.group_name;

                        // column4
                        var column4 = document.createElement('div');
                        column4.className = 'left-team schedule-item_column_4';
                        column4.appendChild(_parseTeam(item, 'left'));

                        // column5
                        var column5 = document.createElement('div');
                        column5.className = 'schedule-item_column_5';
                        if (item.live_status === 2) {
                            column5.innerHTML = '<span>' + item.left_win + '</span><span>:</span><span>' + item.right_win + '</span>';
                        } else {
                            column5.innerHTML = '<span>VS</span>';
                        }

                        // column6
                        var column6 = document.createElement('div');
                        column6.className = 'right-team schedule-item_column_6';
                        column6.appendChild(_parseTeam(item, 'right'));

                        // column7
                        var column7 = document.createElement('div');
                        column7.className = 'schedule-item_column_7 schdule-btn-group';
                        var column7Frag = document.createDocumentFragment();
                        if (item.live_status === 0 || item.live_status === 1) {
                            var scheduleBtn = document.createElement('a');
                            scheduleBtn.className = 'schedule-btn';
                            scheduleBtn.innerText = '赛前分析';
                            scheduleBtn.setAttribute('target', '_blank');
                            scheduleBtn.setAttribute('href', './before.html?gameType=' + gameId + '&id=' + item.id); // TODO
                            column7Frag.appendChild(scheduleBtn);
                        }

                        if (item.live_status === 1) {
                            var gotoLiveBtn = document.createElement('a');
                            gotoLiveBtn.className = 'schedule-btn pink-btn';
                            gotoLiveBtn.setAttribute('href', item.live_url);
                            gotoLiveBtn.innerText = '进入直播';
                            gotoLiveBtn.setAttribute('target', '_blank');
                            column7Frag.appendChild(gotoLiveBtn);
                        } else if (item.live_status === 2) {
                            var afterDataBtn = document.createElement('a');
                            afterDataBtn.className = 'schedule-btn';
                            afterDataBtn.innerText = '赛后数据';
                            afterDataBtn.setAttribute('target', '_blank');
                            afterDataBtn.setAttribute('href', './after.html?gameType=' + gameId + '&id=' + item.id);
                            var replayBtn = document.createElement('a');
                            replayBtn.className = 'schedule-btn pink-btn';
                            replayBtn.innerText = '比赛回放';
                            replayBtn.setAttribute('target', '_blank');
                            replayBtn.setAttribute('href', item.live_url + '&replay=1');
                            column7Frag.appendChild(afterDataBtn);
                            column7Frag.appendChild(replayBtn);
                        }
                        column7.appendChild(column7Frag);

                        scheduleItem.appendChild(column1);
                        scheduleItem.appendChild(column2);
                        scheduleItem.appendChild(column3);
                        scheduleItem.appendChild(column4);
                        scheduleItem.appendChild(column5);
                        scheduleItem.appendChild(column6);
                        scheduleItem.appendChild(column7);

                        // TODO date header
                        // if (date.indexOf(item.start_date) === -1) {
                        //     var dateHeader = document.createElement('div');
                        //     dateHeader.className = 'schedule-date';
                        //     dateHeader.innerText = _setDateHeader(item.timestamp);
                        //     fragment.appendChild(dateHeader);
                        //     date.push(item.start_date);
                        // }

                        fragment.appendChild(scheduleItem);
                    })(i);
                }

                return fragment;
            }

            var weeks = ['日', '一', '二', '三', '四', '五', '六'];

            /**
             * 设置日期
             * @param {Number} timestamp 
             */
            function _setDateHeader(timestamp) {
                var dateTime = new Date(timestamp * 1000);
                var year = dateTime.getFullYear();
                var month = dateTime.getMonth() + 1;
                var day = dateTime.getDay();
                var date = dateTime.getDate();

                console.log(day);

                var nowYear = new Date().getFullYear();

                if (nowYear !== year) {
                    return year + '-' + utils.format(month, 2) + '-' + utils.format(date, 2) + ' 星期' + weeks[day];
                } else {
                    return utils.format(month, 2) + '-' + utils.format(date, 2) + ' 星期' + weeks[day];
                }

            }

            function getSchedule(gameName, e_index, date, isWeek, page, callback) {
                page = page || 1;
                callback = callback || function () { };
                var url = URL.GET_SCHEDULE.replace('{game}', gameName);
                url = url.replace('{e_index}', e_index);
                url = url.replace(/{\/relation}/, '/' + date);
                url = url.replace('{/isWeek}', '/' + isWeek);
                url = url.replace('{page}', page);

                utils.getScript(url, 'list', callback);
            }

            // TODO
            // DATE: utils.timeFormat(new Date(), 'yyyyMMdd')
            getSchedule(game, activeLeagueIndex, utils.timeFormat(curDate, 'yyyyMMdd'), 0, 1, function (err, data) {
                if (err || !data) {
                    console.error(err || data);
                } else {
                    var fragment = appendScheduleList(data);
                    $scheduleList.empty();
                    if (fragment.childNodes.length !== 0) {
                        $scheduleList[0].appendChild(fragment);
                        $('#no-list').hide();
                    } else {
                        $('#no-list').show();
                        $scheduleList.hide();
                    }

                }
            });

            function changeDate(date) {
                getSchedule(game, activeLeagueIndex, utils.timeFormat(date, 'yyyyMMdd'), 0, 1, function (err, data) {
                    if (err || !data) {
                        console.error(err || data);
                    } else {
                        var fragment = appendScheduleList(data);
                        $scheduleList.empty();
                        if (fragment.childNodes.length !== 0) {
                            $scheduleList[0].appendChild(fragment);
                            $scheduleList.show();
                            $('#no-list').hide();
                        } else {
                            $scheduleList.hide();
                            $('#no-list').show();
                        }

                    }
                });
            }

            function getLeagueList(game, callback) {
                var leagueUrl = URL.GET_LEAGUE_LIST.replace(/{game}/, game);
                utils.getScript(leagueUrl, 'list', function (e, data) {
                    if (e || !data) {
                        console.error('get league list error');
                        callback(e);
                    } else {
                        var leagueList = [];
                        for (var i = 0, num = data.length; i < num; i++) {
                            leagueList.push({
                                label: data[i].league_name,
                                value: data[i].league_index + ''
                            });
                        }
                        callback(null, leagueList);
                    }
                });
            }

            function getScheduleDate(game, e_index, date, callback) {
                var url = URL.GET_ACTIVE_DATE.replace(/{e_index}/, activeLeagueIndex);
                url = url.replace(/{game}/, game);
                url = url.replace(/{date}/, date);
                url = url.replace('{/type_id}', '');


                utils.getScript(url, 'date', function (err, data) {
                    if (err || !data) {
                        utils.isFunction(callback) && callback(err);
                    } else {
                        callback(null, data);
                    }
                });
            }
        });
    });
});
