'use strict';

angular.module('addthisWordpress', []);

angular.module('addthisWordpress').factory('$wordpress', function(
  $q,
  $http,
  $filter
) {
  var wordpress = {};

  // settingsPageId must match the backend/PHP variable in $settingsPageId in
  // the relevant feature object
  var featureConfigs = {
    globalOptions: {
      settingsPageId: 'addthis_advanced_settings',
      modeSpecific: false
    },
    followButtons: {
      settingsPageId: 'addthis_follow_buttons',
      modeSpecific: true,
      filter: 'follow'
    },
    sharingButtons: {
      settingsPageId: 'addthis_sharing_buttons',
      modeSpecific: true,
      filter: 'share'
    },
    relatedPosts: {
      settingsPageId: 'addthis_recommended_content',
      modeSpecific: true,
      filter: 'relatedposts'
    }
  };

  // savePrefix must match the backend/PHP variable in $ajaxSavePrefix
  var savePrefix = 'save_settings_';
  // getPrefix must match the backend/PHP variable in $ajaxGetPrefix
  var getPrefix = 'get_settings_';

  var getAjaxEndpoint = function() {
    if (window.addthis_ui.urls.ui) {
      return window.addthis_ui.urls.ajax;
    }
  };

  wordpress.widgetConfigUrl = function() {
    if (window.addthis_ui.urls.widgets) {
      return window.addthis_ui.urls.widgets;
    }
  };

  var wordpressRequest = function(action, data) {
    var deferred = $q.defer();

    var postObject = {
      action: action
    };

    if(angular.isDefined(data)) {
      if(angular.isObject(data)) {
        var dataJson = JSON.stringify(data);
        postObject.data = dataJson;
      } else {
        postObject.data = data;
      }
    }

    var postString = $filter('urlEncodeObject')(postObject);

    $http({
      method: 'POST',
      url: getAjaxEndpoint(),
      data: postString,
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
        'Accept': '*/*'
      }
    }).then(function(response) {
      deferred.resolve(response.data);
    });

    return deferred.promise;
  };

  var getNonce = function() {
    var promise = wordpressRequest('addthis_nonce').then(function(data) {
      return data.nonce;
    });

    return promise;
  };

  var featureSetup = function(featureName, featureObject) {
    featureObject = {
      promise: false,
      data: false
    };

    var get = function() {
      if (!featureObject.promise) {
        // get tool configs from wordpress
        var action = getPrefix + featureConfigs[featureName].settingsPageId;
        featureObject.promise = wordpressRequest(action)
        .then(function(fromWordPress) {
          featureObject.data = fromWordPress;
          return fromWordPress;
        });
      }

      return featureObject.promise;
    };

    var save = function(key, settings) {
      if (typeof key === 'string' && typeof settings === 'object') {
        featureObject.data[key] = settings;
      } else if (typeof key === 'object') {
        featureObject.data = key;
      }

      // update tool configs in wordpress
      featureObject.promise =  getNonce().then(function(nonce) {
        var action = savePrefix + featureConfigs[featureName].settingsPageId;

        var tmpFeatureObject = angular.copy(featureObject);
        angular.forEach(tmpFeatureObject.data, function(value, key) {
          if(key.search(/_json$/) !== -1) {
            try {
              tmpFeatureObject.data[key] = JSON.parse(value);
            } catch (e) {
              tmpFeatureObject.data[key] = '';
            }
          }
        });

        var data = {
          nonce: nonce,
          config: tmpFeatureObject.data
        };

        return wordpressRequest(action, data)
        .then(function(fromWordPress) {
          featureObject.data = fromWordPress;
          return fromWordPress;
        });
      });

      return featureObject.promise;
    };

    var toolsByWidgetIdObject = {
      promise: false,
      done: false,
      data: false
    };

    var getToolsByWidgetId = function() {
      return get().then(function(toolSettings) {
          toolsByWidgetIdObject.data = angular.copy(toolSettings);
          delete toolsByWidgetIdObject.data.startUpgradeAt;
          return toolsByWidgetIdObject.data;
      });
    };

    featureObject.filter = featureConfigs[featureName].filter;
    featureObject.get = get;
    featureObject.save = save;

    wordpress[featureName] = {
      get: get,
      save: save,
      getToolsByWidgetId: getToolsByWidgetId
    };
  };

  var globalOptions;
  featureSetup('globalOptions', globalOptions);

  var followButtons;
  featureSetup('followButtons', followButtons);

  var sharingButtons;
  featureSetup('sharingButtons', sharingButtons);

  var relatedPosts;
  featureSetup('relatedPosts', relatedPosts);

  wordpress.defaults = function(info) {
    var result = '';

    switch (info) {
      case 'email':
        if (window.addthis_ui.defaults.email) {
          result = window.addthis_ui.defaults.email;
        }
        break;
      case 'rss':
        if (window.addthis_ui.defaults.rss) {
          result = window.addthis_ui.defaults.rss;
        }
        break;
      case 'profileName':
        if (window.addthis_ui.siteName) {
          var dirty = window.addthis_ui.siteName;
          dirty = dirty.replace(/[^a-zA-Z0-9_()\s\-]+/g, '');
          dirty = dirty.replace(/\s{2,}/g, ' ');
          result = dirty.substring(0, 255);
        }
        break;
    }

    return result;
  };

  wordpress.templateBaseUrl = function() {
    if (window.addthis_ui.urls.ui) {
      return window.addthis_ui.urls.ui;
    }

    // todo something better here to "guess" at the UI url when not provided
    return 'http://localhost:3000/ui/';
  };

  var getServiceBaseUrl = function() {
    return wordpress.globalOptions.get().then(function(globalOptions) {
      var url;
      if (globalOptions.debug_enable === true &&
        globalOptions.addthis_environment.legnth > 0
      ) {
        var env = globalOptions.addthis_environment;
        url = 'http://cache-' + env + '.addthiscdn.com/';
      } else {
        url = 'https://cache.addthiscdn.com/';
      }

      return url;
    });
  };

  var serviceHttp = function(cfg) {
    var promise = getServiceBaseUrl().then(function(serviceBaseUrl) {
      cfg.url = serviceBaseUrl + cfg.url;

      return $http(cfg);
    });
    return promise;
  };

  var validatedApiKeyPromises = {};
  wordpress.addThisApiKeyCheck = function(profileId, apiKey) {
    if (typeof validatedApiKeyPromises[profileId+apiKey] !== 'undefined') {
      return validatedApiKeyPromises[profileId+apiKey];
    }

    var inputData = {
      profileId: profileId,
      apiKey: apiKey
    };

    var promise = wordpressRequest('addthis_check_api_key', inputData);
    validatedApiKeyPromises[profileId+apiKey] = promise;
    return promise;
  };

  wordpress.addThisGetProfiles = function(email, password) {
    var inputData = {
      email: email,
      password: password
    };

    var promise = wordpressRequest('addthis_get_profiles', inputData);
    return promise;
  };

  wordpress.addThisRelatedPosts = function() {
    var promise = wordpressRequest('addthis_check_recommended_content');
    return promise;
  };

  wordpress.addThisCreateAccount = function(email, password, country, newsletter) {
    var inputData = {
      email: email,
      password: password,
      country: country,
      newsletter: newsletter
    };

    var promise = wordpressRequest('addthis_create_account', inputData);
    return promise;
  };

  wordpress.addThisCreateApiKey = function(email, password, profileId) {
    var inputData = {
      email: email,
      password: password,
      profileId: profileId
    };

    var promise = wordpressRequest('addthis_create_api_key', inputData);
    return promise;
  };

  wordpress.addThisCreateProfile = function(email, password, name) {
    var inputData = {
      email: email,
      password: password,
      name: name
    };

    var promise = wordpressRequest('addthis_create_profile', inputData);
    return promise;
  };

  wordpress.addThisChangeProfileType = function(profileId, apiKey) {
    var inputData = {
      profileId: profileId,
      apiKey: apiKey
    };

    var promise = wordpressRequest('addthis_change_profile_type', inputData);
    return promise;
  };

  wordpress.addThisOtherPlugins = function() {
    var promise = wordpressRequest('addthis_check_old_plugins');
    return promise;
  };

  wordpress.addThisUpdateOtherPlugin = function(source) {
    var promise = getNonce().then(function(nonce) {
      var data = {
        nonce: nonce,
        source: source
      };

      var innerPromise = wordpressRequest(
        'addthis_change_old_plugin_profile_id',
        data
      );
      return innerPromise;
    });

    return promise;
  };

  wordpress.addThisCheckLogin = function(email, password) {
    var inputData = {
      email: email,
      password: password
    };

    var promise = wordpressRequest('addthis_check_login', inputData);
    return promise;
  };

  var compatibleWithBoostPromise;
  wordpress.compatibleWithBoost = function() {
    if (typeof compatibleWithBoostPromise !== 'undefined') {
      return compatibleWithBoostPromise;
    }

    compatibleWithBoostPromise = getNonce().then(function(nonce) {
      var data = {
        nonce: nonce,
        plugin_version: window.addthis_ui.plugin.version,
        plugin_pco: window.addthis_ui.plugin.pco
      };

      var innerPromise = wordpressRequest('addthis_boost_compatibility', data)
      .then(function(data) {
        if (angular.isDefined(data.success) &&
            data.success === true &&
            angular.isDefined(data.compatible) &&
            data.compatible === true
        ) {
          return true;
        }

        return false;
      });

      return innerPromise;
    });

    return compatibleWithBoostPromise;
  };

  var followServicesObject = {
    promise: false,
    done: false,
    data: false
  };

  wordpress.addThisGetFollowServices = function() {
    var deferred = $q.defer();

    if (followServicesObject.data !== false) {
      deferred.resolve(followServicesObject.data);
    } else if (followServicesObject.promise !== false) {
      return followServicesObject.promise;
    } else {
      serviceHttp({
        method: 'GET',
        url: 'services/v1/follow.en.json'
      }).then(function(response) {
        if (typeof response.status !== 'undefined' &&
          response.status === 200 &&
          typeof response.data === 'object' &&
          typeof response.data.data === 'object'
        ) {
          followServicesObject.data = response.data.data;
        }

        followServicesObject.done = true;
        followServicesObject.promise = false;
        deferred.resolve(followServicesObject.data);
      }, function() {
        followServicesObject.done = true;
        followServicesObject.promise = false;
        deferred.resolve(followServicesObject.data);
      });
    }

    followServicesObject.promise = deferred.promise;
    return followServicesObject.promise;
  };

  var shareServicesObject = {
    promise: false,
    done: false,
    data: false
  };

  var addThisShareEndpoint = function() {
    var deferred = $q.defer();

    if (shareServicesObject.data !== false) {
      deferred.resolve(shareServicesObject.data);
    } else if (shareServicesObject.promise !== false) {
      return shareServicesObject.promise;
    } else {
      serviceHttp({
        method: 'GET',
        url: 'services/v1/sharing.en.json'
      }).then(function(response) {
        if (typeof response.status !== 'undefined' &&
          response.status === 200 &&
          typeof response.data === 'object' &&
          typeof response.data.data === 'object'
        ) {
          shareServicesObject.data = response.data.data;
        }

        shareServicesObject.done = true;
        shareServicesObject.promise = false;
        deferred.resolve(shareServicesObject.data);
      }, function() {
        shareServicesObject.done = true;
        shareServicesObject.promise = false;
        deferred.resolve(shareServicesObject.data);
      });
    }

    shareServicesObject.promise = deferred.promise;
    return shareServicesObject.promise;
  };

  var addthisShareServicesObject = {
    promise: false,
    done: false,
    data: false
  };

  wordpress.addThisGetShareServices = function() {
    var deferred = $q.defer();

    if (addthisShareServicesObject.data !== false) {
      deferred.resolve(addthisShareServicesObject.data);
    } else if (addthisShareServicesObject.promise !== false) {
      return addthisShareServicesObject.promise;
    } else {
      addThisShareEndpoint().then(function(input) {
        var output = [];

        var exclude = [
          'facebook_like',
          'foursquare',
          'pinterest',
          'addressbar',
          'googleplus'
        ];

        input.forEach(function(serviceElement) {
          if (exclude.indexOf(serviceElement.code) === -1) {
            var serviceOptionsInfo = {
              code: serviceElement.code,
              icon: serviceElement.code,
              name: serviceElement.name,
              searchString: serviceElement.code + ' ' + serviceElement.name
            };

            output.push(serviceOptionsInfo);
          }
        });

        var addThisServiceOptionInfo = {
            code: 'addthis',
            icon: 'addthis',
            name: 'AddThis',
            searchString: 'addthis more plus counter',
            index: output.length
        };

        output.push(addThisServiceOptionInfo);

        addthisShareServicesObject.data = output;
        addthisShareServicesObject.done = false;
        addthisShareServicesObject.promise = false;

        deferred.resolve(addthisShareServicesObject.data);
      });
    }

    addthisShareServicesObject.promise = deferred.promise;
    return addthisShareServicesObject.promise;
  };


  var thirdPartyShareServicesOptions = [
    {
      code: 'facebook_like',
      icon: 'facebook',
      name: 'Facebook Like',
      searchString: 'Facebook Like'
    },
    {
      code: 'facebook_send',
      icon: 'facebook',
      name: 'Facebook Send',
      searchString: 'Facebook Send Messenger'
    },
    {
      code: 'facebook_share',
      icon: 'facebook',
      name: 'Facebook Share',
      searchString: 'Facebook Share'
    },
    {
      code: 'linkedin_counter',
      icon: 'linkedin',
      name: 'LinkedIn',
      searchString: 'LinkedIn'
    },
    {
      code: 'foursquare',
      icon: 'foursquare_follow',
      name: 'Foursquare',
      searchString: 'Foursquare'
    },
    {
      code: 'stumbleupon_badge',
      icon: 'stumbleupon',
      name: 'StumbleUpon',
      searchString: 'StumbleUpon'
    },
    {
      code: 'tweet',
      icon: 'twitter',
      name: 'Twitter Tweet',
      searchString: 'Twitter Tweet'
    },
    {
      code: 'pinterest_pinit',
      icon: 'pinterest_share',
      name: 'Pinterest Pin It',
      searchString: 'Pinterest Pin It'
    },
    {
      code: 'counter',
      icon: 'addthis',
      name: 'AddThis',
      searchString: 'addthis more plus counter'
    }
  ];

  wordpress.thirdPartyGetShareServices = function() {
    var deferred = $q.defer();
    deferred.resolve(thirdPartyShareServicesOptions);
    return deferred.promise;
  };

  var defaultToolConfigurations = {
    'esb': {
      position: 'bottom-right',
      numPreferredServices: 5,
      themeColor: undefined,
      __hideOnHomepage: false
    },
    'ist': {
      position: 'top-left-outside',
      numPreferredServices: 4,
      querySelector: '',
      borderRadius: '0%',
      buttonColor: undefined,
      iconColor: '#FFFFFF'
    },
    'cmtb': {
      position: 'bottom',
      numPreferredServices: 4,
      textColor: '#000000',
      buttonColor: undefined,
      iconColor: '#FFFFFF',
      backgroundColor: '#FFFFFF',
      __hideOnHomepage: false,
      responsive: 979,
      counts: true,
      shareCountThreshold: 10
    },
    'resh': {
      counters: 'none',
      numPreferredServices: 5,
      responsive: 979,
      elements: [
        '.addthis_responsive_sharing',
        '.at-above-post-homepage',
        '.at-below-post-homepage',
        '.at-above-post',
        '.at-below-post',
        '.at-above-post-page',
        '.at-below-post-page',
        '.at-above-post-cat-page',
        '.at-below-post-cat-page',
        '.at-above-post-arch-page',
        '.at-below-post-arch-page'
      ],
      shareCountThreshold: 10
    },
    'jsc': {
      color: '#666666',
      numPreferredServices: 3,
      responsive: 979,
      label: 'SHARES',
      elements: [
        '.addthis_jumbo_share',
        '.at-above-post-homepage',
        '.at-below-post-homepage',
        '.at-above-post',
        '.at-below-post',
        '.at-above-post-page',
        '.at-below-post-page',
        '.at-above-post-cat-page',
        '.at-below-post-cat-page',
        '.at-above-post-arch-page',
        '.at-below-post-arch-page'
      ],
      countsFontSize: '60px',
      titleFontSize: '18px'
    },
    'ctbx': {
      background: '#666666',
      shape: 'square',
      size: 'large',
      counts: false,
      numPreferredServices: 5,
      theme: 'custom',
      elements: [
        '.addthis_custom_sharing',
        '.at-above-post-homepage',
        '.at-below-post-homepage',
        '.at-above-post',
        '.at-below-post',
        '.at-above-post-page',
        '.at-below-post-page',
        '.at-above-post-cat-page',
        '.at-below-post-cat-page',
        '.at-above-post-arch-page',
        '.at-below-post-arch-page'
      ],
      shareCountThreshold: 10
    },
    'msd': {
      position: 'bottom',
      numPreferredServices: 4,
      services: [],
      __hideOnHomepage: false,
      responsive: 979,
      counts: true,
      shareCountThreshold: 10
    },
    'smlsh': {
      position: 'left',
      numPreferredServices: 5,
      theme: 'transparent',
      __hideOnHomepage: false,
      title: '',
      postShareTitle: 'Thanks for sharing!',
      postShareFollowMsg: 'Follow',
      postShareRecommendedMsg: 'Recommended for you',
      responsive: 979,
      thankyou: true,
      counts: true,
      offset: {
        location: 'top',
        amount: 20,
        unit: '%'
      },
      shareCountThreshold: 10
    },
    'tbx': {
      numPreferredServices: 5,
      size: 'large',
      counts: false,
      elements: [
        '.addthis_sharing_toolbox',
        '.at-above-post-homepage',
        '.at-below-post-homepage',
        '.at-above-post',
        '.at-below-post',
        '.at-above-post-page',
        '.at-below-post-page',
        '.at-above-post-cat-page',
        '.at-below-post-cat-page',
        '.at-above-post-arch-page',
        '.at-below-post-arch-page'
      ],
      shareCountThreshold: 10
    },
    'scopl': {
      numPreferredServices: 5,
      thirdPartyButtons: true,
      services: [
        'facebook_like',
        'tweet',
        'pinterest_pinit',
        'counter'
      ],
      elements: [
        '.addthis_native_toolbox',
        '.at-above-post-homepage',
        '.at-below-post-homepage',
        '.at-above-post',
        '.at-below-post',
        '.at-above-post-page',
        '.at-below-post-page',
        '.at-above-post-cat-page',
        '.at-below-post-cat-page',
        '.at-above-post-arch-page',
        '.at-below-post-arch-page'
      ]
    },
    'smlmo': {
      buttonBarPosition: 'bottom',
      buttonBarTheme: 'light',
      followServices: {},
      __hideOnHomepage: false,
      responsive: 979,
      share: 'on',
      follow: 'on'
    },
    'cflwh': {
      background: '#666666',
      shape: 'round',
      elements: ['.addthis_custom_follow'],
      theme: 'custom'
    },
    'smlfw': {
      title: 'Follow',
      theme: 'transparent',
      __hideOnHomepage: false,
      responsive: 979,
      thankyou: true,
      offset: {
        location: 'top',
        amount: 0,
        unit: 'px'
      }
    },
    'flwh': {
      title: 'Follow',
      size: 'large',
      orientation: 'horizontal',
      elements: ['.addthis_horizontal_follow_toolbox'],
      __hideOnHomepage: false,
      thankyou: true
    },
    'flwv': {
      title: 'Follow',
      size: 'large',
      orientation: 'vertical',
      elements: ['.addthis_vertical_follow_toolbox'],
      __hideOnHomepage: false,
      thankyou: true
    },
    'cod': {
      title: 'Recommended for you',
      position: 'right',
      theme: 'dark',
      promotedUrl: '',
      animationType: 'overlay',
      __hideOnHomepage: false
    },
    'tst': {
      title: 'Recommended for you',
      theme: 'light',
      __hideOnHomepage: false,
      responsive: 979,
      promotedUrl: '',
      scrollDepth: 25,
      offset: {
        location: 'right',
        amount: 0,
        unit: 'px'
      }
    },
    'jrcf': {
      __hideOnHomepage: false,
      responsive: 460,
      promotedUrl: '',
      title: 'Recommended for you',
      elements: []
    },
    'smlwn': {
      title: 'Recommended for you',
      theme: 'light',
      __hideOnHomepage: false,
      responsive: 979,
      promotedUrl: '',
      scrollDepth: 25,
      offset: {
        location: 'right',
        amount: 0,
        unit: 'px'
      }
    },
    'wnm': {
      title: 'Recommended for you',
      theme: 'light',
      promotedUrl: '',
      __hideOnHomepage: false,
      scrollDepth: 25
    },
    'smlre': {
      title: 'Recommended for you',
      theme: 'light',
      numrows: 1,
      maxitems: 3,
      promotedUrl: '',
      __hideOnHomepage: false
    },
    'smlrebh': {
      title: 'Recommended for you',
      theme: 'transparent',
      numrows: 1,
      maxitems: 4,
      promotedUrl: '',
      orientation: 'horizontal',
      elements: ['.addthis_recommended_horizontal']
    },
    'smlrebv': {
      title: 'Recommended for you',
      theme: 'transparent',
      maxitems: 4,
      elements: ['.addthis_recommended_vertical'],
      promotedUrl: '',
      orientation: 'vertical'
    }
  };

  wordpress.addDefaultToolConfigurations = function(toolPco, inputConfigs) {
    var defaultConfigs = {};
    if (typeof defaultToolConfigurations[toolPco] !== 'undefined') {
      defaultConfigs = angular.copy(defaultToolConfigurations[toolPco]);
    }

    if (typeof inputConfigs === 'undefined') {
      inputConfigs = {};
    }

    angular.forEach(defaultConfigs, function(value, key) {
      if (typeof inputConfigs[key] === 'undefined') {
        inputConfigs[key] = value;
      }
    });

    return inputConfigs;
  };

  return wordpress;
});