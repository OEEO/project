let app = require('../server');
const port = process.env.PORT || '3002';

app.listen(port, function () {
    console.log(`listen the port ${port}`);
});

process.on('uncaughtException', function (err) {
    console.log(err);
    process.exit(1);
});