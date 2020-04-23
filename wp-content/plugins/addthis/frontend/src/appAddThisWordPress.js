'use strict';

// Declare app level module which depends on views, and components
var appAddThisWordPress = angular.module(
  'appAddThisWordPress',
  [
    'addthis',
    'addthisDarkseid',
    'addthisWordpress',
    'cfp.hotkeys',
    'ngAria',
    'pascalprecht.translate',
    'ui.router'
  ]
);

appAddThisWordPress.config(function($sceDelegateProvider) {
  $sceDelegateProvider.resourceUrlWhitelist([
    'self',
    'https://www.addthis.com/darkseid/**',
    'https://cache.addthiscdn.com/services/**',
	  //include local and internal URLs for development purposes
    'http://localhost:3000/**',
    'http://darkseid/darkseid/**',
    'http://www-test.addthis.com/darkseid/**',
    'http://www-dev.addthis.com/darkseid/**',
    'http://www-local.addthis.com:8019/darkseid/**'
  ]);
});

appAddThisWordPress.config(function($translateProvider) {
  if ((typeof window.addthis_ui !== undefined) &&
    (typeof window.addthis_ui.locale !== undefined)
  ) {
      $translateProvider.preferredLanguage(window.addthis_ui.locale);
  } else {
      $translateProvider.preferredLanguage('en_US');
  }

  $translateProvider.fallbackLanguage(['en_US']);

  $translateProvider.useStaticFilesLoader({
    prefix: window.addthis_ui.urls.ui + 'build/l10n/addthis-frontend-',
    suffix: '.json'
  });

  $translateProvider.useSanitizeValueStrategy(null);
});

appAddThisWordPress.config(function($stateProvider, $urlRouterProvider) {
  $urlRouterProvider.otherwise(function($injector, $location){
    var state = 'registration';
    var wordpressPageRegex = /\?page=([a-z0-9_]+)/i;
    var matches = $location.absUrl().match(wordpressPageRegex);
    if (matches !== null && typeof matches[1] !== 'undefined') {
      var wpPageId = matches[1];
      if (wpPageId === 'addthis_registration') {
        state = 'registration';
      } else if (wpPageId === 'addthis_advanced_settings') {
        state = 'advanced';
      } else if (wpPageId === 'addthis_follow_buttons') {
        state = 'follow';
      } else if (wpPageId === 'addthis_sharing_buttons') {
        state = 'tools';
      } else if (wpPageId === 'addthis_recommended_content') {
        state = 'relatedpostslist';
      } else {
        state = 'oops';
      }
    }
    return state;
  });

  var globalOptionsPromise = function($wordpress) {
    return $wordpress.globalOptions.get();
  };

  var shareToolSettingsPromise = function(
    $stateParams,
    $q,
    $wordpress,
    modeHelper
  ) {
    var promise;

    if ($stateParams.toolPco === 'new') {
      promise = $q.defer();
      promise.resolve({});
    } else {
      promise = modeHelper.get($wordpress.sharingButtons, true)
      .then(function(result) {
        return result[$stateParams.toolPco];
      });
    }

    return promise;
  };

  var relatedPostsSettingsPromise = function(
    modeHelper,
    $wordpress,
    $darkseid,
    $filter,
    $q
  ) {
    var mainSettingsPromise = modeHelper.get($wordpress.relatedPosts)
    .then(function(result) {
      return $filter('toolType')(result, 'relatedposts');
    });

    var promoteUrlSettings = $wordpress.globalOptions.get()
    .then(function(globalOptions) {
      // if addthis mode
      if (globalOptions.addthis_plugin_controls === 'AddThis') {
        return $darkseid.getPromotedUrl();
      } else {
        return false;
      }
    });

    return $q.all([mainSettingsPromise, promoteUrlSettings])
    .then(function(results) {
      var relatedPosts = results[0];
      var promotedUrls = results[1];

      if (promotedUrls !== false) {
        angular.forEach(promotedUrls, function(urls, toolPco) {
          if (typeof relatedPosts[toolPco] === 'object') {
            relatedPosts[toolPco].promotedUrl = urls[0];
          } else {
            relatedPosts[toolPco] = { promotedUrl: urls[0] };
          }
        });
      }

      return relatedPosts;
    });
  };

  $stateProvider
  .state('registration', {
    url: '/registration',
    templateUrl: '/features/Registration/RegistrationParent.html'
  })
  .state('registration.state', {
    url: '/:registrationState',
    templateUrl: '/features/Registration/RegistrationParent.html'
  })
  .state('advanced', {
    url: '/advanced',
    templateUrl: '/features/AdvancedSettings/AdvancedSettingsParent.html'
  })
  .state('follow', {
    url: '/follow',
    templateUrl:
    '/features/FollowButtonSettings/FollowButtonSettingsParent.html'
  })
  .state('follow.pco', {
    url: '/pco/:toolPco',
    templateUrl:
    '/features/FollowButtonSettings/FollowButtonSettingsParent.html'
  })
  .state('follow_conflict', {
    url: '/follow_conflict/:toolPco',
    templateUrl: '/features/FollowButtonConflict/FollowButtonConflict.html'
  })
  .state('tools', {
    url: '/tools',
    templateUrl: '/features/ToolList/ToolList.html'
  })
  .state('configurator', {
    params: { settings: {} },
    url: '/tool/settings/:toolPco',
    templateUrl: '/features/ToolSettings/ToolSettings.html',
    controller: 'ToolSettingsCtrl',
    resolve: {
      globalOptions: globalOptionsPromise,
      toolSettings: shareToolSettingsPromise
    }
  })
  .state('newShareTool', {
    url: '/tool/share/new',
    templateUrl: '/features/NewTool/NewShareTool.html'
  })
  .state('relatedpostslist', {
    url: '/relatedpostslist',
    templateUrl: '/features/RelatedPostSettings/RelatedPostToolListings.html',
    controller: 'RelatedPostSettingsCtrl',
    resolve: {
      globalOptions: globalOptionsPromise,
      toolSettings: relatedPostsSettingsPromise
    }
  })
  .state('relatedpostssettings', {
    url: '/relatedpostssettings/:toolPco',
    templateUrl: '/features/RelatedPostSettings/RelatedPostSettings.html',
    controller: 'RelatedPostSettingsCtrl',
    resolve: {
      globalOptions: globalOptionsPromise,
      toolSettings: relatedPostsSettingsPromise
    }
  })
  .state('oops', {
    url: '/oops',
    templateUrl: '/features/OopsSettings/OopsSettings.html'
  })
  .state('mock1', {
    url: '/mock1',
    templateUrl: '/mocks/mock1.html'
  })
  .state('mock2', {
    url: '/mock2',
    templateUrl: '/mocks/mock2.html'
  })
  .state('mock3', {
    url: '/mock3',
    templateUrl: '/mocks/mock3.html'
  })
  .state('mock4', {
    url: '/mock4',
    templateUrl: '/mocks/mock4.html'
  })
  .state('mock5', {
    url: '/mock5',
    templateUrl: '/mocks/mock5.html'
  });
});