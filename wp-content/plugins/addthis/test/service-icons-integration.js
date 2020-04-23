var expect = require('chai').expect;
var assert = require('chai').assert;
var supertest = require('supertest');

var environments = {
  'prod':  'https://cache.addthiscdn.com/services',
  'test':  'http://cache-test.addthiscdn.com/services',
  'dev':   'http://cache-dev.addthiscdn.com/services'
};

var serviceIconUrl;
if (process.env.build_env && environments[process.env.build_env]) {
  serviceIconUrl = environments[process.env.build_env];
} else {
  serviceIconUrl = environments.test;
}

var request = supertest(serviceIconUrl);

describe('follow service listing endpoint', function() {
  var services = [];

  it('validates response from follow service listing endpoint', function(done) {
    request
    .get('/v1/follow.en.json')
    .expect(200)
    .end(function(err, res) {
      // expected by the backend in AddThisRegistrationFeature::getFollowServicesProxy
      expect(res.body).to.be.a('object');
      expect(res.body).to.have.property('data');

      services = res.body.data;
      // expected by the frontend in directives/followButtonDeconflictForm and directive/followServicePickerDrct
      expect(res.body.data).to.be.a('array');

      res.body.data.forEach(function(service) {
        //console.log(service);
        expect(service).to.be.a('object');
        expect(service.iconCode).to.be.a('string');
        expect(service.code).to.be.a('string');
        expect(service.name).to.be.a('string');

        expect(service.endpoints).to.be.a('object');
        expect(Object.keys(service.endpoints).length).to.be.above(0);
        Object.keys(service.endpoints).forEach(function(key) {
          expect(key).to.be.a('string');
          expect(service.endpoints[key]).to.be.a('string');
        });

        expect(service.prettyEndpoints).to.be.a('object');
        Object.keys(service.prettyEndpoints).forEach(function(key) {
          expect(key).to.be.a('string');
          expect(service.prettyEndpoints[key]).to.be.a('string');
        });
      });

      done(err, res);
    });
  });
});

describe('share service listing endpoint', function() {
  var services = [];

  it('validates response from share service listing endpoint', function(done) {
    request
    .get('/v1/sharing.en.json')
    .expect(200)
    .end(function(err, res) {
      // expected by the backend in AddThisRegistrationFeature::getShareServicesProxy
      expect(res.body).to.be.a('object');
      expect(res.body).to.have.property('data');

      services = res.body.data;
      // expected by the frontend in directive/shareServicePickerDrct
      expect(res.body.data).to.be.a('array');

      res.body.data.forEach(function(service) {
        expect(service).to.be.a('object');
        expect(service.icon).to.be.a('string');
        expect(service.code).to.be.a('string');
        expect(service.name).to.be.a('string');
      });

      done(err, res);
    });
  });
});