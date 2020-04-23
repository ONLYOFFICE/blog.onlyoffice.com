appAddThisWordPress.controller('ToolSettingsCtrl', function(
  $scope,
  $wordpress,
  $stateParams,
  $state,
  $darkseid,
  $filter,
  modeHelper,
  globalOptions,
  toolSettings,
  $window
) {
  $scope.templateBaseUrl = $wordpress.templateBaseUrl();
  $scope.globalOptions = globalOptions;
  $scope.toolSettings = toolSettings;

  if ($stateParams.toolPco === 'new') {
    if (typeof $stateParams.settings === 'undefined' ||
      typeof $stateParams.settings.id === 'undefined'
    ) {
      $state.go('newShareTool');
    }

    $scope.toolSettings = angular.copy($stateParams.settings);
    $scope.toolSettings.widgetId = 'new';
    $scope.toolSettings.tmp = true;
  }

  $scope.shareButtons = {};
  $scope.generalCardExpended = true;
  $scope.positionCardExpended = false;
  $scope.designCardExpended = false;
  $scope.templateCardExpended = false;
  $scope.advancedCardExpended = false;
  $scope.permissions = $window.addthis_ui.permissions;

  $scope.titlePlaceholder =
    $filter('defaultToolNameMsgid')($scope.toolSettings.id);

  $scope.mobilePosition = {
    title: 'mobile_position_picker_label',
    fieldName: 'addthis-mobile-position-picker',
    positionOptions: [
      {
        value: 'top',
        display: 'layers_offset_location_top_label'
      },
      {
        value: 'bottom',
        display: 'layers_offset_location_bottom_label'
      },
      {
        value: 'hide',
        display: 'layers_offset_location_none_label'
      }
    ]
  };

  $scope.desktopPosition = {
    title: 'desktop_position_picker_label',
    fieldName: 'addthis-desktop-position-picker',
    positionOptions: [
      {
        value: 'left',
        display: 'layers_offset_location_left_label'
      },
      {
        value: 'right',
        display: 'layers_offset_location_right_label'
      },
      {
        value: 'hide',
        display: 'layers_offset_location_none_label'
      }
    ]
  };

  $scope.mobileButtonSize = {
    title: 'mobile_button_size_picker_title',
    fieldName: 'icon-size-picker',
    sizeOptions: [
      {
        value: 'large',
        display: 'icon_size_picker_select_large',
        info: 'icon_size_picker_select_large_description'
      },
      {
        value: 'medium',
        display: 'icon_size_picker_select_medium',
        info: 'icon_size_picker_select_medium_description'
      },
      {
        value: 'small',
        display: 'icon_size_picker_select_small',
        info: 'icon_size_picker_select_small_description'
      }
    ]
  };

  $scope.desktopButtonSize = {
    title: 'share_tool_buttons_label',
    fieldName: 'icon-size-picker',
    sizeOptions: [
      {
        value: '32px',
        display: 'icon_size_picker_select_large',
        info: 'icon_size_picker_select_large_description'
      },
      {
        value: '20px',
        display: 'icon_size_picker_select_medium',
        info: 'icon_size_picker_select_medium_description'
      },
      {
        value: '16px',
        display: 'icon_size_picker_select_small',
        info: 'icon_size_picker_select_small_description'
      }
    ]
  };

  $scope.buttonBarPosition = {
    title: 'layers_position_header',
    fieldName: 'addthis-position-picker',
    positionOptions: [
      {
        value: 'top',
        display: 'layers_offset_location_top_label'
      },
      {
        value: 'bottom',
        display: 'layers_offset_location_bottom_label'
      }
    ]
  };

  $scope.buttonBarTheme = {
    title: 'layers_theme_picker_title',
    fieldName: 'addthis-theme-picker',
    themeOptions: [
      {
        value: 'light',
        display: 'layers_theme_picker_select_light'
      },
      {
        value: 'dark',
        display: 'layers_theme_picker_select_dark'
      },
      {
        value: 'gray',
        display: 'layers_theme_picker_select_grey'
      }
    ]
  };

  $scope.buttonBarTemplate = {
    title: 'share_tool_wordpress_template_label',
    fieldName: 'addthis-template-picker',
    templateOptions: [
      {
        value: 'home',
        display: 'template_picker_select_homepage'
      },
      {
        value: 'posts',
        display: 'template_picker_select_posts'
      },
      {
        value: 'pages',
        display: 'template_picker_select_page'
      },
      {
        value: 'archives',
        display: 'template_picker_select_archives'
      },
      {
        value: 'categories',
        display: 'template_picker_select_categories'
      }

    ]
  };

  $scope.followOnOffSelect = {
    title: 'tool_settings_share_follow_toolbar_follow_label',
    fieldName: 'addthis-follow-on-off-select'
  };

  $scope.sidebarCounts = {
    title: 'share_tool_count_type_picker_title',
    fieldName: 'addthis-count-type-picker',
    countTypeOptions: [
      {
        value: 'each',
        display: 'share_tool_count_type_each'
      },
      {
        value: 'one',
        display: 'share_tool_count_type_one'
      },
      {
        value: 'both',
        display: 'share_tool_count_type_both'
      },
      {
        value: 'none',
        display: 'layers_offset_location_none_label'
      }
    ]
  };

  $scope.inlineCounts = {
    title: 'share_tool_count_type_picker_title',
    fieldName: 'addthis-count-type-picker',
    countTypeOptions: [
      {
        value: 'each',
        display: 'share_tool_count_type_each'
      },
      {
        value: 'one',
        display: 'share_tool_count_type_one'
      },
      {
        value: 'jumbo',
        display: 'share_tool_count_type_jumbo'
      },
      {
        value: 'none',
        display: 'layers_offset_location_none_label'
      }
    ]
  };

  $scope.inlineStyle = {
    title: 'share_tool_style_label',
    fieldName: 'addthis-style-picker',
    styleOptions: [
      {
        value: 'responsive',
        display: 'share_tool_modern_responsive_style'
      },
      {
        value: 'fixed',
        display: 'share_tool_modern_fixed_style'
      },
      {
        value: 'original',
        display: 'share_tool_modern_origin_style'
      }
    ]
  };

  $scope.sidebarStyle = {
    title: 'share_tool_style_label',
    fieldName: 'addthis-style-picker',
    styleOptions: [
      {
        value: 'modern',
        display: 'share_tool_modern_style'
      },
      {
        value: 'bordered',
        display: 'share_tool_modern_bordered_style'
      }
    ]
  };

  $scope.inlineLocations = [
    {
      title: 'tool_settings_share_locations_homepage_title',
      options: [
        {
          value: '.at-above-post-homepage',
          display: 'tool_settings_share_locations_above_excerpt_label'
        },
        {
          value: '.at-below-post-homepage',
          display: 'tool_settings_share_locations_below_excerpt_label'
        }
      ]
    },
    {
      title: 'tool_settings_share_locations_post_title',
      options: [
        {
          value: '.at-above-post',
          display: 'tool_settings_share_locations_above_blog_post_label'
        },
        {
          value: '.at-below-post',
          display: 'tool_settings_share_locations_below_blog_post_label'
        }
      ]
    },
    {
      title: 'tool_settings_share_locations_page_title',
      options: [
        {
          value: '.at-above-post-page',
          display: 'tool_settings_share_locations_above_page_label'
        },
        {
          value: '.at-below-post-page',
          display: 'tool_settings_share_locations_below_page_label'
        }
      ]
    },
    {
      title: 'tool_settings_share_locations_category_title',
      options: [
        {
          value: '.at-above-post-cat-page',
          display: 'tool_settings_share_locations_above_excerpt_label'
        },
        {
          value: '.at-below-post-cat-page',
          display: 'tool_settings_share_locations_below_excerpt_label'
        }
      ]
    },
    {
      title: 'tool_settings_share_locations_archive_title',
      options: [
        {
          value: '.at-above-post-arch-page',
          display: 'tool_settings_share_locations_above_excerpt_label'
        },
        {
          value: '.at-below-post-arch-page',
          display: 'tool_settings_share_locations_below_excerpt_label'
        }
      ]
    }
  ];

  $scope.customLocations = angular.copy($scope.inlineLocations);
  $scope.sidebarTheme = angular.copy($scope.buttonBarTheme);
  $scope.sidebarTheme.themeOptions.push({
    value: 'transparent',
    display: 'layers_theme_picker_select_transparent'
  });

  $scope.saving = false;
  $scope.save = function() {
    // toolPco is really the widgetId
    $scope.saving = true;

    return modeHelper.save(
      $wordpress.sharingButtons,
      $stateParams.toolPco,
      $scope.toolSettings,
      true
    ).then(function(result) {
      var toolList = $filter('americaToolType')(result, 'share');
      $scope.toolSettings = toolList[$stateParams.toolPco];
      $scope.saving = false;
      $state.go('tools');
    });
  };
});