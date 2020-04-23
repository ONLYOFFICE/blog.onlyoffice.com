appAddThisWordPress.directive('validateAddThisProfileId', function(
  $q,
  $darkseid
) {
  return {
    require: 'ngModel',
    link: function(scope, elm, attrs, ctrl) {

      ctrl.$asyncValidators.validProfile = function(modelValue) {
        var def = $q.defer();

        if (ctrl.$isEmpty(modelValue)) {
          // consider empty model valid
          return $q.when();
        }

        $darkseid.validateAddThisProfileId(modelValue).then(function(data) {
            if (data.success) {
              if (data.data.type === 'wp') {
                ctrl.$setValidity('wpProfile', true);
              } else {
                ctrl.$setValidity('wpProfile', false);
              }

              def.resolve();
            } else {
              def.reject();
            }
        });

        return def.promise;
      };
    }
  };
});