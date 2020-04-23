appAddThisWordPress.filter('urlEncodeObject', function() {
  return function(input) {
    var params = [];

    angular.forEach(input, function(value, key, input) {
      if (input.hasOwnProperty(key)) {
        var param = encodeURIComponent(key) + '=' + encodeURIComponent(value);
        params.push(param);
      }
    });
    var seperator = '&';
    var output = params.join(seperator);
    return output;
  };
});