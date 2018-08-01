const CommonProxy = require('./index');
const R = require('../../utils/redisUtil');

class PlayersProxy extends CommonProxy {
    getPlayers(game, id) {
        return this.getData(game, id);
    }

    setPlayers(game, id, data) {
        return this
            .setData(game, id, data)
            .then(_ => {
                return R.zAdd('players-list', `${game}-${id}:players`, parseInt(Date.now() / 1000));
            });
    }

    getPlayerListLikeId(id, game) {
        return R
            .zRange('players-list', 0, -1)
            .then(list => {
                return list.filter(item => {
                    return !!(new RegExp(game + '-' + id + '\\d:players').exec(item));
                });
            });
    }
}

module.exports = new PlayersProxy('players');