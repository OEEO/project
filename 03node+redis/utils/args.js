/**
 * Created by toreant on 2017/9/30.
 */
module.exports = function () {

    /**
     * 对控制台的输入进行对象化
     * 控制台输入：--key value --key3 --key2 value2
     * 返回
     * {
     *  key: value,
     *  key2: value2,
     *  key3: null
     * }
     */
    let argvs = process.argv;

    let result = {};
    let lastArgv = '';

    for (let i = 0, num = argvs.length; i < num; i++) {

        let reg = argvs[i].match(/^--([a-zA-Z0-9]+)/);
        if (reg) {
            result[reg[1]] = null;
            lastArgv = reg[1];
        } else if (lastArgv !== '') {
            result[lastArgv] = argvs[i];
        }
    }

    return result;
};
