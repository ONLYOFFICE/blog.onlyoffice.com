'use strict';

appAddThisWordPress.factory('modeHelper', function(
    $darkseid,
    $wordpress
) {
  var modeHelper = {};

  modeHelper.getByPco = function(featureObject) {
    return $wordpress.globalOptions.get().then(function(globalOptions) {
      if (globalOptions.addthis_plugin_controls === 'AddThis') {
        return $darkseid.getToolSettings();
      } else {
        return featureObject.get();
      }
    });
  };

  modeHelper.getToolsByWidgetId = function(featureObject) {
    return $wordpress.globalOptions.get().then(function(globalOptions) {
      if (globalOptions.addthis_plugin_controls === 'AddThis') {
        return $darkseid.getToolsByWidgetId();
      } else {
        return featureObject.getToolsByWidgetId();
      }
    });
  };

  modeHelper.get = function(featureObject, america) {
    if (america) {
      return modeHelper.getToolsByWidgetId(featureObject)
      .then(function(toolList) {
        return $wordpress.globalOptions.get().then(function(globalOptions) {
          if (typeof globalOptions.html === 'object' &&
            !Array.isArray(globalOptions.html)
          ) {

            return angular.merge(angular.copy(toolList), globalOptions.html);
          } else {
            return toolList;
          }
        });
      });
    } else {
      return modeHelper.getByPco(featureObject);
    }
  };

  var cleanupElements = function(toolSettings) {
    var inlineToolElementBase = {
        'shin': '.addthis_inline_share_toolbox',
        'flwi': '.addthis_inline_follow_toolbow',
        'rpin': '.addthis_relatedposts_inline'
    };

    var allLocationElements = [
      '.at-above-post-homepage',
      '.at-below-post-homepage',
      '.at-above-post',
      '.at-below-post',
      '.at-above-post-page',
      '.at-below-post-page',
      '.at-above-post-cat-page',
      '.at-below-post-cat-page',
      '.at-above-post-arch-page',
      '.at-below-post-arch-page',
      '.at-below-post-recommended'
    ];

    // if we have a default tool element for this widget
    if (typeof inlineToolElementBase[toolSettings.id] !== 'undefined') {
      // construct a default element which will be first in the element array
      var elementBase = inlineToolElementBase[toolSettings.id];
      var firstElement = elementBase + '_' + toolSettings.widgetId;

      if (typeof toolSettings.elements === 'undefined' ||
        !Array.isArray(toolSettings.elements)
      ) {
        // if the elements propoerty isn't defined or isn't an array, make it an
        // array containing the default element
        toolSettings.elements = [firstElement];
      } else {
        var notLocationElements = jQuery(toolSettings.elements).not(allLocationElements).get();
        if (notLocationElements.length === 0) {
          // if the widget does not have any non location based elements, add
          // the default element to the start of the array
          toolSettings.elements.unshift(firstElement);
        } else {
          // reorder the elements so that the non-location elements are first
          var locationElements = jQuery(toolSettings.elements).not(notLocationElements).get();
          toolSettings.elements = notLocationElements.concat(locationElements);
        }
      }
    }
  };

  modeHelper.save = function(featureObject, toolPco, toolSettings, america) {
    if (typeof toolSettings.widgetId !== 'string' ||
      toolSettings.widgetId === 'new'
    ) {
      toolSettings.widgetId = $darkseid.generateNewWidgetId();
    }

    cleanupElements(toolSettings);

    return $wordpress.globalOptions.get().then(function(globalOptions) {
      if (toolSettings.id === 'html') {
        if (typeof globalOptions.html !== 'object' ||
          Array.isArray(globalOptions.html)
        ) {
          globalOptions.html = {};
        }

        globalOptions.html[toolSettings.widgetId] = toolSettings;

        return $wordpress.globalOptions.save(globalOptions);
      } else if (globalOptions.addthis_plugin_controls === 'AddThis') {
        return $darkseid.updateBoostConfigs(toolPco, toolSettings, america)
        .then(function() {
          return modeHelper.get(featureObject, america);
        });
      } else {
        return featureObject.save(toolPco, toolSettings);
      }
    });
  };

  return modeHelper;
});