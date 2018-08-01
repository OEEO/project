const router = require('express').Router();
const playersCtrl = require('../controller/admin/players');

router.get('/', playersCtrl.getPlayersById);
router.post('/', playersCtrl.setPlayers);
router.get('/baseInfo', playersCtrl.getBaseInfo);

module.exports = router;