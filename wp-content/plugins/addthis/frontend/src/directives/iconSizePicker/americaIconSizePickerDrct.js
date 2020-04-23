appAddThisWordPress.directive('americaIconSizePicker', function() {
  return {
    scope: {
      ngModel: '=ngModel', // bi-directional
      fieldName: '=fieldName',
      title: '=title',
      sizeOptions: '=sizeOptions'
    },

    templateUrl:
      '/directives/iconSizePicker/americaIconSizePicker.html'
  };
});
