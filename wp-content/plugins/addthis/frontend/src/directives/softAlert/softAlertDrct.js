appAddThisWordPress.directive('softAlert', function() {
  return {
    scope: {
      level: '=level',
      icon: '=icon'
    },
    transclude: true,
    link: function(scope, el, attrs, ctrl, transclude) {
      var transcludeElements = el.find('.transclude-here-soft-alert');
      if (transcludeElements.length === 0) {
        // for older version of jQuery :-(
        transcludeElements = jQuery(el)
        .find('[class*="transclude-here-soft-alert"]');
      }
      transcludeElements.append(transclude());
    },
    controller: function($scope) {
      var configs = {
        info: {
          icon: 'info',
          alertClass: 'soft-alert alert-info'
        },
        success: {
          icon: 'checkmark',
          alertClass: 'soft-alert alert-success'
        },
        warning: {
          icon: 'exclamation',
          alertClass: 'soft-alert alert-warning'
        },
        danger: {
          icon: 'exclamation',
          alertClass: 'soft-alert alert-danger'
        }
      };

      $scope.getIcon = function() {
        if ($scope.icon) {
          var icon = $scope.icon;
          return icon;
        } else if (configs[$scope.level].icon) {
          return configs[$scope.level].icon;
        } else {
          return configs.info.icon;
        }
      };

      $scope.getAlertClass = function() {
        if (configs[$scope.level].alertClass) {
          return configs[$scope.level].alertClass;
        } else {
          return configs.info.alertClass;
        }
      };
    },
    templateUrl: '/directives/softAlert/softAlert.html'
  };
});