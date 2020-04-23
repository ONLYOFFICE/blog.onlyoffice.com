var expect = require('chai').expect;
var assert = require('chai').assert;
var supertest = require('supertest');

var environments = {
  'prod':         'https://www.addthis.com/darkseid',
  'uat':          'https://www-uat.addthis.com/darkseid',
  'test':         'https://www-test.addthis.com/darkseid',
  'dev':          'https://www-dev.addthis.com/darkseid',
  'local':        'http://www-local.addthis.com:8019/darkseid',
  'jenkinsdev':   'http://ha-dev-www.clearspring.local/darkseid',
  'jenkinstest':  'http://ha-test-www.clearspring.local/darkseid'
};

var darkseidUrl;
if (process.env.build_env && environments[process.env.build_env]) {
  darkseidUrl = environments[process.env.build_env];
} else {
  darkseidUrl = environments.test;
  // darkseidUrl = 'http://localhost:8019/darkseid'
}

var request = supertest(darkseidUrl);

var json = 'application/json';
var pco = 'wpsl';
var version = '2.0.0';
// the version of wpsl at which darkseid stops transforming boost configs for backward compatibility
var followLegacyPcoLastVersion = '3.1.0';
var recommendedLegacyPcoLastVersion = '3.1.0';
var shareLegacyPcoLastVersion = '2.0.0';
var shareConsolidatedVersion = '3.1.0';
var newVersion = 'x.x.x';

var getNewProfile = function(type, callback) {
  var username = 'julkaaddthis+integrationtests@gmail.com';
  var password = '1234'
  var goodBasicAuth = 'Basic ' + new Buffer(username + ':' + password).toString('base64');
  var date = new Date();
  var dateString = date.getTime();

  var body = {
    'type': type,
    'name': 'integration test ' + dateString
  };

  request
  .post('/publisher')
  .type('json')
  .set('Authorization', goodBasicAuth)
  .set('Content-Type', json)
  .set('Accept', json)
  .send(body)
  .end(function(err, res) {
    pubId = res.body.pubId;
    var cuidish = pubId.replace(/^ra-/, '');
    body = { 'name' : 'Integration Test (created '+ dateString +')' };

    request
    .post('/publisher/' + cuidish + '/application')
    .type('json')
    .set('Authorization', goodBasicAuth)
    .set('Content-Type', json)
    .set('Accept', json)
    .send(body)
    .end(function(err, res) {
      var newApiKeyInfo = res.body.pop();
      apiKey = newApiKeyInfo.cuid;

      callback(pubId, apiKey);
    });
  });
};

var confirmToolSettingsMatch = function(expectedSettings, returnedSettings) {
  Object.keys(expectedSettings).forEach(function(field) {
    assert.property(returnedSettings, field);
    if (Array.isArray(expectedSettings[field]) && field ==='services') {
      expect(returnedSettings[field]).to.deep.include.members(expectedSettings[field]);
    } else if (Array.isArray(expectedSettings[field])) {
      assert.equal(expectedSettings[field].length, returnedSettings[field].length);
      assert.deepEqual(expectedSettings[field], returnedSettings[field]);
    } else {
      assert.deepEqual(expectedSettings[field], returnedSettings[field]);
    }
  });
};

var checkBoostSettingsFormat = function(boostSettings, desiredToolPco) {
  var desiredToolSettings;

  expect(boostSettings).to.be.a('object');
  expect(boostSettings.subscription).to.be.a('object');
  expect(boostSettings.subscription.edition).to.be.a('string');
  expect(boostSettings.templates).to.be.a('array');
  Object.keys(boostSettings.templates).forEach(function(key) {
    expect(boostSettings.templates[key]).to.be.a('object');
    if (boostSettings.templates[key].id === '_default') {
      expect(boostSettings.templates[key].widgets).to.be.a('array');
      boostSettings.templates[key].widgets.forEach(function(toolSettings) {
        expect(toolSettings).to.be.a('object');
        if ((typeof desiredToolPco === 'string') &&
          desiredToolPco === toolSettings.id
        ) {
          desiredToolSettings = toolSettings;
        }
      });
    }
  });

  return desiredToolSettings;
};

var getWidgetById = function(boostSettings, widgetId) {
    var desiredToolSettings;

    boostSettings.templates[0].widgets.forEach(function(toolSettings) {
      if (widgetId === toolSettings.widgetId) {
        desiredToolSettings = toolSettings;
      }
    });

    return desiredToolSettings;
}

describe('Darkseid ping endpoint', function() {
  it('has expected format', function(done) {
    request
    .get('/test/ping')
    .set('Accept', json)
    .expect(200)
    .end(function(err, res) {
      expect(res.body).to.be.a('object');
      expect(res.body.hello).to.equal('world');
      done(err, res);
    });
  });
});

describe('WordPress compatibility check endpoint', function() {
  this.timeout(15000);
  var unsupportedPluginVersions = {
    // Website Tools by AddThis
    'wpwt': [],
    // Follow Buttons by AddThis
    'wpf': [],
    // Related Posts by AddThis
    'wprp': [],
    // Smart Layers by AddThis
    'wpsl': [],
    // Share Buttons by AddThis
    'wpp': []
  };

  var supportedPluginVersions = {
    // Website Tools by AddThis
    'wpwt': ['1.0.0', '1.0.1', '1.0.2', '1.1.0', '1.1.1', '1.1.2', '2.0.0', '2.0.1', '2.0.2', '3.0.0', '3.0.1'],
    // Follow Buttons by AddThis
    'wpf': ['2.0.0', '2.0.1', '2.0.2', '3.0.0', '4.0.0', '4.0.1'],
    // Related Posts by AddThis
    'wprp': ['1.0.0', '2.0.0', '2.0.1'],
    // Smart Layers by AddThis
    'wpsl': ['2.0.0', '3.0.0', '3.0.1'],
    // Share Buttons by AddThis
    'wpp': ['6.0.0']
  };

  // make sure it is returning a good results for all supported versions
  Object.keys(supportedPluginVersions).forEach(function(pluginPco) {
    supportedPluginVersions[pluginPco].forEach(function(version) {
      it(pluginPco + '-' + version + ' is supported', function(done) {
        request
        .get('/plugins/'+pluginPco+'/v/'+version+'/check')
        .expect(204, done);
      });
    });
  });
  // should also check for expected result for unsupported versions, but there are none yet
});

