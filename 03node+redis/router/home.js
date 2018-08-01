let router = require('express').Router();
const homeCtrl = require('../controller/admin/home');

router.get('/players', homeCtrl.getPlayers);
router.get('/games', homeCtrl.getGameList);
router.get('/list', homeCtrl.getTypeList);
router.get('/skill', homeCtrl.getSkill);

module.exports = router;