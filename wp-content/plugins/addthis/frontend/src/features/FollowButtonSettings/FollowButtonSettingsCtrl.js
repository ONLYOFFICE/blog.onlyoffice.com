appAddThisWordPress.controller('FollowButtonSettingsCtrl', function(
  $scope,
  $wordpress,
  $stateParams,
  $state,
  $filter,
  modeHelper
) {
  $scope.changeState = function(newState) {
    if (newState === 'all') {
      $state.go('follow');
    } else {
      $state.go('follow.pco', {toolPco: newState});
    }
  };

  $scope.showToolCards = function() {
    if (angular.isDefined($stateParams.toolPco) &&
      $stateParams.toolPco !== ''
    ) {
      return false;
    } else {
      return true;
    }
  };

  $scope.goBack = function() {
    $scope.changeState('all');
  };

  $scope.templateBaseUrl = $wordpress.templateBaseUrl();

  $scope.globalOptions = {};
  $scope.followButtons = {};

  $wordpress.globalOptions.get().then(function(result) {
    $scope.globalOptions = result;
  });

  modeHelper.get($wordpress.followButtons).then(function(result) {
    $scope.followButtons = $filter('toolType')(result, 'follow');
  });

  $scope.saving = false;
  $scope.save = function(toolPco) {
    $scope.saving = true;
    return modeHelper.save(
      $wordpress.followButtons,
      toolPco,
      $scope.followButtons[toolPco]
    ).then(function(result) {
      $scope.followButtons = $filter('toolType')(result, 'follow');
      $scope.saving = false;
    });
  };

  $scope.toggleEvent = function(toolPco) {
    if (angular.isDefined($scope.followButtons[toolPco]) &&
      angular.isDefined($scope.followButtons[toolPco].enabled) &&
      $scope.followButtons[toolPco].enabled === true
    ) {
      $scope.followButtons[toolPco].enabled = false;
      $scope.save(toolPco);
    } else if ($state.current.name === 'follow') {
      $scope.changeState(toolPco);
    }
  };
});