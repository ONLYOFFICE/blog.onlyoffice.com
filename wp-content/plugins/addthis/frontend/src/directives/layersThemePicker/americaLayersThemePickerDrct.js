appAddThisWordPress.directive('americaLayersThemePicker', function() {
  return {
    scope: {
      ngModel: '=ngModel', // bi-directional
      fieldName: '=fieldName',
      title: '=title',
      themeOptions: '=themeOptions'
    },

    templateUrl:
      '/directives/layersThemePicker/americaLayersThemePicker.html'
  };
});
