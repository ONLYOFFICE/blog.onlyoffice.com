appAddThisWordPress.controller('ToolListCtrl', function(
  $scope,
  $wordpress,
  $filter,
  modeHelper,
  $q
) {
  $scope.templateBaseUrl = $wordpress.templateBaseUrl();
  $scope.bulkAction = '';

  $scope.globalOptions = {};
  $scope.shareButtons = {};
  $scope.toolList = {};

  $scope.sort = {
    property: 'displayName',
    reverse: false
  };

  $wordpress.globalOptions.get().then(function(globalOptions) {
    $scope.globalOptions = globalOptions;
  });

  modeHelper.get($wordpress.sharingButtons, true).then(function(result) {
    $scope.toolList = $filter('americaToolType')(result, 'share');
  });

  if (angular.isDefined(window.addthis_ui.plugin) &&
    angular.isDefined(window.addthis_ui.plugin.slug)
  ) {
    $scope.slug = window.addthis_ui.plugin.slug;
  } else {
    $scope.slug = 'addthis-all';
  }

  $scope.bulkActionSelections = [];

  $scope.filterParam = {};

  $scope.sortBy = function(property) {
    $scope.sort.property = property;
    if ($scope.sort.property === property) {
      $scope.sort.reverse = !$scope.sort.reverse;
    } else {
      $scope.sort.reverse = false;
    }
  };

  $scope.filterBy = function(property, value) {
    if (typeof value !== 'undefined') {
      $scope.filterParam[property] = value;
    } else if (typeof $scope.filterParam[property] !== 'undefined') {
      delete $scope.filterParam[property];
    }
  };

  var toolDisplayName = function(toolSettings) {
    if (toolSettings.toolName) {
      return toolSettings.toolName;
    }

    var msgid = $filter('defaultToolNameMsgid')(toolSettings.id);
    if (msgid) {
      return $filter('translate')(msgid);
    } else {
      return 'Unknown';
    }
  };

  $scope.toolListArray = function () {
      var toolListArray = [];
      angular.forEach($scope.toolList, function (toolSettings) {
        toolSettings.displayName = toolDisplayName(toolSettings);
        toolSettings.shortCode = $filter('shortCode')(toolSettings);
        toolSettings.toolType = $filter('toolTypeNameMsgid')(toolSettings.id);

        toolListArray.push(toolSettings);
      });
      return toolListArray;
  };

  $scope.toggleCheck = function(widgetId) {
    var key = $scope.bulkActionSelections.indexOf(widgetId);
    if (key !== -1) {
      // if in array, remove
      $scope.bulkActionSelections.splice(key, 1);
    } else {
      // if not in array, add
      $scope.bulkActionSelections.push(widgetId);
    }
  };

  $scope.toggleCheckAll = function() {
    if ($scope.bulkActionIsChecked()) {
      $scope.bulkActionSelections = [];
    } else {
      angular.forEach($scope.toolList, function(value, widgetId) {
        if ($scope.bulkActionSelections.indexOf(widgetId) === -1) {
          $scope.bulkActionSelections.push(widgetId);
        }
      });
    }
  };

  var generateDeleteBulkActionMessageFunction = function(key) {
    var deleteMessage = function() {
      delete $scope.completedBulkActions[key];
    };

    return deleteMessage;
  };

  $scope.saving = false;
  $scope.completedBulkActions = [];
  $scope.doBulkAction = function() {
    if ($scope.bulkAction !== 'activate' &&
      $scope.bulkAction !== 'deactivate'
    ) {
      // do nothing if the bulk action isn't something usful
      return;
    }

    $scope.saving = true;

    var promises = [];
    $scope.bulkActionSelections.forEach(function(widgetId) {
      var toolSettings = angular.copy($scope.toolList[widgetId]);
      if ($scope.bulkAction === 'activate') {
        toolSettings.enabled = true;
      } else if ($scope.bulkAction === 'deactivate') {
        toolSettings.enabled = false;
      }

      var successMessageObject = {
        displayName: toolSettings.displayName,
        enabled: toolSettings.enabled
      };

      delete toolSettings.shortCode;
      delete toolSettings.displayName;
      delete toolSettings.toolType;

      var promise = modeHelper.save(
        $wordpress.sharingButtons,
        widgetId,
        toolSettings,
        true
      ).then(function(data) {
        $scope.completedBulkActions.push(successMessageObject);
        var lastKey = $scope.completedBulkActions.length - 1;
        successMessageObject.close =
          generateDeleteBulkActionMessageFunction(lastKey);
        return data;
      });

      promises.push(promise);
    });

    $q.all(promises).then(function() {
      modeHelper.get($wordpress.sharingButtons, true).then(function(result) {
        $scope.toolList = $filter('americaToolType')(result, 'share');
      });

      $scope.bulkActionSelections = [];
      $scope.saving = false;
      //$scope.bulkActionIsChecked();
    });
  };

  $scope.isChecked = function(widgetId) {
    var index = $scope.bulkActionSelections.indexOf(widgetId);
    var isChecked = index !== -1;
    return isChecked;
  };

  $scope.bulkActionIsChecked = function() {
    var isChecked =
      $scope.bulkActionSelections.length ===
        Object.keys($scope.toolList).length;
    return isChecked;
  };

});