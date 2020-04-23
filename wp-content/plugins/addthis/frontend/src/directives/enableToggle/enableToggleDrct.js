appAddThisWordPress.directive('enableToggle', function() {
  return {
    scope: {
      ngModel: '=ngModel', // bi-directional
      toolPco: '@toolPco'
    },
    controller: function($scope) {
        $scope.isEnabled = function() {
            if ($scope.ngModel === true) {
                return true;
            } else {
                return false;
            }
        };
    },
    templateUrl: '/directives/enableToggle/enableToggle.html'
  };
});