appAddThisWordPress.controller('AnalyticsAdCtrl', function(
  $scope,
  $wordpress,
  $darkseid
) {
  $scope.state = 'loading';

  $wordpress.globalOptions.get().then(function(globalOptions) {
    $scope.profileId = globalOptions.addthis_profile;
    if ($scope.profileId.length > 0) {
      $darkseid.validateAddThisProfileId(globalOptions.addthis_profile)
      .then(function (result) {
          if (result.success === true) {
              $scope.state = 'viewanalytics';
          } else {
              $scope.state = 'register';
          }
      });
    } else {
      $scope.state = 'register';
    }
  });
});