describe('WordPress registration process on existing account', function() {
  this.timeout(15000);
  var pubIdsOnProfile;
  var wptypePubId;
  var defaultPubId;
  var wpApiKey;
  var defaultApiKey;
  var username = 'julkaaddthis+integrationtests@gmail.com';
  var password = '1234'
  var goodBasicAuth = 'Basic ' + new Buffer(username + ':' + password).toString('base64');
  var badBasicAuth = 'Basic ' + new Buffer(username + ':' + password + '9876').toString('base64');

  it('validates good login', function(done) {
    request
    .get('/user')
    .set('Authorization', goodBasicAuth)
    .set('Accept', json)
    .expect(200)
    .end(function(err, res) {
      expect(res.body).to.be.a('object');
      expect(res.body.email).to.equal(username);
      done(err, res);
    });
  });

  it('rejects bad login', function(done) {
    request
    .get('/user')
    .set('Authorization', badBasicAuth)
    .set('Accept', json)
    .expect(401, done);
  });

  it('retrieves pubids for account', function(done) {
    request
    .get('/publisher')
    .set('Authorization', goodBasicAuth)
    .set('Accept', json)
    .expect(200)
    .end(function(err, res) {
      pubIdsOnProfile = res.body;
      expect(pubIdsOnProfile).to.be.a('array');
      expect(pubIdsOnProfile.length).to.be.above(0);
      done(err, res);
    });
  });

  it('rejects request for pubids for account with bad basic auth', function(done) {
    request
    .get('/publisher')
    .set('Authorization', badBasicAuth)
    .set('Accept', json)
    .expect(401, done);
  });

  it('found pubid named wptype', function(done) {
    pubIdsOnProfile.forEach(function(pubIdInfo) {
      if (pubIdInfo.name === 'wptype') {
        wptypePubId = pubIdInfo.pubId;
      }
    }, this);
    expect(wptypePubId).to.be.a('string');
    done();
  });

  it('validates wp pubid is real and type is wp', function(done) {
    request
    .get('/plugins/'+pco+'/v/'+version+'/site/'+wptypePubId)
    .expect(200)
    .end(function(err, res) {
      expect(res.body).to.be.a('object');
      expect(res.body.type).to.equal('wp');
      done(err, res);
    });
  });

  it('creates a new api key for pubid with wp type', function(done) {
    var cuidish = wptypePubId.replace(/^ra-/, '');
    var date = new Date();
    var dateString = date.getTime();
    var body = { 'name' : 'Integration Test (created '+ dateString +')' };

    request
    .post('/publisher/' + cuidish + '/application')
    .type('json')
    .set('Authorization', goodBasicAuth)
    .set('Content-Type', json)
    .set('Accept', json)
    .send(body)
    .expect(200)
    .end(function(err, res) {
      expect(res.body).to.be.a('array');
      var newApiKeyInfo = res.body.pop();
      expect(newApiKeyInfo).to.be.a('object');
      expect(newApiKeyInfo.cuid).to.be.a('string');
      wpApiKey = newApiKeyInfo.cuid;
      done(err, res);
    });
  });

  it('found pubid named My Site', function(done) {
    pubIdsOnProfile.forEach(function(pubIdInfo) {
      if (pubIdInfo.name === 'My Site') {
        defaultPubId = pubIdInfo.pubId;
      }
    }, this);
    expect(defaultPubId).to.be.a('string');
    done();
  });

  it('validates default pubid is real and type is none', function(done) {
    request
    .get('/plugins/'+pco+'/v/'+version+'/site/'+defaultPubId)
    .expect(200)
    .end(function(err, res) {
      expect(res.body).to.be.a('object');
      expect(res.body.type).to.equal('none');
      done(err, res);
    });
  });

  it('creates an new api key for pubid with none type', function(done) {
    var cuidish = defaultPubId.replace(/^ra-/, '');
    var date = new Date();
    var dateString = date.getTime();
    var body = { 'name' : 'Integration Test (created '+ dateString +')' };

    request
    .post('/publisher/' + cuidish + '/application')
    .type('json')
    .set('Authorization', goodBasicAuth)
    .set('Content-Type', json)
    .set('Accept', json)
    .send(body)
    .expect(200)
    .end(function(err, res) {
      expect(res.body).to.be.a('array');
      var newApiKeyInfo = res.body.pop();
      expect(newApiKeyInfo).to.be.a('object');
      expect(newApiKeyInfo.cuid).to.be.a('string');
      defaultApiKey = newApiKeyInfo.cuid;
      done(err, res);
    });
  });

  it('changes default pubid to type wp', function(done) {
    var type = 'wp';
    var body = { 'type' : type };

    request
    .put('/publisher/' + defaultPubId + '/profile-type')
    .type('json')
    .set('Authorization', defaultApiKey)
    .set('Content-Type', json)
    .set('Accept', json)
    .send(body)
    .expect(200)
    .end(function(err, res) {
      expect(res.body).to.be.a('object');
      expect(res.body.type).to.equal(type);
      done(err, res);
    });
  });

  it('validates default pubid is real and type is wp now', function(done) {
    request
    .get('/plugins/'+pco+'/v/'+version+'/site/'+defaultPubId)
    .expect(200)
    .end(function(err, res) {
      expect(res.body).to.be.a('object');
      expect(res.body.type).to.equal('wp');
      done(err, res);
    });
  });

  it('changes default pubid to type none', function(done) {
    var type = 'none';
    var body = { 'type' : type };

    request
    .put('/publisher/' + defaultPubId + '/profile-type')
    .type('json')
    .set('Authorization', defaultApiKey)
    .set('Content-Type', json)
    .set('Accept', json)
    .send(body)
    .expect(200)
    .end(function(err, res) {
      expect(res.body).to.be.a('object');
      expect(res.body.type).to.equal(type);
      done(err, res);
    });
  });

  it('validates default pubid is real and type is none again', function(done) {
    request
    .get('/plugins/'+pco+'/v/'+version+'/site/'+defaultPubId)
    .expect(200)
    .end(function(err, res) {
      expect(res.body).to.be.a('object');
      expect(res.body.type).to.equal('none');
      done(err, res);
    });
  });

  it('validates a good api key in Authorization header', function(done) {
    var cuidish = wptypePubId.replace(/^ra-/, '');
    request
    .get('/publisher/' + cuidish + '/application')
    .set('Authorization', wpApiKey)
    .set('Accept', json)
    .expect(200)
    .end(function(err, res) {
      expect(res.body).to.be.a('array');
      var match = false;
      res.body.forEach(function(apiKeyInfo) {
        expect(apiKeyInfo).to.be.a('object');
        expect(apiKeyInfo.cuid).to.be.a('string');
        if (apiKeyInfo.cuid === wpApiKey) { match = true; }
      });
      expect(match).to.equal(true);
      done(err, res);
    });
  });

  it('rejects a bad api key in Authorization header', function(done) {
    var cuidish = wptypePubId.replace(/^ra-/, '');

    request
    .get('/publisher/' + cuidish + '/application')
    .set('Authorization', 'gibberish_for_integration_test')
    .set('Accept', json)
    .expect(403, done);
  });

  // not yet used in production
  // k8s will drop X_Api_Key header, we should replace it with dash.
  // comment out this test until we fix the header.
  // it('validates a good api key in X_Api_Key header', function(done) {
  //   var cuidish = wptypePubId.replace(/^ra-/, '');
  //   request
  //   .get('/publisher/' + cuidish + '/application')
  //   .set('X_Api_Key', wpApiKey)
  //   .set('Accept', json)
  //   .expect(200)
  //   .end(function(err, res) {
  //     expect(res.body).to.be.a('array');
  //     var match = false;
  //     res.body.forEach(function(apiKeyInfo) {
  //       expect(apiKeyInfo).to.be.a('object');
  //       expect(apiKeyInfo.cuid).to.be.a('string');
  //       if (apiKeyInfo.cuid === wpApiKey) { match = true; }
  //     });
  //     expect(match).to.equal(true);
  //     done(err, res);
  //   });
  // });

  // not yet used in production
  // k8s will drop X_Api_Key header, we should replace it with dash.
  // comment out this test until we fix the header.
  // it('rejects a bad api key in X_Api_Key header', function(done) {
  //   var cuidish = wptypePubId.replace(/^ra-/, '');

  //   request
  //   .get('/publisher/' + cuidish + '/application')
  //   .set('X_Api_Key', 'gibberish_for_integration_test')
  //   .set('Accept', json)
  //   .expect(404, done);
  // });
});

describe('WordPress registration process on new account ', function() {
  this.timeout(15000);
  var date = new Date();
  var dateString = date.getTime();
  var username = 'julkaaddthis+integrationtests'+dateString+'@gmail.com';
  var password = '1234'
  var goodBasicAuth = 'Basic ' + new Buffer(username + ':' + password).toString('base64');
  var badBasicAuth = 'Basic ' + new Buffer(username + ':' + password + '9876').toString('base64');

  var pluginPco = 'wpwt';
  var pubIdsOnProfile;
  var pubId;

  it('creates a new account', function(done) {
    this.retries(4);

    var body = {
      'username': username,
      'email': username,
      'plainPassword': password,
      'country': 'US',
      'subscribedToNewsletter': true,
      'profileType': 'wp',
      'source': pluginPco,
    };

    request
    .post('/account/register-user')
    .type('json')
    .set('Content-Type', json)
    .set('Accept', json)
    .send(body)
    .expect(200)
    .end(function(err, res) {
      expect(res.body).to.be.a('object');
      expect(res.body.id).to.be.a('string');
      done(err, res);
    });
  });

  it('validates good login', function(done) {
    request
    .get('/user')
    .set('Authorization', goodBasicAuth)
    .set('Accept', json)
    .expect(200)
    .end(function(err, res) {
      expect(res.body).to.be.a('object');
      expect(res.body.email).to.equal(username);
      done(err, res);
    });
  });

  it('rejects bad login', function(done) {
    request
    .get('/user')
    .set('Authorization', badBasicAuth)
    .set('Accept', json)
    .expect(401, done);
  });

  it('retrieves pubids for account', function(done) {
    request
    .get('/publisher')
    .set('Authorization', goodBasicAuth)
    .set('Accept', json)
    .expect(200)
    .end(function(err, res) {
      pubIdsOnProfile = res.body;
      expect(pubIdsOnProfile).to.be.a('array');
      expect(pubIdsOnProfile.length).to.be.above(0);
      done(err, res);
    });
  });

  it('found pubid named My Site', function(done) {
    pubIdsOnProfile.forEach(function(pubIdInfo) {
      if (pubIdInfo.name === 'My Site') {
        pubId = pubIdInfo.pubId;
      }
    }, this);
    expect(pubId).to.be.a('string');
    done();
  });

  it.skip('validates default pubid is real and type is wp (T64290)', function(done) {
    request
    .get('/plugins/'+pco+'/v/'+version+'/site/'+pubId)
    .expect(200)
    .end(function(err, res) {
      expect(res.body).to.be.a('object');
      expect(res.body.type).to.equal('wp');
      done(err, res);
    });
  });

  it('creates an new api key for default pubid', function(done) {
    var cuidish = pubId.replace(/^ra-/, '');
    var date = new Date();
    var dateString = date.getTime();
    var body = { 'name' : 'Integration Test (created '+ dateString +')' };

    request
    .post('/publisher/' + cuidish + '/application')
    .type('json')
    .set('Authorization', goodBasicAuth)
    .set('Content-Type', json)
    .set('Accept', json)
    .send(body)
    .expect(200)
    .end(function(err, res) {
      expect(res.body).to.be.a('array');
      var newApiKeyInfo = res.body.pop();
      expect(newApiKeyInfo).to.be.a('object');
      expect(newApiKeyInfo.cuid).to.be.a('string');
      apiKey = newApiKeyInfo.cuid;
      done(err, res);
    });
  });

  it('validates a good api key in Authorization header', function(done) {
    var cuidish = pubId.replace(/^ra-/, '');

    request
    .get('/publisher/' + cuidish + '/application')
    .set('Authorization', apiKey)
    .set('Accept', json)
    .expect(200)
    .end(function(err, res) {
      expect(res.body).to.be.a('array');
      var match = false;
      res.body.forEach(function(apiKeyInfo) {
        expect(apiKeyInfo).to.be.a('object');
        expect(apiKeyInfo.cuid).to.be.a('string');
        if (apiKeyInfo.cuid === apiKey) { match = true; }
      });
      expect(match).to.equal(true);
      done(err, res);
    });
  });

  it('rejects a bad api key in Authorization header', function(done) {
    var cuidish = pubId.replace(/^ra-/, '');

    request
    .get('/publisher/' + cuidish + '/application')
    .set('Authorization', 'gibberish_for_integration_test')
    .set('Accept', json)
    .expect(403, done);
  });

  // not yet used in production
  // k8s will drop X_Api_Key header, we should replace it with dash.
  // comment out this test until we fix the header.  it('validates a good api key in X_Api_Key header', function(done) {
  // it('validates a good api key in X_Api_Key header', function(done) {
  //   var cuidish = pubId.replace(/^ra-/, '');

  //   request
  //   .get('/publisher/' + cuidish + '/application')
  //   .set('X_Api_Key', apiKey)
  //   .set('Accept', json)
  //   .expect(200)
  //   .end(function(err, res) {
  //     expect(res.body).to.be.a('array');
  //     var match = false;
  //     res.body.forEach(function(apiKeyInfo) {
  //       expect(apiKeyInfo).to.be.a('object');
  //       expect(apiKeyInfo.cuid).to.be.a('string');
  //       if (apiKeyInfo.cuid === apiKey) { match = true; }
  //     });
  //     expect(match).to.equal(true);
  //     done(err, res);
  //   });
  // });

  // not yet used in production
  // k8s will drop X_Api_Key header, we should replace it with dash.
  // comment out this test until we fix the header.
  // it('rejects a bad api key in X_Api_Key header', function(done) {
  //   var cuidish = pubId.replace(/^ra-/, '');

  //   request
  //   .get('/publisher/' + cuidish + '/application')
  //   .set('X_Api_Key', 'gibberish_for_integration_test')
  //   .set('Accept', json)
  //   .expect(404, done);
  // });

  var createdPubId;
  it('creates a new profile of type wp', function(done) {
    var date = new Date();
    var dateString = date.getTime();

    var body = {
      'type': 'wp',
      'name': 'test ' + dateString
    };

    request
    .post('/publisher')
    .type('json')
    .set('Authorization', goodBasicAuth)
    .set('Content-Type', json)
    .set('Accept', json)
    .send(body)
    .expect(200)
    .end(function(err, res) {
      expect(res.body).to.be.a('object');
      expect(res.body.pubId).to.be.a('string');
      createdPubId = res.body.pubId;
      done(err, res);
    });
  });

  it('validates new pubid is type wp', function(done) {
    request
    .get('/plugins/'+pco+'/v/'+version+'/site/'+createdPubId)
    .expect(200)
    .end(function(err, res) {
      expect(res.body).to.be.a('object');
      expect(res.body.type).to.equal('wp');
      done(err, res);
    });
  });
});

