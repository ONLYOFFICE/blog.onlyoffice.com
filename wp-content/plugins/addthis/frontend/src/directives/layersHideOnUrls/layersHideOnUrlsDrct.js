appAddThisWordPress.directive('layersHideOnUrls', function() {
  return {
    scope: {
      ngModel: '=ngModel', // bi-directional
      toolPco: '@toolPco'
    },
    controller: function($scope) {
      $scope.fieldName = 'hideonurls';
    },
    templateUrl: '/directives/layersHideOnUrls/layersHideOnUrls.html'
  };
});