appAddThisWordPress.directive('hideOnPages', function() {
  return {
    scope: {
      ngModel: '=ngModel' // bi-directional
    },
    templateUrl: '/directives/hideOnPages/hideOnPages.html'
  };
});