describe('WordPress related post promoted URL functionality ', function() {
  this.timeout(15000);
  var toolPco = 'smlre';
  var testUrl1 = 'https://addthis.com';
  var testUrl2 = 'http://example.com';
  var pubId;
  var apiKey;

  it('receives empty object of promote URL campaigns for new pubid', function(done) {
    getNewProfile('wp', function(aPubId, anApiKey) {
      pubId = aPubId;
      apiKey = anApiKey;
      request
      .get('/wordpress/site/'+pubId+'/campaigns')
      .set('Authorization', apiKey)
      .set('Content-Type', json)
      .set('Accept', json)
      .expect(200)
      .end(function(err, res) {
        expect(res.body).to.be.a('object');
        expect(Object.keys(res.body)).to.be.empty;
        done(err, res);
      });
    });
  });

  it('promotes first URL for a related posts tool ' + toolPco, function(done) {
    request
    .post('/wordpress/site/'+pubId+'/campaigns/'+toolPco)
    .set('Authorization', apiKey)
    .set('Content-Type', json)
    .set('Accept', json)
    .send([testUrl1])
    .expect(200)
    .end(function(err, res) {
      expect(res.body).to.be.a('object');
      expect(res.body[toolPco]).to.be.a('array');
      assert.equal(res.body[toolPco].length, 1);
      var url = res.body[toolPco].pop();
      assert.equal(url, testUrl1);
      done(err, res);
    });
  });

  it('receives object with the first promoted URL campaign for tool ' + toolPco, function(done) {
    request
    .get('/wordpress/site/'+pubId+'/campaigns')
    .set('Authorization', apiKey)
    .set('Content-Type', json)
    .set('Accept', json)
    .expect(200)
    .end(function(err, res) {
      expect(res.body).to.be.a('object');
      expect(res.body[toolPco]).to.be.a('array');
      assert.equal(res.body[toolPco].length, 1);
      var url = res.body[toolPco].pop();
      assert.equal(url, testUrl1);
      done(err, res);
    });
  });

  it('change promoted URL campaign to use second url for tool ' + toolPco, function(done) {
    request
    .post('/wordpress/site/'+pubId+'/campaigns/'+toolPco)
    .set('Authorization', apiKey)
    .set('Content-Type', json)
    .set('Accept', json)
    .send([testUrl2])
    .expect(200)
    .end(function(err, res) {
      expect(res.body).to.be.a('object');
      expect(res.body[toolPco]).to.be.a('array');
      assert.equal(res.body[toolPco].length, 1);
      var url = res.body[toolPco].pop();
      assert.equal(url, testUrl2);
      done(err, res);
    });
  });

  it('receives object with only the second URL in the promoted URL campaign', function(done) {
    request
    .get('/wordpress/site/'+pubId+'/campaigns')
    .set('Authorization', apiKey)
    .set('Content-Type', json)
    .set('Accept', json)
    .expect(200)
    .end(function(err, res) {
      expect(res.body).to.be.a('object');
      expect(res.body[toolPco]).to.be.a('array');
      assert.equal(res.body[toolPco].length, 1);
      var url = res.body[toolPco].pop();
      assert.equal(url, testUrl2);
      done(err, res);
    });
  });

  it('deletes a promoted URL campaign', function(done) {
    request
    .delete('/wordpress/site/'+pubId+'/campaigns/'+toolPco)
    .set('Authorization', apiKey)
    .set('Content-Type', json)
    .set('Accept', json)
    .expect(200)
    .end(function(err, res) {
      expect(res.body).to.be.a('object');
      expect(Object.keys(res.body)).to.be.empty;
      done(err, res);
    });
  });

  it('confirms there are no promote URL campaigns left on new pubid', function(done) {
    request
    .get('/wordpress/site/'+pubId+'/campaigns')
    .set('Authorization', apiKey)
    .set('Content-Type', json)
    .set('Accept', json)
    .expect(200)
    .end(function(err, res) {
      expect(res.body).to.be.a('object');
      expect(Object.keys(res.body)).to.be.empty;
      done(err, res);
    });
  });
});

describe('WordPress related boost get/update functionality ', function() {
  this.timeout(15000);
  var pubId;
  var apiKey;
  var floatingToolPco = 'smlre'; // Recommended Content Footer
  var inlineToolPco = 'flwh'; // Horizontal Follow Buttons
  var initialFloatingToolSettings = {
    'id': floatingToolPco,
    'enabled': true,
    'maxitems': 3,
    'numrows': 1,
    'responsive': '300px',
    'theme': 'dark',
    'title': 'Read This',
    '__hideOnHomepage': true,
    '__hideOnUrls': [
      'http://www.example.com/test1',
      'http://www.example.com/test2'
    ],
  };
  var updatedFloatingToolSettings = {
    'id': floatingToolPco,
    'enabled': true,
    'maxitems': 2,
    'numrows': 3,
    'responsive': '700px',
    'theme': 'light',
    'title': 'Reccommended for you',
    '__hideOnHomepage': false,
    '__hideOnUrls': [
      'http://www.example.com/test3',
      'http://www.example.com/test4'
    ],
  };
  var initialInlineToolSettings = {
    'id': inlineToolPco,
    'enabled': true,
    'title': 'I am social',
    'size': 'large',
    'services': [
      {
        'service': 'facebook',
        'usertype': 'id',
        'id': 1234
      },
      {
        'service': 'linkedin',
        'usertype': 'company',
        'id': 'addthis'
      }
    ]
  };
  var updatedInlineToolSettings = {
    'id': inlineToolPco,
    'enabled': true,
    'title': 'I am social',
    'size': 'small',
    'services': [
      {
        'service': 'linkedin',
        'usertype': 'company',
        'id': 'addthis'
      },
      {
        'service': 'twitter',
        'usertype': 'user',
        'id': 'addthis'
      }
    ]
  };

  it('receives empty boost settings for new pubid', function(done) {
    getNewProfile('wp', function(aPubId, anApiKey) {
      pubId = aPubId;
      apiKey = anApiKey;
      request
      .get('/plugins/'+pco+'/v/'+version+'/site/'+pubId)
      .set('Authorization', apiKey)
      .set('Accept', json)
      .expect(200)
      .end(function(err, res) {
        checkBoostSettingsFormat(res.body);
        expect(res.body.templates).to.be.empty;
        done(err, res);
      });
    });
  });

  it('creates floating tool recommended content footer boost settings', function(done) {
    request
    .put('/plugins/'+pco+'/v/'+recommendedLegacyPcoLastVersion+'/site/'+pubId+'/widget')
    .set('Authorization', apiKey)
    .set('Content-Type', json)
    .set('Accept', json)
    .send(initialFloatingToolSettings)
    .expect(200)
    .end(function(err, res) {
      var toolSettings = checkBoostSettingsFormat(res.body, initialFloatingToolSettings.id);
      confirmToolSettingsMatch(initialFloatingToolSettings, toolSettings);
      done(err, res);
    });
  });

  it('confirms floating tool recommended content footer boost settings', function(done) {
    request
    .get('/plugins/'+pco+'/v/'+recommendedLegacyPcoLastVersion+'/site/'+pubId)
    .set('Authorization', apiKey)
    .set('Accept', json)
    .expect(200)
    .end(function(err, res) {
      var toolSettings = checkBoostSettingsFormat(res.body, initialFloatingToolSettings.id);
      confirmToolSettingsMatch(initialFloatingToolSettings, toolSettings);
      done(err, res);
    });
  });

  it('changes floating tool recommended content footer boost settings', function(done) {
    request
    .put('/plugins/'+pco+'/v/'+recommendedLegacyPcoLastVersion+'/site/'+pubId+'/widget')
    .set('Authorization', apiKey)
    .set('Content-Type', json)
    .set('Accept', json)
    .send(updatedFloatingToolSettings)
    .expect(200)
    .end(function(err, res) {
      var toolSettings = checkBoostSettingsFormat(res.body, updatedFloatingToolSettings.id);
      confirmToolSettingsMatch(updatedFloatingToolSettings, toolSettings);
      done(err, res);
    });
  });

  it('confirms floating tool recommended content footer has desired new boost settings', function(done) {
    request
    .get('/plugins/'+pco+'/v/'+recommendedLegacyPcoLastVersion+'/site/'+pubId)
    .set('Authorization', apiKey)
    .set('Accept', json)
    .expect(200)
    .end(function(err, res) {
      var toolSettings = checkBoostSettingsFormat(res.body, updatedFloatingToolSettings.id);
      confirmToolSettingsMatch(updatedFloatingToolSettings, toolSettings);
      done(err, res);
    });
  });

  it('disables floating tool recommended content footer boost settings', function(done) {
    updatedFloatingToolSettings.enabled  = false;

    request
    .put('/plugins/'+pco+'/v/'+recommendedLegacyPcoLastVersion+'/site/'+pubId+'/widget')
    .set('Authorization', apiKey)
    .set('Content-Type', json)
    .set('Accept', json)
    .send(updatedFloatingToolSettings)
    .expect(200)
    .end(function(err, res) {
      var toolSettings = checkBoostSettingsFormat(res.body, updatedFloatingToolSettings.id);
      confirmToolSettingsMatch(updatedFloatingToolSettings, toolSettings);
      done(err, res);
    });
  });

  it('confirms floating tool recommended content footer is disabled', function(done) {
    request
    .get('/plugins/'+pco+'/v/'+recommendedLegacyPcoLastVersion+'/site/'+pubId)
    .set('Authorization', apiKey)
    .set('Accept', json)
    .expect(200)
    .end(function(err, res) {
      var toolSettings = checkBoostSettingsFormat(res.body, updatedFloatingToolSettings.id);
      confirmToolSettingsMatch(updatedFloatingToolSettings, toolSettings);
      done(err, res);
    });
  });

  it('creates inline tool horizontal follow boost settings', function(done) {
    request
    .put('/plugins/'+pco+'/v/'+followLegacyPcoLastVersion+'/site/'+pubId+'/widget')
    .set('Authorization', apiKey)
    .set('Content-Type', json)
    .set('Accept', json)
    .send(initialInlineToolSettings)
    .expect(200)
    .end(function(err, res) {
      var toolSettings = checkBoostSettingsFormat(res.body, initialInlineToolSettings.id);
      confirmToolSettingsMatch(initialInlineToolSettings, toolSettings);
      done(err, res);
    });
  });

  it('confirms inline tool horizontal follow boost settings', function(done) {
    request
    .get('/plugins/'+pco+'/v/'+followLegacyPcoLastVersion+'/site/'+pubId)
    .set('Authorization', apiKey)
    .set('Accept', json)
    .expect(200)
    .end(function(err, res) {
      var toolSettings = checkBoostSettingsFormat(res.body, initialInlineToolSettings.id);
      confirmToolSettingsMatch(initialInlineToolSettings, toolSettings);
      done(err, res);
    });
  });

  it('changes inline tool horizontal follow boost settings', function(done) {
    request
    .put('/plugins/'+pco+'/v/'+followLegacyPcoLastVersion+'/site/'+pubId+'/widget')
    .set('Authorization', apiKey)
    .set('Content-Type', json)
    .set('Accept', json)
    .send(updatedInlineToolSettings)
    .expect(200)
    .end(function(err, res) {
      var toolSettings = checkBoostSettingsFormat(res.body, updatedInlineToolSettings.id);
      confirmToolSettingsMatch(updatedInlineToolSettings, toolSettings);
      done(err, res);
    });
  });

  it('confirms inline tool horizontal follow has desired new boost settings', function(done) {
    request
    .get('/plugins/'+pco+'/v/'+followLegacyPcoLastVersion+'/site/'+pubId)
    .set('Authorization', apiKey)
    .set('Accept', json)
    .expect(200)
    .end(function(err, res) {
      var toolSettings = checkBoostSettingsFormat(res.body, updatedInlineToolSettings.id);
      confirmToolSettingsMatch(updatedInlineToolSettings, toolSettings);
      done(err, res);
    });
  });

  it('disable inline horizontal follow tool', function(done) {
    updatedInlineToolSettings.enabled = false;

    request
    .put('/plugins/'+pco+'/v/'+followLegacyPcoLastVersion+'/site/'+pubId+'/widget')
    .set('Authorization', apiKey)
    .set('Content-Type', json)
    .set('Accept', json)
    .send(updatedInlineToolSettings)
    .expect(200)
    .end(function(err, res) {
      var toolSettings = checkBoostSettingsFormat(res.body, updatedInlineToolSettings.id);
      confirmToolSettingsMatch(updatedInlineToolSettings, toolSettings);
      done(err, res);
    });
  });

  it('confirms inline tool horizontal follow is disabled', function(done) {
    request
    .get('/plugins/'+pco+'/v/'+followLegacyPcoLastVersion+'/site/'+pubId)
    .set('Authorization', apiKey)
    .set('Accept', json)
    .expect(200)
    .end(function(err, res) {
      var toolSettings = checkBoostSettingsFormat(res.body, updatedInlineToolSettings.id);
      confirmToolSettingsMatch(updatedInlineToolSettings, toolSettings);
      done(err, res);
    });
  });
});

