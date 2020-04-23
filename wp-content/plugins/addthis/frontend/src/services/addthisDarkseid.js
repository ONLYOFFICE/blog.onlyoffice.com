'use strict';

angular.module('addthisDarkseid', ['addthisWordpress']);

angular.module('addthisDarkseid').factory('$darkseid', function(
    $q,
    $http,
    $wordpress
) {
  var darkseid = {};
  var countStringValues = ['none', 'each', 'one', 'both', 'jumbo'];

  var getDarkseidBaseUrl = function() {
    return $wordpress.globalOptions.get().then(function(globalOptions) {
      var url;
      if (globalOptions.debug_enable === true &&
        globalOptions.darkseid_environment.length > 0
      ) {
        var env = globalOptions.darkseid_environment;
        if (env === 'local') {
          url = 'http://www-local.addthis.com:8019/darkseid/';
        } else {
          url = 'http://www-' + env  +'.addthis.com/darkseid/';
        }
      } else {
        url = 'https://www.addthis.com/darkseid/';
      }

      return url;
    });
  };

  var darkseidHttp = function(cfg) {
    var promise = getDarkseidBaseUrl().then(function(darkseidBaseUrl) {
      cfg.url = darkseidBaseUrl + cfg.url;

      return $http(cfg);
    });

    return promise;
  };

  var validatedProfileIdPromises = {};
  darkseid.validateAddThisProfileId = function(profileId) {
    var url = 'plugins/' + window.addthis_ui.plugin.pco +
      '/v/' + window.addthis_ui.plugin.version +
      '/site/' + profileId;

    if (typeof validatedProfileIdPromises[profileId] !== 'undefined') {
      return validatedProfileIdPromises[profileId];
    }

    var promise = darkseidHttp({
      method: 'GET',
      url: url
    }).then(function(response) {
      var result = { success: false };
      if (typeof response.status !== 'undefined') {
        if (response.status === 200) {
          result.success = true;
        }
      }

      if (typeof response.data !== 'undefined') {
        result.data = response.data;
      }

      if (result.success === false) {
        if (typeof response.data !== 'undefined' &&
          typeof response.data.error !== 'undefined'
        ) {
          result.message = response.data.error;
        } else {
          result.message = 'Something went wrong';
        }
      }

      return result;
    }, function() {
      var result = {
        success: false,
        message: 'Something went wrong'
      };

      return result;
    });

    validatedProfileIdPromises[profileId] = promise;
    return promise;
  };

  var transformOutboundFollowServices = function(input) {
    var output = [];
    angular.forEach(input, function(id, service) {
      if (id === '') {
        return;
      }
      var tmpService = {};
      var delimiter = '_';

      var parts = service.split(delimiter);

      if (parts.length > 1) {
        var tmpUserType = parts.pop();
        if (tmpUserType === 'id') {
          tmpService.usertype = 'user';
        } else {
          tmpService.usertype = tmpUserType;
        }
        tmpService.service = parts.join(delimiter);
      } else {
        //tmpService.usertype = 'id';
        tmpService.service = service;
      }

      tmpService.id = id;

      output.push(tmpService);
    });

    return output;
  };

  var promotedUrlsPromise = false;
  darkseid.getPromotedUrl = function(cache) {
    if (promotedUrlsPromise !== false &&
      cache !== false
    ) {
      return promotedUrlsPromise;
    } else {
      promotedUrlsPromise = $wordpress.globalOptions.get()
      .then(function(globalOptions) {
        var darkseidUrl = 'wordpress/site/' + globalOptions.addthis_profile +
          '/campaigns/';

        return darkseidHttp({
          method: 'GET',
          url: darkseidUrl,
          headers: {
            'Accept':        'application/json',
            'Content-Type':  'application/json',
            'Authorization': globalOptions.api_key
          }
        }).then(function(response) {
          var currentPromotedUrls = response.data;
          return currentPromotedUrls;
        });
      });
    }
    return promotedUrlsPromise;
  };

  var addPromotedUrl = function(toolPco, url) {
    promotedUrlsPromise = $wordpress.globalOptions.get()
    .then(function(globalOptions) {
      var darkseidUrl = 'wordpress/site/' + globalOptions.addthis_profile +
        '/campaigns/' + toolPco;

      return darkseidHttp({
        method: 'POST',
        url: darkseidUrl,
        headers: {
          'Accept':        'application/json',
          'Content-Type':  'application/json',
          'Authorization': globalOptions.api_key
        },
        data: [url]
      }).then(function(response) {
        var currentPromotedUrls = response.data;
        return currentPromotedUrls;
      });
    });

    return promotedUrlsPromise;
  };

  var deletePromotedUrl = function(toolPco) {
    promotedUrlsPromise = $wordpress.globalOptions.get().then(function(globalOptions) {
      var darkseidUrl = 'wordpress/site/' + globalOptions.addthis_profile +
        '/campaigns/' + toolPco;

      return darkseidHttp({
        method: 'DELETE',
        url: darkseidUrl,
        headers: {
          'Accept':        'application/json',
          'Content-Type':  'application/json',
          'Authorization': globalOptions.api_key
        }
      }).then(function(response) {
        var currentPromotedUrls = response.data;
        return currentPromotedUrls;
      });
    });

    return promotedUrlsPromise;
  };

  var savePromotedUrl = function(toolPco, url) {
    var promise;

    return darkseid.getPromotedUrl().then(function(currentPromotedUrls) {
      if (typeof url === 'undefined' || url === '') {
        if (typeof currentPromotedUrls[toolPco] === 'undefined' ||
          url !== currentPromotedUrls[toolPco][0]
        ) {
          promise = deletePromotedUrl(toolPco);
        }
      } else if (typeof currentPromotedUrls[toolPco] === 'undefined' ||
        url !== currentPromotedUrls[toolPco][0]
      ){
        promise = addPromotedUrl(toolPco, url);
      }
      return promise;
    });
  };

  var promoteUrlPromises = [];
  var transformOutboundBoostConfig = function(toolPco, input) {
    var output = angular.copy(input);

    output.id = input.id || toolPco;

    if (input.auto_personalization === true &&
      typeof output.services !== 'undefined'
    ) {
      delete output.services;
    }

    if (typeof output.templates !== 'undefined') {
      delete output.templates;
    }

    // reformat services for follow buttons
    if (angular.isDefined(output.services) &&
      (typeof output.services === 'object')
    ) {
      if (Array.isArray(output.services)) {
        output.services = output.services.join(',');
      } else {
        output.services = transformOutboundFollowServices(output.services);
      }
    }

    // reformat original services for follow buttons
    if (angular.isDefined(output.originalServices) &&
      (typeof output.originalServices === 'object') &&
      Array.isArray(output.originalServices)
    ) {
      output.originalServices = output.originalServices.join(',');
    }

    if (angular.isDefined(output.followServices)) {
      if (typeof output.followServices === 'object') {
        // reformat services for follow buttons
        output.followServices =
          transformOutboundFollowServices(output.followServices);
      }
    }

    if (angular.isDefined(input.__hideOnUrls) &&
      typeof input.__hideOnUrls === 'string'
    ) {
      output.__hideOnUrls = input.__hideOnUrls.split(/\n|,/);
    }

    // reformat offsets for floating tools (remove px and more)
    if (angular.isDefined(output.offset) &&
      angular.isDefined(output.offset.location) &&
      angular.isDefined(output.offset.amount)
    ) {
      var reformatOffset = {};

      var unit = 'px';
      if (angular.isDefined(output.offset.unit)) {
        unit = output.offset.unit;
      }

      reformatOffset[output.offset.location] = output.offset.amount + unit;
      output.offset = reformatOffset;
    }

    // reformat responsive (remove px)
    if (angular.isDefined(output.responsive) &&
      (typeof output.responsive === 'number')
    ) {
      output.responsive = output.responsive + 'px';
    }

    // clean up elements and make it an array
    if (angular.isDefined(output.elements)) {
      output.elements = output.elements.join(',');
    }

    promoteUrlPromises.push(savePromotedUrl(toolPco, output.promotedUrl));
    delete output.promotedUrl;
    delete output.tmp;

    return output;
  };

  var transformIncomingFollowServices = function(input) {
    var output = {};
    angular.forEach(input, function(service) {
      var userType;
      if (service.service === 'facebook') {
        userType = 'user';
      } else if (typeof service.usertype === 'undefined') {
        userType = 'user';
      } else if (service.usertype === 'id') {
        userType = 'user';
      } else {
        userType = service.usertype;
      }

      output[service.service + '_' + userType] = service.id;
    });

    return output;
  };

  var cleanUpIncomingBoostBooleans = function(value) {
    if (value === true || value === 'true' || value === 'on' || value === 1) {
      return true;
    }

    return false;
  };

  darkseid.cleanupBoostConfigValues = function(widget) {
      if (angular.isDefined(widget.services)) {
        if (typeof widget.services === 'object') {
        // reformat services for follow buttons
          widget.services = transformIncomingFollowServices(widget.services);
        } else {
          // reformat services for share buttons
          if (widget.services.trim().length !== 0) {
            widget.services = widget.services.trim().split(',');
          } else {
            widget.services = [];
          }
        }
      } else if (angular.isDefined(widget.numPreferredServices) &&
        !angular.isDefined(widget.auto_personalization)
      ) {
        widget.auto_personalization = true;
      }

      if (angular.isDefined(widget.originalServices)) {
          // reformat original services for share buttons
          if (widget.originalServices.trim().length !== 0) {
            widget.originalServices = widget.originalServices.trim().split(',');
          } else {
            widget.originalServices = [];
          }
      }

      if (angular.isDefined(widget.followServices)) {
        if (typeof widget.followServices === 'object') {
        // reformat services for follow buttons
          widget.followServices =
            transformIncomingFollowServices(widget.followServices);
        }
      }

      if (angular.isDefined(widget.__hideOnUrls) &&
        Array.isArray(widget.__hideOnUrls)
      ) {
        widget.__hideOnUrls = widget.__hideOnUrls.join('\n');
      }

      // reformat offsets for floating tools (remove px and more)
      if (angular.isDefined(widget.offset)) {
        var reformatedOffset = {};
        var rawOffsetAmount = false;

        if (angular.isDefined(widget.offset.top)) {
          reformatedOffset.location = 'top';
          rawOffsetAmount = widget.offset.top;
        }

        if (angular.isDefined(widget.offset.bottom)) {
          reformatedOffset.location = 'bottom';
          rawOffsetAmount = widget.offset.bottom;
        }

        if (angular.isDefined(widget.offset.left)) {
          reformatedOffset.location = 'left';
          rawOffsetAmount = widget.offset.left;
        }

        if (angular.isDefined(widget.offset.right)) {
          reformatedOffset.location = 'right';
          rawOffsetAmount = widget.offset.right;
        }

        if (rawOffsetAmount) {
          var offsetParseRegex = /(\d+)(px|%)?/;
          var offsetMatches = rawOffsetAmount.match(offsetParseRegex);
          if (offsetMatches !== null) {
            if (angular.isDefined(offsetMatches[1])) {
              reformatedOffset.amount = offsetMatches[1];
            }
            if (angular.isDefined(offsetMatches[2])) {
              reformatedOffset.unit = offsetMatches[2];
            }
          }

        }

        widget.offset = reformatedOffset;
      }

      // reformat responsive (remove px)
      if (angular.isDefined(widget.responsive)) {
        if (typeof widget.responsive === 'string') {
          widget.responsive = widget.responsive.substring(
            0,
            widget.responsive.length - 2
          );
        } else if ((typeof widget.responsive === 'object') &&
          angular.isDefined(widget.responsive.maxWidth)
        ) {
          widget.responsive = widget.responsive.maxWidth.substring(
            0,
            widget.responsive.length - 2
          );
        }
      }

      // clean up elements and make it an array
      if (angular.isDefined(widget.elements) &&
        typeof widget.elements === 'string'
      ) {
        widget.elements = widget.elements.split(',');
        widget.elements.forEach(function(element, index) {
          if (element.length === 0) {
            widget.elements.splice(index, 1);
          }
        }, this);
      }

      // don't show me grey - this UI uses gray exclusively for theme
      if (angular.isDefined(widget.theme) && widget.theme === 'grey') {
        widget.theme = 'gray';
      }

      // make sure fields that are suppose to be integers are actually integers
      if (angular.isDefined(widget.offset) &&
        angular.isDefined(widget.offset.amount)
      ) {
        widget.offset.amount = parseInt(widget.offset.amount, 10);
      }

      if (angular.isDefined(widget.responsive)) {
        widget.responsive = parseInt(widget.responsive, 10);
      }

      if (angular.isDefined(widget.numrows)) {
        widget.numrows = parseInt(widget.numrows, 10);
      }

      if (angular.isDefined(widget.maxitems)) {
        widget.maxitems = parseInt(widget.maxitems, 10);
      }

      if (angular.isDefined(widget.numPreferredServices)) {
        widget.numPreferredServices = parseInt(widget.numPreferredServices, 10);
      }

      // booleans: enabled, thankyou, __hideOnHomepage, counts
      if (angular.isDefined(widget.enabled)) {
        widget.enabled = cleanUpIncomingBoostBooleans(widget.enabled);
      }

      if (angular.isDefined(widget.thankyou)) {
        widget.thankyou = cleanUpIncomingBoostBooleans(widget.thankyou);
      }

      if (angular.isDefined(widget.__hideOnHomepage)) {
        widget.__hideOnHomepage =
          cleanUpIncomingBoostBooleans(widget.__hideOnHomepage);
      }

      if (angular.isDefined(widget.counts)) {
        if (countStringValues.indexOf(widget.counts) === -1) {
          widget.counts = cleanUpIncomingBoostBooleans(widget.counts);
        }

      }
  };

  darkseid.transformIncomingBoostConfigs = function(input, america) {
    var output = {};
    angular.forEach(input, function(toolSettings) {
      darkseid.cleanupBoostConfigValues(toolSettings);
      if (america) {
        output[toolSettings.widgetId] = toolSettings;
      } else {
        output[toolSettings.id] = toolSettings;
      }
    });

    return output;
  };

  darkseid.generateNewWidgetId = function() {
    // bit-shift to get an int
    var randomNum = Math.random() * Math.pow(36, 4) << 0;
    var randomString = randomNum.toString(36);
    // The line below guarantees that we get at least 4 digits in case
    // `randomString` happens to have fewer.
    var paddedString = ('0000' + randomString).slice(-4);
    return paddedString;
  };

  darkseid.updateBoostConfigs = function(toolPco, toolSettings, america) {
    var settingsForBoost = transformOutboundBoostConfig(toolPco, toolSettings);

    return $wordpress.globalOptions.get().then(function(globalOptions) {
      var url = 'plugins/' + window.addthis_ui.plugin.pco +
        '/v/' + window.addthis_ui.plugin.version +
        '/site/' + globalOptions.addthis_profile +
        '/widget';

      return darkseidHttp({
        method: 'PUT',
        url: url,
        headers: {
          'Accept':        'application/json',
          'Content-Type':  'application/json',
          'Authorization': globalOptions.api_key
        },
        data: settingsForBoost
      }).then(function(response) {
          response.data.templates.forEach(function(template) {
            if (template.id === '_default' && template.widgets) {
              template.widgets = darkseid.transformIncomingBoostConfigs(
                template.widgets,
                america
              );
            }
          });

          boostConfigsObject.data = response.data;

          var innerInnerPromise = $q.all(promoteUrlPromises).then(function() {
            promoteUrlPromises = [];
            return boostConfigsObject;
          });

          return innerInnerPromise;
      });
    });
  };

  var boostConfigsObject = {
    promise: false,
    done: false,
    data: false
  };

  darkseid.getBoostConfigs = function(cache, america) {
    var deferred = $q.defer();

    if (boostConfigsObject.data !== false &&
      angular.isDefined(cache) &&
      cache === true
    ) {
      deferred.resolve(boostConfigsObject.data);
    } else if (boostConfigsObject.promise !== false) {
      return boostConfigsObject.promise;
    } else {
      $wordpress.globalOptions.get().then(function(globalOptions) {
        var url = 'plugins/' + window.addthis_ui.plugin.pco +
          '/v/' + window.addthis_ui.plugin.version +
          '/site/' + globalOptions.addthis_profile;

        darkseidHttp({
          method: 'GET',
          url: url,
          headers: {
            'Accept':        'application/json',
            'Authorization': globalOptions.api_key
          }
        }).then(function(response) {
          angular.forEach(response.data.templates, function(template) {
            if (angular.isDefined(template.id) &&
              angular.isDefined(template.widgets) &&
              template.id === '_default'
            ) {
              template.widgets = darkseid.transformIncomingBoostConfigs(
                template.widgets,
                america
              );
            }
          });

          boostConfigsObject.data = response.data;
          boostConfigsObject.done = false;
          boostConfigsObject.promise = false;
          deferred.resolve(boostConfigsObject.data);
        });
      });
    }

    boostConfigsObject.promise = deferred.promise;
    return boostConfigsObject.promise;
  };

  darkseid.getToolsByWidgetId = function() {
    return darkseid.getBoostConfigs(true, true)
    .then(function(fromBoost) {
      var output = {};
      if (angular.isDefined(fromBoost) &&
          (typeof fromBoost === 'object') &&
          fromBoost !== null &&
          angular.isDefined(fromBoost.templates)
      ) {
        angular.forEach(fromBoost.templates, function(template) {
          if (angular.isDefined(template.id) &&
            angular.isDefined(template.widgets) &&
            template.id === '_default'
          ) {
            angular.forEach(template.widgets, function(toolSettings) {
              output[toolSettings.widgetId] = toolSettings;
            });
          }
        });

        return output;
      }
    });
  };

  darkseid.getToolSettings = function() {
    // get tool configs from boost
    return darkseid.getBoostConfigs(true)
    .then(function(fromBoost) {
      var output = {};
      if (angular.isDefined(fromBoost) &&
          (typeof fromBoost === 'object') &&
          fromBoost !== null &&
          angular.isDefined(fromBoost.templates)
      ) {
        angular.forEach(fromBoost.templates, function(template) {
          if (angular.isDefined(template.id) &&
            angular.isDefined(template.widgets) &&
            template.id === '_default'
          ) {
            output = template.widgets;
          }
        });
      }

      return output;
    });
  };

  darkseid.testPing = function() {
    var url = 'test/ping';

    var promise = darkseidHttp({
      method: 'GET',
      url: url
    });

    return promise;
  };

  return darkseid;
});