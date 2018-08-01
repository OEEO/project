const spawn = require('child_process').spawn;
const { exec, redisConf } = require('../config');

class Process {
    constructor(game, id) {
        this.game = game;
        this.id = id;
        this.status = 'idel';
    }

    setStatus(status) {
        this.status = status;
    }
}

class ProcessProxy {
    constructor() {
        this.process = {};
    }

    generateProcess(option) {

        let execOption = [
            'live',
            '-g', option.game,
            '-mid', `${option.id}`,
            '-i', option.input,
            '-l', exec.libPath,
            '-h', redisConf.host,
            '-P', redisConf.port,
            '-p', redisConf.password,
            '-t', exec.mode
        ];

        let child = spawn(exec.exec, execOption);

        let key = `${option.game}-${option.id}`;

        let _process = new Process(option.game, key);

        this.process[key] = {
            process: _process,
            execProcess: child,
            pid: 0,
            start_time: parseInt(Date.now() / 1000),
            errors: [],
            warnings: []
        };
        
        if (child.pid) {
            this.process[key].pid = child.pid;
            this.process[key].process.setStatus('running');
        }

        child.stdout.on('data', data => {
            console.log(`stdout: %s for ${data.toString()}`, child.pid);
        });

        child.stderr.on('data', data => {
            console.log(`stderr: ${data.toString()}`);
        });

        child.on('exit', _ => {
            console.log('exit std: %s', child.pid);
        });

        child.on('error', err => {
            this.process[key].process.setStatus('error');
            this.process[key].errors.push(err);
        });
    }

    getProcesses() {
        let result = [];

        for (let key in this.process) {
            let _process = this.process[key];
            result.push({
                game: _process.process.game,
                id: _process.process.id.match(/(\d+)/)[1],
                status: _process.process.status,
                pid: _process.pid,
                startTime: _process.start_time
            });
        }

        return result;
    }

    stopProcess(game, id) {
        let key = `${game}-${id}`;

        let _process = this.process[key];

        if (!_process || !_process.execProcess) {
            throw new Error('no find process');
        } else {
            _process.process.setStatus('exit');
            _process.execProcess.kill('SIGINT');
        }
    }

    delProcess(game, id) {
        this.stopProcess(game, id);
        
        let key = `${game}-${id}`;

        delete this.process[key];
    }
}

module.exports = new ProcessProxy();