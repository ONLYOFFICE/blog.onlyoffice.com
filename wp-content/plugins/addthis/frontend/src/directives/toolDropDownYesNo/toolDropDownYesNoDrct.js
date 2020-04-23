appAddThisWordPress.directive('toolDropDownYesNo', function() {
  return {
    scope: {
      ngModel: '=ngModel', // bi-directional
      toolPco: '@toolPco',
      fieldName: '@fieldName'
    },
    link: function(scope) {
      scope.options = [
        {
          msgid: 'layers_yes',
          value: true
        },
        {
          msgid: 'layers_no',
          value: false
        }
      ];
    },
    templateUrl: '/directives/toolDropDownYesNo/toolDropDownYesNo.html'
  };
});