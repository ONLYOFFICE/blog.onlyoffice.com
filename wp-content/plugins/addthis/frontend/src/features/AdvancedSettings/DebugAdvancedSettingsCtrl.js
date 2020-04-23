appAddThisWordPress.controller('DebugAdvancedSettingsCtrl', function($scope) {

  $scope.modeOptions = [
    {
      value: 'WordPress',
      display: 'WordPress'
    },
    {
      value: 'AddThis',
      display: 'AddThis'
    }
  ];

  $scope.enviromentOptions = [
    {
      value: '',
      display: 'prod'
    },
    {
      value: 'test',
      display: 'test'
    },
    {
      value: 'dev',
      display: 'dev'
    },
    {
      value: 'local',
      display: 'local'
    }
  ];

});