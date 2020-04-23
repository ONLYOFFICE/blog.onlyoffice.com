var expect = require('chai').expect;
var assert = require('chai').assert;
var supertest = require('supertest');

var environments = {
  'prod':  'https://q.addthis.com/feeds',
  'test':  'http://api-test:8015/feeds'
};

var feedsUrl;
if (process.env.build_env && environments[process.env.build_env]) {
  feedsUrl = environments[process.env.build_env];
} else {
  feedsUrl = environments.test;
}

var request = supertest(feedsUrl);

describe('Feeds API tests for Wordpress', function() {
  this.timeout(5000);
  var pubId = 'atblog';
  var domain = 'www.addthis.com';

  it('confirms expected format', function(done) {
    request
    .get('/1.0/views2.json?pubid=' + pubId + '&domain=' + domain + '&limit=25')
    .expect(200)
    .end(function(err, res) {
      expect(res.body).to.be.a('array');
      done(err, res);
    });
  });
});