appAddThisWordPress.directive('validateProFieldUse', function() {
  return {
    require: 'ngModel',
    transclude: true,
    link: function(scope, elm, attrs, ctrl) {
      // this directive manipulates its parent's scope and is only meant be used
      // inside of the toolGallerySettingsPage directive
      ctrl.$validators.addThisProField = function(modelValue) {
        var isProValue = true;

        if (typeof modelValue === 'undefined') {
          isProValue = false;
        } else if ((typeof attrs.basicValue !== 'undefined') &&
          modelValue === attrs.basicValue
        ) {
          isProValue = false;
        } else if ((typeof attrs.basicValue === 'undefined') &&
          (modelValue === '' || modelValue === false)
        ) {
          isProValue = false;
        }

        if (typeof scope.proFields === 'object') {
          scope.proFields[ctrl.$name] = isProValue;
        }

        return isProValue;
      };
    }
  };
});