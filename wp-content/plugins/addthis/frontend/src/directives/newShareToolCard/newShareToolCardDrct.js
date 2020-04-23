appAddThisWordPress.directive('newShareToolCard', function($wordpress) {
    return {
        scope: {
            ngModel: '=ngModel', // bi-directional
            toolPco: '=toolPco'
        },

        controller: function($scope) {
            $scope.templateBaseUrl = $wordpress.templateBaseUrl();
        },

        templateUrl:
            '/directives/newShareToolCard/newShareToolCard.html'
    };
});
