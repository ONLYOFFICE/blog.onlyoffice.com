appAddThisWordPress.directive('validateJson', function($q) {
  return {
    require: 'ngModel',
    link: function(scope, elm, attrs, ctrl) {

      ctrl.$asyncValidators.json = function(modelValue) {
        var def = $q.defer();

        if (ctrl.$isEmpty(modelValue)) {
          // consider empty model valid
          return $q.when();
        }

        var testResult = false;

        try {
            testResult = JSON.parse(modelValue);
        } catch (e) {
            def.reject();
        }

        if(testResult) {
          def.resolve();
        }

        return def.promise;
      };
    }
  };
});