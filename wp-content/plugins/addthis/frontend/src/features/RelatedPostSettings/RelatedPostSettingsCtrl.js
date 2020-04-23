appAddThisWordPress.controller('RelatedPostSettingsCtrl', function(
  $scope,
  $wordpress,
  $state,
  $stateParams,
  $darkseid,
  $filter,
  modeHelper,
  globalOptions,
  toolSettings
) {
  $scope.globalOptions = globalOptions;
  $scope.toolSettings = toolSettings;

  $scope.changeState = function(newState) {
    if (newState === 'all') {
      $state.go('relatedpostslist');
    } else {
      $state.go('relatedpostssettings', {toolPco: newState});
    }
  };

  $scope.goBack = function() {
    $scope.changeState('all');
  };

  $scope.templateBaseUrl = $wordpress.templateBaseUrl();

  var setPromotedUrls = function() {
    return $darkseid.getPromotedUrl().then(function(data) {
      angular.forEach(data, function(urls, toolPco) {
        if (typeof $scope.toolSettings[toolPco] === 'object') {
          $scope.toolSettings[toolPco].promotedUrl = urls[0];
        } else {
          $scope.toolSettings[toolPco] = { promotedUrl: urls[0] };
        }
      });

      return data;
    });
  };

  $scope.saving = false;
  $scope.save = function(toolPco) {
    $scope.saving = true;

    return modeHelper.save(
      $wordpress.relatedPosts,
      toolPco,
      $scope.toolSettings[toolPco]
    ).then(function(result) {
      $scope.toolSettings = $filter('toolType')(result, 'relatedposts');
      $scope.saving = false;

      if ($scope.globalOptions.addthis_plugin_controls === 'AddThis') {
        return setPromotedUrls().then(function() {
          return result;
        });
      } else {
        return result;
      }
    });
  };

  $scope.toggleEvent = function(toolPco) {
    if (angular.isDefined($scope.toolSettings[toolPco]) &&
      angular.isDefined($scope.toolSettings[toolPco].enabled) &&
      $scope.toolSettings[toolPco].enabled === true
    ) {
      $scope.save(toolPco);
    } else if ($state.current.name === 'relatedpostslist') {
      $scope.changeState(toolPco);
    }
  };
});