appAddThisWordPress.directive('validateMatch', function() {
  return {
    require: 'ngModel',
    scope: {
        otherModelValue: '=validateMatch'
    },
    link: function(scope, elm, attrs, ctrl) {

      ctrl.$validators.match = function(modelValue) {
        return modelValue === scope.otherModelValue;
      };

      scope.$watch('otherModelValue', function() {
        ctrl.$validate();
      });
    }
  };
});