appAddThisWordPress.directive('customIcon', function() {
  return {
    scope: {
      level: '@level',
      icon: '@icon'
    },
    transclude: true,
    controller: function($scope) {
      var icons = {
        // level to icon
        info: 'info',
        success: 'checkmark',
        warning: 'exclamation',
        danger: 'exclamation'
      };

      $scope.getIconUrl = function() {
        var image = 'info.svg';

        if ($scope.icon) {
          image = $scope.icon + '.svg';
        } else if ($scope.level && angular.isDefined(icons[$scope.level])) {
          image = icons[$scope.level] + '.svg';
        }
        return  '/images/' + image;
      };

    },
    templateUrl: '/directives/customIcon/customIcon.html'
  };
});