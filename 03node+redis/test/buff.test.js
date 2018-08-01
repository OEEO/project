const expect = require('chai').expect;

const buffProxy = require('../proxy/redis/buff');

describe('test the buff tester', function () {
    describe('#getBuff', function () {

        before(function () {
            buffProxy
                .setBuff('kog', '2', {
                    'left_baojun': 1,
                    'right_baojun': 1,
                    'left_darkbaojun': 2,
                    'right_darkbaojun': 2,
                    'left_zhuzai': 3,
                    'right_zhuzai': 3
                });
        });

        it('should return Object and left_baojun equal 1', done => {
            buffProxy
                .getBuff('kog', '2')
                .then(r => {
                    // console.log(r);
                    expect(r).to.be.an('object');
                    expect(r.left_baojun).to.be.equal('1');
                    done();
                });
        });
        
    });

    describe('#setBuff', function () {
        it('should return OK', done => {
            buffProxy
                .setBuff('kog', '1', {
                    'left_baojun': 1,
                    'right_baojun': 1,
                    'left_darkbaojun': 2,
                    'right_darkbaojun': 2,
                    'left_zhuzai': 3,
                    'right_zhuzai': 3
                })
                .then(r => {
                    expect(r).to.be.equal('OK');
                    done();
                });
        });

        it('shuld return OK', done => {
            buffProxy
                .setBuff('kog', '3', [
                    'left_baojun', 2,
                    'right_baojun', 2,
                    'left_darkbaojun', 3,
                    'right_darkbaojun', 3,
                    'left_zhuzai', 4,
                    'right_zhuzai', 4
                ])
                .then(r => {
                    expect(r).to.be.equal('OK');
                    done();
                });
        });
    });
});