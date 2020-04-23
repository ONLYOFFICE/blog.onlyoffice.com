appAddThisWordPress.directive('iconShapePicker', function($wordpress) {
  return {
    scope: {
      ngModel: '=ngModel', // bi-directional
      toolPco: '@toolPco'
    },
    controller: function($scope) {
      $scope.templateBaseUrl = $wordpress.templateBaseUrl();

      $scope.fieldName = 'shape';

      $scope.shapeOptions = [
        {
          value: 'square',
          display: 'icon_shape_picker_square_option',
          alt: 'icon_shape_picker_square_option_alt',
          image: 'icon-shape-square.png'
        },
        {
          value: 'round',
          display: 'icon_shape_picker_round_option',
          alt: 'icon_shape_picker_round_option_alt',
          image: 'icon-shape-round.png'
        },
        {
          value: 'rounded',
          display: 'icon_shape_picker_rounded_option',
          alt: 'icon_shape_picker_rounded_option_alt',
          image: 'icon-shape-rounded.png'
        }
      ];

      $scope.changeShape = function(newValue) {
        $scope.ngModel = newValue;
      };
    },
    templateUrl: '/directives/iconShapePicker/iconShapePicker.html'
  };
});