describe('WordPress backward compatibility pco translation for follow tools', function() {
  this.timeout(15000);
  var pubId;
  var apiKey;
  var newInlineFollowPCO = 'flwi';
  var newFloatingFollowPCO = 'smlfw';
  var inlineVerticalFollowWidgetId = Math.random().toString().substr(-4);
  var inlineHorizontalFollowWidgetId = Math.random().toString().substr(-4);
  var inlineCustomFollowWidgetId = Math.random().toString().substr(-4);
  var followHeaderWidgetId = Math.random().toString().substr(-4);

  var legacyInlineVerticalFollow = {
    'id': 'flwv',
    'widgetId': inlineVerticalFollowWidgetId,
    'orientation': 'vertical',
    'enabled': true,
    'title': 'I am social',
    'size': 'large',
    'services': [
      {
        'service': 'facebook',
        'usertype': 'id',
        'id': 1234
      },
      {
        'service': 'linkedin',
        'usertype': 'company',
        'id': 'addthis'
      }
    ]
  };

  var legacyInlineHorizontalFollow = {
      'id': 'flwh',
      'widgetId': inlineHorizontalFollowWidgetId,
      'orientation': 'horizontal',
      'enabled': true,
      'title': 'I am social',
      'size': 'large',
      'services': [
        {
          'service': 'facebook',
          'usertype': 'id',
          'id': 1234
        },
        {
          'service': 'linkedin',
          'usertype': 'company',
          'id': 'addthis'
        }
      ]
  };

  var legacyInlineCustomFollow = {
      'id': 'cflwh',
      'widgetId': inlineCustomFollowWidgetId,
      'orientation': 'horizontal',
      'enabled': true,
      'title': 'I am social',
      'size': 'large',
      'theme': 'custom',
      'iconColor': '#FFFFFF',
      'services': [
        {
          'service': 'facebook',
          'usertype': 'id',
          'id': 1234
        },
        {
          'service': 'linkedin',
          'usertype': 'company',
          'id': 'addthis'
        }
      ]
  };

  var legacyFollowHeader = {
      'id': 'smlfw',
      'widgetId': followHeaderWidgetId,
      'enabled': true,
      'theme': 'dark',
      'title': 'I am social',
      "thankyou": true,
      "responsive": "979px",
      'services': [
        {
          'service': 'facebook',
          'usertype': 'id',
          'id': 1234
        },
        {
          'service': 'linkedin',
          'usertype': 'company',
          'id': 'addthis'
        }
      ],
      "offset": {
          "bottom": "0px"
      },
      '__hideOnUrls': [
        'http://www.example.com/test1',
        'http://www.example.com/test2'
      ],
      "__hideOnHomepage": false,
  };

  it('receives empty boost settings for new pubid', function(done) {
    getNewProfile('wp', function(aPubId, anApiKey) {
      pubId = aPubId;
      apiKey = anApiKey;
      request
      .get('/plugins/'+pco+'/v/'+version+'/site/'+pubId)
      .set('Authorization', apiKey)
      .set('Accept', json)
      .expect(200)
      .end(function(err, res) {
        checkBoostSettingsFormat(res.body);
        expect(res.body.templates).to.be.empty;
        done(err, res);
      });
    });
  });

  it('creates legacy inline vertical follow tool', function(done) {
    request
    .put('/plugins/'+pco+'/v/'+followLegacyPcoLastVersion+'/site/'+pubId+'/widget')
    .set('Authorization', apiKey)
    .set('Content-Type', json)
    .set('Accept', json)
    .send(legacyInlineVerticalFollow)
    .expect(200)
    .end(function(err, res) {
      var toolSettings = getWidgetById(res.body, legacyInlineVerticalFollow.widgetId);
      confirmToolSettingsMatch(legacyInlineVerticalFollow, toolSettings);
      done(err, res);
    });
  });

  it('rewrites pco of underlying legacy inline vertical follow tool', function(done) {
    request
    .get('/plugins/'+pco+'/v/'+newVersion+'/site/'+pubId)
    .set('Authorization', apiKey)
    .set('Accept', json)
    .expect(200)
    .end(function(err, res) {
      var toolSettings = getWidgetById(res.body, legacyInlineVerticalFollow.widgetId);
      expect(toolSettings).to.be.a('object');
      expect(toolSettings.id).to.equal(newInlineFollowPCO);
      expect(toolSettings.orientation).to.equal(legacyInlineVerticalFollow.orientation);
      done(err, res);
    });
  });

  it('creates legacy inline horizontal follow tool', function(done) {
    request
    .put('/plugins/'+pco+'/v/'+followLegacyPcoLastVersion+'/site/'+pubId+'/widget')
    .set('Authorization', apiKey)
    .set('Content-Type', json)
    .set('Accept', json)
    .send(legacyInlineHorizontalFollow)
    .expect(200)
    .end(function(err, res) {
      var toolSettings = getWidgetById(res.body, legacyInlineHorizontalFollow.widgetId);
      confirmToolSettingsMatch(legacyInlineHorizontalFollow, toolSettings);
      done(err, res);
    });
  });

  it('rewrites pco of underlying legacy inline horizontal follow tool', function(done) {
    request
    .get('/plugins/'+pco+'/v/'+newVersion+'/site/'+pubId)
    .set('Authorization', apiKey)
    .set('Accept', json)
    .expect(200)
    .end(function(err, res) {
      var toolSettings = getWidgetById(res.body, legacyInlineHorizontalFollow.widgetId);
      expect(toolSettings).to.be.a('object');
      expect(toolSettings.id).to.equal(newInlineFollowPCO);
      expect(toolSettings.orientation).to.equal(legacyInlineHorizontalFollow.orientation);
      done(err, res);
    });
  });

  it('creates legacy inline custom follow tool', function(done) {
    request
    .put('/plugins/'+pco+'/v/'+followLegacyPcoLastVersion+'/site/'+pubId+'/widget')
    .set('Authorization', apiKey)
    .set('Content-Type', json)
    .set('Accept', json)
    .send(legacyInlineCustomFollow)
    .expect(200)
    .end(function(err, res) {
      var toolSettings = getWidgetById(res.body, legacyInlineCustomFollow.widgetId);
      confirmToolSettingsMatch(legacyInlineCustomFollow, toolSettings);
      done(err, res);
    });
  });

  it('rewrites pco of underlying legacy inline custom follow tool', function(done) {
    request
    .get('/plugins/'+pco+'/v/'+newVersion+'/site/'+pubId)
    .set('Authorization', apiKey)
    .set('Accept', json)
    .expect(200)
    .end(function(err, res) {
      var toolSettings = getWidgetById(res.body, legacyInlineCustomFollow.widgetId);
      expect(toolSettings).to.be.a('object');
      expect(toolSettings.id).to.equal(newInlineFollowPCO);
      expect(toolSettings.orientation).to.equal(legacyInlineCustomFollow.orientation);
      done(err, res);
    });
  });

  it('creates legacy follow header tool', function(done) {
    request
    .put('/plugins/'+pco+'/v/'+version+'/site/'+pubId+'/widget')
    .set('Authorization', apiKey)
    .set('Content-Type', json)
    .set('Accept', json)
    .send(legacyFollowHeader)
    .expect(200)
    .end(function(err, res) {
      var toolSettings = getWidgetById(res.body, legacyFollowHeader.widgetId);
      confirmToolSettingsMatch(legacyFollowHeader, toolSettings);
      done(err, res);
    });
  });

  it('rewrites pco of underlying legacy follow header tool', function(done) {
    request
    .get('/plugins/'+pco+'/v/'+newVersion+'/site/'+pubId)
    .set('Authorization', apiKey)
    .set('Accept', json)
    .expect(200)
    .end(function(err, res) {
      var toolSettings = getWidgetById(res.body, legacyFollowHeader.widgetId);
      expect(toolSettings).to.be.a('object');
      expect(toolSettings.id).to.equal(newFloatingFollowPCO);
      expect(toolSettings.title).to.equal(legacyInlineCustomFollow.title);
      done(err, res);
    });
  });
});

