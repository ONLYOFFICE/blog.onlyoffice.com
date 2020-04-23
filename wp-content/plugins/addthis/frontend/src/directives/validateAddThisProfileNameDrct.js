appAddThisWordPress.directive('validateAddThisProfileName', function($q) {
  return {
    require: 'ngModel',
    link: function(scope, elm, attrs, ctrl) {

      ctrl.$asyncValidators.addThisProfileName = function(modelValue) {
        var def = $q.defer();

        if (ctrl.$isEmpty(modelValue)) {
          // consider empty model valid
          return $q.when();
        }

        var badCharacterLocation = modelValue.search(/[^a-zA-Z0-9_() \-]+/);

        if (badCharacterLocation === -1) {
          def.resolve();
        } else {
          def.reject();
        }

        return def.promise;
      };
    }
  };
});