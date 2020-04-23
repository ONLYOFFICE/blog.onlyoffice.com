
appAddThisWordPress.directive('americaLayersOffsetPicker', function() {
  return {
    scope: {
      ngModel: '=ngModel', // bi-directional
      toolPco: '@toolPco',
      positions: '@positions'
    },
    controller: 'layersOffsetPickerCtrl',
    templateUrl: '/directives/layersOffsetPicker/americaLayersOffsetPicker.html'
  };
});