describe('WordPress backward compatibility pco translation for related post tools', function() {
  this.timeout(15000);
  var pubId;
  var apiKey;
  var newRelatedPostSliderPCO = 'rpsl';
  var newRelatedPostInlinePCO = 'rpin';
  var newRelatedPostFooterPCO = 'rpfo';
  var recContentHorizontalWidgetId = Math.random().toString().substr(-4);
  var recContentVerticalWidgetId = Math.random().toString().substr(-4);
  var recContentFooterWidgetId = Math.random().toString().substr(-4);
  var recContentJumboWidgetId = Math.random().toString().substr(-4);
  var recContentDrawerWidgetId = Math.random().toString().substr(-4);
  var whatsNextWidgetId = Math.random().toString().substr(-4);
  var whatsNextMobileWidgetId = Math.random().toString().substr(-4);
  var toasterWidgetId = Math.random().toString().substr(-4);

  var legacyWhatsNext = {
      "id": "smlwn",
      "enabled": true,
      "widgetId": whatsNextWidgetId,
      "offset": {
          "right": "0px"
      },
      "responsive": "979px",
      "theme": "light",
      "title": "Recommended for you",
      "__hideOnHomepage": false,
      "scrollDepth": 25
  };

  var legacyWhatsNextMobile = {
      "id": "wnm",
      "enabled": true,
      "widgetId": whatsNextMobileWidgetId,
      "theme": "light",
      "title": "Recommended for you",
      "__hideOnHomepage": false,
      "scrollDepth": 25
  }

  var legacyToaster = {
      "id": "tst",
      "enabled": true,
      "widgetId": toasterWidgetId,
      "offset": {
          "left": "0px"
      },
      "responsive": "979px",
      "theme": "dark",
      "title": "Recommended for you",
      "__hideOnHomepage": false,
      "scrollDepth": 25
  };

  var legacyRecContentInlineHorizontal = {
      "id": "smlrebh",
      "enabled": true,
      "widgetId": recContentHorizontalWidgetId,
      "orientation": "horizontal",
      "numrows": 1,
      "elements": ".addthis_recommended_horizontal",
      "theme": "transparent",
      "maxitems": 4,
      "title": "Recommended for you"
  };

  var legacyRecContentInlineVertical = {
      "id": "smlrebv",
      "enabled": true,
      "widgetId": recContentVerticalWidgetId,
      "orientation": "vertical",
      "numrows": 1,
      "elements": ".addthis_recommended_vertical",
      "theme": "transparent",
      "maxitems": 4,
      "title": "Recommended for you"
  };

  var legacyRecContentFooter = {
      "id": "smlre",
      "enabled": true,
      "widgetId": recContentFooterWidgetId,
      "numrows": 1,
      "theme": "light",
      "title": "Recommended for you",
      "maxitems": 3,
      "__hideOnHomepage": false
  };

  var legacyRecContentDrawerFooter = {
      "id": "cod",
      "enabled": true,
      "widgetId": recContentDrawerWidgetId,
      "title": "Recommended for you",
      "position": "left",
      "theme": "dark",
      "animationType": "push",
      "__hideOnHomepage": false,
      '__hideOnUrls': [
        'http://www.example.com/test1',
        'http://www.example.com/test2'
      ]
  };

  var legacyRecContentJumboFooter = {
      "id": "jrcf",
      "enabled": true,
      "widgetId": recContentJumboWidgetId,
      "responsive": "460px",
      "elements": "",
      "title": "Recommended for you",
      "__hideOnHomepage": false
  };

  it('receives empty boost settings for new pubid', function(done) {
    getNewProfile('wp', function(aPubId, anApiKey) {
      pubId = aPubId;
      apiKey = anApiKey;
      request
      .get('/plugins/'+pco+'/v/'+version+'/site/'+pubId)
      .set('Authorization', apiKey)
      .set('Accept', json)
      .expect(200)
      .end(function(err, res) {
        checkBoostSettingsFormat(res.body);
        expect(res.body.templates).to.be.empty;
        done(err, res);
      });
    });
  });

  it('creates legacy whats next tool', function(done) {
    request
    .put('/plugins/'+pco+'/v/'+recommendedLegacyPcoLastVersion+'/site/'+pubId+'/widget')
    .set('Authorization', apiKey)
    .set('Content-Type', json)
    .set('Accept', json)
    .send(legacyWhatsNext)
    .expect(200)
    .end(function(err, res) {
      var toolSettings = getWidgetById(res.body, legacyWhatsNext.widgetId);
      confirmToolSettingsMatch(legacyWhatsNext, toolSettings);
      done(err, res);
    });
  });

  it('rewrites pco of underlying legacy whats next tool', function(done) {
    request
    .get('/plugins/'+pco+'/v/'+newVersion+'/site/'+pubId)
    .set('Authorization', apiKey)
    .set('Accept', json)
    .expect(200)
    .end(function(err, res) {
      var toolSettings = getWidgetById(res.body, legacyWhatsNext.widgetId);
      expect(toolSettings).to.be.a('object');
      expect(toolSettings.id).to.equal(newRelatedPostSliderPCO);
      expect(toolSettings.numPosts).to.equal(1);
      expect(toolSettings.desktopPosition).to.equal('right');
      expect(toolSettings.mobilePosition).to.equal('hide');
      done(err, res);
    });
  });

  it('creates legacy whats next mobile tool', function(done) {
    request
    .put('/plugins/'+pco+'/v/'+recommendedLegacyPcoLastVersion+'/site/'+pubId+'/widget')
    .set('Authorization', apiKey)
    .set('Content-Type', json)
    .set('Accept', json)
    .send(legacyWhatsNextMobile)
    .expect(200)
    .end(function(err, res) {
      var toolSettings = getWidgetById(res.body, legacyWhatsNextMobile.widgetId);
      confirmToolSettingsMatch(legacyWhatsNextMobile, toolSettings);
      done(err, res);
    });
  });

  it('rewrites pco of underlying legacy whats next mobile tool', function(done) {
    request
    .get('/plugins/'+pco+'/v/'+newVersion+'/site/'+pubId)
    .set('Authorization', apiKey)
    .set('Accept', json)
    .expect(200)
    .end(function(err, res) {
      var toolSettings = getWidgetById(res.body, legacyWhatsNextMobile.widgetId);
      expect(toolSettings).to.be.a('object');
      expect(toolSettings.id).to.equal(newRelatedPostSliderPCO);
      expect(toolSettings.numPosts).to.equal(1);
      expect(toolSettings.desktopPosition).to.equal('hide');
      expect(toolSettings.mobilePosition).to.equal('bottom');
      done(err, res);
    });
  });

  it('creates legacy toaster tool', function(done) {
    request
    .put('/plugins/'+pco+'/v/'+recommendedLegacyPcoLastVersion+'/site/'+pubId+'/widget')
    .set('Authorization', apiKey)
    .set('Content-Type', json)
    .set('Accept', json)
    .send(legacyToaster)
    .expect(200)
    .end(function(err, res) {
      var toolSettings = getWidgetById(res.body, legacyToaster.widgetId);
      confirmToolSettingsMatch(legacyToaster, toolSettings);
      done(err, res);
    });
  });

  it('rewrites pco of underlying legacy toaster tool', function(done) {
    request
    .get('/plugins/'+pco+'/v/'+newVersion+'/site/'+pubId)
    .set('Authorization', apiKey)
    .set('Accept', json)
    .expect(200)
    .end(function(err, res) {
      var toolSettings = getWidgetById(res.body, legacyToaster.widgetId);
      expect(toolSettings).to.be.a('object');
      expect(toolSettings.id).to.equal(newRelatedPostSliderPCO);
      expect(toolSettings.numPosts).to.equal(2);
      expect(toolSettings.desktopPosition).to.equal('left');
      expect(toolSettings.mobilePosition).to.equal('hide');
      done(err, res);
    });
  });

  it('creates legacy inline horizontal rec content tool', function(done) {
    request
    .put('/plugins/'+pco+'/v/'+recommendedLegacyPcoLastVersion+'/site/'+pubId+'/widget')
    .set('Authorization', apiKey)
    .set('Content-Type', json)
    .set('Accept', json)
    .send(legacyRecContentInlineHorizontal)
    .expect(200)
    .end(function(err, res) {
      var toolSettings = getWidgetById(res.body, legacyRecContentInlineHorizontal.widgetId);
      confirmToolSettingsMatch(legacyRecContentInlineHorizontal, toolSettings);
      done(err, res);
    });
  });

  it('rewrites pco of underlying legacy inline horizontal rec content tool', function(done) {
    request
    .get('/plugins/'+pco+'/v/'+newVersion+'/site/'+pubId)
    .set('Authorization', apiKey)
    .set('Accept', json)
    .expect(200)
    .end(function(err, res) {
      var toolSettings = getWidgetById(res.body, legacyRecContentInlineHorizontal.widgetId);
      expect(toolSettings).to.be.a('object');
      expect(toolSettings.id).to.equal(newRelatedPostInlinePCO);
      expect(toolSettings.orientation).to.equal(legacyRecContentInlineHorizontal.orientation);
      done(err, res);
    });
  });

  it('creates legacy inline vertical rec content tool', function(done) {
    request
    .put('/plugins/'+pco+'/v/'+recommendedLegacyPcoLastVersion+'/site/'+pubId+'/widget')
    .set('Authorization', apiKey)
    .set('Content-Type', json)
    .set('Accept', json)
    .send(legacyRecContentInlineVertical)
    .expect(200)
    .end(function(err, res) {
      var toolSettings = getWidgetById(res.body, legacyRecContentInlineVertical.widgetId);
      confirmToolSettingsMatch(legacyRecContentInlineVertical, toolSettings);
      done(err, res);
    });
  });

  it('rewrites pco of underlying legacy inline vertical rec content tool', function(done) {
    request
    .get('/plugins/'+pco+'/v/'+newVersion+'/site/'+pubId)
    .set('Authorization', apiKey)
    .set('Accept', json)
    .expect(200)
    .end(function(err, res) {
      var toolSettings = getWidgetById(res.body, legacyRecContentInlineVertical.widgetId);
      expect(toolSettings).to.be.a('object');
      expect(toolSettings.id).to.equal(newRelatedPostInlinePCO);
      expect(toolSettings.orientation).to.equal(legacyRecContentInlineVertical.orientation);
      done(err, res);
    });
  });

  it('creates legacy rec content footer tool', function(done) {
    request
    .put('/plugins/'+pco+'/v/'+recommendedLegacyPcoLastVersion+'/site/'+pubId+'/widget')
    .set('Authorization', apiKey)
    .set('Content-Type', json)
    .set('Accept', json)
    .send(legacyRecContentFooter)
    .expect(200)
    .end(function(err, res) {
      var toolSettings = getWidgetById(res.body, legacyRecContentFooter.widgetId);
      confirmToolSettingsMatch(legacyRecContentFooter, toolSettings);
      done(err, res);
    });
  });

  it('rewrites pco of underlying rec content footer tool', function(done) {
    request
    .get('/plugins/'+pco+'/v/'+newVersion+'/site/'+pubId)
    .set('Authorization', apiKey)
    .set('Accept', json)
    .expect(200)
    .end(function(err, res) {
      var toolSettings = getWidgetById(res.body, legacyRecContentFooter.widgetId);
      expect(toolSettings).to.be.a('object');
      expect(toolSettings.id).to.equal(newRelatedPostFooterPCO);
      expect(toolSettings.style).to.equal('standard');
      done(err, res);
    });
  });

  it('creates legacy rec content jumbo footer tool', function(done) {
    request
    .put('/plugins/'+pco+'/v/'+recommendedLegacyPcoLastVersion+'/site/'+pubId+'/widget')
    .set('Authorization', apiKey)
    .set('Content-Type', json)
    .set('Accept', json)
    .send(legacyRecContentJumboFooter)
    .expect(200)
    .end(function(err, res) {
      var toolSettings = getWidgetById(res.body, legacyRecContentJumboFooter.widgetId);
      confirmToolSettingsMatch(legacyRecContentJumboFooter, toolSettings);
      done(err, res);
    });
  });

  it('rewrites pco of underlying rec content jumbo footer tool', function(done) {
    request
    .get('/plugins/'+pco+'/v/'+newVersion+'/site/'+pubId)
    .set('Authorization', apiKey)
    .set('Accept', json)
    .expect(200)
    .end(function(err, res) {
      var toolSettings = getWidgetById(res.body, legacyRecContentJumboFooter.widgetId);
      expect(toolSettings).to.be.a('object');
      expect(toolSettings.id).to.equal(newRelatedPostFooterPCO);
      expect(toolSettings.style).to.equal('jumbo');
      done(err, res);
    });
  });

  it('creates legacy rec content drawer footer tool', function(done) {
    request
    .put('/plugins/'+pco+'/v/'+version+'/site/'+pubId+'/widget')
    .set('Authorization', apiKey)
    .set('Content-Type', json)
    .set('Accept', json)
    .send(legacyRecContentDrawerFooter)
    .expect(200)
    .end(function(err, res) {
      var toolSettings = getWidgetById(res.body, legacyRecContentDrawerFooter.widgetId);
      confirmToolSettingsMatch(legacyRecContentDrawerFooter, toolSettings);
      done(err, res);
    });
  });

  it('rewrites pco of underlying rec content jumbo footer tool', function(done) {
    request
    .get('/plugins/'+pco+'/v/'+newVersion+'/site/'+pubId)
    .set('Authorization', apiKey)
    .set('Accept', json)
    .expect(200)
    .end(function(err, res) {
      var toolSettings = getWidgetById(res.body, legacyRecContentDrawerFooter.widgetId);
      expect(toolSettings).to.be.a('object');
      expect(toolSettings.id).to.equal(legacyRecContentDrawerFooter.id);
      done(err, res);
    });
  });
});

