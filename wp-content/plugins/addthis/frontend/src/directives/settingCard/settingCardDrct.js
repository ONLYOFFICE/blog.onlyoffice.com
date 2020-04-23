appAddThisWordPress.directive('settingCard', function() {
  return {
    scope: {
      name: '@for',
      expended: '=expended'
    },
    transclude: true,
    templateUrl:
      '/directives/settingCard/settingCard.html'
  };
});