appAddThisWordPress.directive('responsivePicker', function() {
  return {
    scope: {
      ngModel: '=ngModel' // bi-directional
    },
    controller: function ($scope) {

      $scope.unitOptions = [
        {
          value: 'px',
          display: 'px'
        },
        {
          value: 'em',
          display: 'em'
        }
      ];
    },

    templateUrl:
      '/directives/responsivePicker/responsivePicker.html'
  };
});
