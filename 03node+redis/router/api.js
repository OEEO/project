let router = require('express').Router();
let eventProxy = require('../controller/api/event');

router.get('/event/list', eventProxy.getEventLine);
router.get('/gradient/event', eventProxy.getGradientEvent);

module.exports = router;