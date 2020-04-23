appAddThisWordPress.directive('validateAddThisApiKey', function(
  $q,
  $wordpress
) {
  return {
    require: 'ngModel',
    scope: {
        profileId: '=validateAddThisApiKey',
        profileIdError: '=profileIdError'
    },
    link: function(scope, elm, attrs, ctrl) {
      ctrl.$asyncValidators.addThisApiKey = function(modelValue) {
        var def = $q.defer();

        if (ctrl.$isEmpty(modelValue)) {
          // consider empty model valid
          return $q.when();
        }

        if (scope.profileIdError === true) {
          def.reject();
        } else {
          $wordpress.addThisApiKeyCheck(scope.profileId, modelValue)
          .then(function(data) {
            if (data.success) {
              def.resolve();
            } else {
              def.reject();
            }
          });
        }

        return def.promise;
      };

      scope.$watch('profileId', function() {
        ctrl.$validate();
      });

    }
  };
});