let router = require('express').Router();
let eventCtrl = require('../controller/admin/event');

router.get('/line', eventCtrl.getGoldsLine);
router.get('/near', eventCtrl.getNearEvents);
router.post('/set/line', eventCtrl.setEventGoldLine);
router.post('/del/line', eventCtrl.delEventGoldLine);
router.get('/list', eventCtrl.getEventList);

module.exports = router;