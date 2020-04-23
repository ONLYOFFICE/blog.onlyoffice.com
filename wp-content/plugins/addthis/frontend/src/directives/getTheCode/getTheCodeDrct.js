appAddThisWordPress.directive('getTheCode', function($wordpress) {
  return {
    scope: {
      toolClasses: '=toolClasses'
    },
    transclude: true,
    controller: function($scope) {
      $scope.widgetConfigUrl = $wordpress.widgetConfigUrl();
      $scope.toolClass = false;

      var avoidClasses = [
        '.at-above-post-homepage',
        '.at-below-post-homepage',
        '.at-above-post',
        '.at-below-post',
        '.at-above-post-page',
        '.at-below-post-page',
        '.at-above-post-cat-page',
        '.at-below-post-cat-page',
        '.at-above-post-arch-page',
        '.at-below-post-arch-page',
        '.at-below-post-recommended'
      ];

      var determinClass = function() {
        var preferredClass = false;
        var backupClass = false;

        $scope.toolClasses.forEach(function(toolClass) {
          // save the first element for use, if needed
          if (backupClass === false) {
            backupClass = toolClass.substr(1);
          }

          // save the first non-location oriented element found
          if(preferredClass === false &&
            avoidClasses.indexOf(toolClass) === -1
          ) {
            preferredClass = toolClass.substr(1);
          }
        });

        if (typeof preferredClass === 'string') {
          $scope.toolClass = preferredClass;
        } else if (typeof backupClass === 'string') {
          $scope.toolClass = backupClass;
        } else {
          $scope.toolClass = false;
        }
      };

      $scope.$watch('toolClasses', determinClass);
    },
    templateUrl: '/directives/getTheCode/getTheCode.html'
  };
});