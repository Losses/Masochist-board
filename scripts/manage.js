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

$(document).ready(function () {
    var newCategloryElement = $('.add_new')
        , newCategloryClasses = newCategloryElement.attr('class');

    $('.delete').click(function () {
        $('.delete_warp').addClass('need_confirm')
            .one('mouseleave', function () {
                $(this).removeClass('need_confirm');
            });
    });

    $('.transport_select_warp').delegate('li', 'click', function () {
        var warp = $('.transport_warp')
            , menu = $('#new_post');

        warp.addClass('need_confirm');

        menu.addClass('extend');

        $('.transport_confirm').click(function (event) {
            if ($(event.target).hasClass('confirm_transport')) {
                /*发送数据*/
            }

            warp.removeClass('need_confirm');
            menu.removeClass('extend');
        })
    });

    $('.color_picker').mouseover(function (event) {
        if ($(event.target).attr('type') === 'submit') {
            $('.add_new').attr('class', newCategloryClasses + ' ' + $(event.target).attr('class'));
        }
    })

});