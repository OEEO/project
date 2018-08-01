exports.getType = function (obj) {
    return Object.prototype.toString.call(obj);
};

exports.isArray = function (obj) {
    return Array.isArray(obj);
};

exports.isObject = function (obj) {
    return exports.getType(obj) === '[object Object]';
};

exports.isFunction = function (obj) {
    return exports.getType(obj) === '[object Function]';
};