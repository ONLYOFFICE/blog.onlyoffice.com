appAddThisWordPress.directive('stylePicker', function() {
  return {
    scope: {
      ngModel: '=ngModel', // bi-directional
      fieldName: '=fieldName',
      title: '=title',
      styleOptions: '=styleOptions'
    },
    templateUrl:
      '/directives/stylePicker/stylePicker.html'
  };
});