// Depreciation info:
// Not used after wpsl-2.0.0, wpf-3.0.0, wpwt-1.1.2, wprp-1.0.0
// never used in wpp
describe('WordPress backward compatibility pco translation for share tools (depricated after wpsl-2.0.0, wpf-3.0.0, wpwt-1.1.2, wprp-1.0.0)', function() {
  this.timeout(15000);
  var newInlineSharingPCO = 'shin';
  var newShareingSidebarPCO = 'shfs';
  var legacyOriginalShareButtonsId = Math.random().toString().substr(-4);
  var legacyShareButtonsId = Math.random().toString().substr(-4);
  var legacyCustomShareButtonsId = Math.random().toString().substr(-4);
  var legacyResponsiveShareButtonsId = Math.random().toString().substr(-4);
  var legacyJumboShareCounterId = Math.random().toString().substr(-4);
  var legacySharingSidebarId = Math.random().toString().substr(-4);
  var legacyCustomSharingSidebarId = Math.random().toString().substr(-4);
  var legacyMobileSharingDockId = Math.random().toString().substr(-4);
  var legacyCustomMobileSharingDockId = Math.random().toString().substr(-4);
  var legacyMobileToolbarId = Math.random().toString().substr(-4);

  var legacyOriginalShareButtons = {
      widgetId: legacyOriginalShareButtonsId,
      "id": "scopl",
      "enabled": true,
      "widgetId": "0sps",
      "thirdPartyButtons": "true",
      "elements": ".addthis_native_toolbox",
      "services": "tweet,counter,pinterest_pinit"
  };

  var legacyShareButtons = {
      widgetId: legacyShareButtonsId,
      "id": "tbx",
      "enabled": true,
      "numPreferredServices": 5,
      "size": "large",
      "counts": false,
      "elements": ".addthis_sharing_toolbox,.at-above-post-page",
      "services": "facebook,twitter,pinterest,linkedin,addthis"
  };

  var legacyCustomShareButtons = {
      widgetId: legacyCustomShareButtonsId,
      "id": "ctbx",
      "enabled": true,
      "elements": ".addthis_custom_sharing",
      "theme": "custom",
      "shape": "rounded",
      "background": "#E74339"
  };

  var legacyResponsiveShareButtons = {
      widgetId: legacyResponsiveShareButtonsId,
      "id": "resh",
      "enabled": true,
      "counts": "none",
      "elements": ".addthis_responsive_sharing",
      "responsive": "",
      "services": ""
  };

  var legacyJumboShareCounter = {
      widgetId: legacyJumboShareCounterId,
      "id": "jsc",
      "enabled": true,
      "elements": ".addthis_jumbo_share"
  };

  var legacySharingSidebar = {
      widgetId: legacySharingSidebarId,
      "id": "smlsh",
      "enabled": false,
      "hideEmailSharingConfirmation": false,
      "thankyou": true,
      "postShareRecommendedMsg": "Recommended for you",
      "offset": {
          "top": "20%"
      },
      "counts": false,
      "shareCountThreshold": 10,
      "mobile": false,
      "title": "Recommended for you",
      "__hideOnHomepage": false,
      "animationType": "overlay",
      "numPreferredServices": 5,
      "responsive": "979px",
      "postShareFollowMsg": "Follow",
      "theme": "dark",
      "position": "right",
      "postShareTitle": "Thanks for sharing!"
  };

  var legacyCustomSharingSidebar = {
      widgetId: legacyCustomSharingSidebarId,
      "id": "csmlsh",
      "enabled": true,
      "backgroundColor": "#FFFFFF",
      "thankyou": true,
      "postShareRecommendedMsg": "Recommended for you",
      "offset": {
          "top": "20%"
      },
      "counts": true,
      "shareCountThreshold": 10,
      "label": "SHARES",
      "textColor": "#A8CE50",
      "__hideOnHomepage": false,
      "numPreferredServices": 5,
      "responsive": "736px",
      "postShareFollowMsg": "Follow",
      "iconColor": "#FFFFFF",
      "position": "right",
      "postShareTitle": "Thanks for sharing!"
  };

  var legacyMobileSharingDock = {
      widgetId: legacyMobileSharingDockId,
      "id": "msd",
      "enabled": true,
      "numPreferredServices": 3,
      "hideEmailSharingConfirmation": false,
      "counts": true,
      "shareCountThreshold": 10,
      "responsive": "",
      "services": "",
      "position": "bottom",
      "__hideOnHomepage": false
  };

  var legacyCustomMobileSharingDock = {
      widgetId: legacyCustomMobileSharingDockId,
      "id": "cmtb",
      "enabled": true,
      "numPreferredServices": 4,
      "hideEmailSharingConfirmation": false,
      "backgroundColor": "#FFFFFF",
      "borderRadius": "20%",
      "counts": true,
      "shareCountThreshold": 10,
      "responsive": "979px",
      "iconColor": "#FFFFFF",
      "label": "SHARES",
      "position": "top",
      "textColor": "#000000",
      "__hideOnHomepage": false
  };

  var legacyMobileToolbar = {
      widgetId: legacyMobileToolbarId,
      "id": "smlmo",
      "enabled": true,
      "responsive": "979px",
      "position": "bottom",
      "buttonBarTheme": "dark",
      "follow": "on",
      "followServices": [
        {
          'service': 'facebook',
          'usertype': 'id',
          'id': 1234
        },
        {
          'service': 'linkedin',
          'usertype': 'company',
          'id': 'addthis'
        }
      ],
      "__hideOnHomepage": false,
      '__hideOnUrls': [
        'http://www.example.com/test1',
        'http://www.example.com/test2'
      ]
  };

  it('receives empty boost settings for new pubid', function(done) {
    getNewProfile('wp', function(aPubId, anApiKey) {
      pubId = aPubId;
      apiKey = anApiKey;
      request
      .get('/plugins/'+pco+'/v/'+version+'/site/'+pubId)
      .set('Authorization', apiKey)
      .set('Accept', json)
      .expect(200)
      .end(function(err, res) {
        checkBoostSettingsFormat(res.body);
        expect(res.body.templates).to.be.empty;
        done(err, res);
      });
    });
  });

  it('creates legacy sharing buttons tool', function(done) {
    request
    .put('/plugins/'+pco+'/v/'+shareLegacyPcoLastVersion+'/site/'+pubId+'/widget')
    .set('Authorization', apiKey)
    .set('Content-Type', json)
    .set('Accept', json)
    .send(legacyShareButtons)
    .expect(200)
    .end(function(err, res) {
      var toolSettings = getWidgetById(res.body, legacyShareButtons.widgetId);
      confirmToolSettingsMatch(legacyShareButtons, toolSettings);
      done(err, res);
    });
  });

  it('rewrites pco of underlying sharing buttons tool', function(done) {
    request
    .get('/plugins/'+pco+'/v/'+newVersion+'/site/'+pubId)
    .set('Authorization', apiKey)
    .set('Accept', json)
    .expect(200)
    .end(function(err, res) {
      var toolSettings = getWidgetById(res.body, legacyShareButtons.widgetId);
      expect(toolSettings).to.be.a('object');
      expect(toolSettings.id).to.equal(newInlineSharingPCO);
      expect(toolSettings.style).to.equal('fixed');
      expect(toolSettings.counts).to.equal('none');
      expect(toolSettings.size).to.equal('32px');
      expect(toolSettings.responsive).to.equal('0px');
      done(err, res);
    });
  });

  it('creates legacy orginal sharing buttons tool', function(done) {
    request
    .put('/plugins/'+pco+'/v/'+shareLegacyPcoLastVersion+'/site/'+pubId+'/widget')
    .set('Authorization', apiKey)
    .set('Content-Type', json)
    .set('Accept', json)
    .send(legacyOriginalShareButtons)
    .expect(200)
    .end(function(err, res) {
      var toolSettings = getWidgetById(res.body, legacyOriginalShareButtons.widgetId);
      confirmToolSettingsMatch(legacyOriginalShareButtons, toolSettings);
      done(err, res);
    });
  });

  it('rewrites pco of underlying orginal sharing buttons tool', function(done) {
    request
    .get('/plugins/'+pco+'/v/'+newVersion+'/site/'+pubId)
    .set('Authorization', apiKey)
    .set('Accept', json)
    .expect(200)
    .end(function(err, res) {
      var toolSettings = getWidgetById(res.body, legacyOriginalShareButtons.widgetId);
      expect(toolSettings).to.be.a('object');
      expect(toolSettings.id).to.equal(newInlineSharingPCO);
      expect(toolSettings.style).to.equal('original');
      expect(toolSettings.thirdPartyButtons).to.equal(true);
      expect(toolSettings.originalServices).to.equal(legacyOriginalShareButtons.services);
      done(err, res);
    });
  });

  it('creates legacy custom sharing buttons tool', function(done) {
    request
    .put('/plugins/'+pco+'/v/'+shareLegacyPcoLastVersion+'/site/'+pubId+'/widget')
    .set('Authorization', apiKey)
    .set('Content-Type', json)
    .set('Accept', json)
    .send(legacyCustomShareButtons)
    .expect(200)
    .end(function(err, res) {
      var toolSettings = getWidgetById(res.body, legacyCustomShareButtons.widgetId);
      confirmToolSettingsMatch(legacyCustomShareButtons, toolSettings);
      done(err, res);
    });
  });

  it('rewrites pco of underlying custom sharing buttons tool', function(done) {
    request
    .get('/plugins/'+pco+'/v/'+newVersion+'/site/'+pubId)
    .set('Authorization', apiKey)
    .set('Accept', json)
    .expect(200)
    .end(function(err, res) {
      var toolSettings = getWidgetById(res.body, legacyCustomShareButtons.widgetId);
      expect(toolSettings).to.be.a('object');
      expect(toolSettings.id).to.equal(newInlineSharingPCO);
      expect(toolSettings.style).to.equal('fixed');
      expect(toolSettings.borderRadius).to.equal('12%');
      expect(toolSettings.buttonColor).to.equal(legacyCustomShareButtons.background);
      done(err, res);
    });
  });

  it('creates legacy responsive sharing buttons tool', function(done) {
    request
    .put('/plugins/'+pco+'/v/'+shareLegacyPcoLastVersion+'/site/'+pubId+'/widget')
    .set('Authorization', apiKey)
    .set('Content-Type', json)
    .set('Accept', json)
    .send(legacyResponsiveShareButtons)
    .expect(200)
    .end(function(err, res) {
      var toolSettings = getWidgetById(res.body, legacyResponsiveShareButtons.widgetId);
      confirmToolSettingsMatch(legacyResponsiveShareButtons, toolSettings);
      done(err, res);
    });
  });

  it('rewrites pco of underlying legacy sharing buttons tool', function(done) {
    request
    .get('/plugins/'+pco+'/v/'+newVersion+'/site/'+pubId)
    .set('Authorization', apiKey)
    .set('Accept', json)
    .expect(200)
    .end(function(err, res) {
      var toolSettings = getWidgetById(res.body, legacyResponsiveShareButtons.widgetId);
      expect(toolSettings).to.be.a('object');
      expect(toolSettings.id).to.equal(newInlineSharingPCO);
      expect(toolSettings.style).to.equal('responsive');
      done(err, res);
    });
  });

  it('creates legacy jumbo share counter tool', function(done) {
    request
    .put('/plugins/'+pco+'/v/'+shareLegacyPcoLastVersion+'/site/'+pubId+'/widget')
    .set('Authorization', apiKey)
    .set('Content-Type', json)
    .set('Accept', json)
    .send(legacyJumboShareCounter)
    .expect(200)
    .end(function(err, res) {
      var toolSettings = getWidgetById(res.body, legacyJumboShareCounter.widgetId);
      confirmToolSettingsMatch(legacyJumboShareCounter, toolSettings);
      done(err, res);
    });
  });

  it('rewrites pco of underlying legacy jumbo share counter tool', function(done) {
    request
    .get('/plugins/'+pco+'/v/'+newVersion+'/site/'+pubId)
    .set('Authorization', apiKey)
    .set('Accept', json)
    .expect(200)
    .end(function(err, res) {
      var toolSettings = getWidgetById(res.body, legacyJumboShareCounter.widgetId);
      expect(toolSettings).to.be.a('object');
      expect(toolSettings.id).to.equal(newInlineSharingPCO);
      expect(toolSettings.style).to.equal('responsive');
      expect(toolSettings.counts).to.equal('jumbo');
      done(err, res);
    });
  });

  it('creates legacy sharing sidebar tool', function(done) {
    request
    .put('/plugins/'+pco+'/v/'+shareLegacyPcoLastVersion+'/site/'+pubId+'/widget')
    .set('Authorization', apiKey)
    .set('Content-Type', json)
    .set('Accept', json)
    .send(legacySharingSidebar)
    .expect(200)
    .end(function(err, res) {
      var toolSettings = getWidgetById(res.body, legacySharingSidebar.widgetId);
      confirmToolSettingsMatch(legacySharingSidebar, toolSettings);
      done(err, res);
    });
  });

  it('rewrites pco of underlying legacy sharing sidebar tool', function(done) {
    request
    .get('/plugins/'+pco+'/v/'+newVersion+'/site/'+pubId)
    .set('Authorization', apiKey)
    .set('Accept', json)
    .expect(200)
    .end(function(err, res) {
      var toolSettings = getWidgetById(res.body, legacySharingSidebar.widgetId);
      expect(toolSettings).to.be.a('object');
      expect(toolSettings.id).to.equal(newShareingSidebarPCO);
      expect(toolSettings.style).to.equal('modern');
      expect(toolSettings.mobilePosition).to.equal('hide');
      expect(toolSettings.desktopPosition).to.equal(legacySharingSidebar.position);
      done(err, res);
    });
  });

  it('creates legacy custom sharing sidebar tool', function(done) {
    request
    .put('/plugins/'+pco+'/v/'+shareLegacyPcoLastVersion+'/site/'+pubId+'/widget')
    .set('Authorization', apiKey)
    .set('Content-Type', json)
    .set('Accept', json)
    .send(legacyCustomSharingSidebar)
    .expect(200)
    .end(function(err, res) {
      var toolSettings = getWidgetById(res.body, legacyCustomSharingSidebar.widgetId);
      confirmToolSettingsMatch(legacyCustomSharingSidebar, toolSettings);
      done(err, res);
    });
  });

  it('rewrites pco of underlying legacy custom sharing sidebar tool', function(done) {
    request
    .get('/plugins/'+pco+'/v/'+newVersion+'/site/'+pubId)
    .set('Authorization', apiKey)
    .set('Accept', json)
    .expect(200)
    .end(function(err, res) {
      var toolSettings = getWidgetById(res.body, legacyCustomSharingSidebar.widgetId);
      expect(toolSettings).to.be.a('object');
      expect(toolSettings.id).to.equal(newShareingSidebarPCO);
      expect(toolSettings.style).to.equal('bordered');
      expect(toolSettings.mobilePosition).to.equal('hide');
      expect(toolSettings.desktopPosition).to.equal(legacyCustomSharingSidebar.position);
      done(err, res);
    });
  });

  it('creates legacy mobile sharing dock tool', function(done) {
    request
    .put('/plugins/'+pco+'/v/'+shareLegacyPcoLastVersion+'/site/'+pubId+'/widget')
    .set('Authorization', apiKey)
    .set('Content-Type', json)
    .set('Accept', json)
    .send(legacyMobileSharingDock)
    .expect(200)
    .end(function(err, res) {
      var toolSettings = getWidgetById(res.body, legacyMobileSharingDock.widgetId);
      confirmToolSettingsMatch(legacyMobileSharingDock, toolSettings);
      done(err, res);
    });
  });

  it('rewrites pco of underlying legacy custom sharing sidebar tool', function(done) {
    request
    .get('/plugins/'+pco+'/v/'+newVersion+'/site/'+pubId)
    .set('Authorization', apiKey)
    .set('Accept', json)
    .expect(200)
    .end(function(err, res) {
      var toolSettings = getWidgetById(res.body, legacyMobileSharingDock.widgetId);
      expect(toolSettings).to.be.a('object');
      expect(toolSettings.id).to.equal(newShareingSidebarPCO);
      expect(toolSettings.style).to.equal('modern');
      expect(toolSettings.desktopPosition).to.equal('hide');
      expect(toolSettings.mobilePosition).to.equal(legacyMobileSharingDock.position);
      done(err, res);
    });
  });

  it('creates legacy custom mobile sharing dock tool', function(done) {
    request
    .put('/plugins/'+pco+'/v/'+shareLegacyPcoLastVersion+'/site/'+pubId+'/widget')
    .set('Authorization', apiKey)
    .set('Content-Type', json)
    .set('Accept', json)
    .send(legacyCustomMobileSharingDock)
    .expect(200)
    .end(function(err, res) {
      var toolSettings = getWidgetById(res.body, legacyCustomMobileSharingDock.widgetId);
      confirmToolSettingsMatch(legacyCustomMobileSharingDock, toolSettings);
      done(err, res);
    });
  });

  it('rewrites pco of underlying legacy custom sharing sidebar tool', function(done) {
    request
    .get('/plugins/'+pco+'/v/'+newVersion+'/site/'+pubId)
    .set('Authorization', apiKey)
    .set('Accept', json)
    .expect(200)
    .end(function(err, res) {
      var toolSettings = getWidgetById(res.body, legacyCustomMobileSharingDock.widgetId);
      expect(toolSettings).to.be.a('object');
      expect(toolSettings.id).to.equal(newShareingSidebarPCO);
      expect(toolSettings.style).to.equal('bordered');
      expect(toolSettings.desktopPosition).to.equal('hide');
      expect(toolSettings.mobilePosition).to.equal(legacyCustomMobileSharingDock.position);
      done(err, res);
    });
  });

  it('creates legacy mobile toolbar tool', function(done) {
    request
    .put('/plugins/'+pco+'/v/'+shareLegacyPcoLastVersion+'/site/'+pubId+'/widget')
    .set('Authorization', apiKey)
    .set('Content-Type', json)
    .set('Accept', json)
    .send(legacyMobileToolbar)
    .expect(200)
    .end(function(err, res) {
      var toolSettings = getWidgetById(res.body, legacyMobileToolbar.widgetId);
      confirmToolSettingsMatch(legacyMobileToolbar, toolSettings);
      done(err, res);
    });
  });

  it('rewrites pco of underlying legacy custom sharing sidebar tool', function(done) {
    request
    .get('/plugins/'+pco+'/v/'+newVersion+'/site/'+pubId)
    .set('Authorization', apiKey)
    .set('Accept', json)
    .expect(200)
    .end(function(err, res) {
      var toolSettings = getWidgetById(res.body, legacyMobileToolbar.widgetId);
      expect(toolSettings).to.be.a('object');
      expect(toolSettings.id).to.equal(legacyMobileToolbar.id);
      done(err, res);
    });
  });
});

