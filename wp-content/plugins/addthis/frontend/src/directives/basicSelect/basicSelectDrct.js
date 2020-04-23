appAddThisWordPress.directive('basicSelect', function() {
  return {
    scope: {
      ngModel: '=ngModel', // bi-directional
      options: '=options',
      label: '@label',
      name: '@for'
    },
    transclude: true,
    link: function(scope, el, attrs, ctrl, transclude) {
      var transcludeElements = el.find('.transclude-here-basic-select');
      if (transcludeElements.length === 0) {
        // for older version of jQuery :-(
        transcludeElements = jQuery(el)
        .find('[class*="transclude-here-basic-select"]');
      }
      transcludeElements.append(transclude());
    },
    templateUrl: '/directives/basicSelect/basicSelect.html'
  };
});