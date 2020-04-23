appAddThisWordPress.directive('toggleSlider', function() {
  return {
    scope: {
      ngModel: '=ngModel', // bi-directional
      flip: '=flip',
      canEnable: '=canEnable',
      eventCallback: '=eventCallback',
      eventParam: '=eventParam'
    },
    transclude: true,
    controller: function($scope) {
      $scope.toggleValue = function() {
        if (typeof $scope.ngModel === 'undefined') {
          $scope.ngModel = true;
        }

        if ($scope.ngModel || $scope.canEnable) {
          $scope.ngModel = !$scope.ngModel;
        }

        if(angular.isFunction($scope.eventCallback)) {
          $scope.eventCallback($scope.eventParam);
        }
      };

      $scope.toggleClass = function() {
        if ($scope.ngModel && !$scope.flip) {
          return 'toggle active';
        } else {
          return 'toggle inactive';
        }
      };

      $scope.toggleAlt = function() {
        if ($scope.ngModel && !$scope.flip) {
          return 'tool_listing_toggle_enabled_alt';
        } else {
          return 'tool_listing_toggle_disabled_alt';
        }
      };

    },
    templateUrl: '/directives/toggleSlider/toggleSlider.html'
  };
});