appAddThisWordPress.directive('positionPicker', function() {
    return {
        scope: {
            ngModel: '=ngModel', // bi-directional
            fieldName: '=fieldName',
            title: '=title',
            positionOptions: '=positionOptions'
        },

        templateUrl:
            '/directives/positionPicker/positionPicker.html'
    };
});