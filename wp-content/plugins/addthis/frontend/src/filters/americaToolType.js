appAddThisWordPress.filter('americaToolType', function() {
  return function(input, type) {
    var output = {};
    var pcos = [];

    if (type === 'follow') {
      pcos = [
        'cflwh',
        'flwh',
        'flwv',
        'smlfw'
      ];
    } else if (type === 'relatedposts') {
      pcos = [
        'cod',
        'jrcf',
        'smlre',
        'smlrebh',
        'smlrebv',
        'smlwn',
        'tst',
        'wnm'
      ];
    } else { // share
      pcos = [
        'cmtb',
        'ctbx',
        'ist',
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
        'html'
      ];
    }

    angular.forEach(input, function(value, key) {
      if (key !== 'startUpgradeAt' && pcos.indexOf(value.id) > -1) {
        output[value.widgetId] = value;
      }
    });

    return output;
  };
});