/**
 * 这是入口文件
 */
require(['../common'], function (common) {
    require(['jquery', 'utils', 'url', 'datePicker', 'collapse', 'extend/schedule/time-slider'], function ($, utils, URL, DatePicker, Collapse) {
        require(['lazyload'], function () {
            $(document).ready(function () {
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
                var id = params['id'] || 26460;
                var GET_PLAYER_INFO = URL.GET_PLAYER_INFO;
                GET_PLAYER_INFO = GET_PLAYER_INFO.replace(/{game}/, game).replace(/{id}/, id);
                var leagueList = [];
                if (gameType === '6') {
                    $('.top-hero-table').removeClass('hide');
                }
                var teamName;
                var $loadMore = $('.load-more');
				
                $.getScript(
                    GET_PLAYER_INFO,
                    function () {
                        var player = window.player;
                        if (player) {
                            var playerInfo = player.player_info;
                            var avgData = player.avg_datas;
                            var league_index = 0;
                            leagueList = player.league_list;
                            var $header = $('.main-header');
                            $header.removeClass('hide');
                            $('.main-content').removeClass('hide');
                            $('#no-info-tip').hide();
    
                            teamName = playerInfo.team_name;
							
                            var getAvgData = function () {
                                var leagueAvgData = avgData[league_index];
                                var league = leagueAvgData.league_name,
                                    winRate = leagueAvgData.win_rate,
                                    kda = leagueAvgData.avg_kda,
                                    k = leagueAvgData.avg_kill,
                                    d = leagueAvgData.avg_death,
                                    a = leagueAvgData.avg_asts,
                                    DPM = leagueAvgData.avg_damage_rate,
                                    golds = leagueAvgData.avg_golds_min,
                                    tuan = leagueAvgData.avg_tuan_rate,
                                    CSPM = leagueAvgData.avg_last_hit;
								
                                if (kda != null) {
                                    kda = kda.toFixed(1) + '<span class="detail">(' + k.toFixed(0) + '/' + d.toFixed(0) + '/' + a.toFixed(0) + ')</span>';
                                } else {
                                    kda = '--';
                                }
								
                                if (league === 'all') {
                                    league = '全部联赛';
                                }
                                $header.find('.league_name span').text(league);
                                $header.find('.winRate .lg').text(winRate != null ? (winRate * 100).toFixed(0) + '%' : '--');
                                $header.find('.KDA .lg').html(kda);
                                $header.find('.DPM .lg').text(DPM != null ? (DPM * 100).toFixed(0) + '%' : '--');
                                $header.find('.CSPM .lg').text(CSPM != null ? CSPM.toFixed(0) : '--');
                                $header.find('.golds .lg').text(golds != null ? golds.toFixed(0) : '--');
                                $header.find('.tuan .lg').text(tuan != null ? (tuan * 100).toFixed(0) + '%' : '--');
                                $header.find('.kill .lg').text(k != null ? k.toFixed(0) : '--');
                                if (game === 'kog') {
                                    $header.find('.CSPM').hide();
                                    $header.find('.league_avg_data div').css('width', '20%');
                                }
                            };
							
                            $header.find('.logo').attr('data-original', playerInfo.img);
                            $header.find('.suptitle').text(playerInfo.name);
                            $header.find('.subtitle').text(playerInfo.team_name);
                            // $header.find('.summary').text(playerInfo);
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
							
                            var usedHero = player.used_hero;
                            var $topHeroTable = $('.top-hero-table');
                            var $tophero = $('.top-hero-body');
							
                            var getTopHero = function (heros) {
                                $tophero.empty();
								
                                var topHerofragment = document.createDocumentFragment();
                                $.each(usedHero, function (index, hero) {
                                    var kda = hero.avg_kda != null ? hero.avg_kda.toFixed(1) : '--',
                                        k = hero.avg_kill != null ? hero.avg_kill.toFixed(0) : '-',
                                        d = hero.avg_death != null ? hero.avg_death.toFixed(0) : '-',
                                        a = hero.avg_asts != null ? hero.avg_asts.toFixed(0) : '-',
                                        img = hero.hero_img,
                                        name = hero.hero_name ? hero.hero_name : '--',
                                        count = hero.lose_count != null ? hero.lose_count + hero.win_count : '--',
                                        maxKill = hero.max_kill != null ? hero.max_kill : '--',
                                        damageMin = hero.avg_damage_min != null ? hero.avg_damage_min.toFixed(0) : '--',
                                        damgeTakenMin = hero.avg_damage_taken_min != null ? hero.avg_damage_taken_min.toFixed(0) : '--',
                                        gold = hero.avg_gold != null ? hero.avg_gold.toFixed(0) : '--',
                                        firstBloodRate = hero.first_blood_rate != null ? (hero.first_blood_rate * 100).toFixed(0) + '%' : '--',
                                        tuan = hero.avg_tuan_rate != null ? (hero.avg_tuan_rate * 100).toFixed(0) + '%' : '--',
                                        winRate = hero.win_rate != null ? (hero.win_rate * 100).toFixed(0) + '%' : '--';

                                    var text = '';
                                    text += '<tr>';
                                    text += "    <td align='left'><img data-original=" + img + " onerror='nofind()' width='36' height='36'/><span>" + name + '</span></td>';
                                    text += '    <td class="count cover"><p>' + count + '</p><p><span class="text-danger">W-' + hero.win_count + '</span><span class="text-blue">L-' + hero.lose_count + '</spanc></p></td>';
                                    text += '    <td>' + winRate + '</td>';
                                    text += '    <td class="kda"><p>' + kda + '</p><p>[<span>' + k + '</span>/<span class="text-danger">' + d + '</span>/<span>' + a + '</span>]</p></td>';
                                    text += '    <td>' + maxKill + '</td>';
                                    text += '    <td>' + damageMin + '</td>';
                                    text += '    <td>' + damgeTakenMin + '</td>';
                                    text += '    <td>' + gold + '</td>';
                                    text += '    <td>' + tuan + '</td>';
                                    text += '    <td>' + firstBloodRate + '</td>';
                                    text += '</tr>';
                                    var $tr = $(text);
                                    if (index % 2 === 1) {
                                        $tr.addClass('odd');
                                    }
                                    if (index > 2 && $topHeroTable.find('tfoot span').hasClass('down-caret')) {
                                        $tr.addClass('hide');
                                    }
                                    topHerofragment.appendChild($tr[0]);
                                });
                                $tophero.append($(topHerofragment));
                            };
							
                            getTopHero(usedHero);
                            $topHeroTable.on('click', 'tfoot tr', function () {
                                var $this = $(this).find('span');
                                if ($this.hasClass('up-caret')) {
                                    $this.removeClass('up-caret');
                                    $this.addClass('down-caret');
                                    $tophero.find('tr:gt(2)').addClass('hide');
                                } else {
                                    $this.removeClass('down-caret');
                                    $this.addClass('up-caret');
                                    $tophero.find('tr:gt(2)').removeClass('hide');
                                }
                            });
                            $topHeroTable.on('click', '.order', function () {
                                var $this = $(this);

                                var orderItem = $this.data('order');
                                var orderWay = 1;
								
                                if ($this.find('.order-triangle').hasClass('down-order')) {
                                    $this.find('.order-triangle').removeClass('down-order');
                                    $this.find('.order-triangle').addClass('up-order');
                                    orderWay = 2;
                                } else if ($this.find('.order-triangle').hasClass('up-order')) {
                                    $this.find('.order-triangle').removeClass('up-order');
                                    $this.find('.order-triangle').addClass('down-order');
                                } else {
                                    $topHeroTable.find('.up-order').removeClass('up-order');
                                    $topHeroTable.find('.down-order').removeClass('down-order');
                                    $this.find('.order-triangle').addClass('down-order');
                                }
								
                                usedHero = usedHero.sort(function (a, b) {
                                    if (orderWay === 2) {
                                        if (orderItem === 'count') {
                                            return a.win_count + a.lose_count - (b.win_count + b.lose_count);
                                        }
                                        return a[orderItem] - b[orderItem];
                                    } else {
                                        if (orderItem === 'count') {
                                            return b.win_count + b.lose_count - (a.win_count + a.lose_count);
                                        }
                                        return b[orderItem] - a[orderItem];
                                    }
                                });
								
                                getTopHero(usedHero);
                                $('.cover').removeClass('cover');
                                var index = $this.index();
                                $this.addClass('cover');
                                $('.top-hero-body tr').each(function () {
                                    var $this = $(this);
                                    $this.find('td:eq(' + index + ')').addClass('cover');
                                });
    
                                $('img').lazyload();
                            });
                            initNav();
                        }
                    }
                );
                var $historyMatchItem = $('.player-history-match');
				
                function getHistoryMatch(data) {
                    if (data.length > 0) {
                        $('#no-list-tip').hide();
                        $loadMore.removeClass('hide');
                        $historyMatchItem.removeClass('hide');
    
                        $('.player-history-match').remove();
                        var fragment = document.createDocumentFragment();
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
							
                            var details = match.details;
							
                            var $bo = $matchItem.find('.match-item');
                            for (var i = 0; i < details.length; i++) {
                                var detail = details[i];
                                var duration = detail.duration;
                                var minutes = Math.floor(duration / 60);
                                var second = duration - (minutes * 60);
                                var $boItem = $bo.clone(true);
                                $boItem.find('.bo').text('BO' + (i + 1));
                                $boItem.find('.duration').text(minutes + ':' + second);
                                $boItem.find('.video').attr('href', match.live_url);
                                if (detail.left_name === teamName) {
                                    $boItem.find('.result').text(detail.left_win ? '胜利' : '失败');
                                } else {
                                    $boItem.find('.result').text(detail.right_win ? '胜利' : '失败');
                                }
								
                                var playerData = detail.playerData;
                                if (playerData) {
									
                                    var items = playerData.items;
                                    var talents = playerData.talents;
                                    var $leftSection = $boItem.find('.left-section');
                                    var $middleSection = $boItem.find('.middle-section');
                                    var $infoBox = $middleSection.find('.info-box');
                                    var $goods = $leftSection.find('.goods img');
                                    var $talent = $leftSection.find('.skill img');
                                    $leftSection.find('.hero img').attr('data-original', playerData.heroAvatar);
                                    $.each(items, function (index, item) {
                                        $goods.eq(index).attr('data-original', item);
                                    });
                                    if (talents.length === 1) {
                                        $talent.eq(1).remove();
                                    }
                                    $.each(talents, function (index, talent) {
                                        $talent.eq(index).attr('data-original', talent);
                                    });
                                    var kda = playerData.kda.split('/');
                                    $infoBox.eq(0).find('p:eq(0)').text(utils.execKDA(kda[0], kda[1], kda[2]).toFixed(1));
                                    $infoBox.eq(1).find('p:eq(0)').text(playerData.damage);
                                    $infoBox.eq(3).find('p:eq(0)').text(playerData.tuan); // 参团率
                                    if (game === 'lol') {
                                        $infoBox.eq(4).find('p:eq(0)').text(playerData.wardsPlaced + '/' + playerData.wardsKilled);
                                        $infoBox.eq(2).find('p:eq(0)').text(playerData.lasthit);
                                    }
                                    $infoBox.eq(5).find('p:eq(0)').text(playerData.gold);
                                }
                                var $rightSection = $boItem.find('.right-section');
                                var $matchInfoTr = $rightSection.find('tr');
                                $matchInfoTr.eq(0).find('th:eq(0) span').text(detail.left_name);
                                $matchInfoTr.eq(0).find('th:eq(0) img').attr('data-original', match.left_logo);
                                $matchInfoTr.eq(0).find('th:eq(1) span').text(detail.right_name);
                                $matchInfoTr.eq(0).find('th:eq(1) img').attr('data-original', match.right_logo);
                                var leftPlayers = detail.left_players;
                                var rightPlayers = detail.right_players;
                                $.each(leftPlayers, function (index, value) {
                                    $matchInfoTr.eq(index + 1).find('td:eq(0) img').attr('data-original', value.heroAvatar);
                                    $matchInfoTr.eq(index + 1).find('td:eq(0) span').text(value.name);
                                    $matchInfoTr.eq(index + 1).find('td:eq(1)').text(value.kda);
                                });
                                $.each(rightPlayers, function (index, value) {
                                    $matchInfoTr.eq(index + 1).find('td:eq(2) img').attr('data-original', value.heroAvatar);
                                    $matchInfoTr.eq(index + 1).find('td:eq(2) span').text(value.name);
                                    $matchInfoTr.eq(index + 1).find('td:eq(3)').text(value.kda);
                                });
								
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
                        $('.player-history-match').remove();
                        $('#no-list-tip').show();
                        $loadMore.hide();
                    }
                }
				
                function initNav() {
					
                    var activeLeagueIndex = leagueList[0].index;
					
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
                                url = url.replace('{/type_id}', '/player_' + id);
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
                        var url = URL.GET_PLAYER_HISTORY.replace('{game}', gameName);
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
                                value: leagueList[i].index + ''
                            });
                        }
                        callback(null, leagueList2);
                    }
					
                    function getScheduleDate(game, e_index, date, callback) {
                        var url = URL.GET_ACTIVE_DATE.replace(/{e_index}/, this.activeLeagueIndex);
                        url = url.replace(/{game}/, game);
                        url = url.replace(/{date}/, date);
						
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
