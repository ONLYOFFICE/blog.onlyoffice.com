appAddThisWordPress.directive('toolGallerySettingsPage', function(
  $wordpress,
  $q,
  $stateParams
) {
  return {
    scope: {
      parentSave: '=save', // bi-directional
      parentGoBack: '=goBack', // bi-directional
      toolNameMsgid: '@toolNameMsgid',
      toolDescriptionMsgid: '@toolDescriptionMsgid',
      featureSettings: '=featureSettings',
      toolPco: '@toolPco'
    },
    transclude: true,
    link: function($scope, el, attrs, ctrl, transclude) {
      $scope.saving = false;

      var stashOldSettings = function(newSettings) {
        $scope.originalSettings = angular.copy(newSettings[$scope.toolPco]);
        $scope.settings = $wordpress.addDefaultToolConfigurations(
          $scope.toolPco,
          newSettings[$scope.toolPco]
        );
      };

      $scope.$watch('featureSettings', function(newValue) {
        stashOldSettings(newValue);
      });

      if (!angular.isDefined($scope.featureSettings[$scope.toolPco])) {
        $scope.featureSettings[$scope.toolPco] = {};
      }

      stashOldSettings($scope.featureSettings);

      var transcludeElements = el
      .find('.transclude-here-tool-gallery-settings-page');

      if (transcludeElements.length === 0) {
        // for older version of jQuery :-(
        transcludeElements = jQuery(el)
        .find('[class*="transclude-here-tool-gallery-settings-page"]');
      }
      transcludeElements.append(transclude($scope));

      $scope.showToolSettings = function() {
        if (angular.isDefined($stateParams.toolPco) &&
          angular.isDefined($scope.toolPco) &&
          $scope.toolPco === $stateParams.toolPco
        ) {
          return true;
        } else {
          return false;
        }
      };
    },
    controller: function($scope) {
      $scope.save = function() {
        $scope.saving = true;

        if (typeof $scope.featureSettings[$scope.toolPco] === 'undefined') {
          $scope.featureSettings[$scope.toolPco] = {};
        }
        $scope.featureSettings[$scope.toolPco].enabled = true;

        $scope.parentSave($scope.toolPco).then(function() {
          $scope.goBack();
          $scope.saving = false;
        });
      };

      $scope.goBack = function() {
        var copy = angular.copy($scope.originalSettings);
        $scope.featureSettings[$scope.toolPco] = copy;
        $scope.parentGoBack();
      };
    },
    templateUrl:
    '/directives/toolGallerySettingsPage/toolGallerySettingsPage.html'
  };
});