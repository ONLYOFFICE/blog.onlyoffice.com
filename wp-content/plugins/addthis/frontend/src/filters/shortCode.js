appAddThisWordPress.filter('shortCode', function() {
  return function(toolSettings) {
    var shortCode = '';

    if (toolSettings.id === 'html') {
      shortCode = '';
      //if (toolSettings.widgetId) {
        //shortCode = '[addthis-custom id="' + toolSettings.widgetId + '"]';
      //}
    } else if (toolSettings.elements &&
      toolSettings.elements[0] &&
      typeof toolSettings.elements[0] === 'string'
    ) {
      shortCode = '[addthis tool="' +
        toolSettings.elements[0].substring(1) +
        '"]';
    }

    return shortCode;
  };
});