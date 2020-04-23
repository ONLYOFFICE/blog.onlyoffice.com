appAddThisWordPress.controller('AdvancedSettingsCtrl', function(
  $scope,
  $wordpress,
  $timeout
) {

  $scope.loadStatus = 'loading';

  $scope.globalOptions = {};
  $wordpress.globalOptions.get().then(function(data) {
    $scope.globalOptions = data;
    if (angular.isObject(data)) {
      $scope.loadStatus = 'loaded';
    } else {
      $scope.loadStatus = 'failed';
    }
  });

  $scope.saving = false;
  $scope.showSaveSuccessMessage = false;
  $scope.save = function(valid) {
    if(!valid) {
      return;
    }

    $scope.saving = true;
    $scope.showSaveSuccessMessage = false;
    $wordpress.globalOptions.save().then(function(data) {
      $scope.globalOptions = data;
      $scope.saving = false;
      if (angular.isObject(data)) {
        $scope.showSaveSuccessMessage = true;
        $timeout(function() {
          $scope.showSaveSuccessMessage = false;
        }, 5000); // show for 5 seconds
      }
    });
  };

  $scope.addthis_config_help = {
    example: {
      services_exclude: 'print'
    },
    linkText: 'addthis_config',
    url: 'https://www.addthis.com/academy/the-addthis_config-variable/'
  };

  $scope.addthis_share_help = {
    example: {
      url: 'http://www.yourdomain.com',
      title: 'Custom Title'
    },
    linkText: 'addthis_share',
    url: 'https://www.addthis.com/academy/the-addthis_share-variable/'
  };

  $scope.addthis_layers_help = {
    example: {
      share: {
        services:
          'facebook,twitter,pinterest_share,print,more'
      }
    },
    linkText: 'addthis.layers()',
    url: 'https://www.addthis.com/academy/smart-layers-api/'
  };
});