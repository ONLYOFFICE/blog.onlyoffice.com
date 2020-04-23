appAddThisWordPress.directive('countTypePicker', function() {
  return {
    scope: {
      ngModel: '=ngModel', // bi-directional
      typeOptions: '=typeOptions',
      title: '=title',
      fieldName: '=fieldName'
    },
    templateUrl: '/directives/countTypePicker/countTypePicker.html'
  };
});