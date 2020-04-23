appAddThisWordPress.directive('settingFrame', function() {
  return {
    scope: {
      name: '@for',
      title: '=title',
      titlePlaceholderMsgid: '=titlePlaceholderMsgid'
    },
    transclude: true,
    templateUrl:
      '/directives/settingFrame/settingFrame.html'
  };
});
