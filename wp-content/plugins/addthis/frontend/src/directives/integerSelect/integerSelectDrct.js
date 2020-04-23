appAddThisWordPress.directive('integerSelect', function() {
  return {
    scope: {
      ngModel: '=ngModel', // bi-directional
      min: '=min',
      max: '=max',
      label: '@label',
      name: '@for'
    },
    transclude: true,
    link: function(scope, el, attrs, ctrl, transclude) {
      var transcludeElements = el.find('.transclude-here-integer-select');
      if (transcludeElements.length === 0) {
        // for older version of jQuery :-(
        transcludeElements = jQuery(el)
        .find('[class*="transclude-here-integer-select"]');
      }
      transcludeElements.append(transclude());

      var makeNumberedOptions = function(start, stop) {
        var options = [];
        var current = 1;

        if (typeof start === 'number') {
          current = start;
        }

        if (typeof stop !== 'number') {
          stop = 10;
        }

        while (current <= stop) {
          var newOption = {value: current, display: current};
          options.push(newOption);
          current++;
        }

        return options;
      };

      scope.options = makeNumberedOptions(scope.min, scope.max);
    },
    templateUrl: '/directives/basicSelect/basicSelect.html'
  };
});