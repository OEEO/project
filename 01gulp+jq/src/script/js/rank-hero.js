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
                rankUrl = URL.GET_LOL_HERO_RANK;
            } else if (gameTypeUrl === '6') {
                gameType = url.replace('{game}', 'kog');
                rankUrl = URL.GET_KOG_HERO_RANK;
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
                        if (err || !window.heroRank) {
                            console.error(err);
                        } else {
                            rankList = window.heroRank;
                            console.log(rankList);
                            initTable(rankList);
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

        });

        function urlDataFind(url, text) { //URL参数的读取函数
            try {
                var allData = url.split('?')[1];
                allData = allData.split('&');
                for (var i in allData) {
                    if (allData[i].split('=')[0] === text) {
                        return (allData[i].split('=')[1]);
                    }
                }
            } catch (e) {
                return 'none';
            }

        }

        function initTable(json) {
            var columnSortClass = ''; //储存表头点击排序的名字
            $('#table').bootstrapTable({
                data: json,
                classes: 'table-no-bordered',
                pagination: true,
                pageSize: '12',
                paginationDetailHAlign: 'right',
                paginationPreText: '上一页',
                paginationNextText: '下一页',
                onSort: function (name) {
                    columnSortClass = name;
                    $('#table').bootstrapTable('refreshOptions', {
                        pageNumber: 1
                    });
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
                columns: [{
                    field: 'index',
                    title: '名次',
                    width: '144',
                    align: 'center',
                    formatter: function (value, row, index) {
                        return index + 1;
                    },
                },
                {
                    field: 'name',
                    title: '英雄',
                    width: '164',
                    align: 'left',
                    formatter: function (value, row, index) {
                        return '<img class="player-img" style="border-radius: 0" src="' + row['img'] + '" onerror="nofind(1)"/> <span> ' + row['name'] + '</span>';
                    },
                },
                {
                    field: 'pick_rate',
                    title: '出场率',
                    width: '210',
                    align: 'center',
                    formatter: function (value, row, index) {
                        return Math.floor(row['pick_rate'] * 100) + '%, ' + row['pick_count'] + '场';
                    },
                    sortable: true,
                    order: 'desc',
                    cellStyle: function (field) {
                        if (columnSortClass === 'pick_rate') {
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
                    field: 'ban_rate',
                    title: '被禁率',
                    width: '210',
                    align: 'center',
                    formatter: function (value, row, index) {
                        return Math.floor(row['ban_rate'] * 100) + '%, ' + row['ban_count'] + '场';
                    },
                    sortable: true,
                    order: 'desc',
                    cellStyle: function (field) {
                        if (columnSortClass === 'ban_rate') {
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
                    field: 'win_rate',
                    title: '胜率',
                    width: '210',
                    align: 'center',
                    formatter: function (value, row, index) {
                        return Math.floor(row['win_rate'] * 1000) / 10 + '%';
                    },
                    sortable: true,
                    order: 'desc',
                    cellStyle: function (field) {
                        if (columnSortClass === 'win_rate') {
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
                    field: 'avg_kda',
                    title: 'KDA',
                    width: '210',
                    align: 'center',
                    formatter: function (value, row, index) {
                        return Math.floor(row['avg_kda'] * 10) / 10;
                    },
                    sortable: true,
                    order: 'desc',
                    cellStyle: function (field) {
                        if (columnSortClass === 'avg_kda') {
                            return {
                                css: {
                                    'background-color': '#FAFAFA'
                                }
                            };
                        }
                        return {};
                    },
                }
                ]
            });
            $('.spinner').css('display', 'none');
        }
    });
});