describe('Multi-tool support (new consolidated PCO) for Share Tools', function() {
  this.timeout(15000);
  var americaInlineShareId1 = Math.random().toString().substr(-4);
  var americaInlineShareId2 = Math.random().toString().substr(-4);
  var americaFloatingShareId = Math.random().toString().substr(-4);
  var legacyMobileToolbarId = Math.random().toString().substr(-4);

  var americaInlineShareTool1 = {
      widgetId: americaInlineShareId1,
      'id': 'shin',
      'enabled': true,
      'countsFontSize': '60px',
      'hideEmailSharingConfirmation': false,
      //'counts': false,
      //'counts': 'none',
      'shareCountThreshold': 10,
      'originalServices':
        'facebook_like,tweet,pinterest_pinit,counter',
      'label': 'SHARES',
      '__hideOnHomepage': false,
      'numPreferredServices': 3,
      'size': 'large',
      'titleFontSize': '18px',
      'responsive': 979,
      'elements': [
          '.addthis_inline_share_toolbox'
      ],
      'counterColor': '#666666',
      'iconColor': '#FFFFFF',
      'creationTimestamp': 1480537145011,
      'hideDevice': 'none'
  };

  var americaInlineShareTool2 = {
      widgetId: americaInlineShareId2,
      'id': 'shin',
      'enabled': true,
      'countsFontSize': '60px',
      'hideEmailSharingConfirmation': false,
      //'counts': false,
      //'counts': 'none',
      'shareCountThreshold': 5,
      'originalServices':
        'facebook_like,tweet,pinterest_pinit,counter',
      'label': 'SHARES',
      '__hideOnHomepage': false,
      'numPreferredServices': 9,
      'size': 'large',
      'titleFontSize': '18px',
      'responsive': 979,
      'elements': [
          '.addthis_inline_share_toolbox_2'
      ],
      'counterColor': '#666666',
      'iconColor': '#FFFFFF',
      'creationTimestamp': 1480537145012,
      'hideDevice': 'none'
  };

  var americaFloatingShareTool = {
      widgetId: americaFloatingShareId,
      'id': 'shfs',
      'enabled': true,
      'hideEmailSharingConfirmation': false,
      //'counts': false,
      //'counts': 'none',
      'shareCountThreshold': 10,
      'desktopPosition': 'left',
      'creationTimestamp': 1480453303892,
      'mobilePosition': 'bottom',
      'postShareTitle': 'Thanks for sharing!',
      'hideLabel': false,
      'toolName': 'Sidebar',
      'backgroundColor': '#FFFFFF',
      'thankyou': true,
      'postShareRecommendedMsg': 'Recommended for you',
      'offset': {
          'location': 'top',
          'amount': 20,
          'unit': '%'
      },
      'label': '',
      'textColor': '#222222',
      '__hideOnHomepage': false,
      'numPreferredServices': 5,
      'borderRadius': '0%',
      'responsive': 979,
      'postShareFollowMsg': 'Follow',
      'iconColor': '#FFFFFF',
      'style': 'modern',
      'position': 'left'
  };

  var legacyMobileToolbar = {
      widgetId: legacyMobileToolbarId,
      "id": "smlmo",
      "enabled": true,
      "responsive": "979px",
      "position": "bottom",
      "buttonBarTheme": "dark",
      "follow": "on",
      "followServices": [
        {
          'service': 'facebook',
          'usertype': 'id',
          'id': 1234
        },
        {
          'service': 'linkedin',
          'usertype': 'company',
          'id': 'addthis'
        }
      ],
      "__hideOnHomepage": false,
      '__hideOnUrls': [
        'http://www.example.com/test1',
        'http://www.example.com/test2'
      ]
  };

  it('receives empty boost settings for new pubid', function(done) {
    getNewProfile('wp', function(aPubId, anApiKey) {
      pubId = aPubId;
      apiKey = anApiKey;
      request
      .get('/plugins/'+pco+'/v/'+version+'/site/'+pubId)
      .set('Authorization', apiKey)
      .set('Accept', json)
      .expect(200)
      .end(function(err, res) {
        checkBoostSettingsFormat(res.body);
        expect(res.body.templates).to.be.empty;
        done(err, res);
      });
    });
  });

  it('creates 1st consolidated inline share tool', function(done) {
    request
    .put('/plugins/'+pco+'/v/'+shareConsolidatedVersion+'/site/'+pubId+'/widget')
    .set('Authorization', apiKey)
    .set('Content-Type', json)
    .set('Accept', json)
    .send(americaInlineShareTool1)
    .expect(200)
    .end(function(err, res) {
      var toolSettings = getWidgetById(res.body, americaInlineShareTool1.widgetId);
      expect(toolSettings).to.be.a('object');
      done(err, res);
    });
  });

  it('saves expected pco outside legacy transformations for consolidated inline share tool', function(done) {
    request
    .get('/plugins/'+pco+'/v/'+shareConsolidatedVersion+'/site/'+pubId)
    .set('Authorization', apiKey)
    .set('Accept', json)
    .expect(200)
    .end(function(err, res) {
      var toolSettings = getWidgetById(res.body, americaInlineShareTool1.widgetId);
      expect(toolSettings).to.be.a('object');
      expect(toolSettings.id).to.equal(americaInlineShareTool1.id);
      assert.deepEqual(toolSettings, americaInlineShareTool1);
      done(err, res);
    });
  });

  it('creates 2nd consolidated inline share tool', function(done) {
    request
    .put('/plugins/'+pco+'/v/'+shareConsolidatedVersion+'/site/'+pubId+'/widget')
    .set('Authorization', apiKey)
    .set('Content-Type', json)
    .set('Accept', json)
    .send(americaInlineShareTool2)
    .expect(200)
    .end(function(err, res) {
      var toolSettings = getWidgetById(res.body, americaInlineShareTool2.widgetId);
      expect(toolSettings).to.be.a('object');

      // we should also be able to find americaInlineShareTool1 in the response
      var toolSettings = getWidgetById(res.body, americaInlineShareTool1.widgetId);
      expect(toolSettings).to.be.a('object');

      done(err, res);
    });
  });

  it('creates consolidated floating share tool', function(done) {
    request
    .put('/plugins/'+pco+'/v/'+shareConsolidatedVersion+'/site/'+pubId+'/widget')
    .set('Authorization', apiKey)
    .set('Content-Type', json)
    .set('Accept', json)
    .send(americaFloatingShareTool)
    .expect(200)
    .end(function(err, res) {
      var toolSettings = getWidgetById(res.body, americaFloatingShareTool.widgetId);
      expect(toolSettings).to.be.a('object');
      done(err, res);
    });
  });

  it('saves expected pco outside legacy transformations for consolidated floating share tool', function(done) {
    request
    .get('/plugins/'+pco+'/v/'+shareConsolidatedVersion+'/site/'+pubId)
    .set('Authorization', apiKey)
    .set('Accept', json)
    .expect(200)
    .end(function(err, res) {
      var toolSettings = getWidgetById(res.body, americaFloatingShareTool.widgetId);
      expect(toolSettings).to.be.a('object');
      expect(toolSettings.id).to.equal(americaFloatingShareTool.id);
      assert.deepEqual(toolSettings, americaFloatingShareTool);
      done(err, res);
    });
  });

  it('creates legacy mobile toolbar tool', function(done) {
    request
    .put('/plugins/'+pco+'/v/'+shareConsolidatedVersion+'/site/'+pubId+'/widget')
    .set('Authorization', apiKey)
    .set('Content-Type', json)
    .set('Accept', json)
    .send(legacyMobileToolbar)
    .expect(200)
    .end(function(err, res) {
      var toolSettings = getWidgetById(res.body, legacyMobileToolbar.widgetId);
      confirmToolSettingsMatch(legacyMobileToolbar, toolSettings);
      done(err, res);
    });
  });

  it('rewrites pco of underlying legacy custom sharing sidebar tool', function(done) {
    request
    .get('/plugins/'+pco+'/v/'+shareConsolidatedVersion+'/site/'+pubId)
    .set('Authorization', apiKey)
    .set('Accept', json)
    .expect(200)
    .end(function(err, res) {
      var toolSettings = getWidgetById(res.body, legacyMobileToolbar.widgetId);
      expect(toolSettings).to.be.a('object');
      expect(toolSettings.id).to.equal(legacyMobileToolbar.id);
      done(err, res);
    });
  });
});
