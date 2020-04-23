appAddThisWordPress.directive('locationChecklist', function() {
  return {
    scope: {
      ngModel: '=ngModel', // bi-directional
      locations: '=locations'
    },
    controller: function ($scope) {
      $scope.toggleSelection = function toggleSelection(location) {
        if  ($scope.ngModel === undefined || !($scope.ngModel instanceof Array))
        {
          $scope.ngModel = [];
        }
        var idx = $scope.ngModel.indexOf(location);

        // is currently selected
        if (idx > -1) {
          $scope.ngModel.splice(idx, 1);
        }

        // is newly selected
        else {
          $scope.ngModel.push(location);
        }
      };
    },
    templateUrl: '/directives/locationChecklist/locationChecklist.html'
  };
});