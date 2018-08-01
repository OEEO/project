/**
 * 这是入口文件
 */
require(['../common'], function (common) {
    require(['jquery', 'utils', 'url', 'rune'], function ($, utils, URL, runeJSON) {
        $(document).ready(function () {
            console.log('这里是after页面');
            // 公共函数
            var cloneObj = function (obj) {
                if (typeof obj !== 'object') {
                    return;
                }
                if (obj === null) {
                    return null;
                }
                var str = '';
                var newObj = obj.varructor === Array ? [] : {};
                if (window.JSON) {
                    str = JSON.stringify(obj);
                    newObj = JSON.parse(str);
                } else {
                    for (var i in obj) {
                        newObj[i] = typeof obj[i] === 'object' ? cloneObj(obj[i]) : obj[i];
                    }
                }
                return newObj;
            };
            var secondToMs = function (num) {
                var m = Math.floor(num / 60);
                var s = Math.floor(num % 60);
                return m + ':' + s;
            };
            // 第1个数据是第2个数据的百分几
            var getDigitOfMax = function (val, max) {
                if (!val || !max) {
                    return false;
                }
                return val / max;
            };

            var afterObject = {
                // 改变第几局时，动态改变的数据
                time: 0, // 持续时间
                leftTeam: null,
                rightTeam: null,
                runeData: null,
                perData: {
                    left: [
                        [],
                        [],
                        []
                    ],
                    right: [
                        [],
                        [],
                        []
                    ]
                }, // 承伤占比...数据顺序--输出、承伤、经济


                // 更改局数时，不需要动态修改的数据
                matchDataArr: [], // 缓存每局比赛的数据，如果已存在，则不用再请求
                howMuchMatch: 0,
                curMatchIndex: 0, //第几局，第一局为 0
                perLineMaxWidth: 400,
                $leftDotItem: $('.after-page .equip-buy-time .left').find('.equip-item').clone().end().remove(), // lol 装备时间线一项
                $rightDotItem: $('.after-page .equip-buy-time .right').find('.equip-item').clone().end().remove(), // lol 装备时间线一项
                $kogRuneItem: $('.after-page .kog-rune-item').clone().end().remove(), // kog 铭文一项
                $kogDescItem: $('.after-page .kog-desc-item').clone().end().remove(), //kog 铭文描述一项
                // 计算属性
                gameType: function () {
                    var type = utils.getQueryString('gameType');
                    if (type === '6') {
                        return 'kog';
                    } else {
                        return 'lol';
                    }
                },
                gameId: function () {
                    var id = utils.getQueryString('id');
                    if (id) {
                        return id;
                    }
                    // 测试id
                    if (this.gameType() === 'kog') {
                        return '5aa53dd95fbc1d3caca15ca5';
                    } else {
                        return '5af143865fbc1d3cac17a6d6';
                        // return '5a61f7087e1175bd3196d640';
                    }
                },
                // 比赛摘要信息获取
                getGeneralData: function () {
                    var gameType = this.gameType();
                    var gameId = this.gameId();
                    var that = this;

                    var $generalDfd = $.Deferred();
                    var generalUrl = URL.GET_GENERAL.replace(/{game}/, gameType);
                    generalUrl = generalUrl.replace(/{id}/, gameId);
                    if (gameType === 'kog') {
                        generalUrl = generalUrl.replace(/\/v2/, '');
                    }
                    utils.getScript(generalUrl, 'general', function (e) {

                        if (e || !window.generalData) {
                            $generalDfd.reject(e || 'get data error');
                        } else {
                            that.generalData = cloneObj(window.generalData);
                            // 局数处理
                            // 局数
                            var howMuchMatch = that.generalData.detail_data.length;

                            that.howMuchMatch = howMuchMatch;

                            var $matchNav = $('.after-page .PART-1-footer .match-nav');
                            if (howMuchMatch < 2) {
                                $matchNav.append('<span class="nav-item"></span>')
                            } else {
                                var width = 1 / howMuchMatch * 100 + '%';
                                for (var i = 0; i < howMuchMatch; i++) {
                                    var numToCh = ['一', '二', '三', '四', '五', '六', '七', '八', '九'];
                                    var $item = $('<span class="nav-item"><span>第' + numToCh[i] + '局</span></span>');
                                    $item.css({
                                        'width': width
                                    });
                                    $matchNav.append($item);
                                }
                            }
                            $generalDfd.resolve();
                        }
                    });
                    return $generalDfd.promise(that);
                },
                // 比赛详细信息获取
                getGameAfterData: function () {
                    var gameType = this.gameType();
                    var gameId = this.gameId();
                    var curMatchIndex = this.curMatchIndex;
                    var that = this;

                    var $DFD = $.Deferred();

                    // 如果这局已经请求过，则不再请求
                    var curMatchData = that.matchDataArr[curMatchIndex];
                    if (curMatchData) {

                        var initMatchData = function () {
                            that.time = curMatchData.time;
                            that.hasRune = curMatchData.hasRune;
                            that.leftTeam = cloneObj(curMatchData.leftTeam);
                            that.rightTeam = cloneObj(curMatchData.rightTeam);
                            that.runeData = cloneObj(curMatchData.runeData);
                            that.perData = cloneObj(curMatchData.perData);
                        }

                        initMatchData();
                        $DFD.resolve(that);
                        return $DFD.promise();
                    }

                    // 当局数据url处理
                    var gameUrl = URL.GET_AFTER.replace(/{game}/, gameType);
                    gameUrl = gameUrl.replace(/{id}/, gameId);
                    gameUrl = gameUrl.replace(/{index}/, curMatchIndex);
                    if (gameType === 'kog') {
                        gameUrl = gameUrl.replace(/\/v2/, '');
                    }

                    if (gameType === 'lol') {
                        $('.LOL').show();
                        $('.KOG').remove();
                    } else {
                        $('.KOG').show();
                        $('.LOL').remove();
                    }
                    utils.getScript(gameUrl, 'after-the-game', function (e) {
                        if (e || !window.info) {

                            $DFD.reject(e || 'get data error');
                        } else {
                            console.log('请求第' + (curMatchIndex + 1) + '局的数据');

                            var data = window.info;
                            that.leftTeam = cloneObj(data.left);
                            that.rightTeam = cloneObj(data.right);
                            that.hasRune = data.hasRune;

                            if (gameType === 'lol') {
                                that.time = data.time;
                            } else {
                                that.time = data.duration;
                            }

                            // 处理数据
                            var leftTeam = that.leftTeam;
                            var rightTeam = that.rightTeam;
                            leftTeam.players.sort(function (a, b) {
                                return a.meta - b.meta;
                            })
                            // 中间输出占比，承伤占比，经济占比数据处理
                            var dealWithPerData = function (team, leftOrRight) {
                                $.each(team.players, function (index, player) {
                                    var teamDam = team.team_damage || team.damage;
                                    var teamDamTaken = team.team_damage_taken || team.damage_taken;
                                    var teamGolds = team.golds || team.gold;

                                    var dam = player.damage;
                                    var damTaken = player.damage_taken;
                                    var golds = player.golds || player.gold;
                                    var damPer = getDigitOfMax(dam, teamDam);
                                    var damTakenPer = getDigitOfMax(damTaken, teamDamTaken);
                                    var goldsPer = getDigitOfMax(golds, teamGolds);

                                    that.perData[leftOrRight][0].push([dam, damPer]);
                                    that.perData[leftOrRight][1].push([damTaken, damTakenPer]);
                                    that.perData[leftOrRight][2].push([golds, goldsPer]);
                                })
                            }
                            dealWithPerData(leftTeam, 'left');
                            dealWithPerData(rightTeam, 'right');

                            $DFD.resolve(that);
                        }
                    })
                    return $DFD.promise();
                },
                // 获取符文信息
                getRuneData: function () {
                    var gameType = this.gameType();
                    var gameId = this.gameId();
                    var that = this;

                    var $runeDfd = $.Deferred();
                    var runeUrl = URL.GET_RUNE.replace(/{game}/, gameType);
                    runeUrl = runeUrl.replace(/{id}/, that.gameId());
                    runeUrl = runeUrl.replace(/{index}/, that.curMatchIndex);
                    utils.getScript(runeUrl, 'rune', function (e) {
                        if (e || !window.info) {
                            $runeDfd.reject(e || 'get data error');
                        } else {
                            that.runeData = cloneObj(window.info);
                            $runeDfd.resolve(that);
                        }
                    });
                    return $runeDfd.promise();
                }

            }

            var renderThisPage = function () {
                afterObject.getGameAfterData()
                    .done(function (that) {
                        // that 指after对象

                        var leftRune = null;
                        var rightRune = null;

                        // 判断有没有铭文
                        if (that.hasRune) {
                            afterObject.getRuneData().done(function () {
                                console.log('载入铭文成功');
                                // 符文
                                leftRune = that.runeData[0];
                                rightRune = that.runeData[1];
                            })
                        } else {
                            console.log('没有铭文页');
                            // 没有铭文页时，去掉铭文 和技能 tab
                            $('.PART-3-nav .nav-item').not(':eq(0)').remove();
                            $('.PART-3-nav .nav-item').eq(0).css({
                                'width': '100%'
                            });

                        }

                        // 将当局数据储存起来
                        if (!that.matchDataArr[that.curMatchIndex]) {
                            var saveMatchData = function () {
                                var obj = {};
                                obj.time = that.time;
                                obj.leftTeam = cloneObj(that.leftTeam);
                                obj.rightTeam = cloneObj(that.rightTeam);
                                obj.perData = cloneObj(that.perData);
                                obj.hasRune = that.hasRune;
                                obj.runeData = cloneObj(that.runeData);
                                that.matchDataArr[that.curMatchIndex] = obj;
                            }
                            saveMatchData();
                        }

                        var gameType = that.gameType();

                        var leftTeam = that.leftTeam;
                        var rightTeam = that.rightTeam;

                        var generalData = that.generalData;

                        // 装备时间线，获取装备图片的 base url
                        var itemImgUrl = 'http://ossweb-img.qq.com/images/lol/img/item/{itemId}.png';

                        // 铭文，获取铭文图片的 base url
                        var runeImgUrl = 'http://lol.qq.com/act/a20170926preseason/img/runeBuilder/runes/108x108/{runeId}.png';

                        // 符文大图
                        var lolBigRuneImg = [{
                                name: '精密',
                                url: 'http://lol.qq.com/act/a20170926preseason/img/runeBuilder/runes/precision/icon-p-36x36.png'
                            },
                            {
                                name: '主宰',
                                url: 'http://lol.qq.com/act/a20170926preseason/img/runeBuilder/runes/domination/icon-d-36x36.png'
                            },
                            {
                                name: '巫术',
                                url: 'http://lol.qq.com/act/a20170926preseason/img/runeBuilder/runes/sorcery/icon-s-36x36.png'
                            },
                            {
                                name: '坚决',
                                url: 'http://lol.qq.com/act/a20170926preseason/img/runeBuilder/runes/resolve/icon-r-36x36.png'
                            },
                            {
                                name: '启迪',
                                url: 'http://lol.qq.com/act/a20170926preseason/img/runeBuilder/runes/inspiration/icon-i-36x36.png'
                            }
                        ];
                        // var lolBigRuneImg = [
                        //     {name: '精密', url: 'http://lol.qq.com/act/a20170926preseason/img/runeBuilder/assets/Precision/icon-p.png'},
                        //     {name: '主宰', url: 'http://lol.qq.com/act/a20170926preseason/img/runeBuilder/assets/Domination/icon-d.png'},
                        //     {name: '巫术', url: 'http://lol.qq.com/act/a20170926preseason/img/runeBuilder/assets/Sorcery/icon-s.png'},
                        //     {name: '坚决', url: 'http://lol.qq.com/act/a20170926preseason/img/runeBuilder/assets/Resolve/icon-r.png'},
                        //     {name: '启迪', url: 'http://lol.qq.com/act/a20170926preseason/img/runeBuilder/assets/Inspiration/icon-i.png'}
                        // ]

                        // 数据渲染
                        // PART-1
                        $('.after-page .PART-1-header .title').text(generalData.league);
                        var beginTime = utils.timeFormat(new Date(generalData.time * 1000), 'MM-DD hh:mm');
                        $('.after-page .PART-1-header .begin-time').text(beginTime);
                        $('.after-page .PART-1-header .bo').text(generalData.bo);

                        // logo
                        $('.after-page .left .team-logo').attr({
                            src: generalData.left_team_logo
                        });
                        $('.after-page .right .team-logo').attr({
                            src: generalData.right_team_logo
                        });
                        // 队名
                        $('.after-page .left .team-name').text(generalData.left_team_name);
                        $('.after-page .right .team-name').text(generalData.right_team_name);
                        $('.after-page .left-team-name').text(generalData.left_team_name);
                        $('.after-page .right-team-name').text(generalData.right_team_name);

                        //比分
                        $('.PART-1 .left-score').text(generalData.left_score);
                        $('.PART-1 .right-score').text(generalData.right_score);

                        //视频回放
                        $('.PART-1 .playback').attr('href', generalData.live_url);

                        // PART-2
                        // 比分
                        var leftKillText = leftTeam.kill || leftTeam.k;
                        var rightKillText = rightTeam.kill || rightTeam.k;
                        $('.after-page .PART-2-header .left-kill').text(leftKillText);
                        $('.after-page .PART-2-header .right-kill').text(rightKillText);
                        $('.after-page .PART-2-header .time-last').text(secondToMs(that.time));

                        // bans
                        // kog 时，特殊处理
                        if (gameType === 'kog') {
                            leftTeam.bans = leftTeam.ban;
                            rightTeam.bans = rightTeam.ban;
                            $('.after-page .PART-2-header .left .bans img').eq(4).remove();
                            $('.after-page .PART-2-header .right .bans img').eq(4).remove();
                        }
                        $.each(leftTeam.bans, function (index, url) {
                            $('.after-page .PART-2-header .left .bans img').eq(index).attr('src', url);
                        })
                        $.each(rightTeam.bans, function (index, url) {
                            $('.after-page .PART-2-header .right .bans img').eq(index).attr('src', url);
                        })

                        // picks
                        if (leftTeam.picks) {
                            $.each(leftTeam.picks, function (index, obj) {
                                $('.after-page .PART-2-main .percent-item .left-hero').eq(index).attr('src', obj.url);
                            })
                            $.each(rightTeam.picks, function (index, obj) {
                                $('.after-page .PART-2-main .percent-item .right-hero').eq(index).attr('src', obj.url);
                            })
                        } else {
                            $.each(leftTeam.pick, function (index, url) {
                                $('.after-page .PART-2-main .percent-item .left-hero').eq(index).attr('src', url);
                            })
                            $.each(rightTeam.pick, function (index, url) {
                                $('.after-page .PART-2-main .percent-item .right-hero').eq(index).attr('src', url);
                            })
                        }

                        // 输出占比...导航
                        var $perNavItems = $('.after-page .per-nav .nav-item');
                        $perNavItems.click(function (e) {
                            var index = $(e.currentTarget).index();
                            $(e.currentTarget).siblings().removeClass('active');
                            $(e.currentTarget).addClass('active');

                            $.each(that.perData.left[index], function (i, per) {
                                var max = that.perLineMaxWidth;
                                $('.after-page .PART-2-main .percent-item .left .num').eq(i).text(per[0]);
                                $('.after-page .PART-2-main .percent-item .left .line').eq(i).width(per[1] * max);
                            })

                            $.each(that.perData.right[index], function (i, per) {
                                var max = that.perLineMaxWidth;
                                $('.after-page .PART-2-main .percent-item .right .num').eq(i).text(per[0]);
                                $('.after-page .PART-2-main .percent-item .right .line').eq(i).width(per[1] * max);
                            })
                        })
                        $perNavItems.eq(0).click();

                        // 赛后数据..装备..铭文导航
                        var $footNavItems = $('.after-page .foot-nav .nav-item');
                        $footNavItems.click(function (e) {
                            var index = $(e.currentTarget).index();

                            $(e.currentTarget).siblings().removeClass('active');
                            $(e.currentTarget).addClass('active');
                            $('.after-page .PART-3 .slider').hide().eq(index).show();

                            // 十个英雄导航条部分
                            var dealWidthComonHeader = function (players, el) {
                                $.each(players, function (i, player) {
                                    el.find('.hero-icon').eq(i).attr('src', player.hero_url);
                                    el.find('.player-icon img').eq(i).attr('src', player.head_url);
                                    el.find('.player-name').eq(i).text(player.name);
                                    el.find('.hero-name').eq(i).text(player.hero_key);
                                })
                            }

                            // 底部赛后数据，铭文等条件渲染

                            if (index === 0 && (gameType === 'lol' || gameType === 'kog')) {
                                // LOL、KOG 赛后数据
                                //头部
                                if (gameType === 'kog') {
                                    leftTeam.golds = leftTeam.gold;
                                    rightTeam.golds = rightTeam.gold;
                                    // kog 没有英雄等级，补兵
                                    $('.after-page .table-body .one-player .hero-level').remove();
                                    $('.after-page .table-head .col8').remove();
                                    $('.after-page .table-body .col8').remove();
                                }

                                $('.after-page .PART-3-main .after-data .header .left')
                                    .find('.big-dragon-num').text(leftTeam.dragon)
                                    .end().find('.lt-dragon-num').text(leftTeam.baron)
                                    .end().find('.tower-num').text(leftTeam.tower);

                                $('.after-page .PART-3-main .after-data .header .right')
                                    .find('.big-dragon-num').text(rightTeam.dragon)
                                    .end().find('.lt-dragon-num').text(rightTeam.baron)
                                    .end().find('.tower-num').text(rightTeam.tower);

                                $('.after-page .PART-3-main .after-data .header .middle')
                                    .find('.left-golds').text((leftTeam.golds / 1000).toFixed(1) + 'K')
                                    .end().find('.right-golds').text((rightTeam.golds / 1000).toFixed(1) + 'K');

                                // 选手们
                                var dealWithTable = function (team, el) {
                                    $.each(team.players, function (i, player) {
                                        function countKda(kda) {
                                            var arr = kda.split('-');
                                            var k = +arr[0];
                                            var d = +arr[1];
                                            var a = +arr[2];
                                            if (d === 0) {
                                                return k + a;
                                            } else {
                                                return ((k + a) / d).toFixed(2)
                                            }
                                        }

                                        function floatToPer(float) {
                                            return Math.floor(float * 100) + '%';
                                        }
                                        var getPerOfMax = function (val, max) {
                                            if (!val || !max) {
                                                return false;
                                            }
                                            return Math.round(val / max * 100) + '%';
                                        };
                                        var $el = el.eq(i);

                                        if (gameType === 'lol') {
                                            // 技能
                                            $.each(player.skills, function (i, skill) {
                                                $el.find('.hero-skill').eq(i).attr('src', skill.url);
                                            })
                                            //装备
                                            $.each(player.items, function (i, item) {
                                                $el.find('.equip-item').eq(i).attr('src', item.url);
                                            })
                                        } else {
                                            $el.find('.hero-skill').eq(1).attr('src', player.skill);
                                            //装备
                                            $.each(player.item, function (i, url) {
                                                $el.find('.equip-item').eq(i).attr('src', url);
                                            })

                                            player.img = player.logo;
                                            player.hero_img = player.hero_logo;
                                            player.kda = player.k + '-' + player.d + '-' + player.a;
                                            team.team_damage = team.damage;
                                            team.team_damage_taken = team.damage_taken;
                                        }

                                        $el.find('.player-icon img').attr('src', player.img);
                                        // 英雄
                                        $el.find('.n1-hero').attr('src', player.hero_img);

                                        $el.find('.player-name').text(player.name);
                                        $el.find('.hero-level').text(player.level);
                                        // kda
                                        $el.find('.kda-num').text(countKda(player.kda));
                                        $el.find('.kda-headcount').text(player.kda.replace(/-/g, ' / '));

                                        //参团率
                                        $el.find('.tuan-rate').text(floatToPer(player.tuan));
                                        //输出
                                        $el.find('.make-damage').text(player.damage);
                                        $el.find('.make-damage-per').text(getPerOfMax(player.damage, team.team_damage));
                                        // 承伤
                                        $el.find('.take-damage').text(player.damage_taken);
                                        $el.find('.take-damage-per').text(getPerOfMax(player.damage_taken, team.team_damage_taken));
                                        // 补兵
                                        $el.find('.last-hit').text(player.lasthit);
                                        $el.find('.last-hit-min').text((player.lasthit / that.time * 60).toFixed(1));
                                        //经济
                                        $el.find('.golds').text(player.gold)
                                        $el.find('.golds-min').text((player.gold / that.time * 60).toFixed(1))

                                    })
                                }
                                dealWithTable(leftTeam, $('.after-page .left-player-table .one-player'));
                                dealWithTable(rightTeam, $('.after-page .right-player-table .one-player'));
                            } else if (gameType === 'lol' && index === 1) {
                                // LOL 出装、技能
                                var $equipAndSkillEl = $('.after-page .equip-and-skill');

                                // dealWidthHeader 部分 LOL 1、LOL 2 、KOG 1 一样
                                dealWidthComonHeader(leftRune, $equipAndSkillEl.find('.equip-heros .left'));
                                dealWidthComonHeader(rightRune, $equipAndSkillEl.find('.equip-heros .right'));


                                var dealWidthSkillTable = function (player, $el) {
                                    // 表格
                                    var skillLine = player.skillLine;
                                    $el.find('.lv-box').hide()
                                    for (var y = 1; y < 5; y++) {
                                        for (var x = 1; x < 19; x++) {
                                            if (skillLine[y][x] !== 0) {
                                                $el.find('tr').eq(y - 1).find('.lv-box').eq(x - 1).show();
                                            }
                                        }
                                    }
                                }

                                var dealWidthEquipLine = function (player, $el, leftOrRight) {
                                    // 装备时间线
                                    var $item = null;
                                    if (leftOrRight === 'left') {
                                        $item = that.$leftDotItem;
                                    } else {
                                        $item = that.$rightDotItem;
                                    }
                                    var itemLine = player.itemLine;
                                    var $line1 = $el.find('.line-x1').empty();
                                    var $line2 = $el.find('.line-x2').empty();
                                    var $line3 = $el.find('.line-x3').empty();

                                    var dotsInLine = Math.ceil(itemLine.length / 3);

                                    // 上一个点的位置
                                    var lastDotPot = 0;
                                    var lastDotWidth = 0;

                                    // 几个装备对应的长度
                                    var baseWidth = 36;

                                    // 是否反向
                                    var curLine = 1;
                                    var distance = 20;
                                    $.each(itemLine, function (i, dots) {
                                        var $newItem = $item.clone();
                                        var $itemImg = $newItem.find('.equip-img').clone()
                                            .end().remove();
                                        $newItem.find('.time').text(dots[0].time);
                                        var $imgs = $newItem.find('.equip-imgs');

                                        var itemWidth = dots.length > 3 ? baseWidth * 3 : baseWidth * dots.length;
                                        var l = 0;
                                        // 判断是否要换行
                                        if ((lastDotPot + lastDotWidth + distance + itemWidth >= 520 && curLine !== 2) || (curLine === 2 && lastDotPot - distance - itemWidth <= 0)) {
                                            lastDotPot = 0;
                                            lastDotWidth = 0;
                                            curLine++
                                            if (curLine === 2) {
                                                lastDotPot = 520;
                                            }
                                        }
                                        if (curLine === 2) {
                                            l = lastDotPot - distance - itemWidth;
                                        } else {
                                            l = distance + lastDotPot + lastDotWidth;
                                        }

                                        lastDotWidth = itemWidth;
                                        lastDotPot = l;

                                        $.each(dots, function (j, dot) {
                                            var url = itemImgUrl.replace(/{itemId}/, dot.itemId);
                                            var $img = $itemImg.clone();
                                            $img.attr('src', url);
                                            $imgs.append($img);
                                        })

                                        if (leftOrRight === 'left') {
                                            $newItem.css({
                                                left: l + 'px'
                                            });
                                        } else {
                                            $newItem.css({
                                                right: l + 'px'
                                            });
                                        }
                                        if (curLine === 1) {
                                            $line1.append($newItem);
                                        } else if (curLine === 2) {
                                            $line2.append($newItem);
                                        } else {
                                            $line3.append($newItem);
                                        }
                                    })
                                }

                                // 英雄点击事件
                                $('.after-page .equip-and-skill .equip-heros .hero-item').click(function (e) {
                                    $(e.currentTarget).siblings().removeClass('active');
                                    $(e.currentTarget).addClass('active');
                                    // i表示点击的英雄的序号 0-4
                                    var i = 0;
                                    if ($(e.currentTarget).parent().hasClass('left')) {
                                        i = $('.after-page .equip-and-skill .equip-heros .left .hero-item').index($(e.currentTarget));
                                        dealWidthSkillTable(leftRune[i], $('.after-page .add-skill-level .left .level-table'));

                                        dealWidthEquipLine(leftRune[i], $('.after-page .equip-buy-time .left'), 'left');
                                    } else {
                                        i = $('.after-page .equip-and-skill .equip-heros .right .hero-item').index($(e.currentTarget));
                                        dealWidthSkillTable(rightRune[i], $('.after-page .add-skill-level .right .level-table'));

                                        dealWidthEquipLine(rightRune[i], $('.after-page .equip-buy-time .right'), 'right');
                                    }
                                })
                                $('.after-page .equip-heros .left .hero-item').eq(0).click()
                                $('.after-page .equip-heros .right .hero-item').eq(0).click()
                            } else if (gameType === 'lol' && index === 2) {
                                var $el = $('.after-page .rune-and-gift');

                                // dealWidthHeader 部分 LOL 1、LOL 2 、KOG 1 一样
                                dealWidthComonHeader(leftRune, $el.find('.equip-heros .left'));
                                dealWidthComonHeader(rightRune, $el.find('.equip-heros .right'));

                                var dealWithRuneAndGift = function (player, $el) {
                                    // 切换英雄时，清空高亮
                                    $el.find('.light').removeClass('light');
                                    // 切换英雄时，清空小符文
                                    $el.find('.gift-item').attr('src', '');

                                    var runeName1 = player.perks.master.series.info.name;
                                    var runeName2 = player.perks.sub.series.info.name;
                                    var master = player.perks.master.perks;
                                    var sub = player.perks.sub.perks;
                                    var leftIdArr = [];
                                    var rightIdArr = [];
                                    $.each(master, function (i, item) {
                                        leftIdArr.push(item.runeId);
                                    })
                                    $.each(sub, function (i, item) {
                                        rightIdArr.push(item.runeId);
                                    })
                                    // 符文大图
                                    $.each(lolBigRuneImg, function (i, item) {
                                        if (runeName1 === item.name) {
                                            $el.find('.gift-1-img').attr('src', item.url);
                                        }
                                        if (runeName2 === item.name) {
                                            $el.find('.gift-2-img').attr('src', item.url);
                                        }
                                    })
                                    // 符文小图
                                    var renderRuneImg = function (runeobj, $el, idArr) {

                                        $.each(runeobj.slots, function (j, runeItems) {
                                            var $curRow = $el.find('.gift-row').eq(j);

                                            $.each(runeItems.runes, function (k, runeItem) {
                                                var $curItem = $curRow.find('.gift-item').eq(k);

                                                var id = runeItem.runeId;
                                                var url = runeImgUrl.replace(/{runeId}/, id);
                                                $curItem.attr('src', url);
                                                $.each(idArr, function (index, oneId) {
                                                    if (oneId === id) {
                                                        $curItem.addClass('light');
                                                    }
                                                })
                                            })
                                        })
                                    }

                                    $.each(runeJSON, function (i, runeobj) {
                                        if (runeobj.name === runeName1) {
                                            //左边
                                            var $leftEl = $el.find('.gift-main-l');
                                            renderRuneImg(runeobj, $leftEl, leftIdArr);
                                        } else if (runeobj.name === runeName2) {
                                            //右边
                                            var $rightEl = $el.find('.gift-main-r');
                                            renderRuneImg(runeobj, $rightEl, rightIdArr);
                                        }
                                    })
                                    $.each(master, function (index, item) {

                                    })
                                    $el.find('.gift-1-name').text(runeName1);
                                    $el.find('.gift-2-name').text(runeName2);
                                }



                                $('.after-page .rune-and-gift .equip-heros .hero-item').click(function (e) {
                                    $(e.currentTarget).siblings().removeClass('active');
                                    $(e.currentTarget).addClass('active');
                                    var index = 0;
                                    if ($(e.currentTarget).parent().hasClass('left')) {
                                        index = $('.after-page .rune-and-gift .equip-heros .left .hero-item').index($(e.currentTarget));
                                        dealWithRuneAndGift(leftRune[index], $('.after-page .rune-and-gift .left'));
                                    } else {
                                        index = $('.after-page .rune-and-gift .equip-heros .right .hero-item').index($(e.currentTarget));
                                        dealWithRuneAndGift(rightRune[index], $('.after-page .rune-and-gift .right'));
                                    }
                                })
                                $('.after-page .rune-and-gift .equip-heros .left .hero-item').eq(0).click();
                                $('.after-page .rune-and-gift .equip-heros .right .hero-item').eq(0).click();
                            } else if (gameType === 'kog' && index === 1) {
                                // kog 铭文
                                var $el = $('.after-page .kog-rune');

                                var kogRuneUrl = 'http://game.gtimg.cn/images/yxzj/img201606/mingwen/{id}.png';
                                // dealWidthHeader 部分 LOL 1、LOL 2 、KOG 1 一样
                                dealWidthComonHeader(leftRune, $el.find('.equip-heros .left'));
                                dealWidthComonHeader(rightRune, $el.find('.equip-heros .right'));

                                var dealWithKogRune = function (runeobj, $el) {
                                    var runeItems = runeobj.mastery.runeItems;
                                    var runeDescs = runeobj.mastery.runeBots;
                                    var $runeList = $el.find('.kog-rune-list');
                                    var $descList = $el.find('.kog-desc-list');
                                    $runeList.empty();
                                    $descList.empty();
                                    $.each(runeItems, function (i, item) {
                                        var $rune = that.$kogRuneItem.clone();
                                        var url = kogRuneUrl.replace(/{id}/, item.id);
                                        $rune.find('img').attr('src', url);
                                        $rune.find('.text').text(item.info);
                                        $rune.find('.rune-name').text(item.name);
                                        $rune.find('.rune-count').text(item.count);
                                        $runeList.append($rune);
                                    });
                                    $.each(runeDescs, function (i, item) {
                                        var $desc = that.$kogDescItem.clone();
                                        $desc.find('.text').text(item.label);
                                        $desc.find('.num').text(item.info);
                                        $descList.append($desc);
                                    });
                                    $wrap = $('.after-page .kog-rune-main');
                                    var leftH = $wrap.children('.left').height();
                                    var rightH = $wrap.children('.right').height();
                                    $wrap.height(Math.max(leftH, rightH) + 72);
                                }

                                $('.after-page .kog-rune .equip-heros .hero-item').click(function (e) {
                                    $(e.currentTarget).siblings().removeClass('active');
                                    $(e.currentTarget).addClass('active');
                                    var index = 0;
                                    if ($(e.currentTarget).parent().hasClass('left')) {
                                        index = $('.after-page .kog-rune .equip-heros .left .hero-item').index($(e.currentTarget));
                                        dealWithKogRune(leftRune[index], $('.after-page .kog-rune-main .left'));
                                    } else {
                                        index = $('.after-page .kog-rune .equip-heros .right .hero-item').index($(e.currentTarget));
                                        dealWithKogRune(rightRune[index], $('.after-page .kog-rune-main .right'));
                                    }
                                })
                                $('.after-page .kog-rune .equip-heros .left .hero-item').eq(0).click();
                                $('.after-page .kog-rune .equip-heros .right .hero-item').eq(0).click();


                            } else {

                            }
                        })
                        $footNavItems.eq(0).click();

                    })
            }

            // 获取局数，渲染局数菜单
            afterObject.getGeneralData().done(function () {
                var $matchNavItem = $('.match-nav .nav-item');
                $matchNavItem.click(function (e) {
                    if (afterObject.howMuchMatch < 2) {
                        // 局数为 1 时，去掉导航
                        $('.PART-1-footer').remove();
                    }
                    afterObject.curMatchIndex = $(e.currentTarget).index();
                    $(e.currentTarget).siblings().removeClass('active');
                    $(e.currentTarget).addClass('active');
                    renderThisPage();
                })
                $matchNavItem.eq(0).click();
            })
        });
    });
});