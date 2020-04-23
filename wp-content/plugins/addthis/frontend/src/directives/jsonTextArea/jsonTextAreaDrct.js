appAddThisWordPress.directive('jsonTextArea', function() {
  return {
    scope: {
      ngModel: '=ngModel', // bi-directional
      form: '=formValidation',
      helpText: '=helpText',
      label: '@label',
      formFieldName: '@for'
    },
    templateUrl: '/directives/jsonTextArea/jsonTextArea.html'
  };
});