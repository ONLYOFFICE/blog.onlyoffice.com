appAddThisWordPress.controller('CrossPluginWarningsCtrl', function(
  $scope,
  $wordpress
) {

  $wordpress.addThisOtherPlugins().then(function(data) {
    $scope.otherPlugins = data;
  });

  $scope.showSuccess = false;
  $scope.changeMode = function() {
    $scope.globalOptions.addthis_plugin_controls = 'AddThis';

    $wordpress.globalOptions.save().then(function(data) {
      $scope.globalOptions = data;
      if ($scope.globalOptions.addthis_plugin_controls === 'AddThis') {
        $scope.showSuccess = true;
      }
    });
  };

  $scope.updateProfileId = function(source) {
    $wordpress.addThisUpdateOtherPlugin(source).then(function(data) {
      $scope.otherPlugins = data;
    });
  };

  $scope.minimalistProPlugin = function() {
    if ($scope.globalOptions.recommended_content_feature_enabled ||
        $scope.globalOptions.follow_buttons_feature_enabled ||
        $scope.globalOptions.sharing_buttons_feature_enabled
    ) {
      return false;
    } else {
      return true;
    }
  };
});