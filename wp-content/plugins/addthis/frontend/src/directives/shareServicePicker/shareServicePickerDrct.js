appAddThisWordPress.directive('shareServicePicker', function(
  $wordpress,
  $timeout
) {
  return {
    scope: {
      thirdParty: '=thirdParty',
      services: '=services', // bi-directional
      thirdPartyServices: '=thirdPartyServices', // bi-directional
      numberOfServices: '=numberOfServices',
      autoPersonalization: '=autoPersonalization',
      min: '@min',
      max: '@max',
      widgetId: '@widgetId'
    },
    link: function($scope) {
      // set up functions
      var addIncrement;

      $scope.isAutoPersonalized = function() {
        return $scope.autoPersonalization;
      };

      $scope.serviceAdded = function(service) {
        if ((typeof service === 'object') &&
          (typeof service.code !== 'undefined') &&
          (typeof $scope.pickedServices === 'object') &&
          $scope.pickedServices.indexOf(service.code) === -1) {
          return false;
        }

        return true;
      };

      $scope.serviceSearch = function(service) {
        var searchString = $scope.searchString.toLowerCase();
        if (service.searchString.toLowerCase().search(searchString) > -1) {
          return true;
        } else {
          return false;
        }
      };

      $scope.addService = function(service) {
        $scope.pickedServices.push(service.code);
        service.rank = addIncrement;
        addIncrement++;
      };

      $scope.deleteService = function(service) {
        var index = $scope.pickedServices.indexOf(service.code);
        if (index > -1) {
          $scope.pickedServices.splice(index, 1);
        }

        service.rank = -1;
      };

      $scope.serviceOptions = [];
      var setServiceOptions = function(input) {
        var shareServices = angular.copy(input);
        shareServices.forEach(function(service, index) {
          service.rank = $scope.pickedServices.indexOf(service.code);
          service.index = index;
        });

        $scope.serviceOptions = shareServices;

        $timeout(function() {
          if (typeof window.addthis !== 'undefined') {
            window.addthis.toolbox('.share-service-picker');
          }
        });
      };

      $scope.searchString = '';

      var setUpPickedServicesObject = function() {
        var servicesPromise = setServiceDefaults();
        servicesPromise.then(setServiceOptions);

        addIncrement = $scope.pickedServices.length;

        if (typeof $scope.autoPersonalization === 'undefined') {
          if ($scope.thirdParty !== true && $scope.services.length === 0) {
            $scope.autoPersonalization = true;
          } else {
            $scope.autoPersonalization = false;
          }
        }
      };

      var setServiceDefaults = function() {
        var servicesPromise;

        if ($scope.thirdParty === true) {
          if (typeof $scope.thirdPartyServices === 'undefined') {
            $scope.thirdPartyServices = [
              'facebook_like',
              'tweet',
              'pinterest_pinit',
              'counter'
            ];
          }
          $scope.pickedServices = $scope.thirdPartyServices;
          servicesPromise = $wordpress.thirdPartyGetShareServices();
        } else {
          if (typeof $scope.services === 'undefined') {
            $scope.services = [
              'facebook',
              'twitter',
              'email',
              'pinterest_share',
              'addthis'
            ];
          }
          $scope.pickedServices = $scope.services;
          servicesPromise = $wordpress.addThisGetShareServices();
        }

        return servicesPromise;
      };

      // populates the list of services and which input array to put results
      // into based off of thirdParty boolean
      $scope.$watch('thirdParty', function() {
        setUpPickedServicesObject();
      });

      $scope.$watch('autoPersonalization', function() {
        var servicesPromise = setServiceDefaults();
        servicesPromise.then(setServiceOptions);
      });
    },
    templateUrl: '/directives/shareServicePicker/shareServicePicker.html'
  };
});