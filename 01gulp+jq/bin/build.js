const fs = require('fs');
const path = require('path');
const spawn = require('child_process').spawn;
const Uglify = require('uglify-js');
let mkdirp = require('mkdirp');

const mkdirP = function (t, options) {
    return new Promise((resolve, reject) => {
        mkdirp(t, options, function (err, data) {
            err 
                ? reject(err)
                : resolve(data);
        });
    });
};

const DIST = process.env.NODE_ENV.trim() === 'production'
    ? 'dist'
    : 'pre';
  
const gameId = {
    'kog': '6',
    'lol': '2'
};    

const header = require('../src/template/header');

function writeFile(folder, filename, content) {
    
    let exit = fs.existsSync(path.resolve(__dirname, folder));

    folder = folder.split('/');

    if (!exit) {
        let _path = '';
        for (let i = 0, num = folder.length; i < num; i++) {
            _path += folder[i] + '/';
            if (_path === '../') {
                continue;
            }

            let _exits = fs.existsSync(path.resolve(__dirname, _path));

            if (_exits) {
                continue;
            }

            console.log(path.resolve(__dirname, _path));
            fs.mkdirSync(path.resolve(__dirname, _path));
        }
    }

    fs.writeFileSync(path.resolve(__dirname, `${folder.join('/')}/${filename}`), content, 'utf-8');
}

let uglifyConfig = {
    ie8: true,
};

if (DIST === 'dist') {
    uglifyConfig.compress = {
        drop_console: true
    };
} else {
    uglifyConfig.sourceMap = true;
}

async function uglify(_path, game) {
    let filePath = path.resolve(__dirname, _path);
    let content = fs.readFileSync(filePath, 'utf-8');

    if (DIST === 'dist') {
        content = content.replace('http://api.tmp.gameday.ren', 'http://matchweb.sports.qq.com/esports/dataAgent?uri=');
    }

    // 替换全局中的GAME_TYPE
    content = content.replace(/var GAME_TYPE(\s+)?=((\s+)?[^;]+);/, function (a, b, c) {
        return `var GAME_TYPE = '${gameId[game]}'`;
    });

    let uglifyContent = Uglify.minify(content, uglifyConfig);

    let tmpPath = _path.split('../')[1];

    let targetPath = path.resolve(__dirname, `../${DIST}/${game}/${tmpPath}`);
    let targetFolder = tmpPath.split('/');
    let t = '';
    for (let item of targetFolder) {
        if (item.indexOf('.js') !== -1) {
            continue;
        } 
        t += item + '/';
    }

    t = path.resolve(__dirname, `../${DIST}/${game}/${t}`);
    let exits = fs.existsSync(t);

    console.log(t, exits);

    if (!exits) {
        await mkdirP(t);
    } 
    fs.writeFileSync(targetPath, uglifyContent.code, 'utf-8');
    
    if (uglifyConfig.sourceMap) {
        fs.writeFileSync(`${targetPath}.map`, uglifyContent.map, 'utf-8');
    }
}

function readdirSync(_path) {
    try {
        let ls = fs.readdirSync(_path);
        return ls;
    } catch (exp) {
        return null;
    }
}

async function _buildJs(_path, game) {
    let ls = readdirSync(path.resolve(__dirname, _path));

    if (!ls) {
        return;
    }

    for (let item of ls) {
        _buildJs(`${_path}/${item}`, game);

        if (!/\.js$/.test(item)) {
            continue;
        }

        await uglify(`${_path}/${item}`, game);
    }
}

async function buildJs(game) {
    let _path = '../src/script';

    await _buildJs(_path, game);
}

function _isFolder(src) {
    let stat = fs.statSync(path.resolve(__dirname, src));
    return stat.isDirectory();
}

function _exits(src) {
    return fs.existsSync(path.resolve(__dirname, src));
}

async function copyFolder(src, target) {

    let i = _isFolder(src);
    let e = _exits(target);
    console.log(i, e);
    if (i && !e) {
        console.log('不存在');
        await mkdirP(path.resolve(__dirname, target));
    }

    let ls = readdirSync(path.resolve(__dirname, src));

    if (!ls) {
        return;
    }

    for (let item of ls) {
        copyFolder(`${src}/${item}`, `${target}/${item}`);

        if (!_isFolder(`${src}/${item}`)) {
            fs.copyFileSync(path.resolve(__dirname, `${src}/${item}`), path.resolve(__dirname, `${target}/${item}`));
        }
    }
}

async function build() {

    let files = fs.readdirSync(path.resolve(__dirname, '../cover'));
    for (let file of files) {
        if (!/\.html$/.test(file)) {
            continue;
        }

        let headerHTML = header(file.split('.')[0]);
        let sourceHTML = fs.readFileSync(path.resolve(__dirname, `../cover/${file}`), 'utf-8');
        sourceHTML = sourceHTML.replace('../tmp', '../src');

        sourceHTML = sourceHTML.replace('<!-- built html will be auto injected -->', headerHTML);
        sourceHTML = sourceHTML.replace(/{VERSION_TIME}/g, Date.now());

        writeFile(`../${DIST}/kog/cover`, file, sourceHTML);
        writeFile(`../${DIST}/lol/cover`, file, sourceHTML);
    }

    await buildJs('kog');
    await buildJs('lol');

    
    await copyFolder('../src/css', `../${DIST}/lol/src/css`);
    await copyFolder('../src/css', `../${DIST}/kog/src/css`);
    await copyFolder('../src/assets', `../${DIST}/lol/src/assets`);
    await copyFolder('../src/assets', `../${DIST}/kog/src/assets`);
    await copyFolder('../public', `../${DIST}/lol/public`);
    await copyFolder('../public', `../${DIST}/kog/public`);
}

// copyFolder('../src/assets', '../pre/lol/src/assets');
build();
// buildJs();