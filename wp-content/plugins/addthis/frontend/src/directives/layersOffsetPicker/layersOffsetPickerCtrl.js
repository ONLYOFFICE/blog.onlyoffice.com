appAddThisWordPress.controller('layersOffsetPickerCtrl', function(
  $scope
){
    $scope.unitOptions = [
      { value: 'px'},
      { value: '%'}
    ];
    
    var positionsLimit = [];
    if (typeof $scope.positions !== 'undefined') {
      positionsLimit = $scope.positions.split(',');
    }
    
    $scope.defaultPositionOptions = [
      {
        value: 'right',
        display: 'layers_offset_location_right_label'
      },
      {
        value: 'left',
        display: 'layers_offset_location_left_label'
      },
      {
        value: 'top',
        display: 'layers_offset_location_top_label'
      },
      {
        value: 'bottom',
        display: 'layers_offset_location_bottom_label'
      }
    ];
    
    $scope.positionOptions = [];

  if (positionsLimit.length > 0) {
    $scope.defaultPositionOptions.forEach(function(positionOption) {
      if (positionsLimit.indexOf(positionOption.value) !== -1) {
        $scope.positionOptions.push(positionOption);
      }
    });
  } else {
    $scope.positionOptions = $scope.defaultPositionOptions;
  }
});
