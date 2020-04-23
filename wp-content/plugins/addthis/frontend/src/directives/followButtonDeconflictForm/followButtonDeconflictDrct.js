appAddThisWordPress.directive('followButtonDeconflictForm', function(
  $wordpress,
  $timeout
) {
  return {
    scope: {
      ngModel: '=ngModel', // bi-directional
      conflicts: '=conflicts' // bi-directional
    },
    controller: function($scope, $element, $filter) {
      $scope.followServices = $filter('followServiceInfo')();
      $wordpress.addThisGetFollowServices().then(function(followServices) {
        $scope.followServices = followServices;

        $timeout(function() {
          if (typeof window.addthis !== 'undefined') {
            window.addthis.toolbox('.follow-service-conflicts');
          }
        });
      });

      $scope.headerName = function(service, thisUserType) {
        var count = 0;
        angular.forEach(service.endpoints, function(id, someUserType) {
          var key = service.code + '_' + someUserType;
          if (typeof $scope.ngModel.services[key] !== 'undefined') {
            count++;
          }
        });

        var msgid;
        if (count > 1) {
          msgid = 'follow_profile_' + service.code + '_' + thisUserType;
        } else {
          msgid = 'social_service_' + service.code;
        }

        var translation = $filter('translate')(msgid);
        if (translation !== msgid) {
          return translation;
        } else {
          return service.name;
        }
      };
    },
    templateUrl:
    '/directives/followButtonDeconflictForm/followButtonDeconflictForm.html'
  };
});