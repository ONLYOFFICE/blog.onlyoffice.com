appAddThisWordPress.directive('canEditToolsCheck', function(
  $wordpress,
  $q,
  $timeout,
  $darkseid,
  $state
) {
  return {
    transclude: true,
    link: function($scope, el, attrs, ctrl, transclude) {
      var removeAlertAndTransclude = function() {
        // transclude
        var transcludeElements = el
        .find('.transclude-here-after-can-edit-tools-check');
        if (transcludeElements.length === 0) {
          // for older version of jQuery :-(
          transcludeElements = jQuery(el)
          .find('[class*="transclude-here-after-can-edit-tools-check"]');
        }
        transcludeElements.append(transclude($scope));

        // delete alert
        var deleteElements = el.find('.can-edit-tools-check-alert');
        if (deleteElements.length === 0) {
          // for older version of jQuery :-(
          deleteElements = jQuery(el)
          .find('[class*="can-edit-tools-check-alert"]');
        }
        deleteElements.remove();

        $timeout(function() {
          if (typeof window.addthis !== 'undefined') {
            window.addthis.toolbox(
              '.transclude-here-after-can-edit-tools-check'
            );
          }
        });
      };

      var checkAddThisModeConfiguration = function() {
        // if we're in AddThis mode, we need to check on more things
        // do we still support this version of the plugin?
        var compatibilityCheck = $wordpress.compatibleWithBoost();
        // is the profile id valid?
        var validateProfile = $darkseid.validateAddThisProfileId(
          $scope.globalOptions.addthis_profile
        );
        // is the API key valid?
        var validateApiKey = $wordpress.addThisApiKeyCheck(
          $scope.globalOptions.addthis_profile,
          $scope.globalOptions.api_key
        );

        $q.all([compatibilityCheck, validateProfile, validateApiKey])
        .then(function(data) {
          var compatibility = data[0];
          var profile = data[1];
          var apikey = data[2];

          if(compatibility === false) {
            $scope.alert = $scope.alerts.unsupported;
          } else if(compatibility !== true) {
            $scope.alert = $scope.alerts.genericError;
          } else if ($state.current.name === 'registration.state') {
            // if we're on a registration page, we don't need to check for the rest
            removeAlertAndTransclude();
          } else if (!angular.isDefined(profile.success)) {
            $scope.alert = $scope.alerts.genericError;
          } else if (!profile.success) {
            $scope.alert = $scope.alerts.bogusProfile;
          } else if (!angular.isDefined(profile.data.type)) {
            $scope.alert = $scope.alerts.genericError;
          } else if (profile.data.type !== 'wp') {
            $scope.alert = $scope.alerts.badProfileType;
          } else if (!angular.isDefined(apikey.success)) {
            $scope.alert = $scope.alerts.genericError;
          } else if (apikey.success === false) {
            $scope.alert = $scope.alerts.badApiKey;
          } else {
            // all is good. no need for alerts
            removeAlertAndTransclude();
          }
        });
      };

      $darkseid.testPing().then(
        function() {
          // if we can talk to darkseid, check for more stuff
          $wordpress.globalOptions.get().then(function(globalOptions) {
            $scope.globalOptions = globalOptions;

            if ($scope.globalOptions.addthis_plugin_controls === 'WordPress') {
              // if we're in WordPress mode, we don't need to check anything more
              removeAlertAndTransclude();
            } else {
              checkAddThisModeConfiguration();
            }
          },
          function() {
            $scope.alert = $scope.alerts.genericError;
          });
        },
        function() {
          // if we can;t talk to darkseid, display relevant error
          $scope.alert = $scope.alerts.pingFailed;
        }
      );
    },
    controller: function($scope) {
      $scope.alerts = {
        loading: {
          level: 'info',
          msgid: 'progress_message_loading'
        },
        unsupported: {
          level: 'danger',
          msgid: 'error_message_unsupported_plugin'
        },
        bogusProfile: {
          level: 'danger',
          msgid: 'error_message_invalid_profile'
        },
        badProfileType: {
          level: 'danger',
          msgid: 'error_message_wrong_profile_type'
        },
        badApiKey: {
          level: 'danger',
          msgid: 'error_message_invalid_api_key'
        },
        genericError: {
          level: 'danger',
          msgid: 'error_message_tool_check_generic'
        },
        pingFailed: {
          level: 'danger',
          msgid: 'error_message_darkseid_ping_failed'
        }
      };

      $scope.alert = $scope.alerts.loading;
    },
    templateUrl: '/directives/canEditToolsCheck/canEditToolsCheck.html'
  };
});