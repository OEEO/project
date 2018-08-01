/**
 * 这是入口文件
 */
require(['../common'], function (common) {
    require(['jquery', 'utils', 'url', 'datePicker', 'collapse', 'extend/schedule/time-slider'], function ($, utils, URL, DatePicker, Collapse) {
        $(document).ready(function () {
            require(['lazyload'], function () {
				
                var params = {};
                $.each(location.search.substring(1).split('&'), function (index, value) {
                    var item = value.split('=');
                    params[item[0]] = item[1];
                });
                var gameMapping = {
                    '2': 'lol',
                    '6': 'kog'
                };
                var gameType = GAME_TYPE;
                var game = gameMapping[gameType];
                var id = params['id'] || 187;
				
                var GET_TEAM_INFO = URL.GET_TEAM_INFO;
                GET_TEAM_INFO = GET_TEAM_INFO.replace(/{game}/, game).replace(/{id}/, id);
				
                var leagueList = [];
                var $loadMore = $('.load-more');
				
                /**
				 * 获取队伍基本数据
				 */
                $.getScript(
                    GET_TEAM_INFO,
                    function () {
                        var team = window.team;
                        if (team) {
                            var teamInfo = team.teamInfo;
                            var avgData = teamInfo.avg_data;
                            var league_index = 0;
                            var players = teamInfo.players;
                            var $header = $('.main-header');
                            $header.removeClass('hide');
                            $('.main-content').removeClass('hide');
                            $('#no-info-tip').hide();
    
                            leagueList = team.leagueList;
							
                            var getAvgData = function () {
                                var leagueAvgData = avgData[league_index];
                                var league = leagueAvgData.league_name,
                                    win = leagueAvgData.win,
                                    lose = leagueAvgData.lose,
                                    kda = leagueAvgData.avg_kda,
                                    k = leagueAvgData.avg_kill,
                                    d = leagueAvgData.avg_death,
                                    a = leagueAvgData.avg_asts,
                                    firstBloodRate = leagueAvgData.avg_first_blood_rate,
                                    golds = leagueAvgData.avg_golds_min,
                                    DPS = leagueAvgData.avg_damage_min,
                                    tower = leagueAvgData.avg_tower,
                                    CSPM = leagueAvgData.avg_last_hit_min;
                                var winRate = '--';
                                if (win + lose > 0) {
                                    winRate = win / (win + lose) * 100;
                                    winRate = winRate.toFixed(0) + '%';
                                }
                                if (kda || kda === 0) {
                                    kda = kda.toFixed(1) + '<span class="detail">(' + k.toFixed(0) + '/' + d.toFixed(0) + '/' + a.toFixed(0) + ')</span>';
                                } else {
                                    kda = '--';
                                }
								
                                if (league === 'all') {
                                    league = '全部联赛';
                                }
                                $header.find('.league_name span').text(league);
                                $header.find('.winRate .lg').text(winRate);
                                $header.find('.KDA .lg').html(kda);
                                $header.find('.firstBloodRate .lg').text(firstBloodRate != null ? (firstBloodRate * 100).toFixed(0) + '%' : '--');
                                $header.find('.CSPM .lg').text(CSPM != null ? CSPM.toFixed(0) : '--');
                                $header.find('.DPS .lg').text(DPS != null ? DPS.toFixed(0) : '--');
                                $header.find('.tower .lg').text(tower != null ? tower.toFixed(1) : '--');
                                $header.find('.golds .lg').text(golds != null ? golds.toFixed(0) : '--');
                                if (game === 'kog') {
                                    $header.find('.CSPM').hide();
                                    $header.find('.league_avg_data div').css('width', '16.6%');
                                }
                            };
							
                            $header.find('.logo').attr('data-original', teamInfo.team_logo);
                            $header.find('.suptitle').text(teamInfo.team_sub_name);
                            $header.find('.subtitle').text(teamInfo.team_name);
                            $header.find('.summary').text(teamInfo.summary);
                            getAvgData();
							
                            $header.find('.previous').click(function () {
                                if (league_index === 0) {
                                    league_index = avgData.length - 1;
                                } else {
                                    league_index--;
                                }
                                getAvgData();
                            });
                            $header.find('.next').click(function () {
                                if (league_index === avgData.length - 1) {
                                    league_index = 0;
                                } else {
                                    league_index++;
                                }
                                getAvgData();
                            });
							
                            var playerfragment = document.createDocumentFragment();
                            var playerDemo = $('.player')[0];
                            var laneMapping = {
                                1: '上路',
                                2: '打野',
                                3: '中路',
                                4: 'ADC',
                                5: '辅助'
                            };
                            for (var i = 0; i < players.length; i++) {
                                var player = players[i];
                                var topHero = player.top_hero;
                                var lane = player.lane;
                                var $player = $(playerDemo).clone(true);
                                if (i % 5 === 4) {
                                    $player.addClass('last-child');
                                }
                                var $topHero = $player.find('.top-hero img');
                                $player.find('.avatar').attr({'href': './player.html?gameType=' + gameType + '&id=' + player.id, 'target': '_blank'});

                                $player.find('.avatar img').attr('data-original', player.avatar);
                                $player.find('.label').text(laneMapping[lane]);
                                for (var j = 0; j < topHero.length; j++) {
                                    var hero = topHero[j];
                                    var $hero = $topHero.clone(true);
                                    $hero.attr('data-original', hero.img);
                                    $player.find('.top-hero').append($hero);
                                }
                                $topHero.remove();
                                playerfragment.appendChild($player[0]);
                            }
                            var playersWrapper = $('.players-wrapper')[0];
                            playersWrapper.removeChild(playerDemo);
                            playersWrapper.appendChild(playerfragment);
                            initNav();
                        }
                    }
                );
                var $historyMatchItem = $('.history-match-item');
                function getHistoryMatch(data) {
                    if (data.length > 0) {
                        $('#no-list-tip').hide();
                        $loadMore.removeClass('hide');
                        $historyMatchItem.removeClass('hide');
                        
                        var fragment = document.createDocumentFragment();
                        $('.history-match-item').remove();
						
                        $.each(data, function (index, match) {
                            var $matchItem = $historyMatchItem.clone(true);
                            var date = match.relation.substring(4);
                            $matchItem.find('.league-name').text(match.league_name);
                            $matchItem.find('.left-team .logo').attr('data-original', match.left_logo);
                            $matchItem.find('.left-team .name').text(match.left_name);
                            $matchItem.find('.right-team .logo').attr('data-original', match.right_logo);
                            $matchItem.find('.right-team .name').text(match.right_name);
                            $matchItem.find('.score').text(match.left_win + ' : ' + match.right_win);
                            $matchItem.find('.date').text(date.substring(0, 2) + '-' + date.substring(2));
                            $matchItem.find('.time').text(match.start_time);
                            $matchItem.find('.type').text('BO' + match.bonum);
                            var isLeft = false;
                            if (match.left_id === params['id']) {
                                isLeft = true;
                            }
							
                            var details = match.details;
                            var $bo = $matchItem.find('.match-item');

                            for (var i = 0; i < details.length; i++) {
                                var detail = details[i];
                                var left = detail.left;
                                var right = detail.right;
                                var duration = detail.duration;
                                var minutes = Math.floor(duration / 60);
                                var second = duration - (minutes * 60);
                                var $boItem = $bo.clone(true);
                                $boItem.find('.video').attr('href', match.live_url);
                                var $left = $boItem.find('.left');
                                var $right = $boItem.find('.right');
                                $boItem.find('.bo').text('BO' + (i + 1));
                                $boItem.find('.duration').text(minutes + ':' + second);
                                if (isLeft) {
                                    $boItem.find('.result').text(left.is_win ? 'WIN' : 'LOSE');
                                } else {
                                    $boItem.find('.result').text(left.is_win ? 'LOSE' : 'WIN');
                                }
                                $left.find('.golds').text(left.golds != null ? (left.golds / 1000).toFixed(1) + 'K' : '--');
                                $left.find('.dragon').text(left.dragon != null ? left.dragon : '--');
                                $left.find('.baron').text(left.baron != null ? left.baron : '--');
                                $left.find('.total_kill').text(left.total_kill != null ? left.total_kill : '--');
                                $right.find('.golds').text(right.golds != null ? (right.golds / 1000).toFixed(1) + 'K' : '--');
                                $right.find('.dragon').text(right.dragon != null ? right.dragon : '--');
                                $right.find('.baron').text(right.baron != null ? right.baron : '--');
                                $right.find('.total_kill').text(right.total_kill != null ? right.total_kill : '--');
								
                                var leftplayer = left.players;
                                var rightplayer = right.players;
                                var j, player, $member;
                                for (j = 0; j < leftplayer.length; j++) {
                                    player = leftplayer[j];
                                    $member = $boItem.find('.left-team-member .member:eq(' + j + ')');
									
                                    $member.find('.avatar').attr('data-original', player.avatar);
                                    $member.find('.name').text(player.name);
                                    $member.find('.hero').attr('data-original', player.hero_img);
                                    $member.find('.kda p:eq(0)').text(player.k != null ? utils.execKDA(player.k, player.d, player.a).toFixed(1) : '--');
                                    $member.find('.kda p:eq(1)').html('[' + (player.k != null ? player.k : '-') + '/<span class="text-danger">' + (player.d != null ? player.d : '-') + '</span>/' + (player.a != null ? player.a : '-') + ']');
                                    $member.find('.gold').text(player.gold != null ? player.gold : '--');
                                }
                                for (j = 0; j < rightplayer.length; j++) {
                                    player = rightplayer[j];
                                    $member = $boItem.find('.right-team-member .member:eq(' + j + ')');
									
                                    $member.find('.avatar').attr('data-original', player.avatar);
                                    $member.find('.name').text(player.name);
                                    $member.find('.hero').attr('data-original', player.hero_img);
                                    $member.find('.kda p:eq(0)').text(player.k != null ? utils.execKDA(player.k, player.d, player.a).toFixed(1) : '--');
                                    $member.find('.kda p:eq(1)').html('[' + (player.k != null ? player.k : '-') + '/<span class="text-danger">' + (player.d != null ? player.d : '-') + '</span>/' + (player.a != null ? player.a : '-') + ']');
                                    $member.find('.gold').text(player.gold != null ? player.gold : '--');
                                }
								
                                var $compare = $boItem.find('.compare');
								
                                if (gameType === 2) {
                                    var leftlasthit = left.total_lasthit,
                                        rightlasthit = right.total_lasthit;
                                    var leftCSDM = leftlasthit * 60 / duration,
                                        rightCSDM = rightlasthit * 60 / duration;
                                    var CSDMPercentage = leftCSDM / (leftCSDM + rightCSDM);
                                    var $CSDMCompare = $compare.find('.compare-item:eq(1)');
                                    var $CSDMLeftProgress = $CSDMCompare.find('.left-progress');
                                    var $CSDMRightProgress = $CSDMCompare.find('.right-progress');
                                    var $CSDMCompareLeft = $CSDMCompare.find('.compare-item-left');
                                    $CSDMCompareLeft.text(leftCSDM.toFixed(0));
                                    var $CSDMCompareRight = $CSDMCompare.find('.compare-item-right');
                                    $CSDMCompareRight.text(rightCSDM.toFixed(0));
                                    $CSDMLeftProgress.css('width', CSDMPercentage * 100 + '%');
                                    $CSDMRightProgress.css('width', (1 - CSDMPercentage) * 100 + '%');
                                } else {
                                    $compare.find('.compare-item:eq(1)').hide();
                                }
								
                                var leftDamage = left.total_damage,
                                    rightDamage = right.total_damage;
                                var leftDPS = (leftDamage * 60 / duration),
                                    rightDPS = (rightDamage * 60 / duration);
                                var DPSPercentage = leftDPS / (leftDPS + rightDPS);
                                var $DPSCompare = $compare.find('.compare-item:eq(2)');
                                var $DPSLeftProgress = $DPSCompare.find('.left-progress');
                                var $DPSRightProgress = $DPSCompare.find('.right-progress');
                                var $DPSCompareLeft = $DPSCompare.find('.compare-item-left');
                                $DPSCompareLeft.text(leftDPS.toFixed(0));
                                var $DPSCompareRight = $DPSCompare.find('.compare-item-right');
                                $DPSCompareRight.text(rightDPS.toFixed(0));
                                $DPSLeftProgress.css('width', DPSPercentage * 100 + '%');
                                $DPSRightProgress.css('width', (1 - DPSPercentage) * 100 + '%');
								
								
                                var leftK = left.total_kill,
                                    leftD = left.total_death,
                                    leftA = left.total_asts,
                                    leftKDA = utils.execKDA(leftK, leftD, leftA).toFixed(1);
                                var rightK = right.total_kill,
                                    rightD = right.total_death,
                                    rightA = right.total_asts,
                                    rightKDA = utils.execKDA(rightK, rightD, rightA).toFixed(1);
                                var kdaPercentage = parseFloat(leftKDA) / (parseFloat(leftKDA) + parseFloat(rightKDA));
								
                                var $kdaCompare = $compare.find('.compare-item:eq(0)');
                                var $kdaLeftProgress = $kdaCompare.find('.left-progress');
                                var $kdaRightProgress = $kdaCompare.find('.right-progress');
                                var $kdaCompareLeft = $kdaCompare.find('.compare-item-left');
                                $kdaCompareLeft.find('p:eq(0)').text(leftKDA);
                                $kdaCompareLeft.find('p:eq(1)').text(leftK + '/' + leftD + '/' + leftA);
                                var $kdaCompareRight = $kdaCompare.find('.compare-item-right');
                                $kdaCompareRight.find('p:eq(0)').text(rightKDA);
                                $kdaCompareRight.find('p:eq(1)').text(rightK + '/' + rightD + '/' + rightA);
                                $kdaLeftProgress.css('width', kdaPercentage * 100 + '%');
                                $kdaRightProgress.css('width', (1 - kdaPercentage) * 100 + '%');
								
                                $matchItem.append($boItem);
                            }
                            $bo.remove();
							
                            fragment.appendChild($matchItem[0]);
                            $loadMore.show();
							
                        });
						
                        $historyMatchItem.remove();
                        $loadMore.before($(fragment));
                        $('img').lazyload();
                    } else {
                        $('.history-match-item').remove();
                        $('#no-list-tip').show();
                        $loadMore.hide();
                    }
                }
				
                function initNav() {
					
                    var activeLeagueIndex = 0;
					
                    var datePicker;
					
                    getScheduleDate(game, activeLeagueIndex, utils.timeFormat(new Date(), 'yyyyMM'), function (err, dates) {
                        var activeDate = [];
						
                        if (err) {
                            console.error(err);
                        } else {
                            activeDate = dates;
                        }
						
                        datePicker = new DatePicker({
                            target: '#date-picker',
                            activeDate: activeDate,
                            onchangeMonth: function (date) {
                                var url = URL.GET_ACTIVE_DATE;
                                url = url.replace('{game}', game);
                                url = url.replace('{e_index}', activeLeagueIndex);
                                url = url.replace('{/type_id}', '/team_' + id);
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
					
                    function getSchedule(gameName, e_index, date, page, callback) {
                        page = page || 1;
                        callback = callback || function () {
                        };
                        var url = URL.GET_TEAM_HISTORY.replace('{game}', gameName);
                        url = url.replace('{id}', id);
                        url = url.replace('{e_index}', e_index);
                        url = url.replace(/{\/relation}/, date ? '/' + date : '');
                        url = url.replace('{page}', page);
						
                        utils.getScript(url, 'data', callback);
						
                        $loadMore.off('click');
                        $loadMore.on('click', function () {
                            getSchedule(gameName, e_index, date, page + 1, function (err, data) {
                                if (err || !data) {
                                    console.error(err || data);
                                } else {
                                    if (data.length === 0) {
                                        $loadMore.hide();
                                        return;
                                    }
                                    getHistoryMatch(data);
                                }
                            });
                            window.scrollTo(0, $('.league-nav').position().top);
                        });
                    }
					
                    getSchedule(game, activeLeagueIndex, '', 1, function (err, data) {
                        if (err || !data) {
                            console.error(err || data);
                        } else {
                            getHistoryMatch(data);
                        }
                    });
					
                    function changeDate(date) {
                        getSchedule(game, activeLeagueIndex, utils.timeFormat(date, 'yyyyMMdd'), 1, function (err, data) {
                            if (err || !data) {
                                console.error(err || data);
                            } else {
                                getHistoryMatch(data);
                            }
                        });
                    }
					
                    function getLeagueList(game, callback) {
                        var leagueList2 = [];
                        for (var i = 0, num = leagueList.length; i < num; i++) {
                            leagueList2.push({
                                label: leagueList[i].league_name,
                                value: leagueList[i].league_index + ''
                            });
                        }
                        callback(null, leagueList2);
                    }
					
                    function getScheduleDate(game, e_index, date, callback) {
                        var url = URL.GET_ACTIVE_DATE.replace(/{e_index}/, activeLeagueIndex);
                        url = url.replace(/{game}/, game);
                        url = url.replace(/{date}/, date);
                        url = url.replace('{/type_id}', '/team_' + id);
						
                        utils.getScript(url, 'date', function (err, data) {
                            if (err || !data) {
                                utils.isFunction(callback) && callback(err);
                            } else {
                                callback(null, data);
                            }
                        });
                    }
                }
            });
        });
    });
});
