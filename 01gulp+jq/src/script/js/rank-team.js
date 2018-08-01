/**
 * 这是入口文件
 */
require(['../common'], function (common) {
    require(['utils', 'url', 'jquery'], function (utils, URL, $) {
        $(document).ready(function () {
            var leagueList = [];
            var url = URL.GET_LEAGUE_LIST;
            var rankUrl;
            var gameType;
            var rankList = [];		//数据储存数组
            var leagueIndex = utils.getQueryString('league_index');		//判断比赛联赛场次
            var gameTypeUrl = GAME_TYPE; //判断比赛类型

            if (gameTypeUrl === '2') {
                gameType = url.replace('{game}', 'lol');
                rankUrl = URL.GET_LOL_TEAM_RANK;
            } else if (gameTypeUrl === '6') {
                gameType = url.replace('{game}', 'kog');
                rankUrl = URL.GET_KOG_TEAM_RANK;
            }

            utils.getScript(gameType, 'league-list', function (err, data) {		//获取kpl联赛list
                if (err || !window.list) {
                    console.error(err);
                } 
                else {
                    leagueList = window.list;
                    $('#now-league span').text(leagueList[0]['league_name']);			//当前联赛框：插入默认列表第一个展示的联赛
                    for (var i in leagueList) {          //插入联赛的ul列表
                        $('#league-select ul').append('<li l-index="' + leagueList[i]['league_index'] + '">' + leagueList[i]['league_name'] + '</li>');
                        if (leagueList[i]['league_index'] === leagueIndex) {				//当前联赛框：如果指定了index则展示index联赛
                            $('#now-league span').text(leagueList[i]['league_name']);
                        }
                    }
                    if (!leagueIndex) {			//如果没定义index则默认查询列表第一个
                        leagueIndex = leagueList[0]['league_index'];
                    }

                    rankUrl = rankUrl.replace('{e_index}', leagueIndex);			//获取当前联赛的player数据
                    utils.getScript(rankUrl, 'team-rank', function (err, data) {
                        if (err || !window.teamRank) {
                            console.error(err);
                        } 
                        else {
                            rankList = window.teamRank;
                            for (var i in rankList) {
                                rankList[i]['total_game'] = rankList[i]['win'] + rankList[i]['lose'];
                                rankList[i]['winRate'] = rankList[i]['win'] / rankList[i]['total_game'];
                            }
                            console.log(rankList);
                            initTable(rankList, gameTypeUrl);
                        }
                    });

                }
            });

            var leagueFade = 0;
            var dataFade = 0;

            $('#now-league').click(function () { //联赛目录的展开关闭
                dataFade = 0;
                $('#data-select').fadeOut(200);
                $('#now-data img').attr('src', '../src/assets/rank-down.png');
                $('#now-data').css('color', '#303030');
                if (leagueFade === 0) {
                    $('#now-league img').attr('src', '../src/assets/rank-up.png');
                    $('#now-league').css('color', '#14b8fb');
                    $('#league-select').fadeIn(200);
                    leagueFade = 1;
                } else {
                    $('#now-league img').attr('src', '../src/assets/rank-down.png');
                    $('#now-league').css('color', '#303030');
                    $('#league-select').fadeOut(200);
                    leagueFade = 0;
                }
            });

            $('#now-data').click(function () { //数据类型的展开关闭
                leagueFade = 0;
                $('#league-select').fadeOut(200);
                $('#now-league img').attr('src', '../src/assets/rank-down.png');
                $('#now-league').css('color', '#303030');
                if (dataFade === 0) {
                    $('#now-data img').attr('src', '../src/assets/rank-up.png');
                    $('#now-data').css('color', '#14b8fb');
                    $('#data-select').fadeIn(200);
                    dataFade = 1;
                } else {
                    $('#now-data img').attr('src', '../src/assets/rank-down.png');
                    $('#now-data').css('color', '#303030');
                    $('#data-select').fadeOut(200);
                    dataFade = 0;
                }
            });

            $('.table-scroll-left').click(function () { //左右滚动按钮事件
                var nowScroll = $('.fixed-table-body').scrollLeft();
                $('.fixed-table-body').animate({
                    scrollLeft: nowScroll - 780
                }, 500);
            });
            $('.table-scroll-right').click(function () {
                var nowScroll = $('.fixed-table-body').scrollLeft();
                $('.fixed-table-body').animate({
                    scrollLeft: nowScroll + 780
                }, 500);
            });

            $('#lane-select ul li').click(function () { //位置类型选择框的开关
                var text = $(this).text();
                if (text === '全部') {
                    text = '';
                    laneSearch(text);
                    $('#now-lane span:eq(0)').text('位置');
                } else {
                    laneSearch(text);
                    $('#now-lane span:eq(0)').text(text);
                }
                $('#lane-select').fadeOut(200);
            });

            $('#league-select').on('click', 'li', function () { //联赛列表的选取事件
                var oldUrl = window.location.href;
                oldUrl = oldUrl.split('?')[0];
                oldUrl += '?gameType=' + gameTypeUrl;
                window.location.href = oldUrl + '&league_index=' + $(this).attr('l-index');
            });

            $('#data-select ul li').click(function () {
                var urlData = window.location.href.split('?')[1];
                var index = $(this).attr('index');
                switch (index) {
                    case '1':
                        window.location.href = 'rank-points.html' + '?' + urlData;
                        break;
                    case '2':
                        window.location.href = 'rank-team.html' + '?' + urlData;
                        break;
                    case '3':
                        window.location.href = 'rank-player.html' + '?' + urlData;
                        break;
                    case '4':
                        window.location.href = 'rank-hero.html' + '?' + urlData;
                        break;
                }
            });

        });

        function laneSearch(text) { //位置的选择刷新搜索函数
            $('#table').bootstrapTable('refreshOptions', {
                searchText: text
            });
        }

        function initTable(json, gameType) {
            var columnSortClass = ''; //储存表头点击排序的名字
            $('#table').bootstrapTable({
                data: json,
                classes: 'table-no-bordered',
                pagination: true,
                pageSize: '12',
                paginationDetailHAlign: 'right',
                paginationPreText: '上一页',
                paginationNextText: '下一页',
                fixedColumns: true, //固定列
                fixedNumber: 2, //固定前两列
                search: true,
                onSort: function (name) {
                    columnSortClass = name;
                },
                rowStyle: function (row, index) {
                    if (index % 2 === 0) {
                        return {
                            classes: 'table-color-red'
                        };
                    } else {
                        return {
                            classes: 'table-color-white'
                        };
                    }
                },
                columns: [
                    {
                        field: 'index',
                        title: '名次',
                        width: '115',
                        align: 'center',
                        formatter: function (value, row, index) {
                            return index + 1;
                        }
                    },
                    {
                        field: 'name',
                        title: '战队',
                        width: '190',
                        align: 'left',
                        formatter: function (value, row, index) {
                            return '<a href="team.html?gameType=' + gameType + '&id=' + row['id'] + '"><img class="player-img" src="' + row['img'] + '" onerror="nofind(1)"/><span style="margin-left: 19px">' + row['name'] + '</span></a>';
                        }
                    },
                    {
                        field: 'winRate',
                        title: '胜率',
                        width: '102',
                        align: 'center',
                        formatter: function (value, row, index) {
                            return Math.floor(row['winRate'] * 1000) / 10 + '%';
                        },
                        sortable: true,
                        order: 'desc',
                        cellStyle: function () {
                            if (columnSortClass === 'winRate') {
                                return {
                                    css: {
                                        'background-color': '#FAFAFA'
                                    }
                                };
                            }
                            return {};
                        }
                    },
                    {
                        field: 'avg_kda',
                        title: 'KDA',
                        width: '78',
                        align: 'center',
                        formatter: function (value, row, index) {
                            return Math.floor(row['avg_kda'] * 10) / 10;
                        },
                        sortable: true,
                        order: 'desc',
                        cellStyle: function () {
                            if (columnSortClass === 'avg_kda') {
                                return {
                                    css: {
                                        'background-color': '#FAFAFA'
                                    }
                                };
                            }
                            return {};
                        }
                    },
                    {
                        field: 'total_game',
                        title: '比赛场数',
                        width: '123',
                        align: 'center',
                        sortable: true,
                        order: 'desc',
                        cellStyle: function () {
                            if (columnSortClass === 'total_game') {
                                return {
                                    css: {
                                        'background-color': '#FAFAFA'
                                    }
                                };
                            }
                            return {};
                        }
                    },
                    {
                        field: 'avg_duration',
                        title: '场均时长',
                        width: '101',
                        align: 'center',
                        formatter: function (value, row, index) {
                            return ('0' + Math.floor(row['avg_duration'] / 60) + ':' + (row['avg_duration'] % 60));
                        },
                        sortable: true,
                        order: 'desc',
                        cellStyle: function () {
                            if (columnSortClass === 'avg_duration') {
                                return {
                                    css: {
                                        'background-color': '#FAFAFA'
                                    }
                                };
                            }
                            return {};
                        }
                    },
                    {
                        field: 'avg_first_blood_rate',
                        title: '一血率',
                        width: '120',
                        align: 'center',
                        formatter: function (value, row, index) {
                            return Math.floor(row['avg_first_blood_rate'] * 1000) / 10 + '%';
                        },
                        sortable: true,
                        order: 'desc',
                        cellStyle: function () {
                            if (columnSortClass === 'avg_first_blood_rate') {
                                return {
                                    css: {
                                        'background-color': '#FAFAFA'
                                    }
                                };
                            }
                            return {};
                        },
                        visible: gameType === '2' ? true : false,
                    },
                    {
                        field: 'avg_kill',
                        title: '场均击杀',
                        width: '115',
                        align: 'center',
                        formatter: function (value, row, index) {
                            return Math.floor(row['avg_kill'] * 10) / 10;
                        },
                        sortable: true,
                        order: 'desc',
                        cellStyle: function () {
                            if (columnSortClass === 'avg_kill') {
                                return {
                                    css: {
                                        'background-color': '#FAFAFA'
                                    }
                                };
                            }
                            return {};
                        }
                    },
                    {
                        field: 'avg_death',
                        title: '场均死亡',
                        width: '115',
                        align: 'center',
                        formatter: function (value, row, index) {
                            return Math.floor(row['avg_death'] * 10) / 10;
                        },
                        sortable: true,
                        order: 'desc',
                        cellStyle: function () {
                            if (columnSortClass === 'avg_death') {
                                return {
                                    css: {
                                        'background-color': '#FAFAFA'
                                    }
                                };
                            }
                            return {};
                        }
                    },
                    {
                        field: 'avg_asts',
                        title: '场均助攻',
                        width: '106',
                        align: 'center',
                        formatter: function (value, row, index) {
                            return Math.floor(row['avg_asts'] * 10) / 10;
                        },
                        sortable: true,
                        order: 'desc',
                        cellStyle: function () {
                            if (columnSortClass === 'avg_asts') {
                                return {
                                    css: {
                                        'background-color': '#FAFAFA'
                                    }
                                };
                            }
                            return {};
                        }
                    },
                    {
                        field: 'avg_golds_min',
                        title: '每分钟经济',
                        width: '116',
                        align: 'center',
                        formatter: function (value, row, index) {
                            return Math.floor(row['avg_golds_min'] * 10) / 10;
                        },
                        sortable: true,
                        order: 'desc',
                        cellStyle: function () {
                            if (columnSortClass === 'avg_golds_min') {
                                return {
                                    css: {
                                        'background-color': '#FAFAFA'
                                    }
                                };
                            }
                            return {};
                        }
                    },
                    {
                        field: 'avg_damage_min',
                        title: '每分钟输出',
                        width: '113',
                        align: 'center',
                        formatter: function (value, row, index) {
                            return Math.floor(row['avg_damage_min'] * 10) / 10;
                        },
                        sortable: true,
                        order: 'desc',
                        cellStyle: function () {
                            if (columnSortClass === 'avg_damage_min') {
                                return {
                                    css: {
                                        'background-color': '#FAFAFA'
                                    }
                                };
                            }
                            return {};
                        }
                    },
                    {
                        field: 'avg_last_hit_min',
                        title: '每分钟补刀',
                        width: '113',
                        align: 'center',
                        formatter: function (value, row, index) {
                            return Math.floor(row['avg_last_hit_min'] * 10) / 10;
                        },
                        sortable: true,
                        order: 'desc',
                        cellStyle: function () {
                            if (columnSortClass === 'avg_last_hit_min') {
                                return {
                                    css: {
                                        'background-color': '#FAFAFA'
                                    }
                                };
                            }
                            return {};
                        },
                        visible: gameType === '2' ? true : false,
                    },
                    {
                        field: 'avg_dragon',
                        title: '场均小龙',
                        width: '116',
                        align: 'center',
                        formatter: function (value, row, index) {
                            return Math.floor(row['avg_dragon'] * 10) / 10;
                        },
                        sortable: true,
                        order: 'desc',
                        cellStyle: function () {
                            if (columnSortClass === 'avg_dragon') {
                                return {
                                    css: {
                                        'background-color': '#FAFAFA'
                                    }
                                };
                            }
                            return {};
                        },
                        visible: gameType === '2' ? true : false,
                    },
                    {
                        field: 'avg_dragon_rate',
                        title: '小龙控制率',
                        width: '116',
                        align: 'center',
                        formatter: function (value, row, index) {
                            return Math.floor(row['avg_dragon_rate'] * 1000) / 10 + '%';
                        },
                        sortable: true,
                        order: 'desc',
                        cellStyle: function () {
                            if (columnSortClass === 'avg_dragon_rate') {
                                return {
                                    css: {
                                        'background-color': '#FAFAFA'
                                    }
                                };
                            }
                            return {};
                        },
                        visible: gameType === '2' ? true : false,
                    },
                    {
                        field: 'avg_baron',
                        title: '场均大龙',
                        width: '116',
                        align: 'center',
                        formatter: function (value, row, index) {
                            return Math.floor(row['avg_baron'] * 10) / 10;
                        },
                        sortable: true,
                        order: 'desc',
                        cellStyle: function () {
                            if (columnSortClass === 'avg_baron') {
                                return {
                                    css: {
                                        'background-color': '#FAFAFA'
                                    }
                                };
                            }
                            return {};
                        },
                        visible: gameType === '2' ? true : false,
                    },
                    {
                        field: 'avg_baron_rate',
                        title: '大龙控制率',
                        width: '116',
                        align: 'center',
                        formatter: function (value, row, index) {
                            return Math.floor(row['avg_baron_rate'] * 1000) / 10 + '%';
                        },
                        sortable: true,
                        order: 'desc',
                        cellStyle: function () {
                            if (columnSortClass === 'avg_baron_rate') {
                                return {
                                    css: {
                                        'background-color': '#FAFAFA'
                                    }
                                };
                            }
                            return {};
                        },
                        visible: gameType === '2' ? true : false,
                    },
                    {
                        field: 'avg_wards_placed_min',
                        title: '每分钟插眼数',
                        width: '148',
                        align: 'center',
                        formatter: function (value, row, index) {
                            return Math.floor(row['avg_wards_placed_min'] * 100) / 100;
                        },
                        sortable: true,
                        order: 'desc',
                        cellStyle: function () {
                            if (columnSortClass === 'avg_wards_placed_min') {
                                return {
                                    css: {
                                        'background-color': '#FAFAFA'
                                    }
                                };
                            }
                            return {};
                        },
                        visible: gameType === '2' ? true : false,
                    },
                    {
                        field: 'avg_wards_killed_min',
                        title: '每分钟拆眼数',
                        width: '148',
                        align: 'center',
                        formatter: function (value, row, index) {
                            return Math.floor(row['avg_wards_killed_min'] * 100) / 100;
                        },
                        sortable: true,
                        order: 'desc',
                        cellStyle: function () {
                            if (columnSortClass === 'avg_wards_killed_min') {
                                return {
                                    css: {
                                        'background-color': '#FAFAFA'
                                    }
                                };
                            }
                            return {};
                        },
                        visible: gameType === '2' ? true : false,
                    },
                    {
                        field: 'avg_tower',
                        title: '场均推塔数',
                        width: '148',
                        align: 'center',
                        formatter: function (value, row, index) {
                            return Math.floor(row['avg_tower'] * 100) / 100;
                        },
                        sortable: true,
                        order: 'desc',
                        cellStyle: function () {
                            if (columnSortClass === 'avg_tower') {
                                return {
                                    css: {
                                        'background-color': '#FAFAFA'
                                    }
                                };
                            }
                            return {};
                        }
                    },
                    {
                        field: 'avg_tower_taken',
                        title: '场均被推塔数',
                        width: '148',
                        align: 'center',
                        formatter: function (value, row, index) {
                            return Math.floor(row['avg_tower_taken'] * 100) / 100;
                        },
                        sortable: true,
                        order: 'desc',
                        cellStyle: function () {
                            if (columnSortClass === 'avg_tower_taken') {
                                return {
                                    css: {
                                        'background-color': '#FAFAFA'
                                    }
                                };
                            }
                            return {};
                        }
                    }
                ]
            });
            $('.spinner').css('display', 'none');
        }
    });
});