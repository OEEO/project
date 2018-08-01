let router = require('express').Router();
const processCtrl = require('../controller/admin/process');

router.post('/', processCtrl.generateProcess);
router.get('/list', processCtrl.getProcess);
router.get('/stop', processCtrl.stopProcess);
router.get('/del', processCtrl.delProcess);

module.exports = router;