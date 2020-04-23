appAddThisWordPress.controller('FollowButtonConflictCtrl', function(
  $scope,
  $wordpress,
  $stateParams,
  $state,
  $timeout
) {
  $scope.withConflicts = {};
  $scope.withoutConflicts = {};
  $scope.conflicts = {flwh: false, flwv: false};

  $scope.showTool = function(toolPco) {
    if (toolPco === $stateParams.toolPco) {
        return true;
    } else {
        return false;
    }
  };

  $wordpress.followButtons.get()
  .then(function(followButtons) {
    $scope.followButtons = followButtons;

    if (angular.isDefined(followButtons.flwh) &&
        angular.isDefined(followButtons.flwh.conflict) &&
        followButtons.flwh.conflict === true
    ) {
      $scope.conflicts.flwh = true;
      $scope.withConflicts.flwh = prepWithConflictSettings(followButtons.flwh);
      $scope.withoutConflicts.flwh =
        prepWithoutConflictSettings(followButtons.flwh);
    }

    if (angular.isDefined(followButtons.flwv) &&
        angular.isDefined(followButtons.flwv.conflict) &&
        followButtons.flwv.conflict === true
    ) {
      $scope.conflicts.flwv = true;
      $scope.withConflicts.flwv = prepWithConflictSettings(followButtons.flwv);
      $scope.withoutConflicts.flwv =
        prepWithoutConflictSettings(followButtons.flwv);
    }

    $timeout(function() {
      if (typeof window.addthis !== 'undefined') {
        window.addthis.toolbox('.conflict-resolution');
      }
    });
  });

  $scope.saving = false;
  $scope.save = function(toolPco) {
    $scope.saving = true;

    return $wordpress.followButtons.save(
      toolPco,
      $scope.withoutConflicts[toolPco]
    )
    .then(function(followButtons) {
      $scope.followButtons = followButtons;
      $scope.saving = false;
      $scope.conflicts[toolPco] = false;

      var nextToolPco = false;
      angular.forEach($scope.conflict, function(isConflict, pco) {
        if (isConflict === true) {
          nextToolPco = pco;
        }
      });

      if (nextToolPco !== false) {
        $state.go('follow_conflict', {toolPco: nextToolPco});
      } else {
        $state.go('follow');
      }

      return followButtons;
    });
  };

  var makeArray = function(input) {
    var output = [];

    if (angular.isArray(input)) {
        output = input;
    } else {
        output = [input];
    }

    return output;
  };

  var pickFirst = function(input) {
    var output = '';

    if (angular.isArray(input)) {
        output = input[0];
    } else {
        output = input;
    }

    return output;
  };

  var prepWithConflictSettings = function(input) {
    var output = {};

    if (angular.isDefined(input.size)) {
      output.size = makeArray(input.size);
    }

    if (angular.isDefined(input.services)) {
      output.services = {};

      angular.forEach(input.services, function(usernames, service) {
         output.services[service] = makeArray(usernames);
      });
    }

    return output;
  };

  var prepWithoutConflictSettings = function(input) {
    var output = {};

    if (angular.isDefined(input.size)) {
      output.size = pickFirst(input.size);
    }

    if (angular.isDefined(input.services)) {
      output.services = {};

      angular.forEach(input.services, function(usernames, service) {
         output.services[service] = pickFirst(usernames);
      });
    }

    output.enabled = true;
    return output;
  };
});