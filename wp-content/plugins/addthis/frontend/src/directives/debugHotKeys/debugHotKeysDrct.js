appAddThisWordPress.directive('debugHotKeys', function(
  hotkeys,
  $translate,
  $wordpress
) {
  return {
    link: function() {
      var globalOptions = {};
      $wordpress.globalOptions.get().then(function(data) {
        globalOptions = data;
      });

      hotkeys.add({
        combo: 'shift+ctrl+d',
        callback: function() {
          globalOptions.debug_enable = !globalOptions.debug_enable;
        }
      });

      hotkeys.add({
        combo: 'shift+ctrl+t',
        callback: function() {
          var debugLanguageName = 'debug';
          if ($translate.use() === debugLanguageName) {
            $translate.use(window.addthis_ui.locale);
          } else {
            $translate.use(debugLanguageName);
          }
        }
      });
    }
  };
});