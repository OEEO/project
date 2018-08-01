const Express = require('express');
const bodyParser = require('body-parser');
const cookieParser = require('cookie-parser');
const path = require('path');

const access = require('./middleware/access');
const router = require('./router');

require('./bin/clear');

let app = new Express();

app.use(access);
app.use(Express.static(path.resolve(__dirname, 'public')));
app.use(bodyParser.json());
app.use(bodyParser.urlencoded({ extended: false }));
app.use(cookieParser('esport', {
    httpOnly: true
}));

router(app);

module.exports = app;

