/**
 * Created by Don on 2/13/2015.
 */
$(document).ready(function () {
    $('#new_post').click(function () {
        $('#post_dialog').addClass('flow_up')
            .removeClass('flow_down')
            .on('click.activeForm', function (event) {
                if (!$(event.target).hasClass('submit'))
                    return false;
            });

        $('#new_post').addClass('hide')
            .removeClass('show');

        setTimeout(function () {
            $('body').on('click.activeBody', function (event) {
                if (!$(event.target).hasClass('submit')) {
                    $('#post_dialog').addClass('flow_down')
                        .removeClass('flow_up')
                        .off('click.activeForm');

                    $(this).off('click.activeBody');

                    $('#new_post').removeClass('hide')
                        .addClass('show');

                }
            });
        }, 100);

    });
});