let router = require('express').Router();
let syncCtrl = require('../controller/admin/sync');

router.get('/backup', syncCtrl.startBackUp);
router.get('/backup/stop', syncCtrl.stopBackup);
router.get('/backup/status', syncCtrl.statusBackup);
router.get('/baseInfo', syncCtrl.syncBaseInfo);
router.get('/', syncCtrl.remoteSync);

// router.get('/check', syncCtrl.checkSync);

module.exports = router;