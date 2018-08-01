const router = require('express').Router();
const bpCtrl = require('../controller/admin/bp');

router.get('/', bpCtrl.getBP);
router.post('/', bpCtrl.setBP);
router.get('/scene', bpCtrl.getBPScene);

module.exports = router;