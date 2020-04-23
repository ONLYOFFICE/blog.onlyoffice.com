appAddThisWordPress.directive('iconColorPicker', function() {
  return {
    scope: {
      ngModel: '=ngModel', // bi-directional
      toolPco: '@toolPco'
    },
    controller: function($scope) {

      $scope.selectFormFieldName = 'addthis-'+$scope.toolPco+'-'+'background';
      $scope.inputFormFieldName = $scope.selectFormFieldName+'-custom';
      $scope.selectValue = $scope.ngModel;
      $scope.customColor = false;
      var defaultCustomColor = '#000000';

      $scope.themeOptions = [
        {
          value: '#666666',
          display: 'icon_color_picker_select_gray'
        },
        {
          value: '#E74339',
          display: 'icon_color_picker_select_red'
        },
        {
          value: '#FF745C',
          display: 'icon_color_picker_select_orange'
        },
        {
          value: '#A8CE50',
          display: 'icon_color_picker_select_green'
        },
        {
          value: '#129BF4',
          display: 'icon_color_picker_select_blue'
        },
        {
          value: '#932C8B',
          display: 'icon_color_picker_select_purple'
        },
        {
          value: '#FF318E',
          display: 'icon_color_picker_select_pink'
        },
        {
          value: defaultCustomColor,
          display: 'icon_color_picker_select_custom'
        }
      ];

      var setSelectValue = function(ngModel) {
        var customColor = true;

        angular.forEach($scope.themeOptions, function(themeOption) {
          if (themeOption.value === ngModel &&
            themeOption.value !== defaultCustomColor
          ) {
            customColor = false;
            $scope.selectValue = ngModel;
          }
        });

        $scope.customColor = customColor;
        if (customColor === true) {
          $scope.selectValue = defaultCustomColor;
        }
      };

      setSelectValue($scope.ngModel);

      $scope.$watch('ngModel', function(newValue) {
        if (angular.isString(newValue)) {
          $scope.ngModel = '#' + newValue
          .replace(/[^0-9a-h]/gi,'')
          .substring(0,6);
        }
        setSelectValue(newValue);
      });

      $scope.selectTouched = function() {
        $scope.ngModel = $scope.selectValue;
      };

      $scope.selectedOption = function(currentThemeOptionValue) {
        var match = false;
        if (currentThemeOptionValue === $scope.ngModel) {
          match = true;
        }

        // if the current option is custom, and the current model value isn't
        // in the list
        if (currentThemeOptionValue === defaultCustomColor) {
          var aCustomColor = true;
          angular.forEach($scope.themeOptions, function(themeOption) {
            if (match === false && themeOption.value === $scope.ngModel) {
                aCustomColor = false;
            }
          });

          if (aCustomColor === true) {
            match = true;
          }
        }

        if (match === true) {
          return 'selected';
        } else {
          return false;
        }
      };

    },
    templateUrl: '/directives/iconColorPicker/iconColorPicker.html'
  };
});