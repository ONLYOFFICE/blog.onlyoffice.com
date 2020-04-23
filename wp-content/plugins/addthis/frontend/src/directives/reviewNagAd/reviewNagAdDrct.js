appAddThisWordPress.directive('reviewNagAd', function($wordpress) {
  return {
    scope: {},
    controller: function($scope, $timeout) {
      if (angular.isDefined(window.addthis_ui.plugin) &&
        angular.isDefined(window.addthis_ui.plugin.slug)
      ) {
        $scope.slug = window.addthis_ui.plugin.slug;
      } else {
        $scope.slug = 'addthis-all';
      }

      $timeout(function() {
        if (typeof window.addthis !== 'undefined' &&
          typeof window.addthis.layers !== 'undefined'  &&
          typeof window.addthis.layers.refresh !== 'undefined'
        ) {
          window.addthis.layers.refresh();
        }
      });

      var globalOptions = {};
      var twoWeeks = 60 * 60 * 24 * 7 * 2; // in seconds
      var defaultState = '';
      var knownStates = [
        'dislike', // boo :-( how can we help?
        'like', // yay!
        'will not rate', // boo :-(
        'rated', // we love you too
        defaultState //nag
      ];

      $scope.helpEmailAddress='help@addthis.com';
      var loaded = false;
      $scope.newlyRated = false;
      $scope.state = defaultState;

      var unixTimestampNow = function() {
        var now = Date.now() / 1000; // in seconds
        now = Math.round(now);
        return now;
      };

      var lastInteraction = unixTimestampNow();

      var expiredInteration = function() {
        if ((unixTimestampNow() - twoWeeks) > lastInteraction) {
          return true;
        }

        return false;
      };

      // get global options
      $wordpress.globalOptions.get().then(function(data) {
        globalOptions = data;

        if (typeof data.addthis_rate_us !== 'undefined') {
          // is the value in state invalid?
          if (knownStates.indexOf(data.addthis_rate_us) === -1) {
            $scope.state = defaultState;
          } else {
            $scope.state = data.addthis_rate_us;
          }
        }

        if (typeof data.addthis_rate_us_timestamp !== 'undefined') {
          lastInteraction = data.addthis_rate_us_timestamp;
        }

        // have we nagged in the last two weeks?
        if ($scope.state !== 'rated' && expiredInteration()) {
          $scope.state = defaultState;
        }

        loaded = true;
      });

      // save stuff when things change
      $scope.changeState = function(newState, debugLastInteraction) {
        if (newState === 'rated' && $scope.state !== newState) {
          $scope.newlyRated = true;
        } else {
          $scope.newlyRated = false;
        }

        $scope.state = newState;

        if (typeof debugLastInteraction === 'undefined') {
          lastInteraction = unixTimestampNow();
        } else {
          lastInteraction = debugLastInteraction;
        }

        globalOptions.addthis_rate_us = newState;
        globalOptions.addthis_rate_us_timestamp = lastInteraction;
        $wordpress.globalOptions.save().then(function(data) {
          globalOptions = data;
        });
      };

      $scope.debug = function() {
        if (typeof globalOptions.debug_enable !== 'undefined') {
          return globalOptions.debug_enable;
        }

        return false;
      };

      $scope.debugStartOver = function() {
        $scope.changeState(defaultState);
      };

      $scope.debugExpireLastInteraction = function() {
        $scope.changeState($scope.state, unixTimestampNow() - twoWeeks - 1);
      };
    },
    templateUrl: '/directives/reviewNagAd/reviewNagAd.html'
  };
});