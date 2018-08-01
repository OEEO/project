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
            var columnSortClass = ''; //储存表头点击排序的名字
            var leagueIndex = utils.getQueryString('league_index'); //判断比赛联赛场次
            var gameTypeUrl = GAME_TYPE; //判断比赛类型

            if (gameTypeUrl === '2') {
                gameType = url.replace('{game}', 'lol');
                rankUrl = URL.GET_LOL_POINTS_RANK;
            } else if (gameTypeUrl === '6') {
                gameType = url.replace('{game}', 'kog');
                rankUrl = URL.GET_KOG_POINTS_RANK;
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

                            /*
                            先判断group是否存在，存在时先遍历分组的名字（东部西部）
                            然后根据分组名找到该分组（group1，group2）
                            再遍历该分组下存在的队伍id
                            最后遍历队伍列表
                            如果有id等于分组下队伍id，则给队伍中增加一个team_group属性（东部西部）
                            */
                            if (rankList['group_info'] !== false) {
                                for (var i in rankList['group_info']['show_name']) {
                                    var teamGroup = 'group' + (parseInt(i) + 1);
                                    for (var k in rankList['group_info'][teamGroup]) {
                                        for (var s in rankList['list']) {
                                            if (parseInt(rankList['group_info'][teamGroup][k]) === rankList['list'][s]['team_id']) {
                                                rankList['list'][s]['team_group'] = rankList['group_info']['show_name'][i];
                                            }
                                        }
                                    }
                                }
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

        function initTable(json, gameType) {
            $('#table').bootstrapTable({
                data: json['list'],
                classes: 'table-no-bordered',
                pagination: true,
                pageSize: '12',
                paginationDetailHAlign: 'right',
                paginationPreText: '上一页',
                paginationNextText: '下一页',
                fixedColumns: true, //固定列
                fixedNumber: 2, //固定前两列
                search: true,
                searchText: json['group_info'] !== false ? json['group_info']['show_name'][0] : '',
                onSearch: function (name) {
                    $('tr:nth-child(odd)').css('background-color', '#fff8fa');
                    $('tr th').css('background-color', '#fff'); //兼容ie8不支持css3 odd 斑马纹
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
                        title: '<div id="team-group"></div>',
                        width: '190',
                        align: 'left',
                        formatter: function (value, row, index) {
                            return '<a href="team.html?gameType=' + gameType + '&id=' + row['team_id'] + '"><img class="player-img" src="' + row['team_logo'] + '" onerror="nofind(1)"/><span style="margin-left: 19px">' + row['team_name'] + '</span></a>';
                        },
                    },
                    {
                        field: 'score',
                        title: '积分',
                        width: '298',
                        align: 'center',
                    },
                    {
                        field: 'winAndLose',
                        title: '胜负',
                        width: '298',
                        align: 'center',
                        formatter: function (value, row, index) {
                            return row['win'] + '/' + row['lose'];
                        },
                    },
                    {
                        field: 'raw_score',
                        title: '净积分',
                        width: '298',
                        align: 'center',
                    },
                    {
                        field: 'team_group',
                        title: '队伍分组',
                        width: '298',
                        align: 'center',
                        visible: false,
                    }
                ]
            });
            $('.spinner').css('display', 'none');

            if (json['group_info'] !== false) {
                var teamGroupForDiv = '';
                for (var h in json['group_info']['show_name']) {
                    var span;
                    if (parseInt(h) !== json['group_info']['show_name'].length - 1) {
                        span = '<span onclick="changeGroup($(this))">' + json['group_info']['show_name'][h] + '</span>' + ' / ';
                    } else {
                        span = '<span onclick="changeGroup($(this))">' + json['group_info']['show_name'][h] + '</span>';
                    }
                    teamGroupForDiv = teamGroupForDiv + span;
                    $('#team-group').html(teamGroupForDiv);
                }
            } else {
                $('#team-group').text('战队');
            }

            changeGroup = function (that) {
                var changeGroup = that.text();
                var index = that.index();
                $('#table').bootstrapTable('refreshOptions', {
                    searchText: changeGroup
                });
                var teamGroupForDiv = '';
                for (h in json['group_info']['show_name']) {
                    var span;
                    if (parseInt(h) !== json['group_info']['show_name'].length - 1) {
                        span = '<span onclick="changeGroup($(this))">' + json['group_info']['show_name'][h] + '</span>' + ' / ';
                    } else {
                        span = '<span onclick="changeGroup($(this))">' + json['group_info']['show_name'][h] + '</span>';
                    }
                    teamGroupForDiv = teamGroupForDiv + span;
                    $('#team-group').html(teamGroupForDiv);
                }
                $('#team-group span').css('color', '#c5c5c5');
                $('#team-group span:eq(' + index + ')').css('color', '#3e3e3e');
            };


        }
    });
});