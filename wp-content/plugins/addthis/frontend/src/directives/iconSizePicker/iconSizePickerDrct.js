appAddThisWordPress.directive('iconSizePicker', function() {
  return {
    scope: {
      ngModel: '=ngModel', // bi-directional
      toolPco: '@toolPco',
      fieldName: '@fieldName'
    },
    controller: function($scope) {

      $scope.fieldName = 'size';

      $scope.sizeOptions = [
        {
          value: 'large',
          display: 'icon_size_picker_select_large',
          info: 'icon_size_picker_select_large_description'
        },
        {
          value: 'medium',
          display: 'icon_size_picker_select_medium',
          info: 'icon_size_picker_select_medium_description'
        },
        {
          value: 'small',
          display: 'icon_size_picker_select_small',
          info: 'icon_size_picker_select_small_description'
        }
      ];

    },
    templateUrl: '/directives/iconSizePicker/iconSizePicker.html'
  };
});