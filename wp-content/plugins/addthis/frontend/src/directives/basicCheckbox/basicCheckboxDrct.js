appAddThisWordPress.directive('basicCheckbox', function() {
  return {
    scope: {
      ngModel: '=ngModel', // bi-directional
      recommend: '=recommend',
      label: '@label',
      formFieldName: '@for',
      warningText: '@warningText'
    },
    transclude: true,
    link: function(scope, el, attrs, ctrl, transclude) {
      var transcludeElements = el.find('.transclude-here-basic-checkbox');
      if (transcludeElements.length === 0) {
        // for older version of jQuery :-(
        transcludeElements = jQuery(el)
        .find('[class*="transclude-here-basic-checkbox"]');
      }
      transcludeElements.append(transclude());
    },
    controller: function($scope) {
      $scope.recommended = false;
      $scope.notRecommended = false;
      $scope.hasRecommendation = false;

      if ($scope.recommend === true) {
        $scope.recommended = true;
        $scope.hasRecommendation = true;
      } else if ($scope.recommend === false) {
        $scope.notRecommended = true;
        $scope.hasRecommendation = true;
      }

      $scope.warning = function() {
        if ($scope.recommended && !$scope.ngModel) {
          return true;
        }

        if ($scope.notRecommended && $scope.ngModel) {
          return true;
        }

        return false;
      };
    },
    templateUrl: '/directives/basicCheckbox/basicCheckbox.html'
  };
});