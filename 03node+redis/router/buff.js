const router = require('express').Router();
const buffCtrl = require('../controller/admin/buff');

router.get('/', buffCtrl.getBuff);
router.post('/', buffCtrl.setBuff);

module.exports = router;