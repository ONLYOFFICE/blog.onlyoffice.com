appAddThisWordPress.directive('toolGalleryCard', function($wordpress) {
  return {
    scope: {
      image: '@image',
      imageAltMsgid: '@imageAltMsgid',
      toolNameMsgid: '@toolNameMsgid',
      toolDescriptionMsgid: '@toolDescriptionMsgid',
      toolEnabled: '=toolEnabled',
      toolPco: '@toolPco',
      changeState: '=changeState',
      toggleEvent: '=toggleEvent'
    },
    transclude: true,
    link: function($scope) {
      $scope.templateBaseUrl = $wordpress.templateBaseUrl();
    },
    templateUrl: '/directives/toolGalleryCard/toolGalleryCard.html'
  };
});