appAddThisWordPress.directive('layersOffsetPicker', function() {
  return {
    scope: {
      ngModel: '=ngModel', // bi-directional
      toolPco: '@toolPco',
      positions: '@positions'
    },
    controller: 'layersOffsetPickerCtrl',
    templateUrl: '/directives/layersOffsetPicker/layersOffsetPicker.html'
  };
});