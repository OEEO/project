const players = require('./players');
const bp = require('./bp');
const buff = require('./buff');
const process = require('./process');
const home = require('./home');
const sync = require('./sync');
const event = require('./event');
const api = require('./api');

module.exports = (app) => {
    app.use('/admin/players', players);
    app.use('/admin/bp', bp);
    app.use('/admin/buff', buff);
    app.use('/admin/process', process);
    app.use('/admin/home', home);
    app.use('/admin/sync', sync);
    app.use('/admin/event', event);
    app.use('/api', api);
};