define(function () {
    var DOMAIN = 'http://api.tmp.gameday.ren';

    return {
        GET_LEAGUE_LIST: DOMAIN + '/data/v2/{game}/league_list.js',
        GET_ACTIVE_DATE: DOMAIN + '/data/v2/{game}/calender/{e_index}{/type_id}/{date}.js',
        GET_KOG_PLAYER_RANK: DOMAIN + '/data/v2/kog/player/{e_index}/rank.js',
        GET_LOL_PLAYER_RANK: DOMAIN + '/data/v2/lol/player/{e_index}/rank.js',

        GET_KOG_TEAM_RANK: DOMAIN + '/data/v2/kog/team/{e_index}/getRank.js',
        GET_LOL_TEAM_RANK: DOMAIN + '/data/v2/lol/team/{e_index}/getRank.js',

        GET_KOG_POINTS_RANK: DOMAIN + '/data/v2/kog/teamScore/{e_index}/getRank.js',
        GET_LOL_POINTS_RANK: DOMAIN + '/data/v2/lol/teamScore/{e_index}/getRank.js',

        GET_KOG_HERO_RANK: DOMAIN + '/data/v2/kog/hero/{e_index}/getRank.js',
        GET_LOL_HERO_RANK: DOMAIN + '/data/v2/lol/hero/{e_index}/getRank.js',
        GET_SCHEDULE: DOMAIN + '/data/v2/{game}/schedule{/relation}/{e_index}/{page}.js',
        GET_TEAM_INFO: DOMAIN + '/data/v2/{game}/team/{id}',
        GET_TEAM_HISTORY: DOMAIN + '/data/v2/{game}/team/history/{id}/{e_index}{/relation}/{page}.js',
        GET_PLAYER_INFO: DOMAIN + '/data/v2/{game}/player/{id}.js',
        GET_PLAYER_HISTORY: DOMAIN + '/data/v2/{game}/player/history/{id}/{e_index}{/relation}/{page}.js',

        GET_TEAM_LIST: DOMAIN + '/data/v2/{game}/teamList.js',

        GET_GENERAL: DOMAIN + '/data/v2/{game}/general/{id}.js',
        GET_BEFORE: DOMAIN + '/data/v2/{game}/before/{id}.js',
        GET_AFTER: DOMAIN + '/data/v2/{game}/after/{id}/{index}.js',
        GET_RUNE: DOMAIN + '/data/v2/{game}/rune/{id}/{index}.js',
    };
});
