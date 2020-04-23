appAddThisWordPress.directive('pointyAlert', function() {
  return {
    scope: {
      icon: '=icon',
      title: '=title'
    },
    transclude: true,
    link: function(scope, el, attrs, ctrl, transclude) {
      var transcludeElements = el.find('.transclude-here-pointy-alert');
      if (transcludeElements.length === 0) {
        // for older version of jQuery :-(
        transcludeElements = jQuery(el)
        .find('[class*="transclude-here-pointy-alert"]');
      }
      transcludeElements.append(transclude());
    },
    templateUrl: '/directives/pointyAlert/pointyAlert.html'
  };
});