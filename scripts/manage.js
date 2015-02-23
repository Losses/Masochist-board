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

        function manageAction(event) {
            var action = $(event.target).attr('data-manage-action')
                , actionContent = {};

            actionContent.action = action;
            if ((losses.router.postId && (losses.multiSelect.length === 0))
                || ($.inArray(losses.router.postId, losses.multiSelect) !== -1)
                || (losses.router.postId && ($.inArray(action, ['sage', 'trans']) !== -1))) {
                actionContent.target = [losses.router.postId];
            } else
                actionContent.target = losses.multiSelect;

            console.log(actionContent.target);

            if (action === 'trans')
                actionContent.category = $('select[name="manage_transform"]').val();

            $.post('api/?manage', actionContent, function (data) {
                    var response;
                    try {
                        response = JSON.parse(data);
                    } catch (e) {
                        publicWarning(data);
                        return;
                    }

                    if (response.code == 200) {
                        if (action == 'delete') {
                            if (losses.router.postId
                                && $.inArray(losses.router.postId, losses.multiSelect) !== -1) {
                                publicWarning('操作成功');
                                magicalLocation('#/');
                            } else {
                                for (var i = 0; i < losses.multiSelect.length; i++) {
                                    $('#post-' + losses.multiSelect[i]).slideUp(300);
                                }
                            }
                        } else {
                            location.refresh(true);
                        }
                        losses.multiSelect = [];
                    } else {
                        publicWarning(response.message);
                    }
                }
            );

        }

        $('.confirm_delete').click(manageAction);
        $('.confirm_transport').click(manageAction);
        $('.confirm_sage').click(manageAction);

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
        });

        function manageCate() {
            var condition = {};

            condition.target = $(this).attr('data-category');
            condition.action = $(this).attr('data-manage-action');

            $.post('api/?manage', condition, function (data) {
                var response;
                try {
                    response = JSON.parse(data);
                } catch (e) {
                    publicWarning(data);
                }

                if (response.code == 200) {
                    var actionClass;
                    if (condition.action == 'mute_cate') {
                        actionClass = 'mute';
                    } else if (condition.action == 'hide_cate') {
                        actionClass = 'hide';
                    }
                    $(this).parents('.category_warp').toggleClass(actionClass);
                } else {
                    publicWarning(response.message);
                }
            });
        }

        $('.category').delegate('.edit_category', 'click', function () {
            var thisCate = $(this).attr('data-category')
                , thisParent = $('.cate-' + thisCate);
            thisParent.addClass('rename');

            $('.cancel-' + thisCate).one('click', function () {
                thisParent.removeClass('rename');
            })
        })
            .delegate('.mute_category', 'click', manageCate)
            .delegate('.hide_category', 'click', manageCate);

    }
);