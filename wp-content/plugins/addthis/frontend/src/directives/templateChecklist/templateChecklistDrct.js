appAddThisWordPress.directive('templateChecklist', function() {
  return {
    scope: {
      ngModel: '=ngModel', // bi-directional
      fieldName: '=fieldName',
      title: '=title',
      templateOptions: '=templateOptions'
    },
    controller: function ($scope) {
      $scope.toggleSelection = function toggleSelection(theme) {
        if  ($scope.ngModel === undefined || !($scope.ngModel instanceof Array))
        {
          $scope.ngModel = [];
        }
        var idx = $scope.ngModel.indexOf(theme);

        // is currently selected
        if (idx > -1) {
          $scope.ngModel.splice(idx, 1);
        }

        // is newly selected
        else {
          $scope.ngModel.push(theme);
        }
      };
    },
    templateUrl:
      '/directives/templateChecklist/templateChecklist.html'
  };
});