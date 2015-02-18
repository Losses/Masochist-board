/**
 * Created by Don on 2/18/2015.
 */

var intervalEvent = setInterval(function () {
    if (losses.data.categories) {
        losses.scope.manage.categories = losses.data.categories;
        losses.scope.manage.$digest();

        setTimeout(function () {
            sSelect('.select_transform');
        }, 100);
        clearInterval(intervalEvent);
    }
}, 500);