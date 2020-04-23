appAddThisWordPress.directive('wordpressAlert', function() {
  return {
    scope: {
      close: '=close'
    },
    transclude: true,
    templateUrl: '/directives/wordpressAlert/wordpressAlert.html'
  };
});