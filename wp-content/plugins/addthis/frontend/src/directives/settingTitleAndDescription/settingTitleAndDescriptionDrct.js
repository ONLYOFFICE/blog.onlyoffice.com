appAddThisWordPress.directive('settingTitleAndDescription', function() {
  return {
    scope: {
      label: '@label',
      name: '@for'
    },
    transclude: true,
    link: function(scope, el, attrs, ctrl, transclude) {
      var transcludeElements = el.find('.transclude-here-settings-description');
      if (transcludeElements.length === 0) {
        // for older version of jQuery :-(
        transcludeElements = jQuery(el)
        .find('[class*="transclude-here-settings-description"]');
      }
      transcludeElements.append(transclude());
    },
    templateUrl:
    '/directives/settingTitleAndDescription/settingTitleAndDescription.html'
  };
});