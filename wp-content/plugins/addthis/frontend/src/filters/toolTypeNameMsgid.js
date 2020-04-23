appAddThisWordPress.filter('toolTypeNameMsgid', function() {
  return function(pco) {
    var msgid;

    var follow = [
      'cflwh',
      'flwh',
      'flwv',
      'smlfw'
    ];

    var relatedposts = [
      'cod',
      'jrcf',
      'smlre',
      'smlrebh',
      'smlrebv',
      'smlwn',
      'tst',
      'wnm'
    ];

    var share = [
      'cmtb',
      'ctbx',
      'jsc',
      'msd',
      'newsletter',
      'resh',
      'scopl',
      'smlmo',
      'smlsh',
      'smlshp',
      'tbx',
      'shfs',
      'shin',
      'ist',
      'esb'
    ];

    if (follow.indexOf(pco) !== -1) {
      msgid = 'menu_item_follow_button_settings';
    } else if (relatedposts.indexOf(pco) !== -1) {
      msgid = 'menu_item_recommended_content_settings';
    } else if (share.indexOf(pco) !== -1) {
      msgid = 'menu_item_sharing_settings';
    } else if (pco === 'html') {
      msgid = 'share_tool_custom_html_label';
    } else {
      msgid = 'tool_listing_type_unknown';
    }

    return msgid;
  };
});