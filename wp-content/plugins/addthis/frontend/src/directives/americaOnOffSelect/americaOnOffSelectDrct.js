appAddThisWordPress.directive('americaOnOffSelect', function() {
  return {
    scope: {
      ngModel: '=ngModel', // bi-directional
      fieldName: '=fieldName',
      title: '=title',
    },

    controller: function ($scope) {
      $scope.options = [
        {
          value:'on',
          display: 'tool_settings_on_label'
        },
        {
          value:'off',
          display:'tool_settings_off_label'
        }
      ];
    },

    templateUrl:
      '/directives/americaOnOffSelect/americaOnOffSelect.html'
  };
});
