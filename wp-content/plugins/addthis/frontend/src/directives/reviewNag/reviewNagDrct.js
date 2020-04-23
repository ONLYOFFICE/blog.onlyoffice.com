appAddThisWordPress.directive('reviewNag', function($wordpress) {
  return {
    scope: {},
    controller: function($scope) {
      if (angular.isDefined(window.addthis_ui.plugin) &&
        angular.isDefined(window.addthis_ui.plugin.slug)
      ) {
        $scope.slug = window.addthis_ui.plugin.slug;
      } else {
        $scope.slug = 'addthis-all';
      }

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
      var now = unixTimestampNow();

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
        if ($scope.state !== 'rated' && ((now - twoWeeks) > lastInteraction)) {
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

      $scope.show = function() {
        // hide if we're not ready yet
        if (loaded === false) {
          return false;
        }

        // hide if they were so kind as to rate us already (if they just rated
        // us, we thank them)
        if ($scope.state === 'rated' && $scope.newlyRated === false) {
          return false;
        }

        if ($scope.state !== 'rated' && expiredInteration()) {
          // if they don't like us or didn't want to rate us more than two
          // weeks ago, reset to default state
          $scope.state = defaultState;
        } else if ($scope.state === 'will not rate') {
          // if they said no to rating us in the last two weeks, don't show
          return false;
        }

        return true;
      };

      var expiredInteration = function() {
        if ((unixTimestampNow() - twoWeeks) > lastInteraction) {
          return true;
        }

        return false;
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
    templateUrl: '/directives/reviewNag/reviewNag.html'
  };
});