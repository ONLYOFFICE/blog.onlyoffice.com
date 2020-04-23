appAddThisWordPress.directive('basicRadiobox', function() {
  return {
    scope: {
      ngModel: '=ngModel', // bi-directional
      value: '=value',
      label: '@label',
      name: '@for',
      checked: '@checked'
    },
    transclude: true,
    link: function(scope, el, attrs, ctrl, transclude) {
      var transcludeElements = el.find('.transclude-here-basic-radiobox');
      if (transcludeElements.length === 0) {
        // for older version of jQuery :-(
        transcludeElements = jQuery(el)
        .find('[class*="transclude-here-basic-radiobox"]');
      }
      transcludeElements.append(transclude());
    },
    templateUrl: '/directives/basicRadiobox/basicRadiobox.html'
  };
});