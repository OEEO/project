var GAME_TYPE = '6';
var VERSION = '1.1.1';

require.config({
    baseUrl: '../src/script', // TODO, build script
    paths: {
        'jquery': 'lib/jquery.1_12_4.min',
        'lazyload': 'lib/jquery.lazyload',
        'utils': 'utils',
        'datePicker': 'extend/date-picker',
        'collapse': 'extend/collapse',
        'url': 'url',
        'rune': 'rune'
    },
    urlArgs: function (id, url) {
        return url.indexOf('?') !== -1
            ? '&v=' + VERSION
            : '?v=' + VERSION;
    }
});



define(['jquery', 'utils', 'url'], function ($, utils, URL) {

    if ($('.path-selector').length === 0) {
        return;
    }

    var $teamCollapseList = $('#team-collapse-list');

    var indexGameId = GAME_TYPE;
    var game = 'lol';
    var gameId = {
        'lol': 2,
        'kog': 6
    };

    var show = false;
    var $teamCollapse = $('#team-collapse');
    
    var lolTeamList = [];
    var kogTeamList = [];

    function getTeamList(game, callback) {

        // 如果缓存内容中已经有了，不再进行请求
        if (game === 'kog' && kogTeamList.length !== 0) {
            callback(null);
            return;
        } else if (game === 'lol' && lolTeamList.length !== 0) {
            callback(null);
            return;
        }

        var url = URL.GET_TEAM_LIST.replace(/{game}/, game);
 
        utils.getScript(url, 'data', function (err, data) {
            if (err) {
                console.error(err);
                callback(err);
            } else {
                if (game === 'kog') {
                    kogTeamList = data;
                } else {
                    lolTeamList = data;
                }

                callback(null);
            }
        });
    }

    function buildTeamList(list) {
        var fragment = document.createDocumentFragment();
        for (var i = 0, num = list.length; i < num; i++) {
            var $wrap = document.createElement('div');
            $wrap.className = 'team-collapse-wrap';

            var $wrapHeader = document.createElement('div');
            $wrapHeader.className = 'team-collapse-wrap_header';
            $wrapHeader.innerHTML = '<div class="pink-block"><span class="star"></span><span class="group-name">' + list[i].name + '</span></div>';

            var $wrapBody = document.createElement('div');
            $wrapBody.className = 'team-collapse-wrap_body';

            for (var j = 0, len = list[i].list.length; j < len; j++) {
                var listItem = list[i].list[j];
                var item = document.createElement('a');
                item.className = 'team-item';
                var img = document.createElement('img');
                img.setAttribute('src', listItem.img);
                var span = document.createElement('span');
                span.innerText = listItem.name;

                item.appendChild(img);
                item.appendChild(span);
                item.setAttribute('target', '_blank');
                item.setAttribute('href', '../../' + game + '/cover/team.html?gameType=' + gameId[game] + '&id=' + listItem.id); // TODO

                $wrapBody.appendChild(item);
            }

            $wrap.appendChild($wrapHeader);
            $wrap.appendChild($wrapBody);

            fragment.appendChild($wrap);
        }

        return fragment;
    }

    

    function toggleGame(game) {
        getTeamList(game, function (err) {

            var fragment;
    
            if (err) {
                //
            } else if (game === 'kog') {
                fragment = buildTeamList(kogTeamList);
                $teamCollapseList.empty();
                $teamCollapseList[0].appendChild(fragment);
            } else {
                fragment = buildTeamList(lolTeamList);
                $teamCollapseList.empty();
                $teamCollapseList[0].appendChild(fragment);
            }
        });
    }


    toggleGame(game);  // 初始化

    $('#team-collapse-selector').click(function (e) {
        e.stopPropagation();
        $teamCollapse.fadeToggle();
        show = !show;
        $(document).on('click', function (e) {
            e.stopPropagation();
            var target = e.target;
            console.log($(target).closest($teamCollapse).length, show);
            if ($(target).closest($teamCollapse).length === 0 && show) {
                $teamCollapse.hide();
                show = false;
            }
        });
    });

    $('[data-game]').click(function () {
        game = $(this).data('game');
        $(this).addClass('active');
        $(this).siblings('[data-game]').removeClass('active');
        toggleGame(game);
    });
});