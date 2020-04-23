appAddThisWordPress.filter('followServiceInfo', function() {
  return function() {
    var output = [
      {
        displayName: 'social_service_facebook',
        icon: 'facebook_follow',
        gfuName: 'facebook',
        userTypes: [
          {
            gfuUserType: 'username',
            settingsField: 'facebook',
            displayName: 'follow_profile_facebook'
          }
        ]
      },
      {
        displayName: 'social_service_twitter',
        icon: 'twitter_follow',
        gfuName: 'twitter',
        userTypes: [
          {
            gfuUserType: false,
            preInputUrl: 'https://twitter.com/',
            postInputUrl: '',
            settingsField: 'twitter',
            displayName: 'follow_profile_twitter'
          }
        ]
      },
      {
        displayName: 'social_service_linkedin',
        icon: 'linkedin_follow',
        gfuName: 'linkedin',
        userTypes: [
          {
            gfuUserType: false,
            settingsField: 'linkedin',
            displayName: 'follow_profile_linkedin'
          },
          {
            gfuUserType: 'company',
            settingsField: 'linkedin-company',
            displayName: 'follow_profile_linkedin_company'
          }
        ]
      },
      {
        displayName: 'social_service_google_plus',
        icon: 'google_follow',
        gfuName: 'google_follow',
        userTypes: [
          {
            gfuUserType: false,
            settingsField: 'google_follow',
            displayName: 'follow_profile_google_plus'
          }
        ]
      },
      {
        displayName: 'social_service_youtube',
        icon: 'youtube_follow',
        gfuName: 'youtube',
        userTypes: [
          {
            gfuUserType: false,
            preInputUrl: 'https://www.youtube.com/user/',
            postInputUrl: '',
            settingsField: 'youtube',
            displayName: 'follow_profile_youtube'
          },
          {
            gfuUserType: 'channel',
            preInputUrl: 'https://www.youtube.com/channel/',
            postInputUrl: '',
            settingsField: 'youtube-channel',
            displayName: 'follow_profile_youtube_channel'
          }
        ]
      },
      {
        displayName: 'social_service_flickr',
        icon: 'flickr_follow',
        gfuName: 'flickr',
        userTypes: [
          {
            gfuUserType: false,
            settingsField: 'flickr',
            displayName: 'follow_profile_flickr'
          }
        ]
      },
      {
        displayName: 'social_service_vimeo',
        icon: 'vimeo_follow',
        gfuName: 'vimeo',
        userTypes: [
          {
            gfuUserType: false,
            settingsField: 'vimeo',
            displayName: 'follow_profile_vimeo'
          }
        ]
      },
      {
        displayName: 'social_service_pinterest',
        icon: 'pinterest_follow',
        gfuName: 'pinterest',
        userTypes: [
          {
            gfuUserType: false,
            settingsField: 'pinterest',
            displayName: 'follow_profile_pinterest'
          }
        ]
      },
      {
        displayName: 'social_service_instagram',
        icon: 'instagram_follow',
        gfuName: 'instagram',
        userTypes: [
          {
            gfuUserType: false,
            settingsField: 'instagram',
            displayName: 'follow_profile_instagram'
          }
        ]
      },
      {
        displayName: 'social_service_foursquare',
        icon: 'foursquare_follow',
        gfuName: 'foursquare',
        userTypes: [
          {
            gfuUserType: false,
            settingsField: 'foursquare',
            displayName: 'follow_profile_foursquare'
          }
        ]
      },
      {
        displayName: 'social_service_behance',
        icon: 'behance_follow',
        gfuName: 'behance',
        userTypes: [
          {
            gfuUserType: false,
            settingsField: 'behance',
            displayName: 'follow_profile_behance'
          }
        ]
      },
      {
        displayName: 'social_service_etsy',
        icon: 'etsy_follow',
        gfuName: 'etsy',
        userTypes: [
          {
            gfuUserType: false,
            settingsField: 'etsy',
            displayName: 'follow_profile_etsy'
          }
        ]
      },
      {
        displayName: 'social_service_disqus',
        icon: 'disqus_follow',
        gfuName: 'disqus',
        userTypes: [
          {
            gfuUserType: false,
            settingsField: 'disqus',
            displayName: 'follow_profile_disqus'
          }
        ]
      },
      {
        displayName: 'social_service_tumblr',
        icon: 'tumblr_follow',
        gfuName: 'tumblr',
        userTypes: [
          {
            gfuUserType: false,
            settingsField: 'tumblr',
            displayName: 'follow_profile_tumblr'
          }
        ]
      },
      {
        displayName: 'social_service_rss',
        icon: 'rss_follow',
        gfuName: 'rss',
        userTypes: [
          {
            gfuUserType: false,
            settingsField: 'rss',
            displayName: 'social_service_rss'
          }
        ]
      }
    ];

    return output;
  };
});