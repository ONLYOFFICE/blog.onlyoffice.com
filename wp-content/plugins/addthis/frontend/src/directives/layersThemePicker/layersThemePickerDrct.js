appAddThisWordPress.directive('layersThemePicker', function() {
  return {
    scope: {
      ngModel: '=ngModel', // bi-directional
      toolPco: '@toolPco',
      themes: '@themes'
    },
    controller: function($scope) {
      $scope.fieldName = 'theme';

      var themesLimit = [];
      if (typeof $scope.themes !== 'undefined') {
        themesLimit = $scope.themes.split(',');
      }

      $scope.defaultThemeOptions = [
        {
          value: 'transparent',
          display: 'layers_theme_picker_select_transparent'
        },
        {
          value: 'light',
          display: 'layers_theme_picker_select_light'
        },
        {
          value: 'dark',
          display: 'layers_theme_picker_select_dark'
        },
        {
          value: 'gray',
          display: 'layers_theme_picker_select_grey'
        }
      ];

      $scope.themeOptions = [];

      if (themesLimit.length > 1) {
        $scope.defaultThemeOptions.forEach(function(themeOption) {
          if (themesLimit.indexOf(themeOption.value) !== -1) {
            $scope.themeOptions.push(themeOption);
          }
        });
      } else {
        $scope.themeOptions = $scope.defaultThemeOptions;
      }

    },
    templateUrl: '/directives/layersThemePicker/layersThemePicker.html'
  };
});