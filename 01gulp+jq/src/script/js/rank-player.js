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
            var rankList = []; //数据储存数组
            var leagueIndex = utils.getQueryString('league_index'); //判断比赛联赛场次
            var gameTypeUrl = GAME_TYPE; //判断比赛类型

            if (gameTypeUrl === '2') {
                gameType = url.replace('{game}', 'lol');
                rankUrl = URL.GET_LOL_PLAYER_RANK;
            } else if (gameTypeUrl === '6') {
                gameType = url.replace('{game}', 'kog');
                rankUrl = URL.GET_KOG_PLAYER_RANK;
            }

            utils.getScript(gameType, 'league-list', function (err, data) { //获取kpl联赛list
                if (err || !window.list) {
                    console.error(err);
                } else {
                    leagueList = window.list;
                    $('#now-league span').text(leagueList[0]['league_name']); //当前联赛框：插入默认列表第一个展示的联赛
                    for (var i in leagueList) { //插入联赛的ul列表
                        $('#league-select ul').append('<li l-index="' + leagueList[i]['league_index'] + '">' + leagueList[i]['league_name'] + '</li>');
                        if (leagueList[i]['league_index'] === leagueIndex) { //当前联赛框：如果指定了index则展示index联赛
                            $('#now-league span').text(leagueList[i]['league_name']);
                        }
                    }
                    if (!leagueIndex) { //如果没定义index则默认查询列表第一个
                        leagueIndex = leagueList[0]['league_index'];
                    }

                    rankUrl = rankUrl.replace('{e_index}', leagueIndex); //获取当前联赛的player数据
                    utils.getScript(rankUrl, 'player-rank', function (err, data) {
                        if (err || !window.rank) {
                            console.error(err);
                        } else {
                            rankList = window.rank;
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

            $('#league-select').on('click', 'li', function () { //联赛列表的选取事件
                var oldUrl = window.location.href;
                oldUrl = oldUrl.split('?')[0];
                oldUrl += '?gameType=' + gameTypeUrl;
                window.location.href = oldUrl + '&league_index=' + $(this).attr('l-index');
            });

            $('#data-select ul li').click(function () { //数据类型列表的选取事件
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


        });


        function laneSearch(text) { //位置的选择刷新搜索函数
            $('#table').bootstrapTable('refreshOptions', {
                searchText: text
            });
        }

        function initTable(json, gameTypeForCol) { //表格渲染函数，gameTypeForCol判断是KOG还是LOL
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
                        width: '114',
                        align: 'center',
                        formatter: function (value, row, index) {
                            return index + 1;
                        },
                    },
                    {
                        field: 'name',
                        title: '选手',
                        width: '190',
                        align: 'left',
                        formatter: function (value, row, index) {
                            return '<a href="player.html?gameType=' + gameTypeForCol + '&id=' + row['id'] + '"><img class="player-img" src="' + row['img'] + '" onerror="nofind(0)"/> <span>' +
                                row['team_name'] + '.' + row['name'] + '</span></a>';
                        },
                    },
                    {
                        field: 'team_name',
                        title: '战队',
                        width: '115',
                        align: 'center',
                    },
                    {
                        field: 'lane',
                        title: '<div id="now-lane" onclick="$(\'#lane-select\').fadeToggle(200);"><span>位置</span> <img src="../src/assets/rank-s-down.png"></div>',
                        width: '94',
                        align: 'center',
                        formatter: function (value, row, index) {
                            var laneName;
                            switch (row['lane'] + '') {
                                case '1':
                                    laneName = '上路';
                                    break;
                                case '2':
                                    laneName = '打野';
                                    break;
                                case '3':
                                    laneName = '中路';
                                    break;
                                case '4':
                                    laneName = 'ADC';
                                    break;
                                case '5':
                                    laneName = '辅助';
                                    break;
                            }
                            return laneName;
                        },
                    },
                    {
                        field: 'avg_kda',
                        title: 'KDA',
                        width: '76',
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
                        },
                    },
                    {
                        field: 'total_game',
                        title: '出场次数',
                        width: '104',
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
                        },
                    },
                    {
                        field: 'avg_tuan_rate',
                        title: '参团率',
                        width: '99',
                        align: 'center',
                        formatter: function (value, row, index) {
                            return Math.floor(row['avg_tuan_rate'] * 1000) / 10 + '%';
                        },
                        sortable: true,
                        order: 'desc',
                        cellStyle: function () {
                            if (columnSortClass === 'avg_tuan_rate') {
                                return {
                                    css: {
                                        'background-color': '#FAFAFA'
                                    }
                                };
                            }
                            return {};
                        },
                    },
                    {
                        field: 'avg_kill',
                        title: '场均击杀',
                        width: '108',
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
                        },
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
                        field: 'avg_death',
                        title: '场均死亡',
                        width: '113',
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
                        },
                    },
                    {
                        field: 'avg_golds_min',
                        title: '每分钟经济',
                        width: '121',
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
                        },
                    },
                    {
                        field: 'avg_last_hit_min',
                        title: '每分钟补刀',
                        width: '137',
                        align: 'center',
                        formatter: function (value, row, index) {
                            return Math.floor(row['avg_last_hit_min'] * 100) / 100;
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
                        visible: gameTypeForCol === '2' ? true : false,
                    },
                    {
                        field: 'avg_damage_min',
                        title: '每分钟输出',
                        width: '113',
                        align: 'center',
                        formatter: function (value, row, index) {
                            return Math.floor(row['avg_damage_min']);
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
                        },
                    },
                    {
                        field: 'avg_damage_taken_min',
                        title: '每分钟承受伤害',
                        width: '148',
                        align: 'center',
                        formatter: function (value, row, index) {
                            return Math.floor(row['avg_damage_taken_min']);
                        },
                        sortable: true,
                        order: 'desc',
                        cellStyle: function () {
                            if (columnSortClass === 'avg_damage_taken_min') {
                                return {
                                    css: {
                                        'background-color': '#FAFAFA'
                                    }
                                };
                            }
                            return {};
                        },
                    },
                    {
                        field: 'avg_damage_rate',
                        title: '场均伤害占比',
                        width: '153',
                        align: 'center',
                        formatter: function (value, row, index) {
                            return Math.floor(row['avg_damage_rate'] * 1000) / 10 + '%';
                        },
                        sortable: true,
                        order: 'desc',
                        cellStyle: function () {
                            if (columnSortClass === 'avg_damage_rate') {
                                return {
                                    css: {
                                        'background-color': '#FAFAFA'
                                    }
                                };
                            }
                            return {};
                        },
                    },
                    {
                        field: 'avg_wards_placed_min',
                        title: '每分钟插眼数',
                        width: '136',
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
                        visible: gameTypeForCol == '2' ? true : false,
                    }
                ]
            });
            $('.spinner').css('display', 'none');
        }
    });
});