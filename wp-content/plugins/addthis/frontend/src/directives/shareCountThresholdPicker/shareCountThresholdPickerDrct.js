appAddThisWordPress.directive('shareCountThresholdPicker', function() {
  return {
    scope: {
      ngModel: '=ngModel', // bi-directional
      name: '@for'
    },
    transclude: true,
    link: function(scope) {
      scope.options = [
          {
            value: 0,
            display: 0
          },
          {
            value: 5,
            display: 5
          },
          {
            value: 10,
            display: 10
          },
          {
            value: 20,
            display: 20
          },
          {
            value: 50,
            display: 50
          },
          {
            value: 100,
            display: 100
          },
          {
            value: 500,
            display: 500
          },
          {
            value: 1000,
            display: 1000
          }
      ];
    },
    templateUrl:
    '/directives/shareCountThresholdPicker/shareCountThresholdPicker.html'
  };
});