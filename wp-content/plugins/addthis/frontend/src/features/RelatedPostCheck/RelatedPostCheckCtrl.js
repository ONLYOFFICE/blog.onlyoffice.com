appAddThisWordPress.controller('RelatedPostCheckCtrl', function(
  $scope,
  $wordpress
) {
  $scope.globalOptions = {};
  $wordpress.globalOptions.get().then(function(data) {
    $scope.globalOptions = data;
  });

  $scope.haveRelatedPosts = true;
  $wordpress.addThisRelatedPosts().then(function(data) {
    if (data.success === false) {
      $scope.addThisRelatedPosts = false;
    }
  });
});