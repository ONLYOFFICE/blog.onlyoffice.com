'use strict';

angular.module('addthis', []);

angular.module('addthis').factory('$addthis', function($q) {
  var service = {};
  var defaultInterval = 100;

  var load = {
    promise: false,
    interval: 10
    // millaseconds for checking when addthis has been loaded -- needs to be
    // shorter than the time it takes for lojson to respond, else we can't
    // catch the lojson request response event
  };

  service.load = function() {
    if(load.promise) {
      return load.promise;
    }
    var deferred = $q.defer();

    if(window.addthis) {
      deferred.resolve(window.addthis);
    } else {
      var addThisCheckInterval = setInterval(function() {
        if(window.addthis) {
          clearInterval(addThisCheckInterval);
          load.done = true;
          deferred.resolve(window.addthis);
        }
      }, load.internal);
    }

    load.promise = deferred.promise;
    return load.promise;
  };

  var atw = {
    promise: false
  };

  service.atw = function() {
    if(atw.promise) {
      return atw.promise;
    }
    var deferred = $q.defer();

    if(window._atw) {
      deferred.resolve(window._atw);
    } else {
      var callback = function() {
        var addThisCheckInterval = setInterval(function() {
          if(window._atw) {
            clearInterval(addThisCheckInterval);
            load.done = true;
            deferred.resolve(window._atw);
          }
        }, defaultInterval);
      };

      service.load().then(callback);
    }

    atw.promise = deferred.promise;
    return atw.promise;
  };

  var lojson = {
    promise: false,
    done: false,
    data: {}
  };

  service.lojson = function() {
    if(lojson.promise) {
      return lojson.promise;
    }

    var deferred = $q.defer();
    if (lojson.done) {
      deferred.resolve(lojson.data);
    } else {
      var callback = function() {
        window.addthis.addEventListener('addthis.pro.init', function(event) {
          lojson.done = true;
          lojson.data = event.data;

          deferred.resolve(lojson.data);
        }, true);
      };

      service.load().then(callback);
    }

    lojson.promise = deferred.promise;
    return lojson.promise;
  };

  var scriptOnPage = false;
  service.checkForScript = function() {
    if(!scriptOnPage) {
      var matches = document.querySelectorAll('script[src~=addthis_widget.js]');
      if(matches.length > 0) {
        scriptOnPage = true;
      }
    }

    return scriptOnPage;
  };

  service.add = function(cfg) {
    var appendElement = {};
    if(cfg.scriptPlacement === 'header') {
      appendElement = angular.element('header');
    } else {
      appendElement = angular.element('body');
    }

    var baseUrl = 'http://s7.addthis.com/js/300/addthis_widget.js';
    var url = baseUrl;

    //if(cfg.enviroment) {
      // build url for local, dev or test
    //}

    if(cfg.profileId) {
      url = url + '#pubid=' + cfg.profileId;
    }

    var script = '<script src="'+url+'"></script>';
    angular.element(script).appendTo(appendElement);
    // do we also want to add namespaces onto the html tag for XHTML?

    return service.load();
  };

  var sharingServices = {
    promise: false,
    done: false,
    data: {}
  };

  service.sharingServices = function() {
    if(sharingServices.promise) {
      return sharingServices.promise;
    }

    var deferred = $q.defer();
    if (sharingServices.done) {
      deferred.resolve(sharingServices.data);
    } else {
      var callback = function(_atw) {
          sharingServices.done = true;
          sharingServices.data = _atw.list;

          deferred.resolve(sharingServices.data);
      };

      service.atw().then(callback);
    }

    sharingServices.promise = deferred.promise;
    return sharingServices.promise;
  };

  return service;
});
