/**
 * 这是入口文件
 */
require(['../common'], function (common) {
    require(['jquery', 'utils', 'url'], function ($, utils, URL) {
        $(document).ready(function () {
            console.log('这里是before页面');
            // 公共方法
            var cloneObj = function (obj) {
                if (typeof obj !== 'object') {
                    return;
                }
                var str = '';
                var newObj = obj.varructor === Array ? [] : {};
                if (window.JSON) {
                    str = JSON.stringify(obj);
                    newObj = JSON.parse(str);
                } else {
                    for(var i in obj){
                        newObj[i] = typeof obj[i] === 'object' ? cloneObj(obj[i]) : obj[i];
                    }
                }
                return newObj;
            };

            // 第1个数据是第2个数据的百分几
            var getPerOfMax = function(val, max) {
                if (!val || !max) {
                    return 0;
                }
                return Math.round(val / max * 100);
            };

            // 两个数据占和的百分比
            var getDataPercent = function(leftNum, rightNum) {
                if (!leftNum || !rightNum) {
                    return false;
                }
                var leftPer = leftNum / (leftNum + rightNum);
                var rightPer = 1 - leftPer;
                return [leftPer, rightPer];
            };

            // 页面主对象
            var beforeObject = {
                info: null,
                lastBattle: null, // 最近比赛，没有对战，则为false
                lastBattleRecord: [], // 最近比赛比分数组
                leftTeam: null, // 左队信息
                rightTeam: null, // 右队信息
                leftWinStreak: '', // 左队连胜连败字符串
                rightWinStreak: '', // 右队 连胜连败字符串
                avgs: [], // 团队平均数据
                playerPosition: ['上单', '打野', '中单', 'ADC', '辅助'],
                curTabIndex: 0, // 上野中A辅
                curLeftPlayerIndex: 0, // 同样位置left的player，切换
                curRightPlayerIndex: 0, // 同样位置right的player，切换
                playerData: [{left: [], right: []}, {left: [], right: []}, {left: [], right: []}, {left: [], right: []}, {left: [], right: []}], // tab 切换时，player数据切换
                playerDataPer: [], // 底部，每分钟输出，参团率，伤害转化
                lineMaxWidth: 400, // 底部，每分钟输出，参团率，伤害转化线的最大长部
                lolRadarMapProps: ['avg_gold', 'avg_kill', 'avg_wards_killed_min', 'avg_asts', 'avg_last_hit', 'avg_wards_placed_min'],
                // 对应['经济', '击杀', '生存', '助攻', '补刀', '视野'],
                kogRadarMapProps: ['first_blood_rate', 'avg_gold', 'avg_kill', 'avg_damage_to_gold_rate', 'avg_damage_taken', 'avg_asts', ],
                // 对应['一血率', '经济', '击杀', '伤害转化率', '承伤', '助攻'],
                lolRadarMapTextArr: ['经济', '击杀', '生存', '助攻', '补刀', '视野'],
                kogRadarMapTextArr: ['一血率', '经济', '击杀', '伤害转化率', '承伤', '助攻'],
                //计算属性
                gameId: function() {
                    var id = utils.getQueryString('id');
                    if (id) {
                        return id;
                    }
                    // 测试id
                    if (this.gameType() === 'kog') {
                        return '5aa53bde5fbc1d3caca14351';
                    } else {
                        return '592a58805a6b6d5d45bfaa97';
                    }
                },
                gameType: function () {
                    var type = utils.getQueryString('gameType');
                    if (type === '6') {
                        return 'kog';
                    } else {
                        return 'lol';
                    }
                },
                curLeftPlayer: function() {
                    return this.playerData[this.curTabIndex].left[this.curLeftPlayerIndex];
                },
                curRightPlayer: function() {
                    return this.playerData[this.curTabIndex].right[this.curRightPlayerIndex];
                },
                curDamageMin: function() {
                    var curLeft = this.curLeftPlayer().avg_damage_min;
                    var curRight = this.curRightPlayer().avg_damage_min;
                    var per100 = Math.max(curLeft, curRight);
                    var leftPer = Math.round(curLeft / per100 * 100);
                    var rightPer = Math.round(curRight / per100  * 100);
                    var leftNum = Math.round(curLeft);
                    var rightNum = Math.round(curRight);
                    return {title: '每分钟输出', leftPer: leftPer, leftNum: leftNum, rightPer: rightPer, rightNum: rightNum};
                },
                curTuanRate: function() {
                    var curLeft = this.curLeftPlayer().avg_tuan_rate;
                    var curRight = this.curRightPlayer().avg_tuan_rate;
                    var leftPer = Math.round(curLeft * 100);
                    var rightPer = Math.round(curRight * 100);
                    var leftNum = leftPer + '%';
                    var rightNum = rightPer + '%';
                    return {title: '参团率', leftPer: leftPer, leftNum: leftNum, rightPer: rightPer, rightNum: rightNum};
                },
                curDamageToGold: function() {
                    var curLeft = this.curLeftPlayer().avg_damage_to_gold_rate;
                    var curRight = this.curRightPlayer().avg_damage_to_gold_rate;
                    var per100 = Math.max(curLeft, curRight);
                    var leftPer = Math.round(curLeft / per100  * 100);
                    var rightPer = Math.round(curRight / per100  * 100);
                    var leftNum = Math.round(curLeft * 100) + '%';
                    var rightNum = Math.round(curRight * 100) + '%';
                    return {title: '伤害转化率', leftPer: leftPer, leftNum: leftNum, rightPer: rightPer, rightNum: rightNum};
                },
                curPlayerDataPer: function() {
                    // 伤害率，参团率，伤害转化比
                    if (this.gameType() === 'lol') {
                        return [this.curDamageMin(), this.curTuanRate(), this.curDamageToGold()];
                    } else {
                        return [this.curDamageMin(), this.curTuanRate()];
                    }
                },
                curRedRadarMapArr: function() {
                    var player = this.curLeftPlayer();

                    var props = null;
                    if (this.gameType() === 'lol') {
                        props = this.lolRadarMapProps;
                    } else {
                        props = this.kogRadarMapProps;
                    }
                    var result = [];
                    $.each(props, function (index, prop) {
                        if (player[prop]) {
                            result.push(getPerOfMax(player[prop].val, player[prop].max));
                        } else {
                            result.push(0);
                        }
                    })
                    return result;
                },
                curBlueRadarMapArr: function() {
                    var player = this.curRightPlayer();
                    // 经济、击杀、生存、助攻、补刀、视野
                    var props = null;
                    if (this.gameType() === 'lol') {
                        props = this.lolRadarMapProps;
                    } else {
                        props = this.kogRadarMapProps;
                    }
                    var result = [];
                    $.each(props, function (index, prop) {
                        if (player[prop]) {
                            result.push(getPerOfMax(player[prop].val, player[prop].max));
                        } else {
                            result.push(0);
                        }
                    })
                    return result;
                },
                getGameBeforeData: function() {
                    var gameType = this.gameType();
                    var gameUrl = URL.GET_BEFORE.replace(/{game}/, gameType);
                    gameUrl = gameUrl.replace(/{id}/, this.gameId());
                    var $dfd = $.Deferred();
                    var that = this
                    console.log(that);
                    utils.getScript(gameUrl, 'before-the-game', function(e) {
                        if (e || !window.beforeData ) {
                            $dfd.reject(e || 'get data error');
                        } else {
                            var data = window.beforeData.data;
                            var info = window.beforeData.info;

                            that.info = cloneObj(info);
                            that.leftTeam = cloneObj(data.left);
                            that.rightTeam = cloneObj(data.right);
                            that.lastBattle = cloneObj(data.last_battle);
                            that.lastBattleRecord = cloneObj(data.last_battle_record);

                            if (that.lastBattle) {
                                // 如果有对战记录，转换数据
                                // var left_name = that.leftTeam.name;
                                var left_name = that.lastBattleRecord[0].left_name;
                                console.log(left_name)
                                $.each(that.lastBattleRecord, function(index, obj){
                                    obj.timeF = utils.timeFormat(new Date(obj.time * 1000), 'yyyy-MM-dd');
                                    if (obj.left_name !== left_name) {
                                        var mid = obj.left_name;
                                        obj.left_name = obj.right_name;
                                        obj.right_name = mid;
                                        mid = obj.left_score;
                                        obj.left_score = obj.right_score;
                                        obj.right_score = mid;
                                    }
                                })
                            }
                            // 连胜连败
                            var leftHistory = that.leftTeam.history;
                            var rightHistory = that.rightTeam.history;
                            var leftTeam = that.leftTeam;
                            var rightTeam = that.rightTeam;

                            that.leftWinStreak = leftHistory.count
                                ? leftHistory.init_win
                                    ? leftHistory.count + '连胜' : leftHistory.count + '连败' : false;
                            that.rightWinStreak = rightHistory.count
                                ? rightHistory.init_win
                                    ? rightHistory.count + '连胜' : rightHistory.count + '连败' : false;

                            // 中部数据
                            var leftKill = Math.round(leftTeam.avg_kill);
                            var leftDeath = Math.round(leftTeam.avg_death);
                            var leftAsts = Math.round(leftTeam.avg_asts);
                            var rightKill = Math.round(rightTeam.avg_kill);
                            var rightDeath = Math.round(rightTeam.avg_death);
                            var rightAsts = Math.round(rightTeam.avg_asts);
                            var kdaPer = getDataPercent(leftTeam.avg_kda, rightTeam.avg_kda);
                            var goldPer = getDataPercent(leftTeam.avg_gold_min, rightTeam.avg_gold_min);
                            var damagePer = getDataPercent(leftTeam.avg_damage_min, rightTeam.avg_damage_min);
                            var dragonPer = getDataPercent(leftTeam.avg_dragon_rate, rightTeam.avg_dragon_rate)
                            var baronPer = getDataPercent(leftTeam.avg_baron_rate, rightTeam.avg_baron_rate);
                            var kda = {
                                id: 'kda',
                                middleText: 'KDA',
                                leftText: [
                                    leftTeam.avg_kda.toFixed(2),
                                    leftKill + '/' + leftDeath + '/' + leftAsts
                                ],
                                rightText: [
                                    rightTeam.avg_kda.toFixed(2),
                                    rightKill + '/' + rightDeath + '/' + rightAsts
                                ],
                                leftPer: kdaPer[0],
                                rightPer: kdaPer[1]
                            }
                            var gold = {
                                id: 'gold',
                                middleText: '分均经济',
                                leftText: [
                                    leftTeam.avg_gold_min,
                                ],
                                rightText: [
                                    rightTeam.avg_gold_min,
                                ],
                                leftPer: goldPer[0],
                                rightPer: goldPer[1]
                            }
                            var damage = {
                                id: 'damage',
                                middleText: '分均输出',
                                leftText: [
                                    leftTeam.avg_damage_min,
                                ],
                                rightText: [
                                    rightTeam.avg_damage_min,
                                ],
                                leftPer: damagePer[0],
                                rightPer: damagePer[1]
                            }

                            var dragon = null;
                            if (leftTeam.avg_dragon_rate) {
                                dragon = {
                                    id: 'dragon',
                                    middleText: '控制小龙率',
                                    leftText: [
                                        Math.round(leftTeam.avg_dragon_rate * 100) + '%',
                                    ],
                                    rightText: [
                                        Math.round(rightTeam.avg_dragon_rate * 100) + '%',
                                    ],
                                    leftPer: dragonPer[0],
                                    rightPer: dragonPer[1]
                                }
                            }
                            var baron = null;
                            if (leftTeam.avg_baron_rate) {
                                baron = {
                                    id: 'baron',
                                    middleText: '控制大龙率',
                                    leftText: [
                                        Math.round(leftTeam.avg_baron_rate * 100) + '%',
                                    ],
                                    rightText: [
                                        Math.round(rightTeam.avg_baron_rate * 100) + '%',
                                    ],
                                    leftPer: baronPer[0],
                                    rightPer: baronPer[1]
                                }
                            }


                            // 分均补刀，LOL有，王者没有
                            var hitPer = '';
                            var hit = null;
                            if (leftTeam.avg_last_hit_min) {
                                hitPer = getDataPercent(leftTeam.avg_last_hit_min, rightTeam.avg_last_hit_min);
                                hit = {
                                    id: 'hit',
                                    middleText: '分均补刀',
                                    leftText: [
                                        leftTeam.avg_last_hit_min,
                                    ],
                                    rightText: [
                                        rightTeam.avg_last_hit_min,
                                    ],
                                    leftPer: hitPer[0],
                                    rightPer: hitPer[1]
                                }
                            }

                            that.avgs.push(kda);

                            if (hit) {
                                that.avgs.push(hit);
                            }
                            that.avgs.push(gold, damage);
                            if (dragon) {
                                that.avgs.push(dragon);
                            }
                            if (baron) {
                                that.avgs.push(baron);
                            }

                            // 底部数据
                            var getPosition = function (player) {
                                return player.lane - 1;
                            }
                            $.each(that.leftTeam.player, function(index, player) {
                                var positionIndex = getPosition(player);
                                that.playerData[positionIndex].left.push(player)
                            })
                            $.each(that.rightTeam.player, function(index, player) {
                                var positionIndex = getPosition(player);
                                that.playerData[positionIndex].right.push(player)
                            })
                            $dfd.resolve(that);
                        }

                    });
                    return $dfd.promise();
                }
            }

            beforeObject.getGameBeforeData()
                .done(function (that) {
                    // that 表示 beforeObject对象

                    var gameType = that.gameType();
                    // 数据渲染
                    //头部

                    var beginTime = utils.timeFormat(new Date(that.info.time * 1000), 'MM-DD hh:mm');
                    $('.part1-header .title').text(that.info.league);
                    $('.part1-header .begin-time').text(beginTime);
                    $('.part1-header .bo').text(that.info.bo);

                    // logo
                    $('.before-page .left .team-logo').attr({
                        src: that.leftTeam.logo
                    });
                    $('.before-page .right .team-logo').attr({
                        src: that.rightTeam.logo
                    });
                    // 队名
                    $('.before-page .left .team-name').text(that.leftTeam.name);
                    $('.before-page .right .team-name').text(that.rightTeam.name);
                    $('.before-page .left-team-name').text(that.leftTeam.name);
                    $('.before-page .right-team-name').text(that.rightTeam.name);

                    // part2
                    // 队伍胜率
                    $('.before-page .left .win-per-num').text(Math.round(that.leftTeam.win_rate * 100) + '%');
                    $('.before-page .right .win-per-num').text(Math.round(that.rightTeam.win_rate * 100) + '%');

                    // 历史战绩
                    $('.before-page .left .win-and-lose .win').text(that.leftTeam.history.win);
                    $('.before-page .left .win-and-lose .lose').text(that.leftTeam.history.lose);
                    $('.before-page .right .win-and-lose .win').text(that.rightTeam.history.win);
                    $('.before-page .right .win-and-lose .lose').text(that.rightTeam.history.lose);

                    // 连胜连负
                    var $leftWinStreak = $('.before-page .left .win-streak');
                    if (that.leftWinStreak) {
                        $leftWinStreak.text(that.leftWinStreak);
                        if (that.leftTeam.history.init_win) {
                            $leftWinStreak.addClass('true');
                        } else {
                            $leftWinStreak.addClass('false');
                        }
                    } else {
                        $leftWinStreak.text('——');
                    }

                    var $rightWinStreak = $('.before-page .right .win-streak');
                    if (that.rightWinStreak) {
                        $rightWinStreak.text(that.rightWinStreak);
                        if (that.rightTeam.history.init_win) {
                            $rightWinStreak.addClass('true');
                        } else {
                            $rightWinStreak.addClass('false');
                        }
                    } else {
                        $rightWinStreak.text('——');
                    }

                    // ban pick
                    var leftBanStr = '';
                    var leftPickStr = '';
                    var rightBanStr = '';
                    var rightPickStr = '';
                    $.each(that.leftTeam.bans, function(index, ban) {
                        leftBanStr += '<img class="hero" src="' + ban.url + '">';
                    })
                    $('.before-page .left .ban').append(leftBanStr);

                    $.each(that.leftTeam.picks, function(index, pick) {
                        leftPickStr += '<img class="hero" src="' + pick.url + '">';
                    })
                    $('.before-page .left .pick').append(leftPickStr);
                    $.each(that.rightTeam.bans, function(index, ban) {
                        rightBanStr += '<img class="hero" src="' + ban.url + '">';
                    })
                    $('.before-page .right .ban').append(rightBanStr);

                    $.each(that.rightTeam.picks, function(index, pick) {
                        rightPickStr += '<img class="hero" src="' + pick.url + '">';
                    })
                    $('.before-page .right .pick').append(rightPickStr);

                    // part2 中间数据 avgs
                    var avgStr = '';
                    $.each(that.avgs, function(index, avg) {
                        if (avg.leftText[0] && avg.rightText[0]) {
                            avgStr += '<li>';
                            avgStr +=     '<span class="left-text">';
                            avgStr +=          '<p>' + avg.leftText[0] +  '</p>';
                            if (avg.leftText[1]) {
                                avgStr +=      '<p>' + avg.leftText[1] +  '</p>';
                            }
                            avgStr +=      '</span>';
                            avgStr +=      '<span class="lines-wrap">';
                            avgStr +=           '<span class="red-line" style="width:' + 350 * avg.leftPer + 'px' + '"></span>';
                            avgStr +=           '<span class="blue-line" style="width:' + 350 * avg.rightPer + 'px' + '"></span>';
                            avgStr +=     '</span>';
                            avgStr +=     '<span class="middle-text">' + avg.middleText + '</span>';
                            avgStr +=     '<span class="right-text">';
                            avgStr +=          '<p>' + avg.rightText[0] +  '</p>';
                            if (avg.rightText[1]) {
                                avgStr +=      '<p>' + avg.rightText[1] +  '</p>';
                            }
                            avgStr +=     '</span>';
                            avgStr += '</li>';
                        }
                    })
                    $('.before-page .part2 .avgs').html(avgStr);

                    // 交战记录
                    $('.before-page .battle-record-title .left .win').text(that.lastBattle.left_win);
                    $('.before-page .battle-record-title .right .win').text(that.lastBattle.right_win);

                    var battleRecordStr = '';
                    if (that.lastBattleRecord.length > 0) {
                        $.each(that.lastBattleRecord, function(index, item) {
                            battleRecordStr += '<li>';
                            battleRecordStr +=     '<span class="game-time">' + item.timeF + '</span>';
                            battleRecordStr +=     '<span class="game-name">' + item.league + '</span>';
                            battleRecordStr +=     '<span class="game-result">';
                            battleRecordStr +=         '<span class="left-team-name">' + item.left_name + '</span>';
                            battleRecordStr +=         '<span class="left-team-score">' + item.left_score + '</span>';
                            battleRecordStr +=         '<span class="colon">:</span>';
                            battleRecordStr +=         '<span class="right-team-score">' + item.right_score + '</span>';
                            battleRecordStr +=         '<span class="right-team-name">' + item.right_name + '</span>';
                            battleRecordStr +=     '</span>';
                            battleRecordStr +=     '<a href="javascript:void(0)" class="game-detail" data-id="' + item.id +'">比赛详情</a>';
                            battleRecordStr +=  '</li>';
                        })
                    }
                    $('.before-page .battle-record-content').html(battleRecordStr);
                    // 跳转比赛详情
                    $('.before-page .battle-record-content .game-detail').click(function (e) {
                        var id = $(e.currentTarget).attr('data-id');
                        var href = window.location.href.replace(that.gameId(), id);
                        href = href.replace('before.html', 'after.html');
                        window.open(href);
                    });

                    // .part3
                    // 选手位置，导航条
                    $('.before-page .play-position .nav-item').click(function (e) {
                        $(e.currentTarget).addClass('active').siblings().removeClass('active');
                        that.curTabIndex = $(e.currentTarget).index();
                        navContentRender(true);
                    })

                    $('.before-page .play-position .nav-item:first-child').click();

                    // 多个小头像时，点点切换选手
                    $('.before-page .player-data .left').bind('click', function (e) {
                        var $item = $(e.target).parent();
                        if ($item.hasClass('player-little-icon')) {
                            $item.siblings().removeClass('active');
                            $item.addClass('active');
                            that.curLeftPlayerIndex = $item.index();
                            navContentRender();
                        }
                    });

                    $('.before-page .player-data .right').bind('click', function (e) {
                        var $item = $(e.target).parent();
                        if ($item.hasClass('player-little-icon')) {
                            $item.siblings().removeClass('active');
                            $item.addClass('active');
                            that.curRightPlayerIndex = $item.index();
                            navContentRender();
                        }
                    });


                    // 导航条点击，及小头像点击
                    function navContentRender(isFromTab) {
                        var curLeftPlayer = that.curLeftPlayer();
                        var curRightPlayer = that.curRightPlayer();
                        if (isFromTab) {
                            var curTabIndex = that.curTabIndex;
                            var playLeftImgStr = '';
                            var playRightImgStr = '';
                            $.each(that.playerData[curTabIndex].left, function(index, player){
                                playLeftImgStr += '<span class="player-little-icon img-wrap">';
                                playLeftImgStr += '<img src="' + player.img + '">';
                                playLeftImgStr += '</span>';
                            })
                            $.each(that.playerData[curTabIndex].right, function(index, player){
                                playRightImgStr += '<span class="player-little-icon img-wrap">';
                                playRightImgStr += '<img src="' + player.img + '">';
                                playRightImgStr += '</span>';
                            })
                            // 小头像
                            $('.player-data .header .left').html(playLeftImgStr);
                            $('.player-data .header .right').html(playRightImgStr);
                            // 让左边大头像显示第一个小头像，让右边大头像显示最后一个小头像
                            that.curLeftPlayerIndex = 0;
                            curLeftPlayer = that.curLeftPlayer();
                            that.curRightPlayerIndex = that.playerData[curTabIndex].right.length - 1;
                            curRightPlayer = that.curRightPlayer();
                            // 小头像高亮
                            $('.before-page .player-data .left .player-little-icon').eq(that.curLeftPlayerIndex).addClass('active');
                            $('.before-page .player-data .right .player-little-icon').eq(that.curRightPlayerIndex).addClass('active');
                        }

                        // 大头像
                        $('.before-page .left .player-big-icon img').attr({
                            src: curLeftPlayer.img
                        });
                        $('.before-page .right .player-big-icon img').attr({
                            src: curRightPlayer.img
                        });

                        //名字
                        $('.before-page .player-data .left .player-name').text(curLeftPlayer.name);
                        $('.before-page .player-data .right .player-name').text(curRightPlayer.name);

                        // 雷达图
                        var curRandarMapTextArr = [];
                        if (gameType === 'lol') {
                            curRandarMapTextArr = that.lolRadarMapTextArr;
                        } else {
                            curRandarMapTextArr = that.kogRadarMapTextArr;
                        }
                        radarMapRender(that.curRedRadarMapArr(), that.curBlueRadarMapArr(), curRandarMapTextArr);
                        console.log(that.curRedRadarMapArr())
                        // 圆环图
                        var per1 = getPerOfMax(curLeftPlayer.win_rate.val, curLeftPlayer.win_rate.max);
                        var num1 = per1 + '%';
                        var num2 = curLeftPlayer.avg_kda.val.toFixed(2);
                        var per2 = getPerOfMax(curLeftPlayer.avg_kda.val, curLeftPlayer.avg_kda.max);

                        var per3 = getPerOfMax(curRightPlayer.win_rate.val, curRightPlayer.win_rate.max);
                        var num3 = per3 + '%';
                        var num4 = curRightPlayer.avg_kda.val.toFixed(2);
                        var per4 = getPerOfMax(curRightPlayer.avg_kda.val, curRightPlayer.avg_kda.max);

                        perCircleRender('per-circle-1', num1, per1, '胜率', '#ff285c', '#ffb6c8');
                        perCircleRender('per-circle-2', num2, per2, '场均KDA', '#ff285c', '#ffb6c8');
                        perCircleRender('per-circle-3', num3, per3, '胜率', '#30adf5', '#cbe6fe');
                        perCircleRender('per-circle-4', num4, per4, '场均KDA', '#30adf5', '#cbe6fe');

                        // 底部数据参团率等
                        var curPlayerDataPerStr = '';
                        $.each(that.curPlayerDataPer(), function (index, item) {
                            curPlayerDataPerStr += '<li>';
                            curPlayerDataPerStr += '    <span class="left">';
                            curPlayerDataPerStr += '        <span class="line-wrap">';
                            curPlayerDataPerStr += '            <span class="num">' + item.leftNum + '</span>';
                            curPlayerDataPerStr += '            <span class="line" style=" width:' + (item.leftPer / 100 * that.lineMaxWidth) + 'px' + '"></span>';
                            curPlayerDataPerStr += '        </span>';
                            curPlayerDataPerStr += '    </span>';
                            curPlayerDataPerStr += '    <span class="middle">' + item.title + '</span>';
                            curPlayerDataPerStr += '    <span class="right">';
                            curPlayerDataPerStr += '        <span class="line-wrap">';
                            curPlayerDataPerStr += '            <span class="line" style=" width:' + (item.rightPer / 100 * that.lineMaxWidth) + 'px' + '"></span>';
                            curPlayerDataPerStr += '            <span class="num">' + item.rightNum + '</span>';
                            curPlayerDataPerStr += '        </span>';
                            curPlayerDataPerStr += '    </span>';
                            curPlayerDataPerStr += '</li>';
                        });
                        $('.before-page .footer-percent-ul').html(curPlayerDataPerStr);
                    }

                    // 雷达图
                    function radarMapRender(numArr1, numArr2, textArr) {
                        //配置
                        var width = 260,
                            height = 260,
                            edgeLength = 100, //六边形的边长
                            c = document.getElementById('radar-map'),
                            ctx = c.getContext('2d'),
                            allPoints = [],
                            point = [],
                            data = numArr1 || [0, 0, 0, 0, 0, 0],  // 权重显示数据
                            data2 = numArr2 || [0, 0, 0, 0, 0, 0],
                            lineColor = '#ff285c',
                            lineColor2 = '#30adf5',
                            coverColor = 'rgba(255, 40, 92, 0.4)',
                            coverColor2 = 'rgba(48, 173, 245, 0.4)',
                            baseLineColor = '#cccccc',
                            fontSize = 14;
                        // pointRadius = 6, //小圆的半径
                        // clickPoints = [2, 2, 2, 2, 2, 2],
                        // mx,my,
                        ctx.clearRect(0, 0, c.width, c.height);
                        drawHexagon(edgeLength);  // 画出6个六边形

                        // 画出3个六边形
                        outStroke(allPoints[0]);
                        outStroke(allPoints[2]);
                        outStroke(allPoints[4]);
                        drawText(allPoints[0], textArr, fontSize, 10);

                        drawLines();  // 画出交叉线
                        // drawPoints(pointRadius); // 是否显示描点
                        linePoint(allPoints);
                        for (var i = 0; i < data.length; i++) {
                            var num = 5 - Math.floor(parseInt(data[i])/20);
                            data[i] = point[i][num];
                        }

                        for (var i = 0; i < data2.length; i++) {
                            var num2 = 5 - Math.floor(parseInt(data2[i])/20);
                            data2[i] = point[i][num2];
                        }
                        drawCover(data, lineColor, coverColor);   // 根据传入数据画出覆盖物范围
                        drawCover(data2, lineColor2, coverColor2);

                        // 将每部分直线上的点归为一个数组
                        function linePoint(allPoints) {
                            var firstPoint = [],
                                secondPoint = [],
                                thirdPoint = [],
                                forthPoint = [],
                                fifthPoint = [],
                                sixthPoint = [];
                            for (var i = 0; i < allPoints.length; i++) {
                                firstPoint.push(allPoints[i][0]);
                                secondPoint.push(allPoints[i][1]);
                                thirdPoint.push(allPoints[i][2]);
                                forthPoint.push(allPoints[i][3]);
                                fifthPoint.push(allPoints[i][4]);
                                sixthPoint.push(allPoints[i][5]);
                            }
                            // 将每部分直线上的点归为一个数组
                            point.push(firstPoint, secondPoint, thirdPoint, forthPoint, fifthPoint, sixthPoint);
                            return point;
                        }


                        //画6个六边形
                        function drawHexagon(sixParam) {
                            for (var i = 0; i < 6; i++) {
                                allPoints[i] = getHexagonPoints(width, height, sixParam - i * sixParam / 5);  // 每个点坐标
                                ctx.beginPath();
                                ctx.fillStyle = '#FFF';
                                ctx.moveTo(allPoints[i][5][0],allPoints[i][5][1]); //5 首尾连接
                                for (var j = 0; j < 6; j++) {
                                    ctx.lineTo(allPoints[i][j][0],allPoints[i][j][1]); //1 1-5端对端连接
                                }
                                // ctx.strokeStyle = 'red';
                                // ctx.stroke();
                                ctx.closePath();
                                ctx.fill();
                            }
                        }

                        //画覆盖物
                        function drawCover(coverPoints, lineColor, coverColor) {
                            ctx.beginPath();
                            ctx.strokeStyle = lineColor;
                            ctx.fillStyle = coverColor;
                            ctx.moveTo(coverPoints[5][0], coverPoints[5][1]); //5
                            for (var j = 0; j < 6; j++) {
                                ctx.lineTo(coverPoints[j][0],coverPoints[j][1]);
                            }
                            ctx.stroke();
                            ctx.closePath();
                            ctx.fill();
                        }

                        function drawText(coverPoints, textArr, fontSize, distance) {
                            ctx.beginPath();
                            ctx.font = fontSize + 'px 微软雅黑';
                            ctx.fillStyle = '#333';
                            for (var j = 0; j < 6; j++) {
                                var x = coverPoints[j][0] - fontSize;
                                var y = coverPoints[j][1];
                                switch (j) {
                                    case 0:
                                        y -= distance;
                                        // kog 一血率文字调整
                                        if (gameType === 'kog') {
                                            x -= distance;
                                        }
                                        break;
                                    case 1:
                                        x += fontSize + distance;
                                        y -= fontSize / 2;
                                        break;
                                    case 2:
                                        x += fontSize + distance;
                                        y += fontSize / 2;
                                        break;
                                    case 3:
                                        y += distance + fontSize;
                                        // kog 伤害转化率文字调整
                                        if (gameType === 'kog') {
                                            x -= distance * 2;
                                        }
                                        break;
                                    case 4:
                                        x -= fontSize + distance;
                                        y += fontSize / 2;
                                        break;
                                    case 5:
                                        x -= fontSize + distance;
                                        y -= fontSize / 2;
                                        break;
                                    default:
                                        break;
                                }
                                ctx.fillText(textArr[j], x, y);
                            }
                            ctx.closePath();
                        }

                        //描点
                        // function drawPoints(pointRadius) {
                        //     ctx.fillStyle='#808080';
                        //     for (var i = 0; i < 5; i++) {
                        //         for (var k = 0; k < 6; k++) {
                        //             ctx.beginPath();
                        //             ctx.arc(allPoints[i][k][0],allPoints[i][k][1],pointRadius,0,Math.PI*2);
                        //             ctx.closePath();
                        //             ctx.fill();
                        //         }
                        //     }
                        // }

                        //画交叉的线
                        function drawLines() {
                            ctx.beginPath();
                            for (var i = 0; i < 3; i++) {
                                ctx.moveTo(allPoints[0][i][0],allPoints[0][i][1]); //1-4
                                ctx.lineTo(allPoints[0][i+3][0],allPoints[0][i+3][1]); //1-4
                                ctx.strokeStyle = baseLineColor;
                                ctx.stroke();
                            }
                            ctx.closePath();
                        }



                        function outStroke(outPoint) {
                            ctx.beginPath();
                            ctx.fillStyle = 'rgba(0,0,0,0)';
                            ctx.moveTo(outPoint[5][0],outPoint[5][1]); //5 首尾连接
                            for (var j = 0; j < 6; j++) {
                                ctx.lineTo(outPoint[j][0],outPoint[j][1]); //1 1-5端对端连接
                            }
                            ctx.strokeStyle = baseLineColor;
                            ctx.stroke();
                            ctx.closePath();
                            ctx.fill();
                        }


                        //传入canvas的宽度和高度还有六边形的边长，就可以确定一个六边形的六个点的坐标了
                        function getHexagonPoints(width, height, edgeLength) {
                            var paramX = edgeLength * Math.sqrt(3) / 2;
                            var marginLeft = (width - 2 * paramX) / 2;
                            var x6 = marginLeft;
                            var x5 = x6;
                            var x3 = marginLeft + paramX * 2;
                            var x2 = x3
                            var x4 = marginLeft + paramX;
                            var x1 = x4;

                            var paramY = edgeLength / 2;
                            var marginTop = (height - 4 * paramY) / 2;
                            var y1 = marginTop;
                            var y2 = marginTop + paramY;
                            var y6 = y2;
                            var y3 = marginTop + 3 * paramY;
                            var y5 = y3;
                            var y4 = marginTop + 4 * paramY;

                            var points = new Array();
                            points[0] = [x1, y1];
                            points[1] = [x2, y2];
                            points[2] = [x3, y3];
                            points[3] = [x4, y4];
                            points[4] = [x5, y5];
                            points[5] = [x6, y6];
                            return points;
                        }
                    }

                    // 圆环图
                    function perCircleRender(el, num, per, desc, color, bgColor) {
                        //  技能
                        var c = document.getElementById(el);
                        $(c).siblings('.num').text(num);

                        var ctx = c.getContext('2d');
                        ctx.clearRect(0,0,c.width,c.height);
                        var centerX = c.width/2;   //Canvas中心点x轴坐标
                        var centerY = c.height/2;  //Canvas中心点y轴坐标
                        var rad = Math.PI*2/100; //将360度分成100份，那么每一份就是rad度
                        drawCircle(ctx, per, num, desc, color, bgColor)

                        function drawCircle(ctx, per) {
                            //画底部的静态圆
                            ctx.strokeStyle = bgColor;
                            ctx.lineWidth = 10;
                            ctx.lineCap = 'round';
                            ctx.beginPath();
                            ctx.arc(centerX, centerY, 38, 0, Math.PI*2, false);
                            ctx.stroke();
                            ctx.closePath();
                            ctx.restore();

                            //画百分比圆环
                            // 解决 ie8 下 2PI 的圆会变成0。
                            var perPI = (-Math.PI / 2 + per * rad) < 3 * Math.PI / 2 ? (-Math.PI / 2 + per * rad) : 2.99 * Math.PI / 2;
                            ctx.strokeStyle = color;
                            ctx.lineWidth = 10;
                            ctx.beginPath();
                            ctx.arc(centerX, centerY, 38, -Math.PI / 2, perPI , false);
                            ctx.stroke();
                            ctx.closePath();
                            ctx.restore();
                        }
                    }
                })
        });
    });
});