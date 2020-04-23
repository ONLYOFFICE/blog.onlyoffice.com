appAddThisWordPress.directive('eula', function() {
  return {
    scope: {
      buttonName: '@buttonName'
    },
    controller: function($scope) {
      $scope.privacyUrl = 'http://www.addthis.com/privacy/privacy-policy';
      $scope.termsUrl = 'http://www.addthis.com/tos';
    },
    templateUrl: '/directives/eula/eula.html'
  };
});