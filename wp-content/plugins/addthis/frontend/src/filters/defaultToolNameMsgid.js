appAddThisWordPress.filter('defaultToolNameMsgid', function() {
  return function(pco) {
    var msgid;

    switch (pco) {
      // follow
      case 'cflwh':
        msgid = 'tool_cflwh_name';
        break;
      case 'flwh':
        msgid = 'tool_flwh_name';
        break;
      case 'flwv':
        msgid = 'tool_flwv_name';
        break;
      case 'smlfw':
        msgid = 'tool_smlfw_name';
        break;
      // related posts
      case 'cod':
        msgid = 'tool_cod_name';
        break;
      case 'jrcf':
        msgid = 'tool_jrcf_name';
        break;
      case 'smlre':
        msgid = 'tool_smlre_name';
        break;
      case 'smlrebh':
        msgid = 'tool_smlrebh_name';
        break;
      case 'smlrebv':
        msgid = 'tool_smlrebv_name';
        break;
      case 'smlwn':
        msgid = 'tool_smlwn_name';
        break;
      case 'tst':
        msgid = 'tool_tst_name';
        break;
      case 'wnm':
        msgid = 'tool_wnm_name';
        break;
      // share
      case 'cmtb':
      case 'smlshp':
      case 'ctbx':
      case 'jsc':
      case 'resh':
      case 'shin':
        msgid = 'menu_item_sharing_settings';
        break;
      case 'esb':
        msgid = 'tool_esb_name';
        break;
      case 'ist':
        msgid = 'tool_ist_name';
        break;
      case 'msd':
        msgid = 'tool_msd_name';
        break;
      case 'scopl':
        msgid = 'tool_scopl_name';
        break;
      case 'smlmo':
        msgid = 'tool_smlmo_name';
        break;
      case 'smlsh':
      case 'shfs':
        msgid = 'tool_smlsh_name';
        break;
      case 'tbx':
        msgid = 'tool_tbx_name';
        break;
      case 'html':
        msgid = 'share_tool_custom_html_label';
        break;
      default:
        msgid = 'tool_listing_type_unknown';
        break;
    }

    return msgid;
  };
});