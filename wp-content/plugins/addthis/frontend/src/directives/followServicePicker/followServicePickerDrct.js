appAddThisWordPress.directive('followServicePicker', function(
  $wordpress,
  $timeout
) {
  return {
    scope: {
      ngModel: '=ngModel', // bi-directional
      toolPco: '@toolPco'
    },
    link: function($scope) {
      $wordpress.addThisGetFollowServices().then(function(followServices) {
        makeServiceOptions(followServices);
      });

      $scope.serviceOptions = {};
      var makeServiceOptions = function(followServices) {
        if (typeof followServices === 'object' &&
          Array.isArray(followServices)
        ) {
          followServices.forEach(function(serviceElement) {
            var serviceOptionsInfo = {
              displayName: 'social_service_'+serviceElement.code,
              icon: serviceElement.iconCode,
              userTypes: {}
            };

            angular.forEach(serviceElement.endpoints, function(
              endpoint,
              userType
            ) {
              var urlTemplate = endpoint;
              var typeOfCheck = typeof serviceElement.prettyEndpoints[userType];
              if (typeOfCheck !== 'undefined') {
                urlTemplate = serviceElement.prettyEndpoints[userType];
              }

              var urlBits = urlTemplate.split('{{id}}');
              var preInputUrl = urlBits[0].replace(/^https?:\/\/(www\.)?/i, '');

              var userTypeObject = {
                fieldName: serviceElement.code + '_' + userType,
                displayName: serviceOptionsInfo.displayName,
                preInputUrl: preInputUrl,
                postInputUrl: urlBits[1],
                formFieldName: 'addthis-' +
                  $scope.toolPco +
                  '-service-' +
                  serviceElement.code +
                  '-' +
                  userType
              };
              serviceOptionsInfo.userTypes[userType] = userTypeObject;
            });

            $scope.serviceOptions[serviceElement.code] = serviceOptionsInfo;
          });
        }

        $timeout(function() {
          if (typeof window.addthis !== 'undefined') {
            window.addthis.toolbox('.follow-service-picker');
          }
        });
      };

      $scope.showGrayscale = function(userTypes) {
        var showGrayscale = true;
        if (angular.isDefined($scope.ngModel)) {
          angular.forEach(userTypes, function(userTypeElement) {
            if (angular.isDefined($scope.ngModel[userTypeElement.fieldName])) {
              showGrayscale = false;
            }
          });
        }
        return showGrayscale;
      };

      $scope.addService = function(userTypes) {
        if (!angular.isDefined($scope.ngModel) ||
          typeof $scope.ngModel !== 'object' ||
          Array.isArray($scope.ngModel)
        ) {
          $scope.ngModel = {};
        }

        angular.forEach(userTypes, function(userTypeElement) {
          if (!angular.isDefined($scope.ngModel[userTypeElement.fieldName])) {
            if (userTypeElement.fieldName === 'rss') {
              $scope.ngModel[userTypeElement.fieldName] =
                $wordpress.defaults('rss');
            } else {
              $scope.ngModel[userTypeElement.fieldName] = '';
            }
          }
        });

        $timeout(function() {
          if (typeof window.addthis !== 'undefined') {
            window.addthis.toolbox('.follow-service-picker');
          }
        });
      };

      $scope.showServiceField = function(feildName) {
        if (angular.isDefined($scope.ngModel) &&
          angular.isDefined($scope.ngModel[feildName])
        ) {
          return true;
        }

        return false;
      };

      $scope.deleteServiceField = function(feildName) {
        if (angular.isDefined($scope.ngModel) &&
          angular.isDefined($scope.ngModel[feildName])
        ) {
          delete $scope.ngModel[feildName];
        }
      };

      $scope.getServiceCode = function(fieldName) {
        var delimiter = '_';
        var parts = fieldName.split(delimiter);
        var serviceCode;

        if (parts.length > 1) {
          parts.pop();
          serviceCode = parts.join(delimiter);
        } else {
          serviceCode = fieldName;
        }

        return serviceCode;
      };

      $scope.getUserType = function(fieldName) {
        var delimiter = '_';
        var parts = fieldName.split(delimiter);
        var userType;

        if (parts.length > 1) {
          userType = parts.pop();
        } else {
          userType = 'user';
        }

        if (userType === 'id') {
          userType = 'user';
        }

        return userType;
      };
    },
    templateUrl: '/directives/followServicePicker/followServicePicker.html'
  };
});