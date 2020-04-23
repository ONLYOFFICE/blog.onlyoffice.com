appAddThisWordPress.directive('editableTitle', function() {
  return {
    scope: {
      title: '=title',
      placeholderMsgid: '@placeholderMsgid'
    },
    templateUrl: '/directives/editableTitle/editableTitle.html'
  };
});