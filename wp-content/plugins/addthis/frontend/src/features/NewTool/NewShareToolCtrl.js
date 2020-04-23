appAddThisWordPress.controller('NewShareToolCtrl', function(
  $scope,
  $wordpress
) {
  $scope.shareToolList = [
    {
      toolTypeMsgid: 'sidebar_tool',
      defaultSettings: {
        id: 'shfs',
        enabled: true,
        hideEmailSharingConfirmation: false,
        backgroundColor: '#FFFFFF',
        thankyou: true,
        postShareRecommendedMsg: 'Recommended for you',
        offset: {
          location: 'top',
          amount: 20,
          unit: '%'
        },
        counts: 'none',
        shareCountThreshold: 10,
        label: '',
        textColor: '#222222',
        __hideOnHomepage: false,
        desktopPosition: 'left',
        numPreferredServices: 5,
        auto_personalization: true,
        borderRadius: '0%',
        responsive: 979,
        postShareFollowMsg: 'Follow',
        iconColor: '#FFFFFF',
        style: 'modern',
        mobilePosition: 'bottom',
        postShareTitle: 'Thanks for sharing!',
        hideLabel: false,
        mobileButtonSize: 'large',
        theme: 'transparent',
        templates: [
          'home',
          'posts',
          'pages',
          'archives',
          'categories',
        ]
      },
      image: 'tool-thumb-sharing-sidebar.png'
    },
    {
      toolTypeMsgid: 'inline_tool',
      defaultSettings: {
        id:'shin',
        enabled: true,
        countsFontSize: '60px',
        hideEmailSharingConfirmation: false,
        counts: 'none',
        shareCountThreshold: 10,
        label: 'SHARES',
        __hideOnHomepage: false,
        numPreferredServices: 3,
        auto_personalization: true,
        size: '32px',
        titleFontSize: '18px',
        responsive: 979,
        iconColor: '#FFFFFF',
        counterColor: '#666666',
        hideDevice: 'none',
        style: 'responsive',
        elements: [
          '.at-above-post-homepage',
          '.at-below-post-homepage',
          '.at-above-post',
          '.at-below-post',
          '.at-above-post-page',
          '.at-below-post-page',
          '.at-above-post-cat-page',
          '.at-below-post-cat-page',
          '.at-above-post-arch-page',
          '.at-below-post-arch-page'
        ],
      },
      image: 'tool-thumb-sharing-inline.png'
    },
    {
      toolTypeMsgid: 'share_tool_custom_html_label',
      defaultSettings: {
        id: 'html',
        enabled: true,
        elements: [
          '.at-above-post-homepage',
          '.at-below-post-homepage',
          '.at-above-post',
          '.at-below-post',
          '.at-above-post-page',
          '.at-below-post-page',
          '.at-above-post-cat-page',
          '.at-below-post-cat-page',
          '.at-above-post-arch-page',
          '.at-below-post-arch-page'
        ],
        html: '',
      },
      image: 'tool-thumb-sharing-custom.png'
    }
  ];

  $scope.globalOptions = {};
  $wordpress.globalOptions.get().then(function(globalOptions) {
    $scope.globalOptions = globalOptions;
  });

  $scope.deprecatedMobileToolbarDefaults = {
    id: 'smlmo',
    enabled: true,
    buttonBarPosition: 'bottom',
    buttonBarTheme: 'light',
    follow: 'off',
    responsive: 979,
    templates: [
      'home',
      'posts',
      'pages',
      'archives',
      'categories',
    ]
  };
});